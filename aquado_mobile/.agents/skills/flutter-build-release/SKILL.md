---
name: flutter-build-release
description: Vérification des builds Android/iOS, Gradle, Proguard et optimisation de l'APK/AAB.
---

# Flutter Build & Release Skill

## Rôle
Résoudre les erreurs de compilation Gradle/Android, optimiser la taille du bundle et préparer les livrables de production.

## Directives Build
1. **Configuration Gradle**: Synchroniser `compileSdk`, `targetSdk`, `minSdk` et les versions du plugin Kotlin/AGP.
2. **Minification & Obfuscation**: Configurer `proguard-rules.pro` et activer `minifyEnabled true` pour réduire la taille et protéger le binaire.
3. **Commandes de Build**:
   - `flutter build appbundle --release` (Google Play Store)
   - `flutter build apk --split-per-abi` (Distribution directe optimisée)
