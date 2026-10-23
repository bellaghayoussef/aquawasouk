import '../models/product.dart';
import '../models/cart.dart';
import '../models/order.dart';
import '../models/review.dart';
import 'api_service.dart';

class ProductService {
  // Get all products
  static Future<List<Product>> getProducts({
    String? search,
    String? category,
    String? vendor,
    double? minPrice,
    double? maxPrice,
    bool? featured,
    String? sortBy,
    String? sortOrder,
    int page = 1,
    int perPage = 15,
  }) async {
    final queryParams = <String, dynamic>{'page': page, 'per_page': perPage};

    if (search != null) queryParams['search'] = search;
    if (category != null) queryParams['category'] = category;
    if (vendor != null) queryParams['vendor'] = vendor;
    if (minPrice != null) queryParams['min_price'] = minPrice;
    if (maxPrice != null) queryParams['max_price'] = maxPrice;
    if (featured != null) queryParams['featured'] = featured;
    if (sortBy != null) queryParams['sort_by'] = sortBy;
    if (sortOrder != null) queryParams['sort_order'] = sortOrder;

    final response = await ApiService.get(
      '/products',
      queryParams: queryParams,
    );

    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      final products = data['data'] as List;
      return products.map((json) => Product.fromJson(json)).toList();
    }

    return [];
  }

  // Get single product
  static Future<Product> getProduct(String slug) async {
    final response = await ApiService.get('/products/$slug');

    if (response['success'] == true && response['data'] != null) {
      return Product.fromJson(response['data']);
    }

    throw Exception('Product not found');
  }

  // Get featured products
  static Future<List<Product>> getFeaturedProducts() async {
    try {
      final response = await ApiService.get('/featured/products');
      print(response.toString());

      if (response['success'] == true && response['data'] != null) {
        final products = response['data'] as List;
        return products.map((json) => Product.fromJson(json)).toList();
      }

      print("Invalid response format");
      return [];
    } catch (e, stack) {
      print("❌ Error in getFeaturedProducts: $e");
      print(stack);
      return [];
    }
  }
}

class CategoryService {
  // Get all categories
  static Future<List<Category>> getCategories({bool? parentOnly}) async {
    final queryParams = <String, dynamic>{};
    if (parentOnly != null) queryParams['parent_only'] = parentOnly;

    final response = await ApiService.get(
      '/categories',
      queryParams: queryParams,
    );

    if (response['success'] == true && response['data'] != null) {
      final categories = response['data'] as List;
      return categories.map((json) => Category.fromJson(json)).toList();
    }

    return [];
  }

  // Get single category
  static Future<Category> getCategory(String slug) async {
    final response = await ApiService.get('/categories/$slug');

    if (response['success'] == true && response['data'] != null) {
      return Category.fromJson(response['data']);
    }

    throw Exception('Category not found');
  }

  // Get category products
  static Future<List<Product>> getCategoryProducts(String slug) async {
    final response = await ApiService.get('/categories/$slug/products');

    if (response['success'] == true && response['data'] != null) {
      final products = response['data'] as List;
      return products.map((json) => Product.fromJson(json)).toList();
    }

    return [];
  }
}

class CartService {
  // Get cart
  static Future<Cart> getCart() async {
    final response = await ApiService.get('/cart', requiresAuth: true);

    if (response['success'] == true && response['data'] != null) {
      return Cart.fromJson(response['data']);
    }

    throw Exception('Failed to get cart');
  }

  // Add to cart
  static Future<void> addToCart({
    required int productId,
    required int quantity,
    String? size,
    String? color,
  }) async {
    final body = <String, dynamic>{
      'product_id': productId,
      'quantity': quantity,
    };
    if (size != null && size.isNotEmpty) body['size'] = size;
    if (color != null && color.isNotEmpty) body['color'] = color;

    final response = await ApiService.post(
      '/cart/add',
      body,
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to add to cart');
    }
  }

  // Update cart item
  static Future<void> updateCartItem(int cartItemId, int quantity) async {
    final response = await ApiService.put(
        '/cart/update/$cartItemId',
        {
          'quantity': quantity,
        },
        requiresAuth: true);

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to update cart');
    }
  }

  // Remove from cart
  static Future<void> removeFromCart(int cartItemId) async {
    final response = await ApiService.delete(
      '/cart/remove/$cartItemId',
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to remove from cart');
    }
  }

  // Clear cart
  static Future<void> clearCart() async {
    final response = await ApiService.delete('/cart/clear', requiresAuth: true);

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to clear cart');
    }
  }

  // Get cart count
  static Future<int> getCartCount() async {
    final response = await ApiService.get('/cart/count', requiresAuth: true);

    if (response['success'] == true && response['data'] != null) {
      return response['data']['count'] as int;
    }

    return 0;
  }
}

class OrderService {
  // Get orders
  static Future<List<Order>> getOrders({
    String? status,
    String? paymentStatus,
  }) async {
    final queryParams = <String, dynamic>{};
    if (status != null) queryParams['status'] = status;
    if (paymentStatus != null) queryParams['payment_status'] = paymentStatus;

    final response = await ApiService.get(
      '/orders',
      queryParams: queryParams,
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final orders = response['data'] as List;

      return orders.map((json) => Order.fromJson(json)).toList();
    }

    return [];
  }

  // Get single order
  static Future<Order> getOrder(int orderId) async {
    final response = await ApiService.get(
      '/orders/$orderId',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      return Order.fromJson(response['data']);
    }

    throw Exception('Order not found');
  }

  // Create order
  static Future<Order> createOrder({
    required String shippingAddress,
    required String shippingCity,
    required String shippingPostalCode,
    required String shippingCountry,
    required String phone,
    required String paymentMethod,
    String? notes,
    int? vendorId,
  }) async {
    final body = <String, dynamic>{
      'shipping_address': shippingAddress,
      'shipping_city': shippingCity,
      'shipping_postal_code': shippingPostalCode,
      'shipping_country': shippingCountry,
      'phone': phone,
      'payment_method': paymentMethod,
    };

    if (notes != null) body['notes'] = notes;
    if (vendorId != null) body['vendor_id'] = vendorId;

    final response = await ApiService.post(
      '/orders/create',
      body,
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final data = response['data'];
      if (data is List && data.isNotEmpty) {
        return Order.fromJson(data[0]);
      } else if (data is Map<String, dynamic>) {
        return Order.fromJson(data);
      }
    }

    throw Exception(response['message'] ?? 'Failed to create order');
  }

  // Place direct single-product order
  static Future<Order> directOrder({
    required int productId,
    required int quantity,
    required String customerName,
    required String phone,
    required String shippingCity,
    required String shippingAddress,
    String? shippingPostalCode,
    String? notes,
    String? size,
    String? color,
  }) async {
    final body = <String, dynamic>{
      'product_id': productId,
      'quantity': quantity,
      'customer_name': customerName,
      'phone': phone,
      'shipping_city': shippingCity,
      'shipping_address': shippingAddress,
      'shipping_postal_code': shippingPostalCode ?? '1000',
    };

    if (notes != null && notes.isNotEmpty) body['notes'] = notes;
    if (size != null && size.isNotEmpty) body['size'] = size;
    if (color != null && color.isNotEmpty) body['color'] = color;

    final response = await ApiService.post(
      '/orders/direct',
      body,
      requiresAuth: false,
    );

    if (response['success'] == true && response['data'] != null) {
      return Order.fromJson(response['data']);
    }

    throw Exception(response['message'] ?? 'Échec de la commande directe');
  }

  // Cancel order
  static Future<void> cancelOrder(int orderId) async {
    final response = await ApiService.post(
      '/orders/$orderId/cancel',
      {},
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to cancel order');
    }
  }
}

class ReviewService {
  // Get product reviews
  static Future<List<Review>> getProductReviews(int productId) async {
    final response = await ApiService.get('/products/$productId/reviews');

    if (response['success'] == true && response['data'] != null) {
      final reviews = response['data'] as List;
      return reviews.map((json) => Review.fromJson(json)).toList();
    }

    return [];
  }

  // Create review
  static Future<void> createReview({
    required int productId,
    required int rating,
    String? comment,
  }) async {
    final response = await ApiService.post(
        '/reviews/products/$productId',
        {
          'rating': rating,
          'comment': comment,
        },
        requiresAuth: true);

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to create review');
    }
  }

  // Update review
  static Future<void> updateReview({
    required int reviewId,
    required int rating,
    String? comment,
  }) async {
    final response = await ApiService.put(
        '/reviews/$reviewId',
        {
          'rating': rating,
          'comment': comment,
        },
        requiresAuth: true);

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to update review');
    }
  }

  // Delete review
  static Future<void> deleteReview(int reviewId) async {
    final response = await ApiService.delete(
      '/reviews/$reviewId',
      requiresAuth: true,
    );

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to delete review');
    }
  }
}
