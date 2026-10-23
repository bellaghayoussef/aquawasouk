import 'package:google_sign_in/google_sign_in.dart';
import 'api_service.dart';

class GoogleAuthService {
  // Configure Google Sign-In with your OAuth credentials
  static final GoogleSignIn _googleSignIn = GoogleSignIn(
    clientId:
        '855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb.apps.googleusercontent.com',
    scopes: [
      'email',
      'profile',
    ],
  );

  // Sign in with Google
  static Future<Map<String, dynamic>> signInWithGoogle() async {
    try {
      // Trigger the Google Sign-In flow
      final GoogleSignInAccount? googleUser = await _googleSignIn.signIn();

      if (googleUser == null) {
        // User cancelled the sign-in
        throw Exception('Google Sign-In was cancelled');
      }

      // Obtain the auth details from the request
      final GoogleSignInAuthentication googleAuth =
          await googleUser.authentication;

      // Get the ID token
      final String? idToken = googleAuth.idToken;
      final String? accessToken = googleAuth.accessToken;

      if (idToken == null) {
        throw Exception('Failed to get Google ID token');
      }

      // Send the ID token to your backend
      final response = await ApiService.post('/auth/google', {
        'id_token': idToken,
        'access_token': accessToken,
      });

      if (response['success'] == true && response['data'] != null) {
        final token = response['data']['access_token'];
        await ApiService.saveToken(token);
        return response['data'];
      }

      throw Exception(response['message'] ?? 'Google Sign-In failed');
    } catch (e) {
      // Sign out from Google if there was an error
      await _googleSignIn.signOut();
      rethrow;
    }
  }

  // Sign out from Google
  static Future<void> signOut() async {
    await _googleSignIn.signOut();
  }

  // Check if user is signed in with Google
  static Future<bool> isSignedIn() async {
    return await _googleSignIn.isSignedIn();
  }

  // Get current Google user
  static GoogleSignInAccount? getCurrentUser() {
    return _googleSignIn.currentUser;
  }
}
