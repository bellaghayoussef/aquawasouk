---
name: flutter-expert
description: Bonnes pratiques Flutter et Dart (Architecture propre, null-safety, immutabilité).
---

# Flutter Expert Skill

## Rôle
Appliquer les meilleures pratiques et conventions de l'écosystème Flutter et Dart.

## Bonnes Pratiques
1. **Null Safety & Immutabilité**: Utiliser des classes immuables (`@immutable`), `final` pour les champs, et `const` constructors partout où c'est possible.
2. **Organisation du Code**: Séparation Feature-first ou Layer-first (Domain, Data, Presentation).
3. **Gestion des Ressources**: Toujours fermer (`dispose()`) les `TextEditingController`, `AnimationController`, `StreamSubscription` et `ScrollController`.
4. **Linter & Analyse**: Respecter les règles `flutter_lints` strictes, sans avertissements ou `print()` résiduels (utiliser `debugPrint` ou `log`).
