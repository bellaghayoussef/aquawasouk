import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/admin_vendor.dart';
import '../../services/admin_vendor_service.dart';

class AdminVendorsScreen extends StatefulWidget {
  const AdminVendorsScreen({super.key});

  @override
  State<AdminVendorsScreen> createState() => _AdminVendorsScreenState();
}

class _AdminVendorsScreenState extends State<AdminVendorsScreen> {
  String _selectedStatus = 'all'; // 'all', 'pending', 'active', 'inactive'
  final TextEditingController _searchController = TextEditingController();

  List<AdminVendor> _vendors = [];
  Map<String, int> _stats = {
    'total': 0,
    'pending': 0,
    'active': 0,
    'inactive': 0
  };
  bool _isLoading = true;
  String? _errorMessage;
  int _currentPage = 1;
  int _lastPage = 1;
  bool _isActionLoading = false;

  @override
  void initState() {
    super.initState();
    _loadVendors(refresh: true);
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadVendors({bool refresh = false}) async {
    if (refresh) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
        _currentPage = 1;
      });
    }

    try {
      final res = await AdminVendorService.fetchVendors(
        status: _selectedStatus,
        search: _searchController.text.trim(),
        page: _currentPage,
      );

      if (!mounted) return;

      setState(() {
        _vendors = res['vendors'] as List<AdminVendor>;
        _stats = res['stats'] as Map<String, int>;
        _lastPage = res['lastPage'] as int;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  Future<void> _updateVendorStatus(AdminVendor vendor, String newStatus) async {
    // Confirmation dialog
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Icon(
              newStatus == 'active'
                  ? Icons.check_circle_rounded
                  : (newStatus == 'inactive'
                      ? Icons.cancel_rounded
                      : Icons.help_outline_rounded),
              color: newStatus == 'active'
                  ? const Color(0xFF10B981)
                  : (newStatus == 'inactive'
                      ? const Color(0xFFEF4444)
                      : const Color(0xFFF59E0B)),
            ),
            const SizedBox(width: 10),
            Text(
              newStatus == 'active'
                  ? 'Approuver la boutique'
                  : (newStatus == 'inactive'
                      ? 'Désactiver la boutique'
                      : 'Mettre en attente'),
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ],
        ),
        content: Text(
          newStatus == 'active'
              ? 'Voulez-vous approuver la boutique "${vendor.shopName}" ? Ses produits deviendront visibles publiquement sur AquawaSouk.'
              : (newStatus == 'inactive'
                  ? 'Voulez-vous désactiver la boutique "${vendor.shopName}" ? Ses produits ne seront plus achetables.'
                  : 'Voulez-vous remettre la boutique "${vendor.shopName}" en statut "en attente" ?'),
          style: const TextStyle(fontSize: 13, color: Color(0xFF475569)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Annuler',
                style: TextStyle(color: Color(0xFF64748B))),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: newStatus == 'active'
                  ? const Color(0xFF10B981)
                  : (newStatus == 'inactive'
                      ? const Color(0xFFEF4444)
                      : const Color(0xFFF59E0B)),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(
              newStatus == 'active' ? 'Oui, Approuver' : 'Confirmer',
              style: const TextStyle(
                  color: Colors.white, fontWeight: FontWeight.bold),
            ),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _isActionLoading = true);

    try {
      final result = await AdminVendorService.updateStatus(
        vendorId: vendor.id,
        status: newStatus,
      );

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle_rounded, color: Colors.white),
              const SizedBox(width: 10),
              Expanded(child: Text(result['message'] as String)),
            ],
          ),
          backgroundColor: newStatus == 'active'
              ? const Color(0xFF10B981)
              : const Color(0xFF1E293B),
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );

      // Refresh list
      await _loadVendors(refresh: true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content:
              Text('Erreur: ${e.toString().replaceAll('Exception: ', '')}'),
          backgroundColor: const Color(0xFFEF4444),
        ),
      );
    } finally {
      if (mounted) setState(() => _isActionLoading = false);
    }
  }

  void _showVendorDetails(AdminVendor vendor) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _VendorDetailsSheet(
        vendor: vendor,
        onUpdateStatus: (newStatus) {
          Navigator.pop(ctx);
          _updateVendorStatus(vendor, newStatus);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.white,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Gestion des Boutiques',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: Color(0xFF0F172A),
                letterSpacing: -0.5,
              ),
            ),
            Row(
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: const BoxDecoration(
                    color: Color(0xFF10B981),
                    shape: BoxShape.circle,
                  ),
                ),
                const SizedBox(width: 6),
                const Text(
                  'Panneau Administrateur',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF64748B),
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Actualiser',
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF0284C7)),
            onPressed: () => _loadVendors(refresh: true),
          ),
        ],
      ),
      body: Stack(
        children: [
          Column(
            children: [
              // 1. KPI Stats Summary Cards
              _buildStatsBar(),

              // 2. Search & Filter Bar
              _buildSearchBar(),

              // 3. Tab Filter Chips
              _buildFilterChips(),

              const SizedBox(height: 8),

              // 4. Vendors List
              Expanded(
                child: RefreshIndicator(
                  color: const Color(0xFF0284C7),
                  onRefresh: () => _loadVendors(refresh: true),
                  child: _buildBody(),
                ),
              ),
            ],
          ),

          // Loading overlay
          if (_isActionLoading)
            Container(
              color: Colors.black.withValues(alpha: 0.3),
              child: const Center(
                child: Card(
                  elevation: 8,
                  shape: CircleBorder(),
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: CircularProgressIndicator(color: Color(0xFF0284C7)),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildStatsBar() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
      child: Row(
        children: [
          _buildStatCard(
            label: 'Total',
            count: _stats['total'] ?? 0,
            color: const Color(0xFF0284C7),
            icon: Icons.storefront_rounded,
            statusKey: 'all',
          ),
          const SizedBox(width: 8),
          _buildStatCard(
            label: 'En attente',
            count: _stats['pending'] ?? 0,
            color: const Color(0xFFF59E0B),
            icon: Icons.hourglass_top_rounded,
            statusKey: 'pending',
            highlight: (_stats['pending'] ?? 0) > 0,
          ),
          const SizedBox(width: 8),
          _buildStatCard(
            label: 'Actives',
            count: _stats['active'] ?? 0,
            color: const Color(0xFF10B981),
            icon: Icons.check_circle_outline_rounded,
            statusKey: 'active',
          ),
          const SizedBox(width: 8),
          _buildStatCard(
            label: 'Inactives',
            count: _stats['inactive'] ?? 0,
            color: const Color(0xFFEF4444),
            icon: Icons.block_rounded,
            statusKey: 'inactive',
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard({
    required String label,
    required int count,
    required Color color,
    required IconData icon,
    required String statusKey,
    bool highlight = false,
  }) {
    final isSelected = _selectedStatus == statusKey;

    return Expanded(
      child: InkWell(
        onTap: () {
          setState(() => _selectedStatus = statusKey);
          _loadVendors(refresh: true);
        },
        borderRadius: BorderRadius.circular(14),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 8),
          decoration: BoxDecoration(
            color: isSelected
                ? color.withValues(alpha: 0.12)
                : const Color(0xFFF8FAFC),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: isSelected
                  ? color
                  : (highlight
                      ? color.withValues(alpha: 0.5)
                      : const Color(0xFFE2E8F0)),
              width: isSelected ? 1.8 : 1.0,
            ),
          ),
          child: Column(
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(icon, size: 14, color: color),
                  const SizedBox(width: 4),
                  Text(
                    count.toString(),
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w900,
                      color: color,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 2),
              Text(
                label,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                  color: isSelected ? color : const Color(0xFF64748B),
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      child: Container(
        height: 42,
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(12),
        ),
        child: TextField(
          controller: _searchController,
          textInputAction: TextInputAction.search,
          onSubmitted: (_) => _loadVendors(refresh: true),
          style: const TextStyle(fontSize: 13, color: Color(0xFF0F172A)),
          decoration: InputDecoration(
            hintText: 'Rechercher une boutique, propriétaire, ville...',
            hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
            prefixIcon: const Icon(Icons.search_rounded,
                size: 20, color: Color(0xFF64748B)),
            suffixIcon: _searchController.text.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear_rounded,
                        size: 18, color: Color(0xFF64748B)),
                    onPressed: () {
                      _searchController.clear();
                      _loadVendors(refresh: true);
                    },
                  )
                : null,
            border: InputBorder.none,
            contentPadding: const EdgeInsets.symmetric(vertical: 10),
          ),
        ),
      ),
    );
  }

  Widget _buildFilterChips() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 10),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          children: [
            _buildChip('Toutes', 'all', count: _stats['total']),
            const SizedBox(width: 8),
            _buildChip('En attente ⏳', 'pending',
                count: _stats['pending'],
                isAlert: (_stats['pending'] ?? 0) > 0),
            const SizedBox(width: 8),
            _buildChip('Actives ✅', 'active', count: _stats['active']),
            const SizedBox(width: 8),
            _buildChip('Inactives ❌', 'inactive', count: _stats['inactive']),
          ],
        ),
      ),
    );
  }

  Widget _buildChip(String label, String statusKey,
      {int? count, bool isAlert = false}) {
    final isSelected = _selectedStatus == statusKey;

    return InkWell(
      onTap: () {
        setState(() => _selectedStatus = statusKey);
        _loadVendors(refresh: true);
      },
      borderRadius: BorderRadius.circular(20),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected
              ? const Color(0xFF0284C7)
              : (isAlert ? const Color(0xFFFEF3C7) : const Color(0xFFF1F5F9)),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: isSelected
                ? const Color(0xFF0284C7)
                : (isAlert ? const Color(0xFFF59E0B) : Colors.transparent),
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                color: isSelected
                    ? Colors.white
                    : (isAlert
                        ? const Color(0xFFB45309)
                        : const Color(0xFF475569)),
              ),
            ),
            if (count != null) ...[
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: isSelected
                      ? Colors.white.withValues(alpha: 0.25)
                      : const Color(0xFFE2E8F0),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  count.toString(),
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                    color: isSelected ? Colors.white : const Color(0xFF334155),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: Color(0xFF0284C7)),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.cloud_off_rounded,
                  size: 50, color: Color(0xFF94A3B8)),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13, color: Color(0xFF64748B)),
              ),
              const SizedBox(height: 16),
              ElevatedButton.icon(
                onPressed: () => _loadVendors(refresh: true),
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('Réessayer'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0284C7),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_vendors.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 70,
                height: 70,
                decoration: BoxDecoration(
                  color: const Color(0xFFE0F2FE),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.store_mall_directory_outlined,
                    size: 36, color: Color(0xFF0284C7)),
              ),
              const SizedBox(height: 16),
              const Text(
                'Aucune boutique trouvée',
                style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B)),
              ),
              const SizedBox(height: 6),
              Text(
                _selectedStatus == 'pending'
                    ? 'Superbe ! Il n\'y a aucune boutique en attente d\'approbation.'
                    : 'Modifiez vos filtres ou effectuez une autre recherche.',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 80),
      itemCount: _vendors.length,
      itemBuilder: (context, index) {
        final vendor = _vendors[index];
        return _buildVendorCard(vendor);
      },
    );
  }

  Widget _buildVendorCard(AdminVendor vendor) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: vendor.isPending
              ? const Color(0xFFFDE68A)
              : const Color(0xFFE2E8F0),
          width: vendor.isPending ? 1.6 : 1.0,
        ),
        boxShadow: [
          BoxShadow(
            color: vendor.isPending
                ? const Color(0xFFF59E0B).withValues(alpha: 0.08)
                : Colors.black.withValues(alpha: 0.02),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: InkWell(
        onTap: () => _showVendorDetails(vendor),
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top row: Logo, Name, Location, Status Badge
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Logo / Avatar
                  Container(
                    width: 52,
                    height: 52,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: vendor.logoUrl != null && vendor.logoUrl!.isNotEmpty
                        ? CachedNetworkImage(
                            imageUrl: vendor.logoUrl!,
                            fit: BoxFit.cover,
                            placeholder: (_, __) => const Center(
                              child: SizedBox(
                                width: 18,
                                height: 18,
                                child:
                                    CircularProgressIndicator(strokeWidth: 2),
                              ),
                            ),
                            errorWidget: (_, __, ___) =>
                                _buildFallbackAvatar(vendor.shopName),
                          )
                        : _buildFallbackAvatar(vendor.shopName),
                  ),
                  const SizedBox(width: 12),

                  // Shop Info
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          vendor.shopName,
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFF0F172A),
                            letterSpacing: -0.3,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 3),
                        Row(
                          children: [
                            const Icon(Icons.location_on_outlined,
                                size: 12, color: Color(0xFF64748B)),
                            const SizedBox(width: 3),
                            Expanded(
                              child: Text(
                                vendor.locationString,
                                style: const TextStyle(
                                    fontSize: 11, color: Color(0xFF64748B)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 3),
                        Row(
                          children: [
                            const Icon(Icons.person_outline_rounded,
                                size: 12, color: Color(0xFF64748B)),
                            const SizedBox(width: 3),
                            Expanded(
                              child: Text(
                                '${vendor.ownerName} (${vendor.ownerEmail})',
                                style: const TextStyle(
                                    fontSize: 11, color: Color(0xFF64748B)),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  // Status Pill
                  _buildStatusPill(vendor),
                ],
              ),

              const Divider(height: 20, color: Color(0xFFF1F5F9)),

              // Middle: Meta counters
              Row(
                children: [
                  _buildMiniMeta(Icons.inventory_2_outlined,
                      '${vendor.productsCount} articles'),
                  const SizedBox(width: 14),
                  _buildMiniMeta(Icons.local_shipping_outlined,
                      '${vendor.shippingPrice.toStringAsFixed(2)} TND livraison'),
                  const Spacer(),
                  const Icon(Icons.chevron_right_rounded,
                      size: 18, color: Color(0xFF94A3B8)),
                ],
              ),

              const SizedBox(height: 10),

              // Bottom Actions: Fast Approve / Reject
              Row(
                children: [
                  if (vendor.isPending) ...[
                    // Approve Button
                    Expanded(
                      flex: 3,
                      child: ElevatedButton.icon(
                        onPressed: () => _updateVendorStatus(vendor, 'active'),
                        icon: const Icon(Icons.check_circle_rounded,
                            size: 16, color: Colors.white),
                        label: const Text(
                          'Approuver la Boutique',
                          style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: Colors.white),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF10B981),
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Reject Button
                    Expanded(
                      flex: 2,
                      child: OutlinedButton(
                        onPressed: () =>
                            _updateVendorStatus(vendor, 'inactive'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFFEF4444),
                          side: const BorderSide(color: Color(0xFFFCA5A5)),
                          padding: const EdgeInsets.symmetric(vertical: 10),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        child: const Text('Rejeter',
                            style: TextStyle(
                                fontSize: 12, fontWeight: FontWeight.bold)),
                      ),
                    ),
                  ] else if (vendor.isActive) ...[
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () =>
                            _updateVendorStatus(vendor, 'inactive'),
                        icon: const Icon(Icons.pause_circle_outline_rounded,
                            size: 16),
                        label: const Text('Désactiver la Boutique'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: const Color(0xFFEF4444),
                          side: const BorderSide(color: Color(0xFFFCA5A5)),
                          padding: const EdgeInsets.symmetric(vertical: 9),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ] else ...[
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: () => _updateVendorStatus(vendor, 'active'),
                        icon: const Icon(Icons.check_circle_rounded, size: 16),
                        label: const Text('Réactiver / Approuver'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF10B981),
                          foregroundColor: Colors.white,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 9),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStatusPill(AdminVendor vendor) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: vendor.statusColor.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: vendor.statusColor.withValues(alpha: 0.4)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(vendor.statusIcon, size: 12, color: vendor.statusColor),
          const SizedBox(width: 4),
          Text(
            vendor.statusLabel,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              color: vendor.statusColor,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFallbackAvatar(String name) {
    final initial = name.isNotEmpty ? name[0].toUpperCase() : 'B';
    return Container(
      color: const Color(0xFFE2E8F0),
      alignment: Alignment.center,
      child: Text(
        initial,
        style: const TextStyle(
          fontSize: 20,
          fontWeight: FontWeight.bold,
          color: Color(0xFF475569),
        ),
      ),
    );
  }

  Widget _buildMiniMeta(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 13, color: const Color(0xFF64748B)),
        const SizedBox(width: 4),
        Text(
          text,
          style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: Color(0xFF475569)),
        ),
      ],
    );
  }
}

// ==========================================
// BOTTOM SHEET: BOUTIQUE DETAILED INSPECTION
// ==========================================
class _VendorDetailsSheet extends StatelessWidget {
  final AdminVendor vendor;
  final Function(String newStatus) onUpdateStatus;

  const _VendorDetailsSheet({
    required this.vendor,
    required this.onUpdateStatus,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      padding: EdgeInsets.only(
        top: 12,
        left: 20,
        right: 20,
        bottom: MediaQuery.of(context).padding.bottom + 20,
      ),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            // Handle bar
            Center(
              child: Container(
                width: 44,
                height: 5,
                decoration: BoxDecoration(
                  color: const Color(0xFFCBD5E1),
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header: Logo, Name, Status
            Row(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  clipBehavior: Clip.antiAlias,
                  child: vendor.logoUrl != null && vendor.logoUrl!.isNotEmpty
                      ? CachedNetworkImage(
                          imageUrl: vendor.logoUrl!,
                          fit: BoxFit.cover,
                          errorWidget: (_, __, ___) =>
                              _buildAvatar(vendor.shopName),
                        )
                      : _buildAvatar(vendor.shopName),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        vendor.shopName,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Slug: /vendors/${vendor.slug}',
                        style: const TextStyle(
                            fontSize: 11, color: Color(0xFF64748B)),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: vendor.statusColor.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                        color: vendor.statusColor.withValues(alpha: 0.4)),
                  ),
                  child: Text(
                    vendor.statusLabel,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w900,
                      color: vendor.statusColor,
                    ),
                  ),
                ),
              ],
            ),

            if (vendor.description != null &&
                vendor.description!.isNotEmpty) ...[
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Text(
                  vendor.description!,
                  style: const TextStyle(
                      fontSize: 12, color: Color(0xFF334155), height: 1.4),
                ),
              ),
            ],

            const SizedBox(height: 20),
            const Text(
              'Informations du Vendeur',
              style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 8),

            _buildInfoTile(
                Icons.person_rounded, 'Propriétaire', vendor.ownerName),
            _buildInfoTile(Icons.email_outlined, 'Email', vendor.ownerEmail,
                isActionable: true, onTap: () {
              launchUrl(Uri.parse('mailto:${vendor.ownerEmail}'));
            }),
            if (vendor.phone != null && vendor.phone!.isNotEmpty)
              _buildInfoTile(Icons.phone_outlined, 'Téléphone', vendor.phone!,
                  isActionable: true, onTap: () {
                launchUrl(Uri.parse('tel:${vendor.phone}'));
              }),
            _buildInfoTile(Icons.location_on_outlined, 'Adresse',
                '${vendor.address ?? "Non renseignée"}, ${vendor.locationString}'),
            _buildInfoTile(Icons.inventory_2_outlined, 'Catalogue',
                '${vendor.productsCount} articles en ligne'),
            _buildInfoTile(Icons.percent_rounded, 'Commission',
                '${vendor.commissionRate.toStringAsFixed(1)}%'),
            _buildInfoTile(Icons.local_shipping_outlined, 'Tarif Livraison',
                '${vendor.shippingPrice.toStringAsFixed(2)} TND'),

            const SizedBox(height: 24),

            // Big action buttons
            if (vendor.isPending) ...[
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () => onUpdateStatus('active'),
                  icon: const Icon(Icons.verified_rounded, color: Colors.white),
                  label: const Text(
                    'Approuver & Activer cette Boutique',
                    style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Colors.white),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
              const SizedBox(height: 10),
              SizedBox(
                width: double.infinity,
                height: 44,
                child: OutlinedButton(
                  onPressed: () => onUpdateStatus('inactive'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFFEF4444),
                    side: const BorderSide(color: Color(0xFFFCA5A5)),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                  ),
                  child: const Text('Rejeter la demande',
                      style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ] else if (vendor.isActive) ...[
              SizedBox(
                width: double.infinity,
                height: 46,
                child: OutlinedButton.icon(
                  onPressed: () => onUpdateStatus('inactive'),
                  icon: const Icon(Icons.block_rounded),
                  label: const Text('Suspendre / Désactiver'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFFEF4444),
                    side: const BorderSide(color: Color(0xFFFCA5A5)),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ] else ...[
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () => onUpdateStatus('active'),
                  icon: const Icon(Icons.check_circle_rounded,
                      color: Colors.white),
                  label: const Text(
                    'Réactiver la Boutique',
                    style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: Colors.white),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildAvatar(String name) {
    return Container(
      color: const Color(0xFFE2E8F0),
      alignment: Alignment.center,
      child: Text(
        name.isNotEmpty ? name[0].toUpperCase() : 'B',
        style: const TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: Color(0xFF475569)),
      ),
    );
  }

  Widget _buildInfoTile(IconData icon, String label, String value,
      {bool isActionable = false, VoidCallback? onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
        child: Row(
          children: [
            Icon(icon,
                size: 16,
                color: isActionable
                    ? const Color(0xFF0284C7)
                    : const Color(0xFF64748B)),
            const SizedBox(width: 10),
            Text(
              label,
              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
            ),
            const Spacer(),
            Text(
              value,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                color: isActionable
                    ? const Color(0xFF0284C7)
                    : const Color(0xFF0F172A),
              ),
            ),
            if (isActionable) ...[
              const SizedBox(width: 4),
              const Icon(Icons.open_in_new_rounded,
                  size: 12, color: Color(0xFF0284C7)),
            ],
          ],
        ),
      ),
    );
  }
}
