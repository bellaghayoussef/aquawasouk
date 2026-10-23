import 'package:flutter_test/flutter_test.dart';
import 'package:aquawasouk/models/product.dart';

void main() {
  group('Product Model Tests', () {
    test('Product.fromJson parses valid JSON correctly', () {
      final json = {
        'id': 101,
        'name': 'Filtre Aquarium Pro',
        'slug': 'filtre-aquarium-pro',
        'description': 'Un filtre externe ultra silencieux',
        'price': '150.00',
        'discount_price': '120.00',
        'stock': 15,
        'is_featured': true,
        'is_active': true,
        'category_id': 3,
        'vendor_id': 1,
        'primary_image': {
          'image_path': '/products/filtre.jpg',
        },
        'specifications': '{"puissance": "15W", "debit": "1000L/h"}',
        'average_rating': '4.5',
        'reviews_count': 12,
      };

      final product = Product.fromJson(json);

      expect(product.id, equals(101));
      expect(product.name, equals('Filtre Aquarium Pro'));
      expect(product.price, equals(150.0));
      expect(product.discountPrice, equals(120.0));
      expect(product.hasDiscount, isTrue);
      expect(product.finalPrice, equals(120.0));
      expect(product.discountPercentage, equals(20));
      expect(product.inStock, isTrue);
      expect(product.specifications, isNotNull);
      expect(product.specifications!['puissance'], equals('15W'));
      expect(product.averageRating, equals(4.5));
      expect(product.reviewsCount, equals(12));
    });

    test('Product calculates no discount when discountPrice is null', () {
      final json = {
        'id': 102,
        'name': 'Pompe à air basique',
        'slug': 'pompe-air-basique',
        'description': 'Pompe standard',
        'price': 45.0,
        'discount_price': null,
        'stock': 0,
        'is_featured': false,
        'is_active': true,
        'category_id': 2,
        'vendor_id': 1,
      };

      final product = Product.fromJson(json);

      expect(product.hasDiscount, isFalse);
      expect(product.finalPrice, equals(45.0));
      expect(product.discountPercentage, equals(0));
      expect(product.inStock, isFalse);
    });

    test(
        'Product.toJson produces valid map matching serialization expectations',
        () {
      final product = Product(
        id: 5,
        name: 'Plante Aquatique Anubias',
        slug: 'plante-aquatique-anubias',
        description: 'Plante naturelle vivante',
        price: 25.0,
        discountPrice: 20.0,
        stock: 50,
        image: 'https://aquawasouk.com/img.jpg',
        isFeatured: true,
        isActive: true,
        categoryId: 4,
        vendorId: 2,
        specifications: {'difficulte': 'facile'},
      );

      final json = product.toJson();

      expect(json['id'], equals(5));
      expect(json['name'], equals('Plante Aquatique Anubias'));
      expect(json['price'], equals(25.0));
      expect(json['discount_price'], equals(20.0));
      expect(json['stock'], equals(50));
      expect(json['specifications']['difficulte'], equals('facile'));
    });
  });
}
