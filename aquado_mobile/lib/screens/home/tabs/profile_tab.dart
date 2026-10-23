import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../providers/auth_provider.dart';
import '../../../config/theme.dart';
import '../../orders/orders_screen.dart';

class ProfileTab extends StatelessWidget {
  const ProfileTab({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Mon Profil',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w800,
            letterSpacing: -0.5,
          ),
        ),
        elevation: 0,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
      ),
      body: Consumer<AuthProvider>(
        builder: (context, authProvider, child) {
          final user = authProvider.user;
          final isAuthenticated = authProvider.isAuthenticated;
          final isVendor = authProvider.isVendor;

          return SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
            child: Column(
              children: [
                // Profile Header Card
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.04),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Row(
                    children: [
                      // Avatar
                      Container(
                        width: 64,
                        height: 64,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: AppTheme.primaryLight.withOpacity(0.15),
                          border: Border.all(
                            color: AppTheme.primaryLight.withOpacity(0.3),
                            width: 2,
                          ),
                        ),
                        child: user?.photo != null
                            ? ClipOval(
                                child: Image.network(
                                  user!.photo!,
                                  width: 64,
                                  height: 64,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => const Icon(
                                    Icons.person_rounded,
                                    size: 34,
                                    color: AppTheme.primaryColor,
                                  ),
                                ),
                              )
                            : const Icon(
                                Icons.person_rounded,
                                size: 34,
                                color: AppTheme.primaryColor,
                              ),
                      ),
                      const SizedBox(width: 16),

                      // User Details
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              isAuthenticated
                                  ? (user?.name ?? 'Utilisateur')
                                  : 'Bienvenue sur AquawaSouk',
                              style: const TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.textPrimary,
                                letterSpacing: -0.3,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Text(
                              isAuthenticated
                                  ? (user?.email ?? '')
                                  : 'Connectez-vous pour profiter de toutes vos fonctionnalités',
                              style: const TextStyle(
                                fontSize: 13,
                                color: AppTheme.textSecondary,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            if (isAuthenticated && isVendor) ...[
                              const SizedBox(height: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color:
                                      AppTheme.primaryLight.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: const Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.storefront_rounded,
                                      size: 14,
                                      color: AppTheme.primaryColor,
                                    ),
                                    SizedBox(width: 4),
                                    Text(
                                      'Compte Vendeur',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w700,
                                        color: AppTheme.primaryColor,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // If not authenticated, login CTA button
                if (!isAuthenticated) ...[
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pushNamed(context, '/login');
                      },
                      icon: const Icon(Icons.login_rounded, size: 18),
                      label: const Text('Connexion / Inscription'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],

                // Menu items container
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    children: [
                      if (isAuthenticated) ...[
                        _buildMenuItem(
                          context,
                          icon: Icons.receipt_long_rounded,
                          iconColor: AppTheme.primaryColor,
                          title: 'Mes Commandes',
                          subtitle: 'Suivre l\'état de vos colis et livraisons',
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => const OrdersScreen(),
                              ),
                            );
                          },
                        ),
                        const Divider(height: 1, color: Color(0xFFF1F5F9)),
                      ],
                      _buildMenuItem(
                        context,
                        icon: Icons.shield_outlined,
                        iconColor: const Color(0xFF10B981),
                        title: 'Livraison & Paiement',
                        subtitle:
                            'Paiement à la livraison sur toute la Tunisie',
                        onTap: () {
                          showDialog(
                            context: context,
                            builder: (context) => AlertDialog(
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              title: const Text('Livraison & Paiement'),
                              content: const Text(
                                '• Livraison dans les 24h à 48h ouvrées.\n• Paiement en espèces à la livraison (COD).\n• Service client disponible 7j/7.',
                              ),
                              actions: [
                                TextButton(
                                  onPressed: () => Navigator.pop(context),
                                  child: const Text('Fermer'),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                      const Divider(height: 1, color: Color(0xFFF1F5F9)),
                      _buildMenuItem(
                        context,
                        icon: Icons.headset_mic_outlined,
                        iconColor: const Color(0xFF6366F1),
                        title: 'Support & Assistance',
                        subtitle: 'Besoin d\'aide pour votre commande ?',
                        onTap: () {
                          showDialog(
                            context: context,
                            builder: (context) => AlertDialog(
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(16),
                              ),
                              title: const Text('Centre d\'assistance'),
                              content: const Text(
                                'Pour toute question concernant votre commande ou réclamation, vous pouvez contacter le support AquawaSouk via notre plateforme web ou nous appeler directement.',
                              ),
                              actions: [
                                TextButton(
                                  onPressed: () => Navigator.pop(context),
                                  child: const Text('Compris'),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                      if (isAuthenticated) ...[
                        const Divider(height: 1, color: Color(0xFFF1F5F9)),
                        _buildMenuItem(
                          context,
                          icon: Icons.logout_rounded,
                          iconColor: AppTheme.errorColor,
                          title: 'Se déconnecter',
                          subtitle: 'Fermer la session actuelle',
                          titleColor: AppTheme.errorColor,
                          onTap: () async {
                            final confirmed = await showDialog<bool>(
                              context: context,
                              builder: (context) => AlertDialog(
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                                title: const Text(
                                  'Déconnexion',
                                  style: TextStyle(fontWeight: FontWeight.w700),
                                ),
                                content: const Text(
                                  'Êtes-vous sûr de vouloir vous déconnecter ?',
                                ),
                                actions: [
                                  TextButton(
                                    onPressed: () =>
                                        Navigator.pop(context, false),
                                    child: const Text('Annuler'),
                                  ),
                                  ElevatedButton(
                                    onPressed: () =>
                                        Navigator.pop(context, true),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: AppTheme.errorColor,
                                      foregroundColor: Colors.white,
                                    ),
                                    child: const Text('Déconnexion'),
                                  ),
                                ],
                              ),
                            );

                            if (confirmed == true) {
                              await authProvider.logout();
                            }
                          },
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 32),

                // App version & branding
                Text(
                  'AquawaSouk • Version 1.0.0',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Colors.grey[400],
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Place de marché multi-vendeurs en Tunisie',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey[400],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildMenuItem(
    BuildContext context, {
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
    Color? titleColor,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 20, color: iconColor),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: titleColor ?? AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.chevron_right_rounded,
                size: 20,
                color: Color(0xFF94A3B8),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
