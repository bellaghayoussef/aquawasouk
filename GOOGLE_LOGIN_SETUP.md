# Google OAuth Login Setup for Flutter App

This document provides step-by-step instructions for implementing Google Sign-In in your Flutter mobile app and configuring the Laravel backend.

## Overview

The implementation consists of:
1. **Flutter Mobile App** - Google Sign-In UI and authentication flow
2. **Laravel API Backend** - ID token verification and user management

## ✅ Completed Changes

### 1. Flutter Mobile App Updates

#### Added Dependencies (pubspec.yaml)
```yaml
dependencies:
  google_sign_in: ^6.2.1
```

#### New Files Created
- **`lib/services/google_auth_service.dart`** - Handles Google Sign-In flow
- **`lib/providers/auth_provider.dart`** - Updated with `signInWithGoogle()` method
- **`lib/services/auth_service.dart`** - Updated with Google authentication
- **`lib/screens/auth/login_screen.dart`** - Updated with Google Sign-In button

### 2. Laravel Backend Updates

#### API Endpoint
- **POST** `/api/v1/auth/google` - Handles Google authentication
- Located in: `app/Http/Controllers/Api/AuthController.php`
- Method: `loginWithGoogle()`

#### Features
- ✅ Supports both ID token (mobile) and access token (web)
- ✅ Creates new user account if email doesn't exist
- ✅ Updates existing user with Google ID and avatar
- ✅ Returns JWT token for authenticated sessions
- ✅ Auto-verifies email for Google sign-in users

## 🔧 Required Backend Setup

### Step 1: Install Google API PHP Client

You need to install the Google API PHP Client for ID token verification:

```bash
cd c:\laragon\www\aquado
composer require google/apiclient:"^2.0"
```

### Step 2: Update User Model

Add these fields to your `users` table migration if they don't exist:

```php
$table->string('google_id')->nullable()->unique();
$table->string('avatar')->nullable();
$table->timestamp('email_verified_at')->nullable();
```

Run the migration:
```bash
php artisan migrate
```

If the migration already exists, create a new one:
```bash
php artisan make:migration add_google_fields_to_users_table
```

Then add:
```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('google_id')->nullable()->unique()->after('email');
        $table->string('avatar')->nullable()->after('photo');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['google_id', 'avatar']);
    });
}
```

### Step 3: Configure Google OAuth Credentials

Update your `.env` file:

```env
GOOGLE_CLIENT_ID=855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb.apps.googleusercontent.com
GOOGLE_PROJECT_ID=aquarium-b71f9
GOOGLE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
GOOGLE_TOKEN_URI=https://oauth2.googleapis.com/token
GOOGLE_AUTH_PROVIDER_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
```

### Step 4: Update config/services.php

Add Google OAuth configuration:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', ''), // Optional for ID token verification
    'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
],
```

## 📱 Flutter App Setup

### Step 1: Install Dependencies

```bash
cd c:\laragon\www\aquado\aquado_mobile
flutter pub get
```

### Step 2: Android Configuration

#### Update AndroidManifest.xml

No additional configuration is required for Android. The package handles everything automatically.

#### Update build.gradle.kts (if needed)

Ensure you have the minimum SDK version 21 or higher in:
`android/app/build.gradle.kts`

```kotlin
defaultConfig {
    minSdk = 21  // Minimum required
    targetSdk = flutter.targetSdkVersion
}
```

### Step 3: iOS Configuration (if supporting iOS)

Add the following to `ios/Runner/Info.plist`:

```xml
<key>CFBundleURLTypes</key>
<array>
  <dict>
    <key>CFBundleTypeRole</key>
    <string>Editor</string>
    <key>CFBundleURLSchemes</key>
    <array>
      <string>com.googleusercontent.apps.855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb</string>
    </array>
  </dict>
</array>
```

## 🚀 Testing the Implementation

### 1. Backend Testing (using Postman or cURL)

First, you need to get an ID token from Google. You can test with the mobile app or use a test token.

```bash
POST http://localhost:1011/api/v1/auth/google
Content-Type: application/json

{
  "id_token": "YOUR_GOOGLE_ID_TOKEN_HERE"
}
```

Expected Response:
```json
{
  "success": true,
  "message": "Logged in with Google successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "google_id": "1234567890",
      "avatar": "https://...",
      "role": "customer"
    },
    "access_token": "1|xxxxxxxxxxxxx",
    "token_type": "Bearer"
  }
}
```

### 2. Mobile App Testing

1. **Build and run the app**:
   ```bash
   cd c:\laragon\www\aquado\aquado_mobile
   flutter run
   ```

2. **Test the login flow**:
   - Open the app
   - Navigate to the login screen
   - Click "Sign in with Google"
   - Select a Google account
   - Verify successful login

## 🔐 Authentication Flow

```
┌─────────────┐
│ Flutter App │
└──────┬──────┘
       │
       │ 1. User taps "Sign in with Google"
       ▼
┌─────────────────────┐
│ Google Sign-In SDK  │
└──────┬──────────────┘
       │
       │ 2. Returns ID Token
       ▼
┌─────────────────────┐
│ GoogleAuthService   │
│ (Flutter)           │
└──────┬──────────────┘
       │
       │ 3. POST /api/v1/auth/google
       │    { "id_token": "..." }
       ▼
┌─────────────────────┐
│ Laravel API         │
│ AuthController      │
└──────┬──────────────┘
       │
       │ 4. Verifies ID token with Google
       │ 5. Creates/updates user
       │ 6. Generates Sanctum token
       ▼
┌─────────────────────┐
│ Returns JWT Token   │
└──────┬──────────────┘
       │
       │ 7. Stores token & user data
       ▼
┌─────────────────────┐
│ User Logged In      │
│ (Flutter App)       │
└─────────────────────┘
```

## 🎨 Google Logo Asset

A Google logo icon has been generated and saved. Copy it to your Flutter assets:

**Location**: Check your artifacts for `google_logo.png`

**Destination**: `aquado_mobile/assets/images/google_logo.png`

Update `pubspec.yaml` to ensure assets are included (already done):
```yaml
flutter:
  assets:
    - assets/images/
```

## 🐛 Troubleshooting

### Issue: "Invalid ID token"
**Solution**: Ensure your `GOOGLE_CLIENT_ID` in `.env` matches the one used in the Flutter app.

### Issue: "google/apiclient not found"
**Solution**: Run `composer require google/apiclient:"^2.0"` in your Laravel project.

### Issue: Google Sign-In cancelled
**Solution**: Normal behavior when user closes the Google account picker. Handle gracefully in the app.

### Issue: "No token provided"
**Solution**: Check that the Flutter app is sending either `id_token` or `access_token` in the request body.

### Issue: Database error on user creation
**Solution**: Ensure `google_id` and `avatar` columns exist in the `users` table.

## 📝 API Documentation

### POST /api/v1/auth/google

**Request Body:**
```json
{
  "id_token": "string (optional)",
  "access_token": "string (optional)"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged in with Google successfully",
  "data": {
    "user": { ... },
    "access_token": "string",
    "token_type": "Bearer"
  }
}
```

**Error Response (401):**
```json
{
  "success": false,
  "message": "Google Login Failed: error details"
}
```

## 🔒 Security Considerations

1. **ID Token Verification**: The backend verifies ID tokens directly with Google's servers
2. **Email Verification**: Users authenticated via Google are automatically email-verified
3. **Token Expiry**: Sanctum tokens are used for session management
4. **HTTPS**: Always use HTTPS in production
5. **Client ID Validation**: Ensure the client ID matches between app and server

## 📚 Next Steps

1. Install Google API PHP Client: `composer require google/apiclient:"^2.0"`
2. Run migrations to add Google fields to users table
3. Update `.env` file with Google credentials
4. Test the implementation
5. Add error handling and user feedback
6. Consider adding logout from Google when user logs out from app

## 🆘 Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Check Flutter debug console
- Verify API endpoints with Postman
- Check database for user records

---

**Created**: December 15, 2025
**Status**: Implementation Complete - Testing Required
