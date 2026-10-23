<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Cache\MarketplaceCacheService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index(Request $request)
    {
        $parentOnly = ($request->has('parent_only') && $request->parent_only == 'true') ? 'true' : 'false';

        // 1-Hour Cached Categories (Auto-cleared on create/update/delete)
        $categories = MarketplaceCacheService::remember("api_categories_{$parentOnly}", function () use ($request) {
            $query = Category::with(['children'])->active();

            if ($request->has('parent_only') && $request->parent_only == 'true') {
                $query->parent();
            }

            return $query->get();
        });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Get single category
     */
    public function show($slug)
    {
        // 1-Hour Cached Single Category
        $category = MarketplaceCacheService::remember("api_category_{$slug}", function () use ($slug) {
            return Category::with(['children', 'parent'])
                ->where('slug', $slug)
                ->firstOrFail();
        });

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    /**
     * Get category products
     */
    public function products(Request $request, $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $query = $category->products()
            ->with(['vendor', 'images', 'primaryImage'])
            ->active();

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
                'category' => $category,
                'products' => $products,
            ],
        ]);
    }
}
