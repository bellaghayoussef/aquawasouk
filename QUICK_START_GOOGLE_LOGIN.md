# Quick Start Guide - Google Login Implementation

## ✅ What's Been Done

### 1. Flutter App Changes
- ✅ Added `google_sign_in: ^6.2.1` package to pubspec.yaml
- ✅ Created `GoogleAuthService` for handling Google Sign-In flow
- ✅ Updated `AuthService` with Google authentication method
- ✅ Updated `AuthProvider` with `signInWithGoogle()` method
- ✅ Updated `LoginScreen` with Google Sign-In button
- ✅ Installed Flutter dependencies (`flutter pub get` completed)

### 2. Laravel Backend Changes
- ✅ Updated `AuthController` with `loginWithGoogle()` method
- ✅ Added API route: `POST /api/v1/auth/google`
- ✅ Supports both ID token (mobile) and access token (web) authentication
- ⏳ Installing Google API PHP Client (`composer require google/apiclient` in progress)

## 🎯 Next Steps (You Must Complete These)

### Step 1: Update Environment Variables

Add to your `.env` file:

```env
GOOGLE_CLIENT_ID=855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb.apps.googleusercontent.com
GOOGLE_PROJECT_ID=aquarium-b71f9
GOOGLE_AUTH_URI=https://accounts.google.com/o/oauth2/auth
GOOGLE_TOKEN_URI=https://oauth2.googleapis.com/token
GOOGLE_AUTH_PROVIDER_CERT_URL=https://www.googleapis.com/oauth2/v1/certs
```

### Step 2: Update config/services.php

Add Google configuration:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET', ''),
    'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
],
```

### Step 3: Database Migration

Check if your `users` table has these columns:
- `google_id` (string, nullable, unique)
- `avatar` (string, nullable)
- `email_verified_at` (timestamp, nullable)

If not, create a migration:

```bash
php artisan make:migration add_google_fields_to_users_table
```

Add to the migration:

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'google_id')) {
            $table->string('google_id')->nullable()->unique()->after('email');
        }
        if (!Schema::hasColumn('users', 'avatar')) {
            $table->string('avatar')->nullable()->after('photo');
        }
    });
}
```

Then run: `php artisan migrate`

### Step 4: Configure Android OAuth Client

**CRITICAL STEP**: The `ApiException: 10` error occurs because your app's SHA-1 fingerprint is missing from Google Cloud Console.

1. Go to [Google Cloud Console](https://console.cloud.google.com/) -> Credentials
2. Create/Edit an **Android** OAuth Client (not Web)
3. Package Name: `com.belagha.youssef.aquawasouk`
4. **SHA-1 Fingerprint**: `14:D8:F8:27:1E:17:7A:72:B3:AA:EE:52:D0:04:A1:38:98:96:8F:F1`
5. Save

### Step 5: Copy Google Logo Asset

A Google logo has been generated. Copy it to:
**Source**: Check artifacts folder for `google_logo.png`
**Destination**: `c:\laragon\www\aquado\aquado_mobile\assets\images\google_logo.png`

### Step 5: Test the Implementation

#### Backend Test (using Postman):
```
POST http://localhost:1011/api/v1/auth/google
Content-Type: application/json

{
  "id_token": "YOUR_GOOGLE_ID_TOKEN"
}
```

#### Mobile App Test:
```bash
cd c:\laragon\www\aquado\aquado_mobile
flutter run
```

1. Open the app
2. Go to login screen
3. Click "Sign in with Google"
4. Select your Google account
5. Verify successful login

## 📋 Summary of Files Modified

### Flutter Files
```
✅ aquado_mobile/pubspec.yaml
✅ aquado_mobile/lib/services/google_auth_service.dart (NEW)
✅ aquado_mobile/lib/services/auth_service.dart
✅ aquado_mobile/lib/providers/auth_provider.dart
✅ aquado_mobile/lib/screens/auth/login_screen.dart
```

### Laravel Files
```
✅ app/Http/Controllers/Api/AuthController.php
✅ routes/api.php
⏳ composer.json (Google API Client installation in progress)
```

### Documentation
```
✅ GOOGLE_LOGIN_SETUP.md (Comprehensive guide)
✅ QUICK_START.md (This file)
```

## 🔍 How It Works

1. User taps "Sign in with Google" in Flutter app
2. Google Sign-In SDK shows account picker
3. User selects account → SDK returns ID token
4. App sends ID token to: `POST /api/v1/auth/google`
5. Laravel verifies token with Google servers
6. Laravel creates/updates user in database
7. Laravel returns Sanctum access token
8. App stores token and redirects to home screen

## ⚠️ Important Notes

1. **Client ID**: The same Google Client ID is used in both Flutter app and Laravel backend
2. **Security**: ID tokens are verified server-side with Google's servers
3. **User Creation**: If email doesn't exist, a new user is created automatically
4. **Email Verification**: Google-authenticated users are automatically email-verified
5. **Password**: Google users get a random password (they won't use it)

## 🐛 Common Issues & Solutions

**"Invalid ID token"**
→ Check that GOOGLE_CLIENT_ID in .env matches the one in Flutter app

**"google/apiclient not found"**
→ Wait for composer installation to complete, or run: `composer require google/apiclient`

**"Column not found: google_id"**
→ Run the database migration to add Google fields

**Google Sign-In cancelled**
→ Normal behavior, handle gracefully in the app

## 📞 Support

Full documentation: `GOOGLE_LOGIN_SETUP.md`

Check logs:
- Laravel: `storage/logs/laravel.log`
- Flutter: Debug console in IDE

---

**Status**: ✅ Code Complete | ⏳ Configuration Pending
**Last Updated**: 2025-12-15
