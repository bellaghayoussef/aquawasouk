import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

class ApiService {
  // API URL is now configured in lib/config/api_config.dart
  static String get baseUrl => ApiConfig.baseUrl;

  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
    iOptions: IOSOptions(accessibility: KeychainAccessibility.first_unlock),
  );

  static String? _token;

  // Initialize token from secure storage (with legacy fallback migration)
  static Future<void> init() async {
    try {
      // 1. Try secure encrypted storage first
      _token = await _secureStorage.read(key: 'auth_token');

      // 2. Fallback to legacy SharedPreferences if not found
      if (_token == null) {
        final prefs = await SharedPreferences.getInstance();
        final legacyToken = prefs.getString('auth_token');
        if (legacyToken != null && legacyToken.isNotEmpty) {
          _token = legacyToken;
          // Migrate to secure storage & remove from unencrypted storage
          await _secureStorage.write(key: 'auth_token', value: legacyToken);
          await prefs.remove('auth_token');
        }
      }

      print(
          '🔐 Token initialized (SecureStorage): ${_token != null ? "Yes" : "No"}');
    } catch (e) {
      print('❌ Error initializing token from SecureStorage: $e');
    }
  }

  // Save token encrypted
  static Future<void> saveToken(String token) async {
    try {
      _token = token;
      await _secureStorage.write(key: 'auth_token', value: token);

      // Clean up legacy storage if it existed
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');

      print('✅ Token securely saved in FlutterSecureStorage');
    } catch (e) {
      print('❌ Error saving secure token: $e');
      throw Exception('Failed to save authentication token');
    }
  }

  // Clear token
  static Future<void> clearToken() async {
    try {
      _token = null;
      await _secureStorage.delete(key: 'auth_token');

      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');

      print('🗑️ Token cleared from SecureStorage');
    } catch (e) {
      print('❌ Error clearing token: $e');
    }
  }

  // Get headers
  static Map<String, String> _getHeaders({bool includeAuth = true}) {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };

    if (includeAuth && _token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }

    return headers;
  }

  // Generic GET request
  static Future<Map<String, dynamic>> get(
    String endpoint, {
    Map<String, dynamic>? queryParams,
    bool requiresAuth = false,
  }) async {
    try {
      var uri = Uri.parse('$baseUrl$endpoint');

      if (queryParams != null && queryParams.isNotEmpty) {
        uri = uri.replace(
          queryParameters: queryParams.map(
            (key, value) => MapEntry(key, value.toString()),
          ),
        );
      }

      print('📡 GET Request: $uri');
      print('🔑 Auth Required: $requiresAuth');

      final response = await http
          .get(
        uri,
        headers: _getHeaders(includeAuth: requiresAuth),
      )
          .timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('Request timeout - Server took too long to respond');
        },
      );

      print('📥 Response Status: ${response.statusCode}');

      return _handleResponse(response, endpoint);
    } on SocketException catch (e) {
      print('❌ Socket Exception: $e');
      throw Exception('No internet connection. Please check your network.');
    } on FormatException catch (e) {
      print('❌ Format Exception: $e');
      throw Exception('Invalid response format from server');
    } on http.ClientException catch (e) {
      print('❌ Client Exception: $e');
      throw Exception('Failed to connect to server. Please check API URL.');
    } catch (e) {
      print('❌ GET Error: $e');
      throw Exception('Network error: ${e.toString()}');
    }
  }

  // Generic POST request
  static Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> body, {
    bool requiresAuth = false,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');

      print('📡 POST Request: $uri');
      print('📤 Body: ${jsonEncode(body)}');
      print('🔑 Auth Required: $requiresAuth');

      final response = await http
          .post(
        uri,
        headers: _getHeaders(includeAuth: requiresAuth),
        body: jsonEncode(body),
      )
          .timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('Request timeout - Server took too long to respond');
        },
      );

      print('📥 Response Status: ${response.statusCode}');
      print('📥 Response Body: ${response.body}');

      return _handleResponse(response, endpoint);
    } on SocketException catch (e) {
      print('❌ Socket Exception: $e');
      throw Exception('No internet connection. Please check your network.');
    } on FormatException catch (e) {
      print('❌ Format Exception: $e');
      throw Exception('Invalid data format sent to server');
    } on http.ClientException catch (e) {
      print('❌ Client Exception: $e');
      throw Exception('Failed to connect to server. Please check API URL.');
    } catch (e) {
      print('❌ POST Error: $e');
      throw Exception('Network error: ${e.toString()}');
    }
  }

  // Generic PUT request
  static Future<Map<String, dynamic>> put(
    String endpoint,
    Map<String, dynamic> body, {
    bool requiresAuth = false,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');

      print('📡 PUT Request: $uri');
      print('📤 Body: ${jsonEncode(body)}');
      print('🔑 Auth Required: $requiresAuth');

      final response = await http
          .put(
        uri,
        headers: _getHeaders(includeAuth: requiresAuth),
        body: jsonEncode(body),
      )
          .timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('Request timeout - Server took too long to respond');
        },
      );

      print('📥 Response Status: ${response.statusCode}');

      return _handleResponse(response, endpoint);
    } on SocketException catch (e) {
      print('❌ Socket Exception: $e');
      throw Exception('No internet connection. Please check your network.');
    } on FormatException catch (e) {
      print('❌ Format Exception: $e');
      throw Exception('Invalid data format sent to server');
    } on http.ClientException catch (e) {
      print('❌ Client Exception: $e');
      throw Exception('Failed to connect to server. Please check API URL.');
    } catch (e) {
      print('❌ PUT Error: $e');
      throw Exception('Network error: ${e.toString()}');
    }
  }

  // Generic DELETE request
  static Future<Map<String, dynamic>> delete(
    String endpoint, {
    bool requiresAuth = false,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');

      print('📡 DELETE Request: $uri');
      print('🔑 Auth Required: $requiresAuth');

      final response = await http
          .delete(
        uri,
        headers: _getHeaders(includeAuth: requiresAuth),
      )
          .timeout(
        const Duration(seconds: 30),
        onTimeout: () {
          throw Exception('Request timeout - Server took too long to respond');
        },
      );

      print('📥 Response Status: ${response.statusCode}');

      return _handleResponse(response, endpoint);
    } on SocketException catch (e) {
      print('❌ Socket Exception: $e');
      throw Exception('No internet connection. Please check your network.');
    } on FormatException catch (e) {
      print('❌ Format Exception: $e');
      throw Exception('Invalid response format from server');
    } on http.ClientException catch (e) {
      print('❌ Client Exception: $e');
      throw Exception('Failed to connect to server. Please check API URL.');
    } catch (e) {
      print('❌ DELETE Error: $e');
      throw Exception('Network error: ${e.toString()}');
    }
  }

  // Generic Multipart POST request
  static Future<Map<String, dynamic>> postMultipart(
    String endpoint, {
    required Map<String, String> fields,
    required List<File> files,
    String fileField = 'images[]',
    bool requiresAuth = true,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final request = http.MultipartRequest('POST', uri);

      print('📡 MULTIPART POST Request: $uri');
      print('🔑 Auth Required: $requiresAuth');
      print('📝 Fields: $fields');
      print('📁 Files: ${files.length}');

      // Headers
      request.headers['Accept'] = 'application/json';
      if (requiresAuth && _token != null) {
        request.headers['Authorization'] = 'Bearer $_token';
      }

      // Fields
      request.fields.addAll(fields);

      // Files
      for (var file in files) {
        final stream = http.ByteStream(file.openRead());
        final length = await file.length();
        final multipartFile = http.MultipartFile(
          fileField,
          stream,
          length,
          filename: file.path.split('/').last,
        );
        request.files.add(multipartFile);
      }

      // Send
      final streamedResponse = await request.send().timeout(
        const Duration(seconds: 60),
        onTimeout: () {
          throw Exception('Request timeout - Upload took too long');
        },
      );

      final response = await http.Response.fromStream(streamedResponse);

      print('📥 Response Status: ${response.statusCode}');
      print('📥 Response Body: ${response.body}');

      return _handleResponse(response, endpoint);
    } on SocketException catch (e) {
      print('❌ Socket Exception: $e');
      throw Exception('No internet connection. Please check your network.');
    } on FormatException catch (e) {
      print('❌ Format Exception: $e');
      throw Exception('Invalid response format from server');
    } on http.ClientException catch (e) {
      print('❌ Client Exception: $e');
      throw Exception('Failed to connect to server. Please check API URL.');
    } catch (e) {
      print('❌ Multipart Error: $e');
      throw Exception('Upload failed: ${e.toString()}');
    }
  }

  // Handle response
  static Map<String, dynamic> _handleResponse(
    http.Response response,
    String endpoint,
  ) {
    try {
      // Success responses (200-299)
      if (response.statusCode >= 200 && response.statusCode < 300) {
        try {
          final data = jsonDecode(response.body);
          print('✅ Success Response: ${response.statusCode}');
          return data as Map<String, dynamic>;
        } catch (e) {
          print('❌ JSON Decode Error: $e');
          print('📄 Response Body: ${response.body}');
          throw Exception('Invalid JSON response from server');
        }
      }

      // Unauthorized (401)
      if (response.statusCode == 401) {
        print('🚫 Unauthorized - Clearing token');
        clearToken();
        throw Exception('Session expired. Please login again.');
      }

      // Validation Error (422)
      if (response.statusCode == 422) {
        try {
          final data = jsonDecode(response.body);
          print('⚠️ Validation Error: $data');

          final errors = data['errors'] as Map<String, dynamic>?;
          if (errors != null && errors.isNotEmpty) {
            final firstError = errors.values.first;
            final message = firstError is List && firstError.isNotEmpty
                ? firstError[0]
                : firstError.toString();
            throw Exception(message);
          }

          final message = data['message'] ?? 'Validation error';
          throw Exception(message);
        } catch (e) {
          if (e is Exception) rethrow;
          throw Exception('Validation error occurred');
        }
      }

      // Not Found (404)
      if (response.statusCode == 404) {
        print('🔍 Not Found: $endpoint');
        throw Exception('Resource not found');
      }

      // Server Error (500+)
      if (response.statusCode >= 500) {
        print('💥 Server Error: ${response.statusCode}');
        throw Exception('Server error. Please try again later.');
      }

      // Other errors
      try {
        final data = jsonDecode(response.body);
        print('❌ Error Response: $data');
        final message = data['message'] ?? 'Request failed';
        throw Exception(message);
      } catch (e) {
        if (e is Exception) rethrow;
        print('❌ Unknown Error: Status ${response.statusCode}');
        throw Exception('Request failed with status ${response.statusCode}');
      }
    } catch (e) {
      if (e is Exception) {
        rethrow;
      } else {
        print('❌ Handler Error: $e');
        throw Exception('Failed to process server response');
      }
    }
  }

  // Check if user is authenticated
  static bool get isAuthenticated => _token != null;

  // Get current token (for debugging)
  static String? get currentToken => _token;

  // Print API configuration
  static void printConfig() {
    print('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    print('📡 API Configuration');
    print('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    print('🌐 Base URL: $baseUrl');
    print('🖼️ Image Base: ${ApiConfig.imageBaseUrl}');
    print('🏷️ Environment: ${ApiConfig.environment}');
    print('🔐 Authenticated: $isAuthenticated');
    print('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  }
}
