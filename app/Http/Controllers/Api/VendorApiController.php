<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorApiController extends Controller
{
    /**
     * Get all vendors
     */
    public function index(Request $request)
    {
        $query = Vendor::with(['user'])->active();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by city
        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        // Filter by country
        if ($request->has('country')) {
            $query->where('country', $request->country);
        }

        $vendors = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $vendors,
        ]);
    }

    /**
     * Get single vendor
     */
    public function show($slug)
    {
        $vendor = Vendor::with(['user'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Get vendor stats
        $stats = [
            'total_products' => $vendor->products()->active()->count(),
            'total_sales' => $vendor->getTotalSales(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => $vendor,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Get vendor products
     */
    public function products(Request $request, $slug)
    {
        $vendor = Vendor::where('slug', $slug)->active()->firstOrFail();

        $query = $vendor->products()
            ->with(['category', 'images', 'primaryImage'])
            ->active();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => $vendor,
                'products' => $products,
            ],
        ]);
    }
}
