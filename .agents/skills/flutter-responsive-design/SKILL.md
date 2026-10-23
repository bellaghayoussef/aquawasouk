---
name: flutter-responsive-design
description: Adapter l'interface aux différentes tailles d'écran (smartphones, tablettes, pliables).
---

# Flutter Responsive Design Skill

## Rôle
Adapter dynamiquement l’interface aux différentes résolutions d'écran et orientations (Portrait / Paysage).

## Techniques Clés
1. **LayoutBuilder & MediaQuery**: Utiliser `LayoutBuilder` pour les contraintes parentes et `MediaQuery.sizeOf(context)` pour les dimensions d'écran.
2. **Widgets Flexibles**: Utiliser `Flexible`, `Expanded`, `FractionallySizedBox`, `Wrap`, et `AspectRatio` pour éviter tout dépassement (`RenderFlex overflowed`).
3. **Breakpoints Standards**:
   - Mobile Compact : < 600dp
   - Tablettes / Pliables : 600dp - 1024dp
   - Desktop / Web : > 1024dp
