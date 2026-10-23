---
name: flutter-theme-management
description: Gestion des thèmes Clair / Sombre (ThemeData, ColorScheme, persistance).
---

# Flutter Theme Management Skill

## Rôle
Implémenter et synchroniser les thèmes clair (Light) et sombre (Dark) avec bascule instantanée et persistance locale.

## Directives
1. **ThemeData & ColorScheme**: Utiliser `ThemeData(colorScheme: ColorScheme.fromSeed(...))` pour une cohérence chromatique Material 3.
2. **ThemeExtensions**: Créer des extensions de thème personnalisées (`ThemeExtension<T>`) pour les couleurs sémantiques spécifiques.
3. **Persistance**: Enregistrer la préférence utilisateur (Système, Clair, Sombre) via `shared_preferences` ou `hydrated_bloc`.
