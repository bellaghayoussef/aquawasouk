import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'config/theme.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/wishlist_provider.dart';
import 'services/api_service.dart';
import 'screens/admin/admin_vendors_screen.dart';
import 'screens/admin/admin_login_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize API service
  await ApiService.init();

  runApp(const AquadoAdminApp());
}

class AquadoAdminApp extends StatelessWidget {
  const AquadoAdminApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => WishlistProvider()),
      ],
      child: MaterialApp(
        title: 'AquawaSouk Admin - Boutiques',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.lightTheme.copyWith(
          colorScheme: ColorScheme.fromSeed(
            seedColor: const Color(0xFF0284C7),
            primary: const Color(0xFF0284C7),
          ),
        ),
        home: const _AdminRootGate(),
        routes: {
          '/admin/vendors': (context) => const AdminVendorsScreen(),
          '/admin/login': (context) => const AdminLoginScreen(),
        },
      ),
    );
  }
}

class _AdminRootGate extends StatefulWidget {
  const _AdminRootGate();

  @override
  State<_AdminRootGate> createState() => _AdminRootGateState();
}

class _AdminRootGateState extends State<_AdminRootGate> {
  bool _isChecking = true;

  @override
  void initState() {
    super.initState();
    _checkAuth();
  }

  Future<void> _checkAuth() async {
    final auth = context.read<AuthProvider>();
    await auth.init();
    if (mounted) {
      setState(() => _isChecking = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isChecking) {
      return const Scaffold(
        backgroundColor: Color(0xFF0F172A),
        body: Center(
          child: CircularProgressIndicator(color: Color(0xFF0284C7)),
        ),
      );
    }

    return Consumer<AuthProvider>(
      builder: (context, auth, _) {
        if (auth.isAuthenticated && auth.isAdmin) {
          return const AdminVendorsScreen();
        }
        return const AdminLoginScreen();
      },
    );
  }
}

