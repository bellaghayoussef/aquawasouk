import 'package:flutter/material.dart';
import '../../models/order.dart';
import '../../services/vendor_service.dart';
import '../../config/theme.dart';

class VendorOrdersScreen extends StatefulWidget {
  const VendorOrdersScreen({super.key});

  @override
  State<VendorOrdersScreen> createState() => _VendorOrdersScreenState();
}

class _VendorOrdersScreenState extends State<VendorOrdersScreen> {
  bool _isLoading = true;
  List<OrderItem> _orders = [];
  String _selectedFilter = 'all';

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);

    try {
      final status = _selectedFilter == 'all' ? null : _selectedFilter;
      final orders = await VendorService.getVendorOrders(status: status);

      setState(() {
        _orders = orders;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  Future<void> _updateOrderStatus(OrderItem order) async {
    final statuses = ['pending', 'processing', 'shipped', 'delivered'];

    final selectedStatus = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Update Order Status'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: statuses.map((status) {
            return RadioListTile<String>(
              title: Text(_getStatusDisplay(status)),
              value: status,
              groupValue: order.status,
              onChanged: (value) {
                Navigator.pop(context, value);
              },
            );
          }).toList(),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
        ],
      ),
    );

    if (selectedStatus != null && selectedStatus != order.status) {
      try {
        await VendorService.updateOrderStatus(
          orderItemId: order.id,
          status: selectedStatus,
        );

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Order status updated'),
              backgroundColor: AppTheme.successColor,
            ),
          );
          _loadOrders();
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Error: $e')),
          );
        }
      }
    }
  }

  String _getStatusDisplay(String status) {
    switch (status) {
      case 'pending':
        return 'Pending';
      case 'processing':
        return 'Processing';
      case 'shipped':
        return 'Shipped';
      case 'delivered':
        return 'Delivered';
      case 'cancelled':
        return 'Cancelled';
      default:
        return status;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return AppTheme.warningColor;
      case 'processing':
        return AppTheme.primaryColor;
      case 'shipped':
        return AppTheme.accentColor;
      case 'delivered':
        return AppTheme.successColor;
      case 'cancelled':
        return AppTheme.errorColor;
      default:
        return Colors.grey;
    }
  }

  Map<int, List<OrderItem>> _getGroupedOrders() {
    final Map<int, List<OrderItem>> groups = {};
    for (var order in _orders) {
      if (!groups.containsKey(order.orderId)) {
        groups[order.orderId] = [];
      }
      groups[order.orderId]!.add(order);
    }
    return groups;
  }

  @override
  Widget build(BuildContext context) {
    final groupedOrders = _getGroupedOrders();
    final sortedOrderIds = groupedOrders.keys.toList()
      ..sort((a, b) => b.compareTo(a)); // Descending order ID (roughly newest)

    return Scaffold(
      appBar: AppBar(
        title: const Text('Manage Orders'),
      ),
      body: Column(
        children: [
          // Filter Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                _buildFilterChip('All', 'all'),
                const SizedBox(width: 8),
                _buildFilterChip('Pending', 'pending'),
                const SizedBox(width: 8),
                _buildFilterChip('Processing', 'processing'),
                const SizedBox(width: 8),
                _buildFilterChip('Shipped', 'shipped'),
                const SizedBox(width: 8),
                _buildFilterChip('Delivered', 'delivered'),
              ],
            ),
          ),

          // Orders List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _orders.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              Icons.shopping_bag_outlined,
                              size: 100,
                              color: Colors.grey[400],
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'No orders yet',
                              style: Theme.of(context).textTheme.headlineMedium,
                            ),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadOrders,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: sortedOrderIds.length,
                          itemBuilder: (context, index) {
                            final orderId = sortedOrderIds[index];
                            final items = groupedOrders[orderId]!;
                            return _buildOrderGroupCard(orderId, items);
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String label, String value) {
    final isSelected = _selectedFilter == value;
    return FilterChip(
      label: Text(label),
      selected: isSelected,
      onSelected: (selected) {
        setState(() {
          _selectedFilter = value;
        });
        _loadOrders();
      },
      selectedColor: AppTheme.primaryColor,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : AppTheme.textPrimaryColor,
      ),
    );
  }

  Widget _buildOrderGroupCard(int orderId, List<OrderItem> items) {
    final firstItem = items.first;
    final order = firstItem.order;

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      elevation: 3,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header: Order ID and Customer Details
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Order #$orderId',
                  style: Theme.of(context)
                      .textTheme
                      .titleLarge
                      ?.copyWith(fontWeight: FontWeight.bold),
                ),
                if (order != null)
                  Text(
                    '${order.createdAt.year}-${order.createdAt.month}-${order.createdAt.day}',
                    style: const TextStyle(color: Colors.grey),
                  ),
              ],
            ),
            const Divider(height: 24),

            if (order != null) ...[
              const Text('Customer Details',
                  style: TextStyle(
                      fontWeight: FontWeight.bold, color: Colors.grey)),
              const SizedBox(height: 4),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.person, size: 16, color: Colors.grey),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      '${order.customerName ?? "Unknown"}',
                      style: const TextStyle(fontWeight: FontWeight.w500),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(Icons.location_on, size: 16, color: Colors.grey),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      '${order.shippingAddress}, ${order.shippingCity}, ${order.shippingCountry}\nPhone: ${order.phone}',
                      style: const TextStyle(fontSize: 13),
                    ),
                  ),
                ],
              ),
              const Divider(height: 24),
            ],

            // Items List
            ...items.map((item) => _buildItemRow(item)).toList(),
          ],
        ),
      ),
    );
  }

  Widget _buildItemRow(OrderItem item) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: Colors.grey[200],
              borderRadius: BorderRadius.circular(8),
            ),
            child: item.product?.image != null
                ? Image.network(
                    item.product!.image!,
                    fit: BoxFit.cover,
                    errorBuilder: (ctx, error, stackTrace) =>
                        const Icon(Icons.image_not_supported),
                  )
                : const Icon(Icons.image, color: Colors.grey),
          ),
          const SizedBox(width: 12),
          // Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.product?.name ?? 'Unknown Product',
                  style: const TextStyle(
                      fontWeight: FontWeight.bold, fontSize: 16),
                ),
                Text(
                    'Qty: ${item.quantity} x ${item.price.toStringAsFixed(2)} TND'),
                if (item.color != null || item.size != null)
                  Text('Size: ${item.size ?? '-'}, Color: ${item.color ?? '-'}',
                      style: TextStyle(color: Colors.grey[600], fontSize: 12)),
              ],
            ),
          ),
          // Status & Action
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: _getStatusColor(item.status).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  _getStatusDisplay(item.status),
                  style: TextStyle(
                    color: _getStatusColor(item.status),
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              if (item.status != 'delivered' && item.status != 'cancelled')
                TextButton(
                  onPressed: () => _updateOrderStatus(item),
                  style: TextButton.styleFrom(
                    padding: EdgeInsets.zero,
                    minimumSize: const Size(50, 30),
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  child: const Text('Update'),
                )
            ],
          )
        ],
      ),
    );
  }
}
