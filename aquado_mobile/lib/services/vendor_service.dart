import 'dart:convert';
import 'dart:io';
import '../models/product.dart';
import '../models/order.dart';
import 'api_service.dart';

class VendorService {
  // ==========================================
  // VENDOR DASHBOARD
  // ==========================================

  // Get vendor dashboard data
  static Future<Map<String, dynamic>> getDashboard() async {
    final response =
        await ApiService.get('/vendor/dashboard', requiresAuth: true);

    if (response['success'] == true && response['data'] != null) {
      return response['data'] as Map<String, dynamic>;
    }

    throw Exception('Failed to get dashboard data');
  }

  // Get vendor statistics
  static Future<Map<String, dynamic>> getStatistics(
      {String period = '30days'}) async {
    final response = await ApiService.get(
      '/vendor/statistics',
      queryParams: {'period': period},
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      return response['data'] as Map<String, dynamic>;
    }

    return {};
  }

  // ==========================================
  // VENDOR PROFILE
  // ==========================================

  // Get vendor profile
  static Future<Map<String, dynamic>> getProfile() async {
    final response = await ApiService.get(
      '/vendor/profile',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      return response['data'] as Map<String, dynamic>;
    }

    throw Exception('Failed to get vendor profile');
  }

  // Update vendor profile
  static Future<Map<String, dynamic>> updateProfile(
    Map<String, dynamic> data,
  ) async {
    final response = await ApiService.put(
      '/vendor/profile',
      data,
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      return response['data'] as Map<String, dynamic>;
    }

    throw Exception(response['message'] ?? 'Failed to update profile');
  }

  // Update vendor logo
  static Future<void> updateLogo(File file) async {
    final response = await ApiService.postMultipart(
      '/vendor/profile/logo',
      fields: {},
      files: [file],
      fileField: 'logo',
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to update logo');
    }
  }

  // Update vendor banner
  static Future<void> updateBanner(File file) async {
    final response = await ApiService.postMultipart(
      '/vendor/profile/banner',
      fields: {},
      files: [file],
      fileField: 'banner',
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to update banner');
    }
  }

  // ==========================================
  // VENDOR PRODUCTS MANAGEMENT
  // ==========================================

  // Get vendor products
  static Future<List<Product>> getVendorProducts({
    String? status,
    String? search,
    int page = 1,
  }) async {
    final queryParams = <String, dynamic>{'page': page};
    if (status != null) queryParams['status'] = status;
    if (search != null) queryParams['search'] = search;

    final response = await ApiService.get(
      '/vendor/products',
      queryParams: queryParams,
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final products = data['data'] as List;
      return products.map((json) => Product.fromJson(json)).toList();
    }

    return [];
  }

  // Get single vendor product
  static Future<Product> getVendorProduct(int productId) async {
    final response = await ApiService.get(
      '/vendor/products/$productId',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      return Product.fromJson(response['data']);
    }

    throw Exception('Product not found');
  }

  // Create product
  static Future<Product> createProduct({
    required int categoryId,
    required String name,
    required String description,
    required double price,
    double? discountPrice,
    required int stock,
    Map<String, dynamic>? specifications,
    bool isFeatured = false,
    List<File>? images,
  }) async {
    Map<String, dynamic> response;

    if (images != null && images.isNotEmpty) {
      final fields = {
        'category_id': categoryId.toString(),
        'name': name,
        'description': description,
        'price': price.toString(),
        'stock': stock.toString(),
        'is_featured': isFeatured ? '1' : '0',
      };

      if (discountPrice != null) {
        fields['discount_price'] = discountPrice.toString();
      }
      if (specifications != null) {
        fields['specifications'] = jsonEncode(specifications);
      }

      response = await ApiService.postMultipart(
        '/vendor/products',
        fields: fields,
        files: images,
      );
    } else {
      final body = {
        'category_id': categoryId,
        'name': name,
        'description': description,
        'price': price,
        'stock': stock,
        'is_featured': isFeatured,
      };

      if (discountPrice != null) body['discount_price'] = discountPrice;
      if (specifications != null) body['specifications'] = specifications;

      response = await ApiService.post(
        '/vendor/products',
        body,
        requiresAuth: true,
      );
    }

    if (response['success'] == true && response['data'] != null) {
      try {
        return Product.fromJson(response['data']);
      } catch (e) {
        print(e);
      }
    }

    throw Exception(response['message'] ?? 'Failed to create product');
  }

  // Update product
  static Future<Product> updateProduct({
    required int productId,
    required int categoryId,
    required String name,
    required String description,
    required double price,
    double? discountPrice,
    required int stock,
    Map<String, dynamic>? specifications,
    bool? isFeatured,
    List<File>? images,
  }) async {
    Map<String, dynamic> response;

    if (images != null && images.isNotEmpty) {
      final fields = {
        '_method': 'PUT',
        'category_id': categoryId.toString(),
        'name': name,
        'description': description,
        'price': price.toString(),
        'stock': stock.toString(),
      };

      if (discountPrice != null) {
        fields['discount_price'] = discountPrice.toString();
      }
      if (specifications != null) {
        fields['specifications'] = jsonEncode(specifications);
      }
      if (isFeatured != null) {
        fields['is_featured'] = isFeatured ? '1' : '0';
      }

      response = await ApiService.postMultipart(
        '/vendor/products/$productId',
        fields: fields,
        files: images,
      );
    } else {
      final body = {
        'category_id': categoryId,
        'name': name,
        'description': description,
        'price': price,
        'stock': stock,
      };

      if (discountPrice != null) body['discount_price'] = discountPrice;
      if (specifications != null) body['specifications'] = specifications;
      if (isFeatured != null) body['is_featured'] = isFeatured;

      response = await ApiService.put(
        '/vendor/products/$productId',
        body,
        requiresAuth: true,
      );
    }

    if (response['success'] == true && response['data'] != null) {
      return Product.fromJson(response['data']);
    }

    throw Exception(response['message'] ?? 'Failed to update product');
  }

  // Delete product
  static Future<void> deleteProduct(int productId) async {
    final response = await ApiService.delete(
      '/vendor/products/$productId',
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to delete product');
    }
  }

  // Toggle product status (active/inactive)
  static Future<void> toggleProductStatus(int productId) async {
    final response = await ApiService.put(
      '/vendor/products/$productId/toggle-status',
      {},
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to toggle status');
    }
  }

  // Toggle product featured
  static Future<void> toggleProductFeatured(int productId) async {
    final response = await ApiService.put(
      '/vendor/products/$productId/toggle-featured',
      {},
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to toggle featured');
    }
  }

  // ==========================================
  // VENDOR ORDERS MANAGEMENT
  // ==========================================

  // Get vendor orders
  static Future<List<OrderItem>> getVendorOrders({
    String? status,
    int page = 1,
  }) async {
    final queryParams = <String, dynamic>{'page': page};
    if (status != null) queryParams['status'] = status;

    final response = await ApiService.get(
      '/vendor/orders',
      queryParams: queryParams,
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final orders = data['data'] as List;
      return orders.map((json) => OrderItem.fromJson(json)).toList();
    }

    return [];
  }

  // Get single order item details
  static Future<OrderItem> getVendorOrder(int orderItemId) async {
    final response = await ApiService.get(
      '/vendor/orders/$orderItemId',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      return OrderItem.fromJson(response['data']);
    }

    throw Exception('Order not found');
  }

  // Update order status
  static Future<void> updateOrderStatus({
    required int orderItemId,
    required String status,
  }) async {
    final response = await ApiService.put(
      '/vendor/orders/$orderItemId/status',
      {'status': status},
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to update order status');
    }
  }

  // ==========================================
  // VENDOR ANALYTICS
  // ==========================================

  // Get top products
  static Future<List<Product>> getTopProducts({int limit = 10}) async {
    final response = await ApiService.get(
      '/vendor/analytics/top-products',
      queryParams: {'limit': limit},
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final products = response['data'] as List;
      return products.map((json) => Product.fromJson(json)).toList();
    }

    return [];
  }

  // Get recent orders
  static Future<List<OrderItem>> getRecentOrders({int limit = 10}) async {
    final response = await ApiService.get(
      '/vendor/analytics/recent-orders',
      queryParams: {'limit': limit},
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final orders = response['data'] as List;
      return orders.map((json) => OrderItem.fromJson(json)).toList();
    }

    return [];
  }
}
