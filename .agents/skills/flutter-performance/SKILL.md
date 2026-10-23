---
name: flutter-performance
description: Optimiser le framerate (60/120 FPS), éliminer les reconstructions inutiles et fuites mémoire.
---

# Flutter Performance Skill

## Rôle
Réduire les saccades (jank), minimiser les reconstructions de widgets et optimiser l'utilisation de la mémoire.

## Règles d'Or
1. **ListView.builder**: Ne jamais utiliser `SingleChildScrollView` avec une `Column` pour de longues listes de données ; toujours utiliser `ListView.builder` ou `CustomScrollView` avec `SliverList`.
2. **Const Everywhere**: Ajouter `const` devant les widgets statiques pour que Flutter réutilise les instances sans les recréer.
3. **Mise en cache des images**: Utiliser `cached_network_image` avec des dimensions précises (`memCacheWidth`, `memCacheHeight`).
4. **RepaintBoundary**: Isoler les animations complexes ou Canvas avec `RepaintBoundary`.
