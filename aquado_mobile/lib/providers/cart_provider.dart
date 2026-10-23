import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/cart.dart';
import '../models/product.dart';
import '../services/product_service.dart';
import '../services/api_service.dart';

class CartProvider with ChangeNotifier {
  Cart? _cart;
  bool _isLoading = false;
  String? _error;

  Cart? get cart => _cart;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get itemCount => _cart?.itemCount ?? 0;
  double get total => _cart?.total ?? 0.0;
  List<CartItem> get items => _cart?.items ?? [];

  // Load cart
  Future<void> loadCart() async {
    if (ApiService.isAuthenticated) {
      // Authenticated: Load from API
      try {
        _isLoading = true;
        _error = null;
        notifyListeners();

        _cart = await CartService.getCart();
        _error = null;
      } catch (e) {
        _error = e.toString().replaceAll('Exception: ', '');
      } finally {
        _isLoading = false;
        notifyListeners();
      }
    } else {
      // Guest: Load from Local Storage
      _isLoading = true;
      notifyListeners();
      try {
        final prefs = await SharedPreferences.getInstance();
        final String? guestCartJson = prefs.getString('guest_cart');

        if (guestCartJson != null) {
          final List<dynamic> decoded = jsonDecode(guestCartJson);
          final List<CartItem> localItems =
              decoded.map((item) => CartItem.fromJson(item)).toList();

          double total = localItems.fold(
              0, (sum, item) => sum + (item.price * item.quantity));

          _cart = Cart(id: 0, userId: 0, items: localItems, total: total);
        } else {
          _cart = Cart(id: 0, userId: 0, items: [], total: 0);
        }
        _error = null;
      } catch (e) {
        print("Error loading guest cart: $e");
        _error = "Failed to load guest cart";
      } finally {
        _isLoading = false;
        notifyListeners();
      }
    }
  }

  // Add to cart
  Future<bool> addToCart({
    required int productId,
    required int quantity,
    String? color,
    String? size,
    Product? product,
  }) async {
    if (ApiService.isAuthenticated) {
      // Authenticated: API
      try {
        _isLoading = true;
        _error = null;
        notifyListeners();

        await CartService.addToCart(
          productId: productId,
          quantity: quantity,
          size: size,
          color: color,
        );

        await loadCart();
        return true;
      } catch (e) {
        _error = e.toString().replaceAll('Exception: ', '');
        notifyListeners();
        return false;
      }
    } else {
      // Guest: Local Storage
      if (product == null) {
        _error = 'Product details required for guest mode';
        notifyListeners();
        return false;
      }

      try {
        final prefs = await SharedPreferences.getInstance();
        final String? guestCartJson = prefs.getString('guest_cart');
        List<CartItem> localItems = [];

        if (guestCartJson != null) {
          final List<dynamic> decoded = jsonDecode(guestCartJson);
          localItems = decoded.map((item) => CartItem.fromJson(item)).toList();
        }

        // Check if item exists
        final existingIndex = localItems.indexWhere((item) =>
            item.productId == productId &&
            item.color == color &&
            item.size == size);

        if (existingIndex >= 0) {
          // Update quantity
          final existing = localItems[existingIndex];
          final updated = CartItem(
              id: existing.id,
              cartId: 0,
              productId: productId,
              quantity: existing.quantity + quantity,
              price: existing.price,
              color: color,
              size: size,
              product: product);
          localItems[existingIndex] = updated;
        } else {
          // Add new
          final newItem = CartItem(
              id: DateTime.now().millisecondsSinceEpoch, // Temp ID
              cartId: 0,
              productId: productId,
              quantity: quantity,
              price: product.finalPrice, // Use discounted price if available
              color: color,
              size: size,
              product: product);
          localItems.add(newItem);
        }

        // Save
        final encoded = jsonEncode(localItems.map((e) => e.toJson()).toList());
        await prefs.setString('guest_cart', encoded);

        await loadCart(); // Refresh state
        return true;
      } catch (e) {
        _error = "Failed to add to guest cart: $e";
        notifyListeners();
        return false;
      }
    }
  }

  // Update cart item
  Future<bool> updateCartItem(int cartItemId, int quantity) async {
    if (ApiService.isAuthenticated) {
      try {
        _error = null;
        await CartService.updateCartItem(cartItemId, quantity);
        await loadCart();
        return true;
      } catch (e) {
        _error = e.toString().replaceAll('Exception: ', '');
        notifyListeners();
        return false;
      }
    } else {
      // Guest
      try {
        final prefs = await SharedPreferences.getInstance();
        final String? guestCartJson = prefs.getString('guest_cart');
        if (guestCartJson == null) return false;

        List<CartItem> localItems = (jsonDecode(guestCartJson) as List)
            .map((item) => CartItem.fromJson(item))
            .toList();

        final index = localItems.indexWhere((item) => item.id == cartItemId);
        if (index >= 0) {
          final existing = localItems[index];
          localItems[index] = CartItem(
              id: existing.id,
              cartId: 0,
              productId: existing.productId,
              quantity: quantity,
              price: existing.price,
              color: existing.color,
              size: existing.size,
              product: existing.product);

          await prefs.setString('guest_cart',
              jsonEncode(localItems.map((e) => e.toJson()).toList()));
          await loadCart();
          return true;
        }
        return false;
      } catch (e) {
        _error = "Failed to update guest cart";
        notifyListeners();
        return false;
      }
    }
  }

  // Remove from cart
  Future<bool> removeFromCart(int cartItemId) async {
    if (ApiService.isAuthenticated) {
      try {
        _error = null;

        await CartService.removeFromCart(cartItemId);
        await loadCart();
        return true;
      } catch (e) {
        _error = e.toString().replaceAll('Exception: ', '');
        notifyListeners();
        return false;
      }
    } else {
      // Guest
      try {
        final prefs = await SharedPreferences.getInstance();
        final String? guestCartJson = prefs.getString('guest_cart');
        if (guestCartJson == null) return false;

        List<CartItem> localItems = (jsonDecode(guestCartJson) as List)
            .map((item) => CartItem.fromJson(item))
            .toList();

        localItems.removeWhere((item) => item.id == cartItemId);

        await prefs.setString('guest_cart',
            jsonEncode(localItems.map((e) => e.toJson()).toList()));
        await loadCart();
        return true;
      } catch (e) {
        _error = "Failed to remove from guest cart";
        notifyListeners();
        return false;
      }
    }
  }

  // Clear cart
  Future<bool> clearCart() async {
    // ... Logic to clear both API and Local ...
    // Usually clearCart is called carefully. If auth, clear API. If guest, clear local.
    try {
      _isLoading = true;
      _error = null;
      notifyListeners();

      if (ApiService.isAuthenticated) {
        await CartService.clearCart();
      } else {
        final prefs = await SharedPreferences.getInstance();
        await prefs.remove('guest_cart');
      }

      _cart = null;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Helper to sync guest items to server after login
  Future<void> syncGuestCart() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final String? guestCartJson = prefs.getString('guest_cart');

      if (guestCartJson != null) {
        final List<dynamic> decoded = jsonDecode(guestCartJson);
        for (var itemJson in decoded) {
          final productId = itemJson['product_id'];
          final quantity = itemJson['quantity'];
          // Note: Ignoring color/size as ApiService doesn't support them yet?
          // Assuming it might support them eventually, we'd pass them here.
          await CartService.addToCart(productId: productId, quantity: quantity);
        }
        await prefs.remove('guest_cart');
        await loadCart(); // Reload from server
      }
    } catch (e) {
      print("Sync failed: $e");
    }
  }

  // Clear error
  void clearError() {
    _error = null;
    notifyListeners();
  }

  // Reset cart (on logout)
  void reset() {
    _cart = null;
    _error = null;
    _isLoading = false;
    notifyListeners();
  }
}
