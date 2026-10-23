---
name: flutter-api-integration
description: Appels API REST/GraphQL, gestion des erreurs, sérialisation JSON et états de chargement.
---

# Flutter API Integration Skill

## Rôle
Sécuriser et optimiser les communications avec les API backend (Laravel Sanctum REST API).

## Bonnes Pratiques
1. **Client HTTP Robuste**: Utiliser `Dio` ou `http` avec Interceptors pour l'injection automatique du token Bearer et la gestion des timeouts.
2. **Sérialisation Type-Safe**: Utiliser `json_serializable` ou `freezed` pour mapper les modèles JSON sans erreurs de typage à l'exécution.
3. **Gestion des Exceptions**: Mapper les codes HTTP (401, 403, 404, 422, 500) en exceptions applicatives compréhensibles pour l'utilisateur.
4. **Retry & Offline Cache**: Prévoir des tentatives automatiques en cas de coupure réseau temporaire.
