import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:carousel_slider/carousel_slider.dart' as slider;
import 'package:provider/provider.dart';
import '../../models/product.dart';
import '../../models/review.dart';
import '../../services/product_service.dart';
import '../../providers/cart_provider.dart';
import '../../providers/auth_provider.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';

class ProductDetailsScreen extends StatefulWidget {
  final Product product;

  const ProductDetailsScreen({super.key, required this.product});

  @override
  State<ProductDetailsScreen> createState() => _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends State<ProductDetailsScreen> {
  final slider.CarouselSliderController _carouselController =
      slider.CarouselSliderController();
  int _quantity = 1;
  int _currentImageIndex = 0;
  List<Review> _reviews = [];
  bool _isLoadingReviews = false;
  bool _isAddingToCart = false;

  // Sizes & Colors
  String? _selectedSize;
  String? _selectedColor;
  List<String> _availableSizes = [];
  List<String> _availableColors = [];

  static const List<String> _tunisianGovernorates = [
    'Tunis',
    'Ariana',
    'Ben Arous',
    'Manouba',
    'Nabeul',
    'Zaghouan',
    'Bizerte',
    'Béja',
    'Jendouba',
    'Le Kef',
    'Siliana',
    'Sousse',
    'Monastir',
    'Mahdia',
    'Sfax',
    'Kairouan',
    'Kasserine',
    'Sidi Bouzid',
    'Gabès',
    'Medenine',
    'Tataouine',
    'Gafsa',
    'Tozeur',
    'Kebili'
  ];

  @override
  void initState() {
    super.initState();
    _parseSpecifications();
    _loadReviews();
  }

  void _parseSpecifications() {
    final specs = widget.product.specifications;
    if (specs == null) return;

    // Check for sizes
    for (var key in ['sizes', 'tailles', 'taille', 'size']) {
      if (specs.containsKey(key)) {
        final val = specs[key];
        if (val is List) {
          _availableSizes = val.map((e) => e.toString().trim()).toList();
        } else if (val is String && val.contains(',')) {
          _availableSizes = val.split(',').map((e) => e.trim()).toList();
        } else if (val is String && val.isNotEmpty) {
          _availableSizes = [val.trim()];
        }
        if (_availableSizes.isNotEmpty) {
          _selectedSize = _availableSizes.first;
        }
        break;
      }
    }

    // Check for colors
    for (var key in ['colors', 'couleurs', 'couleur', 'color']) {
      if (specs.containsKey(key)) {
        final val = specs[key];
        if (val is List) {
          _availableColors = val.map((e) => e.toString().trim()).toList();
        } else if (val is String && val.contains(',')) {
          _availableColors = val.split(',').map((e) => e.trim()).toList();
        } else if (val is String && val.isNotEmpty) {
          _availableColors = [val.trim()];
        }
        if (_availableColors.isNotEmpty) {
          _selectedColor = _availableColors.first;
        }
        break;
      }
    }
  }

  Future<void> _loadReviews() async {
    setState(() => _isLoadingReviews = true);
    try {
      final reviews = await ReviewService.getProductReviews(widget.product.id);
      if (mounted) {
        setState(() {
          _reviews = reviews;
          _isLoadingReviews = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoadingReviews = false);
      }
    }
  }

  void _showDirectOrderModal() {
    final authUser = context.read<AuthProvider>().user;
    final nameController = TextEditingController(text: authUser?.name ?? '');
    final phoneController = TextEditingController();
    final addressController = TextEditingController();
    final notesController = TextEditingController();
    String selectedGov = 'Tunis';
    final formKey = GlobalKey<FormState>();
    bool isSubmitting = false;

    final shippingPrice = widget.product.vendor?.shippingPrice ?? 0.0;
    final subtotal = widget.product.finalPrice * _quantity;
    final total = subtotal + shippingPrice;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (bottomContext) => StatefulBuilder(
        builder: (modalCtx, setModalState) => Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(modalCtx).viewInsets.bottom,
          ),
          child: SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
              child: Form(
                key: formKey,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Header
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(
                              Icons.flash_on_rounded,
                              color: Color(0xFFF59E0B),
                              size: 22,
                            ),
                            SizedBox(width: 8),
                            Text(
                              'Commande directe',
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.textPrimary,
                                letterSpacing: -0.3,
                              ),
                            ),
                          ],
                        ),
                        IconButton(
                          icon: const Icon(Icons.close, size: 20),
                          onPressed: () => Navigator.pop(context),
                        ),
                      ],
                    ),
                    const Text(
                      'Paiement à la livraison • Livraison express en Tunisie',
                      style: TextStyle(
                        fontSize: 12,
                        color: AppTheme.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Order summary card
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  widget.product.name,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w700,
                                    color: AppTheme.textPrimary,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 3),
                                Text(
                                  'Qté : $_quantity'
                                  '${_selectedSize != null ? ' • Taille: $_selectedSize' : ''}'
                                  '${_selectedColor != null ? ' • Couleur: $_selectedColor' : ''}',
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: AppTheme.textSecondary,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                '${total.toStringAsFixed(2)} DT',
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF059669),
                                ),
                              ),
                              Text(
                                shippingPrice > 0
                                    ? '(dont ${shippingPrice.toStringAsFixed(2)} DT livr.)'
                                    : 'Livraison gratuite',
                                style: const TextStyle(
                                  fontSize: 10,
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Nom & prénom
                    TextFormField(
                      controller: nameController,
                      decoration: const InputDecoration(
                        labelText: 'Nom et Prénom *',
                        prefixIcon: Icon(Icons.person_outline_rounded),
                      ),
                      validator: (val) => val == null || val.trim().isEmpty
                          ? 'Veuillez saisir votre nom complet'
                          : null,
                    ),
                    const SizedBox(height: 12),

                    // Téléphone
                    TextFormField(
                      controller: phoneController,
                      keyboardType: TextInputType.phone,
                      decoration: const InputDecoration(
                        labelText: 'Numéro de téléphone *',
                        hintText: 'Ex: 98 123 456 / 20 123 456',
                        prefixIcon: Icon(Icons.phone_outlined),
                      ),
                      validator: (val) => val == null || val.trim().length < 8
                          ? 'Numéro de téléphone valide requis'
                          : null,
                    ),
                    const SizedBox(height: 12),

                    // Gouvernorat Dropdown
                    DropdownButtonFormField<String>(
                      initialValue: selectedGov,
                      decoration: const InputDecoration(
                        labelText: 'Gouvernorat *',
                        prefixIcon: Icon(Icons.location_city_outlined),
                      ),
                      items: _tunisianGovernorates.map((gov) {
                        return DropdownMenuItem(
                          value: gov,
                          child: Text(gov),
                        );
                      }).toList(),
                      onChanged: (val) {
                        if (val != null) {
                          setModalState(() => selectedGov = val);
                        }
                      },
                    ),
                    const SizedBox(height: 12),

                    // Adresse exacte
                    TextFormField(
                      controller: addressController,
                      decoration: const InputDecoration(
                        labelText: 'Adresse de livraison détaillée *',
                        hintText: 'Rue, N°, Bâtiment, Quartier...',
                        prefixIcon: Icon(Icons.home_outlined),
                      ),
                      validator: (val) => val == null || val.trim().isEmpty
                          ? 'Adresse de livraison requise'
                          : null,
                    ),
                    const SizedBox(height: 12),

                    // Remarques
                    TextFormField(
                      controller: notesController,
                      decoration: const InputDecoration(
                        labelText: 'Remarques (Optionnel)',
                        hintText: 'Ex: Appeler avant d\'arriver...',
                        prefixIcon: Icon(Icons.note_outlined),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Big confirmation button
                    SizedBox(
                      width: double.infinity,
                      height: 52,
                      child: ElevatedButton.icon(
                        onPressed: isSubmitting
                            ? null
                            : () async {
                                if (!formKey.currentState!.validate()) return;
                                final messenger = ScaffoldMessenger.of(context);
                                setModalState(() => isSubmitting = true);

                                try {
                                  final order = await OrderService.directOrder(
                                    productId: widget.product.id,
                                    quantity: _quantity,
                                    customerName: nameController.text.trim(),
                                    phone: phoneController.text.trim(),
                                    shippingCity: selectedGov,
                                    shippingAddress:
                                        addressController.text.trim(),
                                    notes: notesController.text.trim(),
                                    size: _selectedSize,
                                    color: _selectedColor,
                                  );

                                  if (!mounted) return;
                                  Navigator.pop(
                                      bottomContext); // Close bottom sheet

                                  // Show confirmation dialog
                                  if (mounted) {
                                    showDialog(
                                      context: context,
                                      barrierDismissible: false,
                                      builder: (ctx) => AlertDialog(
                                        shape: RoundedRectangleBorder(
                                          borderRadius:
                                              BorderRadius.circular(20),
                                        ),
                                        content: Padding(
                                          padding: const EdgeInsets.symmetric(
                                              vertical: 8.0),
                                          child: Column(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Container(
                                                padding:
                                                    const EdgeInsets.all(16),
                                                decoration: BoxDecoration(
                                                  color: AppTheme.successColor
                                                      .withOpacity(0.12),
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
                                                'Votre commande directe a été envoyée au vendeur. Vous recevrez un appel pour confirmation.',
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
                                              onPressed: () =>
                                                  Navigator.pop(ctx),
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor:
                                                    AppTheme.primaryColor,
                                                foregroundColor: Colors.white,
                                                shape: RoundedRectangleBorder(
                                                  borderRadius:
                                                      BorderRadius.circular(12),
                                                ),
                                              ),
                                              child: const Text('Continuer'),
                                            ),
                                          ),
                                        ],
                                      ),
                                    );
                                  }
                                } catch (e) {
                                  setModalState(() => isSubmitting = false);
                                  if (!mounted) return;
                                  messenger.showSnackBar(
                                    SnackBar(
                                      content: Text('Erreur: $e'),
                                      backgroundColor: AppTheme.errorColor,
                                    ),
                                  );
                                }
                              },
                        icon: isSubmitting
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Icon(Icons.flash_on_rounded, size: 20),
                        label: Text(
                          isSubmitting
                              ? 'Confirmation...'
                              : 'Confirmer la commande directe',
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF059669), // Emerald
                          foregroundColor: Colors.white,
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(14),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final images =
        widget.product.images?.map((img) => img.imagePath).toList() ??
            (widget.product.image != null ? [widget.product.image!] : []);

    final formattedImages = images
        .map((img) => ApiConfig.getImageUrl(img))
        .where((url) => url.isNotEmpty)
        .toList();

    final hasImages = formattedImages.isNotEmpty;
    final stock = widget.product.stock;
    final inStock = widget.product.inStock && stock > 0;

    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      body: CustomScrollView(
        slivers: [
          // Silver App Bar with Image Carousel
          SliverAppBar(
            expandedHeight: 380,
            pinned: true,
            elevation: 0,
            backgroundColor: Colors.white,
            surfaceTintColor: Colors.transparent,
            leading: Padding(
              padding: const EdgeInsets.all(8.0),
              child: CircleAvatar(
                backgroundColor: Colors.white.withOpacity(0.9),
                child: IconButton(
                  icon: const Icon(
                    Icons.arrow_back_rounded,
                    color: AppTheme.textPrimary,
                    size: 20,
                  ),
                  onPressed: () => Navigator.pop(context),
                ),
              ),
            ),
            actions: [
              Padding(
                padding: const EdgeInsets.only(right: 8.0),
                child: CircleAvatar(
                  backgroundColor: Colors.white.withOpacity(0.9),
                  child: IconButton(
                    icon: const Icon(
                      Icons.share_outlined,
                      color: AppTheme.textPrimary,
                      size: 20,
                    ),
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Lien copié dans le presse-papier'),
                          duration: Duration(seconds: 2),
                        ),
                      );
                    },
                  ),
                ),
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Container(
                color: const Color(0xFFF8FAFC),
                child: Stack(
                  children: [
                    if (hasImages)
                      slider.CarouselSlider(
                        carouselController: _carouselController,
                        options: slider.CarouselOptions(
                          height: 420,
                          viewportFraction: 1.0,
                          enableInfiniteScroll: formattedImages.length > 1,
                          onPageChanged: (index, reason) {
                            setState(() => _currentImageIndex = index);
                          },
                        ),
                        items: formattedImages.map((imageUrl) {
                          return CachedNetworkImage(
                            imageUrl: imageUrl,
                            fit: BoxFit.cover,
                            width: double.infinity,
                            placeholder: (_, __) => const Center(
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                            errorWidget: (_, __, ___) => Container(
                              color: const Color(0xFFF1F5F9),
                              child: const Icon(
                                Icons.image_not_supported_rounded,
                                size: 64,
                                color: Color(0xFF94A3B8),
                              ),
                            ),
                          );
                        }).toList(),
                      )
                    else
                      Container(
                        color: const Color(0xFFF1F5F9),
                        child: const Center(
                          child: Icon(
                            Icons.image_not_supported_rounded,
                            size: 80,
                            color: Color(0xFF94A3B8),
                          ),
                        ),
                      ),

                    // Dot Indicators
                    if (formattedImages.length > 1)
                      Positioned(
                        bottom: 16,
                        left: 0,
                        right: 0,
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children:
                              formattedImages.asMap().entries.map((entry) {
                            final isCurrent = _currentImageIndex == entry.key;
                            return AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              width: isCurrent ? 20 : 6,
                              height: 6,
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(3),
                                color: isCurrent
                                    ? AppTheme.primaryColor
                                    : Colors.white.withOpacity(0.7),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.15),
                                    blurRadius: 4,
                                  ),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ),

          // Content body
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(16, 20, 16, 120),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Multi-image Thumbnail Strip
                  if (formattedImages.length > 1) ...[
                    SizedBox(
                      height: 60,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: formattedImages.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 8),
                        itemBuilder: (context, index) {
                          final isSelected = _currentImageIndex == index;
                          return GestureDetector(
                            onTap: () {
                              setState(() => _currentImageIndex = index);
                              _carouselController.animateToPage(index);
                            },
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              width: 60,
                              height: 60,
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: isSelected
                                      ? AppTheme.primaryColor
                                      : const Color(0xFFE2E8F0),
                                  width: isSelected ? 2.5 : 1,
                                ),
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: CachedNetworkImage(
                                  imageUrl: formattedImages[index],
                                  fit: BoxFit.cover,
                                  placeholder: (_, __) => Container(
                                    color: const Color(0xFFF1F5F9),
                                  ),
                                  errorWidget: (_, __, ___) => const Icon(
                                    Icons.image_not_supported_rounded,
                                    size: 20,
                                    color: Color(0xFF94A3B8),
                                  ),
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Badges Row (Vendor & Stock)
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 10,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: inStock
                              ? AppTheme.successColor.withOpacity(0.12)
                              : AppTheme.errorColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              inStock
                                  ? Icons.check_circle_rounded
                                  : Icons.cancel_rounded,
                              size: 14,
                              color: inStock
                                  ? AppTheme.successColor
                                  : AppTheme.errorColor,
                            ),
                            const SizedBox(width: 5),
                            Text(
                              inStock
                                  ? 'En stock ($stock dispo)'
                                  : 'Rupture de stock',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w700,
                                color: inStock
                                    ? AppTheme.successColor
                                    : AppTheme.errorColor,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Spacer(),

                      // Vendor pill
                      if (widget.product.vendor != null)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.storefront_rounded,
                                size: 14,
                                color: AppTheme.primaryColor,
                              ),
                              const SizedBox(width: 5),
                              Text(
                                widget.product.vendor?.shopName ?? 'Vendeur',
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                            ],
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Product Title
                  Text(
                    widget.product.name,
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                      height: 1.25,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Price and Discount row
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.baseline,
                    textBaseline: TextBaseline.alphabetic,
                    children: [
                      Text(
                        '${widget.product.finalPrice.toStringAsFixed(2)} DT',
                        style: const TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.w800,
                          color: AppTheme.primaryColor,
                          letterSpacing: -0.5,
                        ),
                      ),
                      if (widget.product.hasDiscount) ...[
                        const SizedBox(width: 10),
                        Text(
                          '${widget.product.price.toStringAsFixed(2)} DT',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF94A3B8),
                            decoration: TextDecoration.lineThrough,
                          ),
                        ),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEF4444),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            '-${widget.product.discountPercentage}%',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Size selection chips (Taille) if available
                  if (_availableSizes.isNotEmpty) ...[
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Taille / Pointure',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                              if (_selectedSize != null)
                                Text(
                                  'Sélection : $_selectedSize',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: AppTheme.primaryColor,
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: _availableSizes.map((size) {
                              final isSelected = _selectedSize == size;
                              return ChoiceChip(
                                label: Text(size),
                                selected: isSelected,
                                onSelected: (selected) {
                                  if (selected) {
                                    setState(() => _selectedSize = size);
                                  }
                                },
                                selectedColor:
                                    AppTheme.primaryLight.withOpacity(0.15),
                                backgroundColor: const Color(0xFFF1F5F9),
                                labelStyle: TextStyle(
                                  fontWeight: FontWeight.w700,
                                  color: isSelected
                                      ? AppTheme.primaryColor
                                      : AppTheme.textPrimary,
                                ),
                                side: BorderSide(
                                  color: isSelected
                                      ? AppTheme.primaryColor
                                      : const Color(0xFFCBD5E1),
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Color selection chips (Couleur) if available
                  if (_availableColors.isNotEmpty) ...[
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'Couleur',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w700,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                              if (_selectedColor != null)
                                Text(
                                  'Sélection : $_selectedColor',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: AppTheme.primaryColor,
                                  ),
                                ),
                            ],
                          ),
                          const SizedBox(height: 10),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: _availableColors.map((color) {
                              final isSelected = _selectedColor == color;
                              return ChoiceChip(
                                label: Text(color),
                                selected: isSelected,
                                onSelected: (selected) {
                                  if (selected) {
                                    setState(() => _selectedColor = color);
                                  }
                                },
                                selectedColor:
                                    AppTheme.primaryLight.withOpacity(0.15),
                                backgroundColor: const Color(0xFFF1F5F9),
                                labelStyle: TextStyle(
                                  fontWeight: FontWeight.w700,
                                  color: isSelected
                                      ? AppTheme.primaryColor
                                      : AppTheme.textPrimary,
                                ),
                                side: BorderSide(
                                  color: isSelected
                                      ? AppTheme.primaryColor
                                      : const Color(0xFFCBD5E1),
                                ),
                              );
                            }).toList(),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Trust guarantees card (matching website identity)
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      children: [
                        _buildTrustRow(
                          icon: Icons.flash_on_rounded,
                          iconColor: const Color(0xFFF59E0B),
                          title: 'Livraison express 24h - 48h',
                          subtitle: 'Expédition directe vers toute la Tunisie',
                        ),
                        const Divider(height: 16, color: Color(0xFFF1F5F9)),
                        _buildTrustRow(
                          icon: Icons.payments_outlined,
                          iconColor: const Color(0xFF10B981),
                          title: 'Paiement à la livraison',
                          subtitle: 'Payez en espèces à réception du colis',
                        ),
                        const Divider(height: 16, color: Color(0xFFF1F5F9)),
                        _buildTrustRow(
                          icon: Icons.verified_user_outlined,
                          iconColor: AppTheme.primaryColor,
                          title: 'Garantie conformité',
                          subtitle: 'Produit vérifié avant chaque expédition',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Quantity selection card
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 14,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Quantité',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w700,
                            color: AppTheme.textPrimary,
                          ),
                        ),
                        Row(
                          children: [
                            Container(
                              decoration: BoxDecoration(
                                color: const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                children: [
                                  IconButton(
                                    onPressed: _quantity > 1
                                        ? () => setState(() => _quantity--)
                                        : null,
                                    icon: const Icon(Icons.remove_rounded),
                                    iconSize: 20,
                                    color: _quantity > 1
                                        ? AppTheme.textPrimary
                                        : const Color(0xFFCBD5E1),
                                  ),
                                  Text(
                                    '$_quantity',
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w800,
                                      color: AppTheme.textPrimary,
                                    ),
                                  ),
                                  IconButton(
                                    onPressed: inStock && _quantity < stock
                                        ? () => setState(() => _quantity++)
                                        : null,
                                    icon: const Icon(Icons.add_rounded),
                                    iconSize: 20,
                                    color: inStock && _quantity < stock
                                        ? AppTheme.textPrimary
                                        : const Color(0xFFCBD5E1),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Description section
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Description du produit',
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w800,
                            color: AppTheme.textPrimary,
                            letterSpacing: -0.3,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          widget.product.description.isNotEmpty
                              ? widget.product.description
                              : 'Aucune description disponible pour cet article.',
                          style: const TextStyle(
                            fontSize: 14,
                            color: AppTheme.textSecondary,
                            height: 1.6,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Reviews section
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Text(
                              'Avis clients',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w800,
                                color: AppTheme.textPrimary,
                                letterSpacing: -0.3,
                              ),
                            ),
                            const Spacer(),
                            if (_reviews.isNotEmpty)
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color:
                                      AppTheme.primaryLight.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  '${_reviews.length} avis',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                    color: AppTheme.primaryColor,
                                  ),
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        if (_isLoadingReviews)
                          const Center(
                            child: Padding(
                              padding: EdgeInsets.all(16.0),
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                          )
                        else if (_reviews.isEmpty)
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 8),
                            child: Text(
                              'Soyez le premier à donner votre avis sur ce produit.',
                              style: TextStyle(
                                fontSize: 13,
                                color: AppTheme.textSecondary,
                              ),
                            ),
                          )
                        else
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _reviews.length.clamp(0, 3),
                            separatorBuilder: (_, __) => const Divider(
                              height: 16,
                              color: Color(0xFFF1F5F9),
                            ),
                            itemBuilder: (context, index) {
                              final review = _reviews[index];
                              return Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        review.user?.name ?? 'Client vérifié',
                                        style: const TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w700,
                                          color: AppTheme.textPrimary,
                                        ),
                                      ),
                                      const Spacer(),
                                      Row(
                                        children: List.generate(
                                          5,
                                          (i) => Icon(
                                            i < review.rating
                                                ? Icons.star_rounded
                                                : Icons.star_outline_rounded,
                                            size: 16,
                                            color: const Color(0xFFF59E0B),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  if (review.comment != null &&
                                      review.comment!.isNotEmpty) ...[
                                    const SizedBox(height: 4),
                                    Text(
                                      review.comment!,
                                      style: const TextStyle(
                                        fontSize: 13,
                                        color: AppTheme.textSecondary,
                                      ),
                                    ),
                                  ],
                                ],
                              );
                            },
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),

      // Sticky Dual Action Bottom Bar
      bottomNavigationBar: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
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
              // Price block
              Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Total',
                    style: TextStyle(
                      fontSize: 11,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                  Text(
                    '${(widget.product.finalPrice * _quantity).toStringAsFixed(2)} DT',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w800,
                      color: AppTheme.textPrimary,
                      letterSpacing: -0.5,
                    ),
                  ),
                ],
              ),
              const SizedBox(width: 12),

              // Button 1: Add to cart
              Consumer<CartProvider>(
                builder: (context, cartProvider, child) {
                  return SizedBox(
                    height: 48,
                    child: OutlinedButton(
                      onPressed: inStock && !_isAddingToCart
                          ? () async {
                              setState(() => _isAddingToCart = true);
                              final success = await cartProvider.addToCart(
                                productId: widget.product.id,
                                quantity: _quantity,
                                size: _selectedSize,
                                color: _selectedColor,
                                product: widget.product,
                              );
                              if (mounted) {
                                setState(() => _isAddingToCart = false);
                                ScaffoldMessenger.of(context).showSnackBar(
                                  SnackBar(
                                    content: Text(
                                      success
                                          ? 'Ajouté au panier !'
                                          : cartProvider.error ??
                                              'Erreur lors de l\'ajout',
                                    ),
                                    backgroundColor: success
                                        ? AppTheme.successColor
                                        : AppTheme.errorColor,
                                    behavior: SnackBarBehavior.floating,
                                  ),
                                );
                              }
                            }
                          : null,
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(
                          color: AppTheme.primaryColor,
                          width: 1.5,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                      ),
                      child: _isAddingToCart
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: AppTheme.primaryColor,
                              ),
                            )
                          : const Icon(
                              Icons.add_shopping_cart_rounded,
                              color: AppTheme.primaryColor,
                              size: 20,
                            ),
                    ),
                  );
                },
              ),
              const SizedBox(width: 8),

              // Button 2: Direct Buy (Commander directement)
              Expanded(
                child: SizedBox(
                  height: 48,
                  child: ElevatedButton.icon(
                    onPressed: inStock ? _showDirectOrderModal : null,
                    icon: const Icon(Icons.flash_on_rounded, size: 18),
                    label: Text(
                      inStock ? 'Acheter direct' : 'Épuisé',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669), // Emerald
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
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

  Widget _buildTrustRow({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String subtitle,
  }) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.12),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, size: 18, color: iconColor),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w700,
                  color: AppTheme.textPrimary,
                ),
              ),
              Text(
                subtitle,
                style: const TextStyle(
                  fontSize: 11,
                  color: AppTheme.textSecondary,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
