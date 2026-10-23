import '../models/user.dart';
import 'api_service.dart';
import 'google_auth_service.dart';

class AuthService {
  // Register
  static Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String role = 'customer',
  }) async {
    final response = await ApiService.post('/register', {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'role': role,
    });

    if (response['success'] == true && response['data'] != null) {
      final token = response['data']['access_token'];
      await ApiService.saveToken(token);
      return response['data'];
    }

    throw Exception(response['message'] ?? 'Registration failed');
  }

  // Login
  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await ApiService.post('/login', {
      'email': email,
      'password': password,
    });

    if (response['success'] == true && response['data'] != null) {
      final token = response['data']['access_token'];
      await ApiService.saveToken(token);
      return response['data'];
    }

    print(response);
    throw Exception(response['message'] ?? 'Login failed');
  }

  // Google Sign-In
  static Future<Map<String, dynamic>> signInWithGoogle() async {
    final data = await GoogleAuthService.signInWithGoogle();
    return data;
  }

  // Logout
  static Future<void> logout() async {
    try {
      await ApiService.post('/logout', {}, requiresAuth: true);
    } finally {
      await ApiService.clearToken();
    }
  }

  // Get current user
  static Future<User> getCurrentUser() async {
    final response = await ApiService.get('/user', requiresAuth: true);

    if (response['success'] == true && response['data'] != null) {
      return User.fromJson(response['data']);
    }

    throw Exception('Failed to get user data');
  }

  // Update profile
  static Future<User> updateProfile({
    required String name,
    required String email,
  }) async {
    final response = await ApiService.put(
        '/user',
        {
          'name': name,
          'email': email,
        },
        requiresAuth: true);

    if (response['success'] == true && response['data'] != null) {
      return User.fromJson(response['data']);
    }

    throw Exception('Failed to update profile');
  }

  // Update password
  static Future<void> updatePassword({
    required String currentPassword,
    required String newPassword,
    required String passwordConfirmation,
  }) async {
    final response = await ApiService.put(
        '/user/password',
        {
          'current_password': currentPassword,
          'password': newPassword,
          'password_confirmation': passwordConfirmation,
        },
        requiresAuth: true);

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to update password');
    }
  }

  // Forgot password
  static Future<void> forgotPassword(String email) async {
    final response = await ApiService.post('/forgot-password', {
      'email': email,
    });

    if (response['success'] != true) {
      throw Exception(response['message'] ?? 'Failed to send reset email');
    }
  }

  // Delete account
  static Future<void> deleteAccount() async {
    final response = await ApiService.delete('/user', requiresAuth: true);

    if (response['success'] == true) {
      await ApiService.clearToken();
    } else {
      throw Exception(response['message'] ?? 'Failed to delete account');
    }
  }
}
