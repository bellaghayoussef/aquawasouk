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
  final List<ProductVariation>? variations;
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
    this.variations,
    this.category,
    this.vendor,
    this.averageRating,
    this.reviewsCount,
  });

  factory Product.fromJson(Map<String, dynamic> rawJson) {
    // Helper function to safely parse nested JSON
    Map<String, dynamic> json = rawJson;
    if (rawJson.containsKey('product') && rawJson['product'] is Map<String, dynamic>) {
      json = rawJson['product'] as Map<String, dynamic>;
    }

    Map<String, dynamic>? parseJsonMap(dynamic value) {
      if (value == null) return null;
      if (value is Map<String, dynamic>) return value;
      if (value is String) {
        try {
          final decoded = jsonDecode(value);
          return decoded is Map<String, dynamic> ? decoded : null;
        } catch (e) {
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
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String? ?? '',
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      discountPrice: json['discount_price'] != null
          ? double.tryParse(json['discount_price'].toString())
          : null,
      stock: json['stock'] is int ? json['stock'] as int : int.tryParse(json['stock']?.toString() ?? '0') ?? 0,
      image: getPrimaryImagePath(),
      isFeatured: json['is_featured'] == 1 || json['is_featured'] == true,
      // Handle both 'is_active' boolean and 'status' string
      isActive: json['is_active'] != null
          ? (json['is_active'] == 1 || json['is_active'] == true)
          : (json['status'] == 'active'),
      categoryId: json['category_id'] is int ? json['category_id'] as int : int.tryParse(json['category_id']?.toString() ?? '0') ?? 0,
      vendorId: json['vendor_id'] is int ? json['vendor_id'] as int : int.tryParse(json['vendor_id']?.toString() ?? '0') ?? 0,
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
          return null;
        }
      }(),
      variations: () {
        final vList = parseJsonList(json['variations']);
        if (vList == null) return null;
        try {
          return vList
              .whereType<Map<String, dynamic>>()
              .map((v) => ProductVariation.fromJson(v))
              .toList();
        } catch (e) {
          return null;
        }
      }(),
      category: () {
        final categoryData = parseJsonMap(json['category']);
        if (categoryData == null) return null;
        try {
          return Category.fromJson(categoryData);
        } catch (e) {
          return null;
        }
      }(),
      vendor: () {
        final vendorData = parseJsonMap(json['vendor']);
        if (vendorData == null) return null;
        try {
          return Vendor.fromJson(vendorData);
        } catch (e) {
          return null;
        }
      }(),
      averageRating: json['average_rating'] != null
          ? double.tryParse(json['average_rating'].toString())
          : null,
      reviewsCount: json['reviews_count'] as int?,
    );
  }

  double get finalPrice => discountPrice ?? price;
  bool get hasDiscount => discountPrice != null && discountPrice! < price;
  int get discountPercentage =>
      hasDiscount ? (((price - finalPrice) / price) * 100).round() : 0;
  bool get inStock => stock > 0;

  List<String> get availableColors {
    if (variations != null && variations!.isNotEmpty) {
      final colors = variations!
          .map((v) => v.color?.trim())
          .where((c) => c != null && c.isNotEmpty)
          .cast<String>()
          .toSet()
          .toList();
      if (colors.isNotEmpty) return colors;
    }
    // Fallback to specifications
    if (specifications != null) {
      for (var key in ['colors', 'couleurs', 'couleur', 'color']) {
        if (specifications!.containsKey(key)) {
          final val = specifications![key];
          if (val is List) {
            return val.map((e) => e.toString().trim()).where((e) => e.isNotEmpty).toList();
          } else if (val is String && val.contains(',')) {
            return val.split(',').map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
          } else if (val is String && val.trim().isNotEmpty) {
            return [val.trim()];
          }
        }
      }
    }
    return [];
  }

  List<String> get availableSizes {
    if (variations != null && variations!.isNotEmpty) {
      final sizes = variations!
          .map((v) => v.size?.trim())
          .where((s) => s != null && s.isNotEmpty)
          .cast<String>()
          .toSet()
          .toList();
      if (sizes.isNotEmpty) return sizes;
    }
    // Fallback to specifications
    if (specifications != null) {
      for (var key in ['sizes', 'tailles', 'taille', 'size']) {
        if (specifications!.containsKey(key)) {
          final val = specifications![key];
          if (val is List) {
            return val.map((e) => e.toString().trim()).where((e) => e.isNotEmpty).toList();
          } else if (val is String && val.contains(',')) {
            return val.split(',').map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
          } else if (val is String && val.trim().isNotEmpty) {
            return [val.trim()];
          }
        }
      }
    }
    return [];
  }

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
      'variations': variations?.map((x) => x.toJson()).toList(),
      'category': category?.toJson(),
      'vendor': vendor?.toJson(),
      'average_rating': averageRating,
      'reviews_count': reviewsCount,
    };
  }
}

class ProductVariation {
  final int id;
  final int productId;
  final String? sku;
  final String? color;
  final String? size;
  final double price;
  final int stock;
  final String status;
  final String? ecomnessCode;
  final String? image;

  ProductVariation({
    required this.id,
    required this.productId,
    this.sku,
    this.color,
    this.size,
    required this.price,
    required this.stock,
    required this.status,
    this.ecomnessCode,
    this.image,
  });

  factory ProductVariation.fromJson(Map<String, dynamic> json) {
    return ProductVariation(
      id: json['id'] is int ? json['id'] as int : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      productId: json['product_id'] is int ? json['product_id'] as int : int.tryParse(json['product_id']?.toString() ?? '0') ?? 0,
      sku: json['sku']?.toString(),
      color: json['color']?.toString(),
      size: json['size']?.toString(),
      price: double.tryParse(json['price']?.toString() ?? '0') ?? 0.0,
      stock: json['stock'] is int ? json['stock'] as int : int.tryParse(json['stock']?.toString() ?? '0') ?? 0,
      status: json['status']?.toString() ?? 'active',
      ecomnessCode: json['ecomness_code']?.toString(),
      image: json['image'] != null ? ApiConfig.getImageUrl(json['image'].toString()) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'product_id': productId,
      'sku': sku,
      'color': color,
      'size': size,
      'price': price,
      'stock': stock,
      'status': status,
      'ecomness_code': ecomnessCode,
      'image': image,
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
