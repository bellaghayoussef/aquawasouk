import java.util.Properties
import java.io.FileInputStream

plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}
val keystoreProperties = Properties().apply {
    val keystorePropertiesFile = listOf(
        rootProject.file("key.properties"),
        rootProject.file("../key.properties"),
        project.file("key.properties")
    ).firstOrNull { it.exists() }
    if (keystorePropertiesFile != null) {
        FileInputStream(keystorePropertiesFile).use { load(it) }
    }
}


android {
    namespace = "com.belagha.youssef.aquawasouk"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_11
        targetCompatibility = JavaVersion.VERSION_11
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_11.toString()
    }

    defaultConfig {
        // TODO: Specify your own unique Application ID (https://developer.android.com/studio/build/application-id.html).
        applicationId = "com.belagha.youssef.aquawasouk"
        // You can update the following values to match your application needs.
        // For more information, see: https://flutter.dev/to/review-gradle-config.
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }
    signingConfigs {
        val storeFilePath = keystoreProperties["storeFile"] as String?
        val resolvedStoreFile = if (storeFilePath != null) {
            when {
                file(storeFilePath).exists() -> file(storeFilePath)
                rootProject.file(storeFilePath).exists() -> rootProject.file(storeFilePath)
                file("my-release-key.jks").exists() -> file("my-release-key.jks")
                else -> null
            }
        } else if (file("my-release-key.jks").exists()) {
            file("my-release-key.jks")
        } else {
            null
        }

        if (resolvedStoreFile != null && resolvedStoreFile.exists()) {
            create("release") {
                keyAlias = keystoreProperties["keyAlias"] as String? ?: "my-key-alias"
                keyPassword = keystoreProperties["keyPassword"] as String? ?: "000000"
                storePassword = keystoreProperties["storePassword"] as String? ?: "000000"
                storeFile = resolvedStoreFile
            }
        }
    }

    buildTypes {
        getByName("release") {
            val releaseConfig = signingConfigs.findByName("release")
            if (releaseConfig != null) {
                signingConfig = releaseConfig
            } else {
                throw org.gradle.api.GradleException(
                    "Erreur: Configuration de signature (keystore) introuvable ! Vérifiez le fichier key.properties."
                )
            }
            isMinifyEnabled = true
            isShrinkResources = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }
        getByName("debug") {
            isMinifyEnabled = false
        }
    }
}

flutter {
    source = "../.."
}
