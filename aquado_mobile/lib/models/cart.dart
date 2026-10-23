import 'product.dart';

class Cart {
  final int id;
  final int userId;
  final List<CartItem> items;
  final double total;

  Cart({
    required this.id,
    required this.userId,
    required this.items,
    required this.total,
  });

  factory Cart.fromJson(Map<String, dynamic> json) {
    return Cart(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      items: json['items'] != null
          ? (json['items'] as List)
              .map((item) => CartItem.fromJson(item))
              .toList()
          : [],
      total: double.parse(json['total']?.toString() ?? '0'),
    );
  }

  int get itemCount => items.fold(0, (sum, item) => sum + item.quantity);
}

class CartItem {
  final int id;
  final int cartId;
  final int productId;
  final int quantity;
  final double price;
  final String? color;
  final String? size;
  final Product? product;

  CartItem({
    required this.id,
    required this.cartId,
    required this.productId,
    required this.quantity,
    required this.price,
    this.color,
    this.size,
    this.product,
  });

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      id: json['id'] as int,
      cartId: json['cart_id'] as int,
      productId: json['product_id'] as int,
      quantity: json['quantity'] as int,
      price: double.parse(json['price'].toString()),
      color: json['color'] as String?,
      size: json['size'] as String?,
      product:
          json['product'] != null ? Product.fromJson(json['product']) : null,
    );
  }

  double get subtotal => price * quantity;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'cart_id': cartId,
      'product_id': productId,
      'quantity': quantity,
      'price': price,
      'color': color,
      'size': size,
      'product': product?.toJson(),
    };
  }
}
