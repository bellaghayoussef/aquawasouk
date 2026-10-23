---
name: flutter-accessibility
description: Accessibilité, lecteurs d'écran (TalkBack/VoiceOver), contraste et redimensionnement texte.
---

# Flutter Accessibility Skill

## Rôle
Garantir l'accessibilité de l'application selon les recommandations WCAG et les fonctionnalités d'assistance mobile.

## Checklist
1. **Semantics**: Envelopper les boutons personnalisés et images informatives avec des widgets `Semantics(label: "...", button: true)`.
2. **Texte Dynamique**: Vérifier que l'interface ne casse pas lorsque l'utilisateur augmente la taille de police du système (`textScaler`).
3. **Contraste des Couleurs**: Ratio minimal de contraste de 4.5:1 pour le texte standard.
