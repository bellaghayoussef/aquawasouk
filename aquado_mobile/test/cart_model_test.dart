import 'package:flutter_test/flutter_test.dart';
import 'package:aquawasouk/models/cart.dart';

void main() {
  group('Cart & CartItem Model Tests', () {
    test('CartItem correctly computes subtotal and parses JSON', () {
      final json = {
        'id': 10,
        'cart_id': 1,
        'product_id': 45,
        'quantity': 3,
        'price': '29.50',
        'color': 'Bleu',
        'size': 'M',
      };

      final item = CartItem.fromJson(json);

      expect(item.id, equals(10));
      expect(item.cartId, equals(1));
      expect(item.productId, equals(45));
      expect(item.quantity, equals(3));
      expect(item.price, equals(29.50));
      expect(item.color, equals('Bleu'));
      expect(item.size, equals('M'));
      expect(item.subtotal, equals(88.50));
    });

    test('Cart correctly computes itemCount across all items', () {
      final json = {
        'id': 1,
        'user_id': 9,
        'total': '145.00',
        'items': [
          {
            'id': 1,
            'cart_id': 1,
            'product_id': 10,
            'quantity': 2,
            'price': '35.00',
          },
          {
            'id': 2,
            'cart_id': 1,
            'product_id': 11,
            'quantity': 3,
            'price': '25.00',
          },
        ],
      };

      final cart = Cart.fromJson(json);

      expect(cart.id, equals(1));
      expect(cart.userId, equals(9));
      expect(cart.total, equals(145.00));
      expect(cart.items.length, equals(2));
      expect(cart.itemCount, equals(5));
    });
  });
}
