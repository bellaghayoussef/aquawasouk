import 'product.dart';

class Order {
  final int id;
  final int userId;
  final String orderNumber;
  final double totalAmount;
  final String status;
  final String paymentStatus;
  final String paymentMethod;
  final String shippingAddress;
  final String shippingCity;
  final String shippingPostalCode;
  final String shippingCountry;
  final String phone;
  final String? notes;
  final DateTime createdAt;
  final List<OrderItem>? items;
  final String? customerName;
  final String? customerEmail;

  Order({
    required this.id,
    required this.userId,
    required this.orderNumber,
    required this.totalAmount,
    required this.status,
    required this.paymentStatus,
    required this.paymentMethod,
    required this.shippingAddress,
    required this.shippingCity,
    required this.shippingPostalCode,
    required this.shippingCountry,
    required this.phone,
    this.notes,
    required this.createdAt,
    this.items,
    this.customerName,
    this.customerEmail,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      userId: int.tryParse(json['user_id']?.toString() ?? '0') ?? 0,
      orderNumber: json['order_number']?.toString() ?? '#${json['id']}',
      totalAmount:
          double.tryParse(json['total_amount']?.toString() ?? '0') ?? 0.0,
      status: json['status']?.toString() ?? 'pending',
      paymentStatus: json['payment_status']?.toString() ?? 'pending',
      paymentMethod: json['payment_method']?.toString() ?? 'cod',
      shippingAddress: json['shipping_address']?.toString() ?? '',
      shippingCity: json['shipping_city']?.toString() ?? '',
      shippingPostalCode: json['shipping_postal_code']?.toString() ?? '',
      shippingCountry: json['shipping_country']?.toString() ?? 'Tunisie',
      phone: json['phone']?.toString() ?? '',
      notes: json['notes']?.toString(),
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString()) ?? DateTime.now()
          : DateTime.now(),
      items: json['items'] != null
          ? (json['items'] as List)
              .map((item) => OrderItem.fromJson(item as Map<String, dynamic>))
              .toList()
          : null,
      customerName: json['customer_name'] ??
          (json['user'] != null ? json['user']['name'] : null),
      customerEmail: json['customer_email'] ??
          (json['user'] != null ? json['user']['email'] : null),
    );
  }

  String get statusDisplay {
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

  bool get canCancel => status == 'pending' || status == 'processing';
}

class OrderItem {
  final int id;
  final int orderId;
  final int productId;
  final int vendorId;
  final int quantity;
  final double price;
  final String? color;
  final String? size;
  final String status;
  final Product? product;
  final Vendor? vendor;
  final Order? order;

  OrderItem({
    required this.id,
    required this.orderId,
    required this.productId,
    required this.vendorId,
    required this.quantity,
    required this.price,
    this.color,
    this.size,
    required this.status,
    this.product,
    this.vendor,
    this.order,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      orderId: int.tryParse(json['order_id']?.toString() ?? '0') ?? 0,
      productId: int.tryParse(json['product_id']?.toString() ?? '0') ?? 0,
      vendorId: int.tryParse(json['vendor_id']?.toString() ?? '0') ?? 0,
      quantity: int.tryParse(json['quantity']?.toString() ?? '1') ?? 1,
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      color: json['color']?.toString(),
      size: json['size']?.toString(),
      status: json['status']?.toString() ?? 'pending',
      product:
          json['product'] != null ? Product.fromJson(json['product']) : null,
      vendor: json['vendor'] != null ? Vendor.fromJson(json['vendor']) : null,
      order: json['order'] != null ? Order.fromJson(json['order']) : null,
    );
  }

  double get subtotal => price * quantity;
}
