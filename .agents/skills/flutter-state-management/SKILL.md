---
name: flutter-state-management
description: Gestion efficace de l'état applicatif avec Riverpod, BLoC, Provider ou Cubit.
---

# Flutter State Management Skill

## Rôle
Améliorer et fiabiliser le gestionnaire d'état de l'application (Riverpod, BLoC, Provider) pour un flux de données unidirectionnel prévisible.

## Directives
1. **Flux Unidirectionnel**: UI -> Événement/Action -> State Manager -> Nouvel État Immuable -> Re-render ciblé.
2. **Isolation UI/Logique**: Aucun appel HTTP direct ou requête de base de données à l'intérieur des méthodes `build()` des widgets.
3. **Sélecteurs Fins**: Utiliser `ref.watch(provider.select(...))` ou `BlocSelector` pour ne reconstruire que les widgets impactés.
