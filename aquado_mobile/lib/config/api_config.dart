import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

enum ApiEnvironment {
  emulator(
    name: 'Émulateur Android (10.0.2.2:8000)',
    baseUrl: 'https://aquawasouk.com/api/v1',
    imageBaseUrl: 'https://aquawasouk.com/uploads',
  ),
  physicalDevice(
    name: 'Appareil Wi-Fi (192.168.1.107:8000)',
    baseUrl: 'https://aquawasouk.com/api/v1',
    imageBaseUrl: 'https://aquawasouk.com/uploads',
  ),
  localhost(
    name: 'Localhost / Windows (127.0.0.1:8000)',
    baseUrl: 'https://aquawasouk.com/api/v1',
    imageBaseUrl: 'https://aquawasouk.com/uploads',
  ),
  production(
    name: 'Production (aquawasouk.com)',
    baseUrl: 'https://aquawasouk.com/api/v1',
    imageBaseUrl: 'https://aquawasouk.com/uploads',
  );

  final String name;
  final String baseUrl;
  final String imageBaseUrl;

  const ApiEnvironment({
    required this.name,
    required this.baseUrl,
    required this.imageBaseUrl,
  });
}

class ApiConfig {
  static const String _prefKeyEnv = 'api_active_environment';
  static const String _prefKeyCustomUrl = 'api_custom_base_url';

  // Default active environment: Production
  static ApiEnvironment _activeEnv = ApiEnvironment.production;
  static String? _customBaseUrl;

  // Initialize saved environment from SharedPreferences
  static Future<void> init() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedEnv = prefs.getString(_prefKeyEnv);
      if (savedEnv != null) {
        for (final env in ApiEnvironment.values) {
          if (env.name == savedEnv || env.toString() == savedEnv) {
            _activeEnv = env;
            break;
          }
        }
      }
      _customBaseUrl = prefs.getString(_prefKeyCustomUrl);
    } catch (e) {
      debugPrint('⚠️ Error initializing ApiConfig: $e');
    }
  }

  // Active Base URL
  static String get baseUrl {
    if (_customBaseUrl != null && _customBaseUrl!.isNotEmpty) {
      return _customBaseUrl!;
    }
    return _activeEnv.baseUrl;
  }

  // Active Image Base URL
  static String get imageBaseUrl {
    if (_customBaseUrl != null && _customBaseUrl!.isNotEmpty) {
      return _customBaseUrl!.replaceAll('/api/v1', '/uploads');
    }
    return _activeEnv.imageBaseUrl;
  }

  static ApiEnvironment get currentEnvironment => _activeEnv;

  static Future<void> setEnvironment(ApiEnvironment env) async {
    _activeEnv = env;
    _customBaseUrl = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_prefKeyEnv, env.name);
    await prefs.remove(_prefKeyCustomUrl);
  }

  static Future<void> setCustomBaseUrl(String url) async {
    var clean = url.trim();
    if (clean.endsWith('/')) {
      clean = clean.substring(0, clean.length - 1);
    }
    if (!clean.endsWith('/api/v1')) {
      clean = '$clean/api/v1';
    }
    _customBaseUrl = clean;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_prefKeyCustomUrl, clean);
  }

  // Timeout settings
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);

  // Helper method to resolve full image URL
  static String getImageUrl(String? path) {
    if (path == null || path.isEmpty) return '';

    // If already absolute URL
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }

    var cleanPath = path.startsWith('/') ? path.substring(1) : path;
    if (cleanPath.startsWith('uploads/')) {
      cleanPath = cleanPath.substring(8);
    }

    return '$imageBaseUrl/$cleanPath';
  }

  static bool get isDevelopment =>
      baseUrl.contains('localhost') ||
      baseUrl.contains('10.0.2.2') ||
      baseUrl.contains('127.0.0.1') ||
      baseUrl.contains('192.168.');

  static bool get isProduction => !isDevelopment;

  static String get environment => _customBaseUrl != null
      ? 'Personnalisé ($_customBaseUrl)'
      : _activeEnv.name;
}
