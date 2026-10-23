---
name: flutter-widget-refactoring
description: Simplifier, découper et réutiliser les widgets Flutter pour une maintenance optimale.
---

# Flutter Widget Refactoring Skill

## Rôle
Découper les widgets volumineux ("monolithic build methods") en widgets plus petits, autonomes et réutilisables.

## Règles de Refactoring
1. **Extraire en Widget vs Méthode**: Privilégier l'extraction en sous-classes `StatelessWidget` plutôt qu'en fonctions helpers `Widget _buildItem()` pour bénéficier du cache de reconstruction (`const`) et de l'arbre d'éléments Flutter.
2. **Responsabilité Unique**: Un widget ne doit gérer qu'une seule partie de l'interface ou une seule responsabilité de présentation.
3. **Paramétrage Propre**: Fournir des callbacks typés (`VoidCallback`, `ValueChanged<T>`) au lieu de passer des contextes profonds.
