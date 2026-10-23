# Fixing Google Sign-In Error 10 (DEVELOPER_ERROR)

## Problem
You're getting this error: `PlatformException(sign_in_failed, com.google.android.gms.common.api.ApiException: 10: , null, null)`

**Error 10 = DEVELOPER_ERROR** - This means your Android app's SHA-1 fingerprint is not registered in the Google Cloud Console.

## Solution Steps

### Step 1: Get Your SHA-1 Fingerprint

#### Option A: Using keytool (Recommended)

Open PowerShell and run:

```powershell
keytool -list -v -keystore $env:USERPROFILE\.android\debug.keystore -alias androiddebugkey -storepass android -keypass android
```

Look for the **SHA-1** line in the output, it will look like:
```
SHA1: XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX:XX
```

#### Option B: Using Gradle

```bash
cd c:\laragon\www\aquado\aquado_mobile\android
.\gradlew signingReport
```

Look for the **debug** variant SHA-1 fingerprint.

#### Option C: Using Android Studio

1. Open the project in Android Studio
2. Click on **Gradle** tab (right side)
3. Navigate to: `aquado_mobile > android > Tasks > android > signingReport`
4. Double click to run
5. Check the output in the Run window

### Step 2: Register SHA-1 in Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project: **aquarium-b71f9**
3. Navigate to **APIs & Services** → **Credentials**
4. Find your OAuth 2.0 Client ID for Android (or create a new one if it doesn't exist)

#### If Creating New Android Client ID:

1. Click **+ CREATE CREDENTIALS** → **OAuth client ID**
2. Select **Android** as Application type
3. Fill in:
   - **Name**: `Aquado Mobile App` (or any name)
   - **Package name**: `com.belagha.youssef.aquawasouk`
   - **SHA-1 certificate fingerprint**: Paste the SHA-1 you got from Step 1
4. Click **Create**

#### If Editing Existing Client ID:

1. Click on the existing Android OAuth client ID
2. Add the SHA-1 fingerprint
3. Save

### Step 3: Update Your OAuth Client Configuration

You have an **installed app** OAuth client. You need to create an **Android** OAuth client specifically.

**Current Client ID** (from your credentials):
```
855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb.apps.googleusercontent.com
```

This appears to be for an **installed application** type. For Android, you need:

1. Keep this client ID for the backend/web
2. Create a NEW **Android** OAuth client ID with:
   - Package name: `com.belagha.youssef.aquawasouk`
   - Your SHA-1 fingerprint

### Step 4: Update Flutter GoogleAuthService

You have two options:

#### Option A: Use the Web Client ID (Current Setup)

Keep using the current client ID in your Flutter code, but you MUST add the Android OAuth client to the same project:

```dart
static final GoogleSignIn _googleSignIn = GoogleSignIn(
  clientId: '855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb.apps.googleusercontent.com',
  scopes: ['email', 'profile'],
);
```

#### Option B: Use Server Client ID (Recommended)

Update to use serverClientId:

```dart
static final GoogleSignIn _googleSignIn = GoogleSignIn(
  serverClientId: '855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb.apps.googleusercontent.com',
  scopes: ['email', 'profile'],
);
```

### Step 5: Verify Package Name and Application ID

Check these files match:

#### android/app/build.gradle.kts
```kotlin
defaultConfig {
    applicationId = "com.belagha.youssef.aquawasouk"
    // ...
}
```

#### android/app/src/main/AndroidManifest.xml
The package should match in the manifest.

### Step 6: Complete Google Cloud Console Setup

#### Enable Required APIs:

1. Go to **APIs & Services** → **Library**
2. Search and enable:
   - ✅ **Google+ API** (if not deprecated)
   - ✅ **People API**
   - ✅ **Google Identity Toolkit API**

#### OAuth Consent Screen:

1. Go to **APIs & Services** → **OAuth consent screen**
2. Ensure it's configured with:
   - App name
   - User support email
   - Developer contact information
3. Add test users if in testing mode (your Google account)
4. Add scopes: `email`, `profile`

### Step 7: Clean and Rebuild

After making all changes:

```bash
cd c:\laragon\www\aquado\aquado_mobile
flutter clean
flutter pub get
flutter run
```

## Quick Checklist

- [ ] Got SHA-1 fingerprint from debug keystore
- [ ] Created/Updated Android OAuth client in Google Cloud Console
- [ ] Added SHA-1 to Android OAuth client
- [ ] Verified package name matches: `com.belagha.youssef.aquawasouk`
- [ ] Enabled required APIs in Google Cloud Console
- [ ] OAuth consent screen is configured
- [ ] Test user added (if in testing mode)
- [ ] Updated Flutter code (if using serverClientId)
- [ ] Cleaned and rebuilt the app

## Alternative: Using Only Web Client ID

If you want to use ONLY the web client ID (simpler approach):

1. In Google Cloud Console, use the **Web application** OAuth client
2. Don't create Android OAuth client
3. Update `GoogleAuthService.dart`:

```dart
static final GoogleSignIn _googleSignIn = GoogleSignIn(
  serverClientId: '855637978298-tjvmcj55ofcqclbdc5k8kqvkvnnbsalb.apps.googleusercontent.com',
  scopes: ['email', 'profile'],
);
```

This tells the plugin to use the web client ID for server-side authentication.

## Expected Result

After completing these steps, Google Sign-In should work without error 10.

## Debugging

If still getting error 10:

1. **Check logs** for more details: `adb logcat | grep GoogleSignIn`
2. **Verify client ID** in Google Cloud Console matches your code
3. **Wait 5-10 minutes** after making changes (Google's cache)
4. **Try on a real device** (emulators can sometimes have issues)
5. **Check OAuth consent screen** is published or your account is added as test user

## Common Mistakes

1. ❌ Using wrong package name
2. ❌ Wrong SHA-1 fingerprint (release vs debug)
3. ❌ Not creating Android OAuth client
4. ❌ OAuth consent screen not configured
5. ❌ App not added to test users (when in testing mode)
6. ❌ Using clientId instead of serverClientId for web client

---

**Next Step**: Get your SHA-1 fingerprint and add it to Google Cloud Console under an Android OAuth client.
