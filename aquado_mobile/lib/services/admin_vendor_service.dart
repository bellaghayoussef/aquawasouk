import '../models/admin_vendor.dart';
import 'api_service.dart';

class AdminVendorService {
  /// Fetch paginated vendors list with optional status and search filter.
  static Future<Map<String, dynamic>> fetchVendors({
    String? status,
    String? search,
    int page = 1,
  }) async {
    final queryParams = <String, String>{
      'page': page.toString(),
    };

    if (status != null && status.isNotEmpty && status != 'all') {
      queryParams['status'] = status;
    }

    if (search != null && search.trim().isNotEmpty) {
      queryParams['search'] = search.trim();
    }

    final response = await ApiService.get(
      '/admin/vendors',
      queryParams: queryParams,
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final dataWrapper = response['data'];
      final rawList = dataWrapper['data'] as List<dynamic>? ?? [];

      final vendors = rawList
          .map((item) => AdminVendor.fromJson(item as Map<String, dynamic>))
          .toList();

      final statsRaw = response['stats'] as Map<String, dynamic>? ?? {};
      final stats = {
        'total': int.tryParse(statsRaw['total']?.toString() ?? '0') ?? 0,
        'pending': int.tryParse(statsRaw['pending']?.toString() ?? '0') ?? 0,
        'active': int.tryParse(statsRaw['active']?.toString() ?? '0') ?? 0,
        'inactive': int.tryParse(statsRaw['inactive']?.toString() ?? '0') ?? 0,
      };

      final lastPage = dataWrapper['last_page'] is int
          ? dataWrapper['last_page'] as int
          : int.tryParse(dataWrapper['last_page']?.toString() ?? '1') ?? 1;

      return {
        'vendors': vendors,
        'stats': stats,
        'lastPage': lastPage,
        'currentPage': page,
      };
    }

    throw Exception(
        response['message'] ?? 'Impossible de charger les boutiques.');
  }

  /// Update boutique status (active = approved, inactive = rejected, pending = pending).
  static Future<Map<String, dynamic>> updateStatus({
    required int vendorId,
    required String status,
  }) async {
    final response = await ApiService.put(
      '/admin/vendors/$vendorId/status',
      {'status': status},
      requiresAuth: true,
    );

    if (response['success'] == true) {
      return {
        'message': response['message'] as String? ?? 'Statut mis à jour.',
        'vendor': response['data'] != null
            ? AdminVendor.fromJson(response['data'] as Map<String, dynamic>)
            : null,
      };
    }

    throw Exception(
        response['message'] ?? 'Échec de la mise à jour du statut.');
  }

  /// Fetch quick statistics counters.
  static Future<Map<String, int>> fetchStats() async {
    final response = await ApiService.get(
      '/admin/vendors/stats',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      final raw = response['data'] as Map<String, dynamic>;
      return {
        'total': int.tryParse(raw['total']?.toString() ?? '0') ?? 0,
        'pending': int.tryParse(raw['pending']?.toString() ?? '0') ?? 0,
        'active': int.tryParse(raw['active']?.toString() ?? '0') ?? 0,
        'inactive': int.tryParse(raw['inactive']?.toString() ?? '0') ?? 0,
      };
    }

    return {'total': 0, 'pending': 0, 'active': 0, 'inactive': 0};
  }

  /// Fetch single vendor details.
  static Future<AdminVendor> fetchVendorDetails(int vendorId) async {
    final response = await ApiService.get(
      '/admin/vendors/$vendorId',
      requiresAuth: true,
    );

    if (response['success'] == true && response['data'] != null) {
      return AdminVendor.fromJson(response['data'] as Map<String, dynamic>);
    }

    throw Exception(response['message'] ?? 'Boutique introuvable.');
  }
}
