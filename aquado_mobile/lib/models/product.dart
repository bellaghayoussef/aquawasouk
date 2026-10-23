import 'dart:convert';
import '../config/api_config.dart';

class Product {
  final int id;
  final String name;
  final String slug;
  final String description;
  final double price;
  final double? discountPrice;
  final int stock;
  final String? image;
  final bool isFeatured;
  final bool isActive;
  final int categoryId;
  final int vendorId;
  final Map<String, dynamic>? specifications;
  final List<ProductImage>? images;
  final Category? category;
  final Vendor? vendor;
  final double? averageRating;
  final int? reviewsCount;

  Product({
    required this.id,
    required this.name,
    required this.slug,
    required this.description,
    required this.price,
    this.discountPrice,
    required this.stock,
    this.image,
    required this.isFeatured,
    required this.isActive,
    required this.categoryId,
    required this.vendorId,
    this.specifications,
    this.images,
    this.category,
    this.vendor,
    this.averageRating,
    this.reviewsCount,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    // Helper function to safely parse nested JSON
    Map<String, dynamic>? parseJsonMap(dynamic value) {
      if (value == null) return null;
      if (value is Map<String, dynamic>) return value;
      if (value is String) {
        try {
          final decoded = jsonDecode(value);
          return decoded is Map<String, dynamic> ? decoded : null;
        } catch (e) {
          print('⚠️ Failed to parse JSON string: $e');
          return null;
        }
      }
      return null;
    }

    List<dynamic>? parseJsonList(dynamic value) {
      if (value == null) return null;
      if (value is List) return value;
      if (value is String) {
        try {
          final decoded = jsonDecode(value);
          return decoded is List ? decoded : null;
        } catch (e) {
          print('⚠️ Failed to parse JSON array: $e');
          return null;
        }
      }
      return null;
    }

    // Get primary image path from primary_image object or images array
    String? getPrimaryImagePath() {
      // Try primary_image first
      if (json['primary_image'] != null) {
        final primaryImage = parseJsonMap(json['primary_image']);
        if (primaryImage != null && primaryImage['image_path'] != null) {
          return ApiConfig.getImageUrl(primaryImage['image_path'] as String);
        }
      }

      // Try first image from images array
      final imagesList = parseJsonList(json['images']);
      if (imagesList != null && imagesList.isNotEmpty) {
        final firstImage = imagesList[0];
        if (firstImage is Map<String, dynamic> &&
            firstImage['image_path'] != null) {
          return ApiConfig.getImageUrl(firstImage['image_path'] as String);
        }
      }

      return null;
    }

    return Product(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      description: json['description'] as String? ?? '',
      price: double.parse(json['price'].toString()),
      discountPrice: json['discount_price'] != null
          ? double.parse(json['discount_price'].toString())
          : null,
      stock: json['stock'] as int? ?? 0,
      image: getPrimaryImagePath(),
      isFeatured: json['is_featured'] == 1 || json['is_featured'] == true,
      // Handle both 'is_active' boolean and 'status' string
      isActive: json['is_active'] != null
          ? (json['is_active'] == 1 || json['is_active'] == true)
          : (json['status'] == 'active'),
      categoryId: json['category_id'] as int,
      vendorId: json['vendor_id'] as int,
      specifications: parseJsonMap(json['specifications']),
      images: () {
        final imagesList = parseJsonList(json['images']);
        if (imagesList == null) return null;
        try {
          return imagesList
              .map((img) => ProductImage.fromJson(
                    img is Map<String, dynamic> ? img : {},
                  ))
              .toList();
        } catch (e) {
          print('⚠️ Error parsing images: $e');
          return null;
        }
      }(),
      category: () {
        final categoryData = parseJsonMap(json['category']);
        if (categoryData == null) return null;
        try {
          return Category.fromJson(categoryData);
        } catch (e) {
          print('⚠️ Error parsing category: $e');
          return null;
        }
      }(),
      vendor: () {
        final vendorData = parseJsonMap(json['vendor']);
        if (vendorData == null) return null;
        try {
          return Vendor.fromJson(vendorData);
        } catch (e) {
          print('⚠️ Error parsing vendor: $e');
          return null;
        }
      }(),
      averageRating: json['average_rating'] != null
          ? double.parse(json['average_rating'].toString())
          : null,
      reviewsCount: json['reviews_count'] as int?,
    );
  }

  double get finalPrice => discountPrice ?? price;
  bool get hasDiscount => discountPrice != null && discountPrice! < price;
  int get discountPercentage =>
      hasDiscount ? (((price - finalPrice) / price) * 100).round() : 0;
  bool get inStock => stock > 0;

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'slug': slug,
      'description': description,
      'price': price,
      'discount_price': discountPrice,
      'stock': stock,
      'primary_image': {'image_path': image},
      'is_featured': isFeatured,
      'is_active': isActive,
      'category_id': categoryId,
      'vendor_id': vendorId,
      'specifications': specifications,
      'images': images?.map((x) => x.toJson()).toList(),
      'category': category?.toJson(),
      'vendor': vendor?.toJson(),
      'average_rating': averageRating,
      'reviews_count': reviewsCount,
    };
  }
}

class ProductImage {
  final int id;
  final String imagePath;
  final bool isPrimary;

  ProductImage({
    required this.id,
    required this.imagePath,
    required this.isPrimary,
  });

  factory ProductImage.fromJson(Map<String, dynamic> json) {
    return ProductImage(
      id: json['id'] as int,
      imagePath: ApiConfig.getImageUrl(json['image_path'] as String),
      isPrimary: json['is_primary'] == 1 || json['is_primary'] == true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'image_path': imagePath,
      'is_primary': isPrimary,
    };
  }
}

class Category {
  final int id;
  final String name;
  final String slug;
  final String? image;
  final int? parentId;

  Category({
    required this.id,
    required this.name,
    required this.slug,
    this.image,
    this.parentId,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      image: json['image'] != null
          ? ApiConfig.getImageUrl(json['image'] as String)
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'slug': slug,
      'image': image,
      'parent_id': parentId,
    };
  }
}

class Vendor {
  final int id;
  final int userId;
  final String shopName;
  final String slug;
  final String? description;
  final String? logo;
  final String? banner;
  final String? phone;
  final String? address;
  final String? city;
  final String? country;
  final double? shippingPrice;

  Vendor({
    required this.id,
    required this.userId,
    required this.shopName,
    required this.slug,
    this.description,
    this.logo,
    this.banner,
    this.phone,
    this.address,
    this.city,
    this.country,
    this.shippingPrice,
  });

  factory Vendor.fromJson(Map<String, dynamic> json) {
    return Vendor(
      id: json['id'] as int,
      userId: json['user_id'] as int,
      shopName: json['shop_name'] as String,
      slug: json['slug'] as String,
      description: json['description'] as String?,
      logo: json['logo'] != null
          ? ApiConfig.getImageUrl(json['logo'] as String)
          : null,
      banner: json['banner'] != null
          ? ApiConfig.getImageUrl(json['banner'] as String)
          : null,
      phone: json['phone'] as String?,
      address: json['address'] as String?,
      city: json['city'] as String?,
      country: json['country'] as String?,
      shippingPrice: json['shipping_price'] != null
          ? double.parse(json['shipping_price'].toString())
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'shop_name': shopName,
      'slug': slug,
      'description': description,
      'logo': logo,
      'banner': banner,
      'phone': phone,
      'address': address,
      'city': city,
      'country': country,
      'shipping_price': shippingPrice,
    };
  }
}
