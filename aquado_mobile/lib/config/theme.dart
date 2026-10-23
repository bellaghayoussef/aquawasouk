import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // Website Brand Colors (Matching Tailwind config & Storefront)
  static const Color primaryColor = Color(0xFF0284C7); // brand-600
  static const Color primaryLight = Color(0xFF0EA5E9); // brand-500
  static const Color primarySoft = Color(0xFFF0F9FF); // brand-50
  static const Color secondaryColor =
      Color(0xFF0F172A); // slate-900 (Obsidian dark)
  static const Color darkSurface = Color(0xFF020617); // slate-950

  static const Color accentColor = Color(0xFF0EA5E9);
  static const Color successColor = Color(0xFF059669); // emerald-600
  static const Color successSoft = Color(0xFFECFDF5);
  static const Color warningColor = Color(0xFFF59E0B); // amber-500
  static const Color errorColor = Color(0xFFDC2626); // red-600
  static const Color errorSoft = Color(0xFFFEF2F2);

  // Neutral Surfaces & Borders
  static const Color backgroundColor =
      Color(0xFFFAFBFD); // Website page background
  static const Color cardColor = Colors.white;
  static const Color surfaceMuted = Color(0xFFF8FAFC); // slate-50
  static const Color surfaceSecondary = Color(0xFFF1F5F9); // slate-100
  static const Color dividerColor = Color(0xFFE2E8F0); // slate-200
  static const Color borderSubtle = Color(0xFFF1F5F9);

  // Text Colors
  static const Color textPrimaryColor = Color(0xFF0F172A); // slate-900
  static const Color textSecondaryColor = Color(0xFF64748B); // slate-500
  static const Color textMutedColor = Color(0xFF94A3B8); // slate-400
  static const Color textPrimary = textPrimaryColor;
  static const Color textSecondary = textSecondaryColor;
  static const Color textMuted = textMutedColor;

  // Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [Color(0xFF0284C7), Color(0xFF0369A1)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  static const LinearGradient darkGradient = LinearGradient(
    colors: [Color(0xFF0F172A), Color(0xFF020617)],
    begin: Alignment.topLeft,
    end: Alignment.bottomRight,
  );

  // Modern Plus Jakarta Sans Text Theme (matching website typography)
  static TextTheme textTheme = TextTheme(
    displayLarge: GoogleFonts.plusJakartaSans(
      fontSize: 28,
      fontWeight: FontWeight.w800,
      color: textPrimaryColor,
      letterSpacing: -0.5,
    ),
    displayMedium: GoogleFonts.plusJakartaSans(
      fontSize: 24,
      fontWeight: FontWeight.w800,
      color: textPrimaryColor,
      letterSpacing: -0.5,
    ),
    displaySmall: GoogleFonts.plusJakartaSans(
      fontSize: 20,
      fontWeight: FontWeight.w700,
      color: textPrimaryColor,
      letterSpacing: -0.3,
    ),
    headlineMedium: GoogleFonts.plusJakartaSans(
      fontSize: 18,
      fontWeight: FontWeight.w700,
      color: textPrimaryColor,
      letterSpacing: -0.2,
    ),
    titleLarge: GoogleFonts.plusJakartaSans(
      fontSize: 16,
      fontWeight: FontWeight.w700,
      color: textPrimaryColor,
    ),
    titleMedium: GoogleFonts.plusJakartaSans(
      fontSize: 15,
      fontWeight: FontWeight.w600,
      color: textPrimaryColor,
    ),
    bodyLarge: GoogleFonts.plusJakartaSans(
      fontSize: 14,
      fontWeight: FontWeight.w500,
      color: textPrimaryColor,
    ),
    bodyMedium: GoogleFonts.plusJakartaSans(
      fontSize: 13,
      fontWeight: FontWeight.normal,
      color: textSecondaryColor,
    ),
    bodySmall: GoogleFonts.plusJakartaSans(
      fontSize: 11,
      fontWeight: FontWeight.w500,
      color: textMutedColor,
    ),
    labelLarge: GoogleFonts.plusJakartaSans(
      fontSize: 14,
      fontWeight: FontWeight.w700,
      color: Colors.white,
    ),
  );

  // App Theme
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      primaryColor: primaryColor,
      scaffoldBackgroundColor: backgroundColor,
      colorScheme: const ColorScheme.light(
        primary: primaryColor,
        secondary: secondaryColor,
        surface: cardColor,
        error: errorColor,
        onPrimary: Colors.white,
        onSecondary: Colors.white,
        onSurface: textPrimaryColor,
      ),
      textTheme: textTheme,
      appBarTheme: AppBarTheme(
        backgroundColor: cardColor,
        foregroundColor: textPrimaryColor,
        elevation: 0,
        scrolledUnderElevation: 0.5,
        centerTitle: false,
        titleTextStyle: GoogleFonts.plusJakartaSans(
          fontSize: 18,
          fontWeight: FontWeight.w800,
          color: textPrimaryColor,
          letterSpacing: -0.3,
        ),
        iconTheme: const IconThemeData(color: textPrimaryColor),
      ),
      cardTheme: CardThemeData(
        color: cardColor,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: dividerColor, width: 0.8),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: secondaryColor,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          elevation: 0,
          textStyle: GoogleFonts.plusJakartaSans(
            fontSize: 14,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: textPrimaryColor,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
          side: const BorderSide(color: dividerColor, width: 1.2),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          textStyle: GoogleFonts.plusJakartaSans(
            fontSize: 14,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surfaceMuted,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 14,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: dividerColor),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: dividerColor),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: primaryColor, width: 1.8),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: errorColor),
        ),
        labelStyle: GoogleFonts.plusJakartaSans(
            fontSize: 13, color: textSecondaryColor),
        hintStyle:
            GoogleFonts.plusJakartaSans(fontSize: 13, color: textMutedColor),
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: cardColor,
        selectedItemColor: primaryColor,
        unselectedItemColor: textMutedColor,
        type: BottomNavigationBarType.fixed,
        elevation: 0,
      ),
    );
  }
}
