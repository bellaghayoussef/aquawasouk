import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:aquawasouk/config/theme.dart';

void main() {
  group('Theme & UI Widget Tests', () {
    test('AppTheme brand colors and tokens are properly configured', () {
      expect(AppTheme.primaryColor, equals(const Color(0xFF0284C7)));
      expect(AppTheme.secondaryColor, equals(const Color(0xFF0F172A)));
      expect(AppTheme.successColor, equals(const Color(0xFF059669)));
      expect(AppTheme.errorColor, equals(const Color(0xFFDC2626)));
    });

    testWidgets(
        'AppTheme.lightTheme renders buttons and text with theme styles',
        (WidgetTester tester) async {
      await tester.pumpWidget(
        MaterialApp(
          theme: AppTheme.lightTheme,
          home: Scaffold(
            appBar: AppBar(
              title: const Text('Aquawasouk Test'),
            ),
            body: Center(
              child: ElevatedButton(
                onPressed: () {},
                child: const Text('Commander'),
              ),
            ),
          ),
        ),
      );

      // Verify widget elements exist in hierarchy
      expect(find.text('Aquawasouk Test'), findsOneWidget);
      expect(find.text('Commander'), findsOneWidget);
      expect(find.byType(ElevatedButton), findsOneWidget);
      expect(find.byType(AppBar), findsOneWidget);
    });
  });
}
