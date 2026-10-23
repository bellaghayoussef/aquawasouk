import 'package:flutter/material.dart';
import '../../models/order.dart';
import '../../services/product_service.dart';
import '../../config/theme.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  bool _isLoading = true;
  List<Order> _orders = [];

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    setState(() => _isLoading = true);
    try {
      final orders = await OrderService.getOrders();
      if (mounted) {
        setState(() {
          _orders = orders;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Impossible de charger vos commandes: $e'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    }
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
      case 'en_attente':
        return const Color(0xFFF59E0B);
      case 'processing':
      case 'en_traitement':
        return const Color(0xFF0284C7);
      case 'shipped':
      case 'expédiée':
      case 'expediee':
        return const Color(0xFF6366F1);
      case 'delivered':
      case 'livrée':
      case 'livree':
        return const Color(0xFF10B981);
      case 'cancelled':
      case 'annulée':
      case 'annulee':
        return const Color(0xFFEF4444);
      default:
        return const Color(0xFF64748B);
    }
  }

  String _formatDate(DateTime date) {
    final day = date.day.toString().padLeft(2, '0');
    final month = date.month.toString().padLeft(2, '0');
    final year = date.year;
    final hour = date.hour.toString().padLeft(2, '0');
    final minute = date.minute.toString().padLeft(2, '0');
    return '$day/$month/$year $hour:$minute';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Mes Commandes',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w800,
            letterSpacing: -0.5,
          ),
        ),
        elevation: 0,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(
                strokeWidth: 3,
                color: AppTheme.primaryColor,
              ),
            )
          : _orders.isEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(32.0),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.all(24),
                          decoration: BoxDecoration(
                            color: AppTheme.primaryLight.withOpacity(0.12),
                            shape: BoxShape.circle,
                          ),
                          child: const Icon(
                            Icons.receipt_long_rounded,
                            size: 56,
                            color: AppTheme.primaryColor,
                          ),
                        ),
                        const SizedBox(height: 20),
                        const Text(
                          'Aucune commande trouvée',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            color: AppTheme.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Vos commandes passées apparaîtront ici avec leur statut en direct.',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            fontSize: 14,
                            color: AppTheme.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 24),
                        ElevatedButton.icon(
                          onPressed: _loadOrders,
                          icon: const Icon(Icons.refresh, size: 18),
                          label: const Text('Actualiser'),
                        ),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  color: AppTheme.primaryColor,
                  onRefresh: _loadOrders,
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
                    itemCount: _orders.length,
                    itemBuilder: (context, index) {
                      final order = _orders[index];
                      final statusColor = _getStatusColor(order.status);

                      return Container(
                        margin: const EdgeInsets.only(bottom: 14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.04),
                              blurRadius: 8,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Theme(
                          data: Theme.of(context).copyWith(
                            dividerColor: Colors.transparent,
                          ),
                          child: ExpansionTile(
                            tilePadding: const EdgeInsets.all(16),
                            childrenPadding:
                                const EdgeInsets.fromLTRB(16, 0, 16, 16),
                            title: Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    'Commande #${order.orderNumber}',
                                    style: const TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w800,
                                      color: AppTheme.textPrimary,
                                    ),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 9,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: statusColor.withOpacity(0.12),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    order.statusDisplay,
                                    style: TextStyle(
                                      color: statusColor,
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                            subtitle: Padding(
                              padding: const EdgeInsets.only(top: 8.0),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    '${order.totalAmount.toStringAsFixed(2)} DT',
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w800,
                                      color: AppTheme.primaryColor,
                                    ),
                                  ),
                                  Text(
                                    _formatDate(order.createdAt),
                                    style: const TextStyle(
                                      fontSize: 12,
                                      color: AppTheme.textSecondary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            children: [
                              const Divider(
                                  height: 1, color: Color(0xFFF1F5F9)),
                              const SizedBox(height: 14),

                              // Address info
                              _buildInfoRow(
                                icon: Icons.location_on_outlined,
                                label: 'Adresse de livraison',
                                value:
                                    '${order.shippingAddress}, ${order.shippingCity}, ${order.shippingCountry} ${order.shippingPostalCode}',
                              ),
                              const SizedBox(height: 10),

                              // Phone info
                              _buildInfoRow(
                                icon: Icons.phone_outlined,
                                label: 'Téléphone',
                                value: order.phone,
                              ),
                              const SizedBox(height: 10),

                              // Payment info
                              _buildInfoRow(
                                icon: Icons.payments_outlined,
                                label: 'Mode de paiement',
                                value: order.paymentMethod.toUpperCase(),
                              ),

                              // Cancel Button if eligible
                              if (order.canCancel) ...[
                                const SizedBox(height: 16),
                                SizedBox(
                                  width: double.infinity,
                                  child: OutlinedButton.icon(
                                    onPressed: () async {
                                      final scaffoldMessenger =
                                          ScaffoldMessenger.of(context);
                                      final confirmed = await showDialog<bool>(
                                        context: context,
                                        builder: (dialogCtx) => AlertDialog(
                                          shape: RoundedRectangleBorder(
                                            borderRadius:
                                                BorderRadius.circular(16),
                                          ),
                                          title: const Text(
                                            'Annuler la commande',
                                            style: TextStyle(
                                              fontWeight: FontWeight.w700,
                                            ),
                                          ),
                                          content: const Text(
                                            'Êtes-vous sûr de vouloir annuler cette commande ?',
                                          ),
                                          actions: [
                                            TextButton(
                                              onPressed: () => Navigator.pop(
                                                  dialogCtx, false),
                                              child: const Text('Non'),
                                            ),
                                            ElevatedButton(
                                              onPressed: () => Navigator.pop(
                                                  dialogCtx, true),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor:
                                                    AppTheme.errorColor,
                                                foregroundColor: Colors.white,
                                              ),
                                              child: const Text('Oui, Annuler'),
                                            ),
                                          ],
                                        ),
                                      );

                                      if (confirmed == true) {
                                        try {
                                          await OrderService.cancelOrder(
                                              order.id);
                                          if (mounted) {
                                            scaffoldMessenger.showSnackBar(
                                              const SnackBar(
                                                content: Text(
                                                    'Commande annulée avec succès'),
                                                backgroundColor:
                                                    AppTheme.successColor,
                                              ),
                                            );
                                            _loadOrders();
                                          }
                                        } catch (e) {
                                          if (mounted) {
                                            scaffoldMessenger.showSnackBar(
                                              SnackBar(
                                                content: Text('Erreur: $e'),
                                                backgroundColor:
                                                    AppTheme.errorColor,
                                              ),
                                            );
                                          }
                                        }
                                      }
                                    },
                                    icon: const Icon(
                                      Icons.cancel_outlined,
                                      size: 16,
                                      color: AppTheme.errorColor,
                                    ),
                                    label: const Text(
                                      'Annuler la commande',
                                      style: TextStyle(
                                        color: AppTheme.errorColor,
                                        fontWeight: FontWeight.w700,
                                      ),
                                    ),
                                    style: OutlinedButton.styleFrom(
                                      side: const BorderSide(
                                        color: AppTheme.errorColor,
                                      ),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: const Color(0xFF64748B)),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textSecondary,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: AppTheme.textPrimary,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
