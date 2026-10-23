---
name: flutter-debugging
description: Diagnostiquer et corriger les bugs réels, analyser les stack traces et DevTools.
---

# Flutter Debugging Skill

## Rôle
Identifier la cause racine des bugs, analyser les exceptions Flutter (`FlutterError.onError`) et inspecter l'arborescence avec Flutter DevTools.

## Méthodologie
1. **Reproduction Isolée**: Isoler le cas de test ou les données d'entrée provoquant l'anomalie.
2. **Inspection des Logs**: Examiner les logs d'erreurs détaillés, codes d'état réseau et traces d'appels.
3. **Flutter Inspector**: Vérifier les contraintes de disposition (`hasSize`, `BoxConstraints`) pour éliminer les `Null check operator used on a null value` ou `RenderBox was not laid out`.
