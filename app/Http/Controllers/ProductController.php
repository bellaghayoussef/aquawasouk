<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Banner;
use App\Http\Helpers\StructuredData;
use App\Services\Cache\MarketplaceCacheService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'primaryImage', 'reviews'])
            ->active()->whereHas('vendor', function ($q) {
                $q->active();   // vendor scope active()
            });

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->query('category'));
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->query('search') . '%');
        }

        // Filter by Min Price
        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $minPrice = (float) $request->min_price;
            $query->where(function ($q) use ($minPrice) {
                $q->where(function ($sub) use ($minPrice) {
                    $sub->whereNotNull('discount_price')->where('discount_price', '>=', $minPrice);
                })->orWhere(function ($sub) use ($minPrice) {
                    $sub->whereNull('discount_price')->where('price', '>=', $minPrice);
                });
            });
        }

        // Filter by Max Price
        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $maxPrice = (float) $request->max_price;
            $query->where(function ($q) use ($maxPrice) {
                $q->where(function ($sub) use ($maxPrice) {
                    $sub->whereNotNull('discount_price')->where('discount_price', '<=', $maxPrice);
                })->orWhere(function ($sub) use ($maxPrice) {
                    $sub->whereNull('discount_price')->where('price', '<=', $maxPrice);
                });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderByRaw('COALESCE(discount_price, price) asc');
                break;
            case 'price_high':
                $query->orderByRaw('COALESCE(discount_price, price) desc');
                break;
            case 'popular':
                $query->orderBy('views', 'desc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::all();

        $banners = Banner::where('is_active', true)
            ->where('page', 'products')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['category', 'search', 'sort', 'min_price', 'max_price']),
            'banners' => $banners,
        ]);
    }

    public function show($slug)
    {

        // 1-Hour Cached Product Details (Auto-cleared on update/delete)
        $product = MarketplaceCacheService::remember("product_web_{$slug}", function () use ($slug) {
            return Product::with([
                'vendor.user',
                'category',
                'images',
                'variations',
                'reviews.user',
            ])->where('slug', $slug)->whereHas('vendor', function ($q) {
                $q->active();
            })->firstOrFail();
        });

        // Increment views safely
        $product->increment('views');

        // 1-Hour Cached Related Products
        $relatedProducts = MarketplaceCacheService::remember("product_related_{$product->id}", function () use ($product) {
            return Product::with(['vendor', 'category', 'primaryImage'])
                ->where('category_id', $product->category_id)
                ->whereHas('vendor', function ($q) {
                    $q->active();
                })
                ->where('id', '!=', $product->id)
                ->active()
                ->take(4)
                ->get();
        });

        // Generate structured data for SEO
        $structuredData = StructuredData::product($product, $product->vendor);

        // SEO metadata
        $price = $product->discount_price > 0 ? $product->discount_price : $product->price;
        $seoData = [
            'title' => $product->name . ' - Aquawasouk',
            'description' => $product->description
                ? substr(strip_tags($product->description), 0, 160)
                : "Achetez {$product->name} sur Aquawasouk. Prix: {$price} TND",
            'keywords' => $product->name . ', aquarium, ' . $product->category->name . ', aquariophilie',
            'image' => $product->images && $product->images->count() > 0
                ? asset('uploads/' . $product->images->first()->image_path)
                : asset('logo/logo.png'),
            'type' => 'product',
            'canonical' => route('products.show', $product->slug),
        ];

        $banners = Banner::where('is_active', true)
            ->where('page', 'product_detail')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Products/Show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'structuredData' => $structuredData,
            'seo' => $seoData,
            'banners' => $banners,
        ]);
    }
}
