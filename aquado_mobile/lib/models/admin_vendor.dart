import 'package:flutter/material.dart';
import '../config/api_config.dart';

class AdminVendor {
  final int id;
  final String shopName;
  final String slug;
  final String? description;
  final String? logo;
  final String? banner;
  final String? phone;
  final String? address;
  final String? city;
  final String? country;
  final String? postalCode;
  final String status; // 'pending', 'active', 'inactive'
  final double commissionRate;
  final double shippingPrice;
  final int productsCount;
  final DateTime? createdAt;
  final String ownerName;
  final String ownerEmail;
  final String? ownerPhoto;

  AdminVendor({
    required this.id,
    required this.shopName,
    required this.slug,
    this.description,
    this.logo,
    this.banner,
    this.phone,
    this.address,
    this.city,
    this.country,
    this.postalCode,
    required this.status,
    required this.commissionRate,
    required this.shippingPrice,
    required this.productsCount,
    this.createdAt,
    required this.ownerName,
    required this.ownerEmail,
    this.ownerPhoto,
  });

  factory AdminVendor.fromJson(Map<String, dynamic> json) {
    final user = json['user'] as Map<String, dynamic>?;

    return AdminVendor(
      id: json['id'] is int
          ? json['id'] as int
          : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      shopName: json['shop_name'] as String? ?? 'Boutique Sans Nom',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String?,
      logo: json['logo'] as String?,
      banner: json['banner'] as String?,
      phone: json['phone'] as String?,
      address: json['address'] as String?,
      city: json['city'] as String?,
      country: json['country'] as String?,
      postalCode: json['postal_code'] as String?,
      status: (json['status'] as String? ?? 'pending').toLowerCase(),
      commissionRate: json['commission_rate'] != null
          ? double.tryParse(json['commission_rate'].toString()) ?? 0.0
          : 0.0,
      shippingPrice: json['shipping_price'] != null
          ? double.tryParse(json['shipping_price'].toString()) ?? 7.0
          : 7.0,
      productsCount: json['products_count'] is int
          ? json['products_count'] as int
          : int.tryParse(json['products_count']?.toString() ?? '0') ?? 0,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
      ownerName: user?['name'] as String? ?? 'Inconnu',
      ownerEmail: user?['email'] as String? ?? '',
      ownerPhoto: user?['photo'] as String?,
    );
  }

  bool get isPending => status == 'pending';
  bool get isActive => status == 'active';
  bool get isInactive => status == 'inactive';

  Color get statusColor {
    switch (status) {
      case 'active':
        return const Color(0xFF10B981); // Emerald
      case 'pending':
        return const Color(0xFFF59E0B); // Amber / Orange
      case 'inactive':
      default:
        return const Color(0xFFEF4444); // Red
    }
  }

  String get statusLabel {
    switch (status) {
      case 'active':
        return 'Active';
      case 'pending':
        return 'En attente';
      case 'inactive':
      default:
        return 'Inactive';
    }
  }

  IconData get statusIcon {
    switch (status) {
      case 'active':
        return Icons.verified_rounded;
      case 'pending':
        return Icons.hourglass_top_rounded;
      case 'inactive':
      default:
        return Icons.block_rounded;
    }
  }

  String? get logoUrl => ApiConfig.getImageUrl(logo);
  String? get bannerUrl => ApiConfig.getImageUrl(banner);

  String get locationString {
    final parts = [city, country].where((s) => s != null && s.isNotEmpty).toList();
    return parts.isEmpty ? 'Tunisie' : parts.join(', ');
  }

  AdminVendor copyWith({
    String? status,
  }) {
    return AdminVendor(
      id: id,
      shopName: shopName,
      slug: slug,
      description: description,
      logo: logo,
      banner: banner,
      phone: phone,
      address: address,
      city: city,
      country: country,
      postalCode: postalCode,
      status: status ?? this.status,
      commissionRate: commissionRate,
      shippingPrice: shippingPrice,
      productsCount: productsCount,
      createdAt: createdAt,
      ownerName: ownerName,
      ownerEmail: ownerEmail,
      ownerPhoto: ownerPhoto,
    );
  }
}

