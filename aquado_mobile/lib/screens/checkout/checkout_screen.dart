import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../providers/cart_provider.dart';
import '../../services/product_service.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';

class CheckoutScreen extends StatefulWidget {
  final int? vendorId;
  final List<dynamic>? checkoutItems;

  const CheckoutScreen({super.key, this.vendorId, this.checkoutItems});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final _addressController = TextEditingController();
  final _cityController = TextEditingController();
  final _postalCodeController = TextEditingController();
  final _countryController = TextEditingController(text: 'Tunisie');
  final _phoneController = TextEditingController();
  final _notesController = TextEditingController();

  String _paymentMethod = 'cash_on_delivery';
  bool _isProcessing = false;

  @override
  void dispose() {
    _addressController.dispose();
    _cityController.dispose();
    _postalCodeController.dispose();
    _countryController.dispose();
    _phoneController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _placeOrder() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isProcessing = true);

    try {
      final order = await OrderService.createOrder(
        shippingAddress: _addressController.text.trim(),
        shippingCity: _cityController.text.trim(),
        shippingPostalCode: _postalCodeController.text.trim(),
        shippingCountry: _countryController.text.trim(),
        phone: _phoneController.text.trim(),
        paymentMethod: _paymentMethod,
        notes: _notesController.text.trim(),
        vendorId: widget.vendorId,
      );

      if (mounted) {
        // Reload cart
        await context.read<CartProvider>().loadCart();
        if (!mounted) return;

        // Show modern success dialog
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
            content: Padding(
              padding: const EdgeInsets.symmetric(vertical: 8.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppTheme.successColor.withOpacity(0.12),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.check_circle_rounded,
                      color: AppTheme.successColor,
                      size: 56,
                    ),
                  ),
                  const SizedBox(height: 18),
                  const Text(
                    'Commande confirmée !',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'N° #${order.orderNumber}',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.primaryColor,
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'Merci pour votre confiance. Le vendeur prépare votre colis pour expédition rapide.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 13,
                      color: AppTheme.textSecondary,
                      height: 1.4,
                    ),
                  ),
                ],
              ),
            ),
            actions: [
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).popUntil((route) => route.isFirst);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  child: const Text('Continuer mes achats'),
                ),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isProcessing = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cartProvider = context.watch<CartProvider>();
    final items = widget.checkoutItems ?? cartProvider.items;

    // Group items by vendor
    final Map<int, List<dynamic>> groupedItems = {};
    for (var item in items) {
      final product = item.product;
      if (product?.vendor != null) {
        final vendorId = product!.vendor!.id;
        if (!groupedItems.containsKey(vendorId)) {
          groupedItems[vendorId] = [];
        }
        groupedItems[vendorId]!.add(item);
      } else {
        if (!groupedItems.containsKey(-1)) {
          groupedItems[-1] = [];
        }
        groupedItems[-1]!.add(item);
      }
    }

    // Calculate totals
    double subtotal = 0.0;
    double totalShippingCost = 0.0;

    groupedItems.forEach((vendorId, vendorItems) {
      for (var item in vendorItems) {
        subtotal += (item.price * item.quantity);
      }
      if (vendorId != -1 && vendorItems.isNotEmpty) {
        totalShippingCost +=
            vendorItems.first.product?.vendor?.shippingPrice ?? 0.0;
      }
    });

    final total = subtotal + totalShippingCost;

    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: const Text(
          'Finaliser ma commande',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w800,
            letterSpacing: -0.5,
          ),
        ),
        elevation: 0,
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.transparent,
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Shipping Address Card
              _buildSectionCard(
                title: 'Adresse de livraison',
                icon: Icons.local_shipping_outlined,
                children: [
                  TextFormField(
                    controller: _addressController,
                    decoration: const InputDecoration(
                      labelText: 'Adresse exacte (Rue, N°, Bâtiment)',
                      prefixIcon: Icon(Icons.home_outlined),
                    ),
                    validator: (value) => value?.isEmpty == true
                        ? 'Veuillez saisir votre adresse'
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: _cityController,
                          decoration: const InputDecoration(
                            labelText: 'Ville / Région',
                            prefixIcon: Icon(Icons.location_city_outlined),
                          ),
                          validator: (value) =>
                              value?.isEmpty == true ? 'Ville requise' : null,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: TextFormField(
                          controller: _postalCodeController,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                            labelText: 'Code Postal',
                          ),
                          validator: (value) =>
                              value?.isEmpty == true ? 'Requis' : null,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _phoneController,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'Numéro de téléphone portable',
                      prefixIcon: Icon(Icons.phone_outlined),
                    ),
                    validator: (value) => value?.isEmpty == true
                        ? 'Numéro de téléphone requis'
                        : null,
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _notesController,
                    maxLines: 2,
                    decoration: const InputDecoration(
                      labelText: 'Remarques de livraison (Optionnel)',
                      hintText: 'Précisions pour le livreur...',
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Payment Method Card
              _buildSectionCard(
                title: 'Mode de paiement',
                icon: Icons.payment_outlined,
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryLight.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: AppTheme.primaryLight.withOpacity(0.3)),
                    ),
                    child: Row(
                      children: [
                        Radio<String>(
                          value: 'cash_on_delivery',
                          groupValue: _paymentMethod,
                          activeColor: AppTheme.primaryColor,
                          onChanged: (val) =>
                              setState(() => _paymentMethod = val!),
                        ),
                        const SizedBox(width: 4),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Paiement en espèces à la livraison (COD)',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                              SizedBox(height: 2),
                              Text(
                                'Réglez votre commande directement auprès du transporteur.',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Order Summary Card
              _buildSectionCard(
                title: 'Récapitulatif de la commande',
                icon: Icons.receipt_outlined,
                children: [
                  ...groupedItems.entries.map((entry) {
                    final vendorItems = entry.value;
                    if (vendorItems.isEmpty) return const SizedBox.shrink();

                    final firstItem = vendorItems.first;
                    final vendor = firstItem.product?.vendor;
                    final vendorName = vendor?.shopName ?? 'Articles divers';
                    final vendorShipping = vendor?.shippingPrice ?? 0.0;

                    return Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.storefront_rounded,
                                  size: 16, color: AppTheme.primaryColor),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  vendorName,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w700,
                                    fontSize: 13,
                                    color: AppTheme.textPrimary,
                                  ),
                                ),
                              ),
                              Text(
                                vendorShipping > 0
                                    ? 'Livraison : ${vendorShipping.toStringAsFixed(2)} DT'
                                    : 'Livraison gratuite',
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                          const Divider(height: 16, color: Color(0xFFE2E8F0)),
                          ...vendorItems.map((item) {
                            final product = item.product;
                            final imageUrl =
                                ApiConfig.getImageUrl(product?.image);
                            return Padding(
                              padding: const EdgeInsets.symmetric(vertical: 4),
                              child: Row(
                                children: [
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(6),
                                    child: Container(
                                      width: 38,
                                      height: 38,
                                      color: Colors.white,
                                      child: imageUrl.isNotEmpty
                                          ? CachedNetworkImage(
                                              imageUrl: imageUrl,
                                              fit: BoxFit.cover,
                                              errorWidget: (_, __, ___) =>
                                                  const Icon(Icons.image,
                                                      size: 16),
                                            )
                                          : const Icon(Icons.image, size: 16),
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Text(
                                      '${item.quantity}x ${product?.name ?? "Article"}',
                                      style: const TextStyle(
                                        fontSize: 13,
                                        fontWeight: FontWeight.w600,
                                        color: AppTheme.textPrimary,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                  Text(
                                    '${(item.price * item.quantity).toStringAsFixed(2)} DT',
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.w700,
                                      color: AppTheme.primaryColor,
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                        ],
                      ),
                    );
                  }),
                  const SizedBox(height: 8),
                  _buildSummaryLine('Sous-total articles',
                      '${subtotal.toStringAsFixed(2)} DT'),
                  const SizedBox(height: 6),
                  _buildSummaryLine(
                    'Frais de livraison',
                    totalShippingCost > 0
                        ? '${totalShippingCost.toStringAsFixed(2)} DT'
                        : 'Gratuit',
                  ),
                  const Divider(height: 20, color: Color(0xFFE2E8F0)),
                  _buildSummaryLine(
                    'Total à payer',
                    '${total.toStringAsFixed(2)} DT',
                    isTotal: true,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          border: const Border(
            top: BorderSide(color: Color(0xFFE2E8F0)),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 16,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: SafeArea(
          child: Row(
            children: [
              Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Total TTC',
                    style: TextStyle(
                      fontSize: 11,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                  Text(
                    '${total.toStringAsFixed(2)} DT',
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                      letterSpacing: -0.5,
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 16),
              Expanded(
                child: SizedBox(
                  height: 48,
                  child: ElevatedButton(
                    onPressed: _isProcessing ? null : _placeOrder,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryColor,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _isProcessing
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Text(
                            'Confirmer la commande',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionCard({
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: AppTheme.primaryColor),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w800,
                  color: AppTheme.textPrimary,
                  letterSpacing: -0.3,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          ...children,
        ],
      ),
    );
  }

  Widget _buildSummaryLine(String label, String value, {bool isTotal = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: isTotal ? 15 : 13,
            fontWeight: isTotal ? FontWeight.w800 : FontWeight.w500,
            color: isTotal ? AppTheme.textPrimary : AppTheme.textSecondary,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: isTotal ? 17 : 13,
            fontWeight: isTotal ? FontWeight.w800 : FontWeight.w700,
            color: isTotal ? AppTheme.primaryColor : AppTheme.textPrimary,
          ),
        ),
      ],
    );
  }
}
