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
      id: json['id'] as int,
      userId: json['user_id'] as int,
      orderNumber: json['order_number'] as String,
      totalAmount: double.parse(json['total_amount'].toString()),
      status: json['status'] as String,
      paymentStatus: json['payment_status'] as String,
      paymentMethod: json['payment_method'] as String,
      shippingAddress: json['shipping_address'] as String,
      shippingCity: json['shipping_city'] as String,
      shippingPostalCode: json['shipping_postal_code'] as String,
      shippingCountry: json['shipping_country'] as String,
      phone: json['phone'] as String,
      notes: json['notes'] as String?,
      createdAt: DateTime.parse(json['created_at']),
      items: json['items'] != null
          ? (json['items'] as List)
              .map((item) => OrderItem.fromJson(item))
              .toList()
          : null,
      customerName: json['user'] != null ? json['user']['name'] : null,
      customerEmail: json['user'] != null ? json['user']['email'] : null,
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
      id: json['id'] as int,
      orderId: json['order_id'] as int,
      productId: json['product_id'] as int,
      vendorId: json['vendor_id'] as int,
      quantity: json['quantity'] as int,
      price: double.parse(json['price'].toString()),
      color: json['color'] as String?,
      size: json['size'] as String?,
      status: json['status'] as String,
      product:
          json['product'] != null ? Product.fromJson(json['product']) : null,
      vendor: json['vendor'] != null ? Vendor.fromJson(json['vendor']) : null,
      order: json['order'] != null ? Order.fromJson(json['order']) : null,
    );
  }

  double get subtotal => price * quantity;
}
