import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/product.dart';
import '../../services/product_service.dart';
import '../../widgets/product_card.dart';

class CategoryProductsScreen extends StatefulWidget {
  final Category category;
  final String? initialSearch;

  const CategoryProductsScreen({
    super.key,
    required this.category,
    this.initialSearch,
  });

  @override
  State<CategoryProductsScreen> createState() => _CategoryProductsScreenState();
}

class _CategoryProductsScreenState extends State<CategoryProductsScreen> {
  bool _isLoading = true;
  List<Product> _products = [];
  String _sortBy = 'created_at';
  String _sortOrder = 'desc';
  String? _searchQuery;
  double? _minPrice;
  double? _maxPrice;
  final TextEditingController _minPriceController = TextEditingController();
  final TextEditingController _maxPriceController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _searchQuery = widget.initialSearch;
    _loadProducts();
  }

  @override
  void dispose() {
    _minPriceController.dispose();
    _maxPriceController.dispose();
    super.dispose();
  }

  Future<void> _loadProducts() async {
    setState(() => _isLoading = true);

    try {
      final products = await ProductService.getProducts(
        category: widget.category.slug.isNotEmpty ? widget.category.slug : null,
        search: _searchQuery,
        minPrice: _minPrice,
        maxPrice: _maxPrice,
        sortBy: _sortBy,
        sortOrder: _sortOrder,
        perPage: 30,
      );

      if (mounted) {
        setState(() {
          _products = products;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _showSortOptions() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 12),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Trier par',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textPrimaryColor,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, size: 20),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
              ),
              const Divider(height: 1),
              _sortTile('Plus récents', 'created_at', 'desc'),
              _sortTile('Prix: Moins cher au plus cher', 'price', 'asc'),
              _sortTile('Prix: Plus cher au moins cher', 'price', 'desc'),
              _sortTile('Nom: A à Z', 'name', 'asc'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _sortTile(String title, String sortBy, String sortOrder) {
    final isSelected = _sortBy == sortBy && _sortOrder == sortOrder;
    return ListTile(
      title: Text(
        title,
        style: TextStyle(
          fontSize: 13.5,
          fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
          color: isSelected ? AppTheme.primaryColor : AppTheme.textPrimaryColor,
        ),
      ),
      trailing: isSelected
          ? const Icon(Icons.check_rounded, color: AppTheme.primaryColor)
          : null,
      onTap: () {
        setState(() {
          _sortBy = sortBy;
          _sortOrder = sortOrder;
        });
        Navigator.pop(context);
        _loadProducts();
      },
    );
  }

  void _showPriceFilterSheet() {
    _minPriceController.text = _minPrice?.toStringAsFixed(0) ?? '';
    _maxPriceController.text = _maxPrice?.toStringAsFixed(0) ?? '';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
        padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Filtrer par prix (DT)',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.textPrimaryColor,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close, size: 20),
                      onPressed: () => Navigator.pop(context),
                    ),
                  ],
                ),
                const SizedBox(height: 12),

                // Min & Max inputs
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _minPriceController,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                          labelText: 'Min (DT)',
                          hintText: '0',
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: TextField(
                        controller: _maxPriceController,
                        keyboardType: TextInputType.number,
                        decoration: const InputDecoration(
                          labelText: 'Max (DT)',
                          hintText: '500',
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Quick Budget Chips
                const Text(
                  'Budgets suggérés :',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                    color: AppTheme.textSecondaryColor,
                  ),
                ),
                const SizedBox(height: 8),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _budgetChip('< 20 DT', null, 20),
                    _budgetChip('20 - 50 DT', 20, 50),
                    _budgetChip('50 - 100 DT', 50, 100),
                    _budgetChip('100 - 200 DT', 100, 200),
                    _budgetChip('> 200 DT', 200, null),
                  ],
                ),
                const SizedBox(height: 20),

                // Action Buttons
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () {
                          setState(() {
                            _minPrice = null;
                            _maxPrice = null;
                          });
                          Navigator.pop(context);
                          _loadProducts();
                        },
                        child: const Text('Effacer'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: () {
                          final minVal =
                              double.tryParse(_minPriceController.text.trim());
                          final maxVal =
                              double.tryParse(_maxPriceController.text.trim());
                          setState(() {
                            _minPrice = minVal;
                            _maxPrice = maxVal;
                          });
                          Navigator.pop(context);
                          _loadProducts();
                        },
                        child: const Text('Appliquer'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _budgetChip(String label, double? min, double? max) {
    return InkWell(
      onTap: () {
        _minPriceController.text = min?.toStringAsFixed(0) ?? '';
        _maxPriceController.text = max?.toStringAsFixed(0) ?? '';
      },
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: AppTheme.surfaceMuted,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: AppTheme.dividerColor),
        ),
        child: Text(
          label,
          style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w600,
            color: AppTheme.textPrimaryColor,
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final title = widget.category.name.isNotEmpty
        ? widget.category.name
        : (_searchQuery != null ? 'Recherche: "$_searchQuery"' : 'Catalogue');

    final hasActiveFilter =
        _minPrice != null || _maxPrice != null || _searchQuery != null;

    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        title: Text(
          title,
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w800),
        ),
        actions: [
          IconButton(
            icon: Stack(
              clipBehavior: Clip.none,
              children: [
                const Icon(Icons.filter_list_rounded, size: 22),
                if (_minPrice != null || _maxPrice != null)
                  Positioned(
                    top: -2,
                    right: -2,
                    child: Container(
                      width: 8,
                      height: 8,
                      decoration: const BoxDecoration(
                        color: AppTheme.primaryColor,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
              ],
            ),
            tooltip: 'Filtrer par prix',
            onPressed: _showPriceFilterSheet,
          ),
          IconButton(
            icon: const Icon(Icons.swap_vert_rounded, size: 22),
            tooltip: 'Trier',
            onPressed: _showSortOptions,
          ),
        ],
      ),
      body: Column(
        children: [
          // Active Filters Strip
          if (hasActiveFilter)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: Colors.white,
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    if (_minPrice != null || _maxPrice != null)
                      Container(
                        margin: const EdgeInsets.only(right: 8),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppTheme.primarySoft,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(
                              color: AppTheme.primaryColor.withOpacity(0.3)),
                        ),
                        child: Row(
                          children: [
                            Text(
                              'Prix: ${_minPrice?.toStringAsFixed(0) ?? '0'} - ${_maxPrice != null ? '${_maxPrice!.toStringAsFixed(0)} DT' : 'Max'}',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: AppTheme.primaryColor,
                              ),
                            ),
                            const SizedBox(width: 4),
                            InkWell(
                              onTap: () {
                                setState(() {
                                  _minPrice = null;
                                  _maxPrice = null;
                                });
                                _loadProducts();
                              },
                              child: const Icon(Icons.close,
                                  size: 14, color: AppTheme.primaryColor),
                            ),
                          ],
                        ),
                      ),
                    if (_searchQuery != null && _searchQuery!.isNotEmpty)
                      Container(
                        margin: const EdgeInsets.only(right: 8),
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: AppTheme.surfaceSecondary,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            Text(
                              'Mot-clé: "$_searchQuery"',
                              style: const TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(width: 4),
                            InkWell(
                              onTap: () {
                                setState(() => _searchQuery = null);
                                _loadProducts();
                              },
                              child: const Icon(Icons.close, size: 14),
                            ),
                          ],
                        ),
                      ),
                    TextButton(
                      onPressed: () {
                        setState(() {
                          _minPrice = null;
                          _maxPrice = null;
                          _searchQuery = null;
                        });
                        _loadProducts();
                      },
                      style: TextButton.styleFrom(padding: EdgeInsets.zero),
                      child: const Text('Tout effacer',
                          style: TextStyle(
                              fontSize: 11, color: AppTheme.errorColor)),
                    ),
                  ],
                ),
              ),
            ),

          // Product List Grid
          Expanded(
            child: RefreshIndicator(
              color: AppTheme.primaryColor,
              onRefresh: _loadProducts,
              child: _isLoading
                  ? const Center(
                      child: CircularProgressIndicator(
                          color: AppTheme.primaryColor),
                    )
                  : _products.isEmpty
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(32),
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(20),
                                  decoration: BoxDecoration(
                                    color: AppTheme.surfaceMuted,
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(
                                    Icons.search_off_rounded,
                                    size: 48,
                                    color: AppTheme.textMutedColor,
                                  ),
                                ),
                                const SizedBox(height: 16),
                                const Text(
                                  'Aucun produit trouvé',
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.w800,
                                    color: AppTheme.textPrimaryColor,
                                  ),
                                ),
                                const SizedBox(height: 6),
                                const Text(
                                  'Essayez d\'ajuster vos filtres de prix ou votre recherche.',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: AppTheme.textSecondaryColor,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                                if (hasActiveFilter) ...[
                                  const SizedBox(height: 16),
                                  ElevatedButton(
                                    onPressed: () {
                                      setState(() {
                                        _minPrice = null;
                                        _maxPrice = null;
                                        _searchQuery = null;
                                      });
                                      _loadProducts();
                                    },
                                    child:
                                        const Text('Réinitialiser les filtres'),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        )
                      : GridView.builder(
                          padding: const EdgeInsets.all(16),
                          gridDelegate:
                              const SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: 2,
                            childAspectRatio: 0.58,
                            crossAxisSpacing: 12,
                            mainAxisSpacing: 12,
                          ),
                          itemCount: _products.length,
                          itemBuilder: (context, index) {
                            return ProductCard(product: _products[index]);
                          },
                        ),
            ),
          ),
        ],
      ),
    );
  }
}
