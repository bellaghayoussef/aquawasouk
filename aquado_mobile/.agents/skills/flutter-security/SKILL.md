---
name: flutter-security
description: Sécurisation des tokens JWT/Sanctum, stockage chiffré et protection des requêtes réseau.
---

# Flutter Security Skill

## Rôle
Sécuriser les données sensibles, les tokens d'authentification et les échanges réseau de l'application mobile.

## Mesures de Sécurité
1. **Stockage Chiffré**: Utiliser `flutter_secure_storage` (Keystore sur Android, Keychain sur iOS) pour stocker le token d'authentification.
2. **SSL Pinning**: Valider les certificats TLS pour bloquer les attaques de type Man-in-the-Middle (MitM).
3. **Protection des Secrets**: Ne jamais stocker de clés API secrètes en clair dans le code Dart (utiliser `--dart-define` ou variables d'environnement).
