import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/product.dart';

class WishlistProvider with ChangeNotifier {
  List<Product> _items = [];
  bool _isLoading = false;

  List<Product> get items => _items;
  List<Product> get favorites => _items;
  bool get isLoading => _isLoading;

  bool isFavorite(int productId) => isInWishlist(productId);
  Future<void> toggleFavorite(Product product) => toggleWishlist(product);

  WishlistProvider() {
    loadWishlist();
  }

  Future<void> loadWishlist() async {
    _isLoading = true;
    notifyListeners();

    try {
      final prefs = await SharedPreferences.getInstance();
      final wishlistString = prefs.getString('wishlist');

      if (wishlistString != null) {
        final List<dynamic> decodedList = jsonDecode(wishlistString);
        _items = decodedList.map((item) => Product.fromJson(item)).toList();
      }
    } catch (e) {
      print('Error loading wishlist: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> toggleWishlist(Product product) async {
    final isExist = _items.any((item) => item.id == product.id);

    if (isExist) {
      _items.removeWhere((item) => item.id == product.id);
    } else {
      _items.add(product);
    }

    notifyListeners();
    await _saveWishlist();
  }

  bool isInWishlist(int productId) {
    return _items.any((item) => item.id == productId);
  }

  Future<void> _saveWishlist() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      // We need to implement toJson in Product model or use a simplified map
      // For now, let's create a simplified map list to save
      // Ideally Product.toJson() should exist, but it doesn't usually in typical Flutter models unless generated
      // Let's assume we can't save the full complex object easily back to JSON exact match
      // So we will just save what we need or verify if Product has toJson

      // Since Product model doesn't have toJson, let's just save IDs for now?
      // No, we need to show the product in the wishlist screen.
      // So we need to serialize it.

      // Let's assume for this demo we'll just save it effectively.
      // Since I can't easily add toJson to the huge Product model without risk,
      // I'll create a manual map here.

      final List<Map<String, dynamic>> listToSave = _items.map((product) {
        return {
          'id': product.id,
          'name': product.name,
          'slug': product.slug,
          'description': product.description,
          'price': product.price,
          'discount_price': product.discountPrice,
          'stock': product.stock,
          'image': product.image, // URL
          'is_featured': product.isFeatured ? 1 : 0,
          'is_active': product.isActive ? 1 : 0,
          'category_id': product.categoryId,
          'vendor_id': product.vendorId,
          if (product.vendor != null)
            'vendor': {
              'id': product.vendor!.id,
              'user_id': product.vendor!.userId,
              'shop_name': product.vendor!.shopName,
              'slug': product.vendor!.slug,
              'shipping_price': product.vendor!.shippingPrice,
            },
          // We skip complex nested objects for local storage simplicity to avoid circular deps or huge data
          // Minimal data needed for display
        };
      }).toList();

      await prefs.setString('wishlist', jsonEncode(listToSave));
    } catch (e) {
      print('Error saving wishlist: $e');
    }
  }

  Future<void> clearWishlist() async {
    _items = [];
    notifyListeners();
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('wishlist');
  }
}
