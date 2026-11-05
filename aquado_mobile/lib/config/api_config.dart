class ApiConfig {
  // ==========================================
  // API CONFIGURATION
  // ==========================================
  // Change this based on your environment

  // OPTION 1: Android Emulator (Default)
  static const String baseUrl = 'http://aquawasouk.com/api/v1';

  // OPTION 2: iOS Simulator (Uncomment to use)
  // static const String baseUrl = 'http://localhost/api/v1';

  // OPTION 3: Physical Device (Replace with your PC's IP address)
  // Find your IP:
  // - Windows: Open CMD and run 'ipconfig' - look for IPv4 Address
  // - Mac: Open Terminal and run 'ifconfig' - look for inet
  // - Linux: Open Terminal and run 'ifconfig' or 'ip addr'
  // Example: static const String baseUrl = 'http://192.168.1.100/api/v1';

  // OPTION 4: Production Server (When deploying)
  // static const String baseUrl = 'https://your-domain.com/api/v1';

  // ==========================================
  // IMAGE URL CONFIGURATION
  // ==========================================
  // Base URL for images (usually same as API without /api/v1)

  // For local development
  static const String imageBaseUrl = 'http://aquawasouk.com/uploads';

  // For iOS Simulator
  // static const String imageBaseUrl = 'http://localhost';

  // For Physical Device (same IP as above)
  // static const String imageBaseUrl = 'http://192.168.1.100';

  // For Production
  // static const String imageBaseUrl = 'https://your-domain.com';

  // ==========================================
  // TIMEOUT CONFIGURATION
  // ==========================================
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);

  // ==========================================
  // HELPER METHODS
  // ==========================================

  // Get full image URL
  static String getImageUrl(String? path) {
    if (path == null || path.isEmpty) return '';

    // If path is already a full URL, return as is
    if (path.startsWith('http://') || path.startsWith('https://')) {
      return path;
    }

    // If path starts with /, remove it
    final cleanPath = path.startsWith('/') ? path.substring(1) : path;

    return '$imageBaseUrl/$cleanPath';
  }

  // Check if we're in development mode
  static bool get isDevelopment =>
      baseUrl.contains('localhost') ||
      baseUrl.contains('10.0.2.2') ||
      baseUrl.contains('192.168.');

  // Check if we're in production mode
  static bool get isProduction => !isDevelopment;

  // Get environment name
  static String get environment {
    if (baseUrl.contains('192.168.1.151')) return 'Android Emulator';
    if (baseUrl.contains('localhost')) return 'iOS Simulator';
    if (baseUrl.contains('192.168.')) return 'Physical Device';
    return 'Production';
  }
}

// ==========================================
// QUICK START GUIDE
// ==========================================
/*

STEP 1: Choose your environment above by uncommenting one option

STEP 2: Make sure your Laravel server is running:
cd c:\laragon\www\aquado
php artisan serve

STEP 3: Test the connection:
- In browser, visit: http://10.0.2.2/api/v1/products
- Should see JSON response

STEP 4: Run the Flutter app:
cd c:\laragon\www\aquado\aquado_mobile
flutter run

TROUBLESHOOTING:
- Can't connect? Check if Laravel is running
- Still can't connect? Try different IP option above
- Physical device? Make sure phone and PC are on same WiFi

*/
