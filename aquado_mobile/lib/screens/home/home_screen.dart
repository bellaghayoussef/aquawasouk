import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../widgets/modern_bottom_nav_bar.dart';
import 'tabs/home_tab.dart';
import 'tabs/categories_tab.dart';
import 'tabs/cart_tab.dart';
import 'tabs/profile_tab.dart';
import '../vendor/vendor_dashboard_screen.dart';
import '../vendor/vendor_products_screen.dart';
import '../vendor/vendor_orders_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    final authProvider = context.read<AuthProvider>();
    final cartProvider = context.read<CartProvider>();

    // Only load cart for customers, not vendors
    if (authProvider.isAuthenticated && !authProvider.isVendor) {
      await cartProvider.loadCart();
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = context.watch<AuthProvider>();
    final isVendor = authProvider.isVendor;

    // Different tabs for customers and vendors
    final customerTabs = const [
      HomeTab(),
      CategoriesTab(),
      CartTab(),
      ProfileTab(),
    ];

    final vendorTabs = const [
      VendorDashboardScreen(),
      VendorProductsScreen(),
      VendorOrdersScreen(),
      ProfileTab(),
    ];

    final tabs = isVendor ? vendorTabs : customerTabs;

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: tabs,
      ),
      bottomNavigationBar: ModernBottomNavBar(
        currentIndex: _currentIndex,
        isVendor: isVendor,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
    );
  }
}
