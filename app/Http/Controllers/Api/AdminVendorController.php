<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminVendorController extends Controller
{
    /**
     * Get list of all boutiques / vendors with filters for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::with('user:id,name,email,photo')
            ->withCount('products');

        // Status Filter (pending, active, inactive, all)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Order: prioritize pending shops first, then latest
        $vendors = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->latest('id')
            ->paginate($request->get('per_page', 25));

        $stats = [
            'total' => Vendor::count(),
            'pending' => Vendor::where('status', 'pending')->count(),
            'active' => Vendor::where('status', 'active')->count(),
            'inactive' => Vendor::where('status', 'inactive')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $vendors,
            'stats' => $stats,
        ]);
    }

    /**
     * Get boutique statistics for quick KPI counters.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total' => Vendor::count(),
                'pending' => Vendor::where('status', 'pending')->count(),
                'active' => Vendor::where('status', 'active')->count(),
                'inactive' => Vendor::where('status', 'inactive')->count(),
            ],
        ]);
    }

    /**
     * Get single boutique details.
     */
    public function show(Vendor $vendor): JsonResponse
    {
        $vendor->load(['user:id,name,email,photo', 'products' => function ($q) {
            $q->with('primaryImage')->latest()->take(6);
        }])->loadCount('products');

        return response()->json([
            'success' => true,
            'data' => $vendor,
        ]);
    }

    /**
     * Update boutique status (Approve = active, Reject/Deactivate = inactive, Pending = pending).
     */
    public function updateStatus(Request $request, Vendor $vendor): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $oldStatus = $vendor->status;
        $vendor->status = $request->status;
        $vendor->save();

        $message = match ($request->status) {
            'active' => "La boutique '{$vendor->shop_name}' a été approuvée avec succès !",
            'inactive' => "La boutique '{$vendor->shop_name}' a été désactivée.",
            default => "La boutique '{$vendor->shop_name}' est désormais en attente.",
        };

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $vendor->fresh(['user:id,name,email,photo'])->loadCount('products'),
            'old_status' => $oldStatus,
            'new_status' => $vendor->status,
        ]);
    }
}

