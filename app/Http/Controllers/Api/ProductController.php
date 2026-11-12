<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Cache\MarketplaceCacheService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with filters
     */
    public function index(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'images', 'primaryImage', 'variations'])
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

        // Vendor filter
        if ($request->has('vendor')) {
            $query->whereHas('vendor', function ($q) use ($request) {
                $q->where('slug', $request->vendor);
            });
        }

        // Price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Featured only
        if ($request->has('featured') && $request->featured == 'true') {
            $query->featured();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $sortOrder);
        } elseif ($sortBy === 'popularity') {
            $query->orderBy('views', 'desc');
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get a single product
     */
    public function show($slug)
    {
        $product = Product::with([
            'vendor',
            'category',
            'images',
            'primaryImage',
            'variations',
            'reviews.user',
            'reviews' => function ($query) {
                $query->approved()->latest();
            }
        ])->where(function ($q) use ($slug) {
            $q->where('slug', $slug);
            if (is_numeric($slug)) {
                $q->orWhere('id', (int) $slug);
            }
        })->firstOrFail();

        // Increment views
        $product->increment('views');

        // Calculate average rating
        $avgRating = $product->reviews()->approved()->avg('rating');
        $totalReviews = $product->reviews()->approved()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'product' => $product,
                'average_rating' => round($avgRating, 1),
                'total_reviews' => $totalReviews,
            ],
        ]);
    }

    /**
     * Get featured products
     */
    public function featured()
    {
        // 1-Hour Cached Featured Products for Mobile App (Auto-cleared on update/delete)
        $products = MarketplaceCacheService::remember('api_featured_products', function () {
            return Product::with(['vendor', 'category', 'images', 'primaryImage', 'variations'])
                ->where('is_featured', true)
                ->limit(10)
                ->get();
        });

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Search products
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $products = Product::with(['vendor', 'category', 'images', 'primaryImage', 'variations'])
            ->active()
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->q}%")
                      ->orWhere('description', 'like', "%{$request->q}%");
            })
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
  public function allproducts()
{
    $products = Product::with(['vendor', 'category', 'images', 'primaryImage'])
        ->where('status', 'active')
        ->get()
        ->map(function ($product) {

            $price = $product->discount_price ?? $product->price;
            $availability = $product->stock > 0 ? 'in stock' : 'out of stock';

            return [
                // GOOGLE REQUIRED RESOURCE ID
                'id' => 'online:fr:TN:' . $product->id,

                // REQUIRED FIELDS
                'offerId' => (string) $product->id,
                'title' => $product->name,
                'description' => $product->description ?: $product->name,
                'link' => url('/product/' . $product->slug),
                'image_link' => optional($product->primaryImage)
                    ? url('/uploads/' . $product->primaryImage->image_path)
                    : null,

                'availability' => $availability,
                'condition' => 'new',

                // PRICE FORMAT
                'price' => [
                    'value' => number_format((float) $price, 2, '.', ''),
                    'currency' => 'TND',
                ],

                // BRAND
                'brand' => optional($product->vendor)->shop_name ?? 'AquawaSouk',

                // REQUIRED CONTEXT
                'content_language' => 'fr',
                'target_country' => 'TN',
                'channel' => 'online',

                // IDENTIFIERS
                'mpn' => $product->sku,
                'identifier_exists' => !empty($product->sku),

                // GOOGLE CATEGORY (NUMERIC ID ONLY)
                'google_product_category' => optional($product->category)->google_id ?? '3238',

                // OPTIONAL BUT RECOMMENDED
                'product_type' => optional($product->category)->name,

                // SHIPPING (ARRAY REQUIRED)
                'shipping' => [
                    [
                        'country' => 'TN',
                        'service' => 'Standard',
                        'price' => [
                            'value' => number_format(
                                (float) (optional($product->vendor)->shipping_price ?? 0),
                                2,
                                '.',
                                ''
                            ),
                            'currency' => 'TND',
                        ],
                    ]
                ],
            ];
        })
        ->filter(fn ($item) => !empty($item['image_link'])) // prevent image disapproval
        ->values();

    return response()->json($products);
}
}
