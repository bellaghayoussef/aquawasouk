<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use App\Http\Helpers\StructuredData;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Banner; // Add import

use App\Services\Cache\MarketplaceCacheService;

class HomeController extends Controller
{
    public function index()
    {
        // 1-Hour Cached Homepage Data (Auto-cleared on any DB update)
        $banners = MarketplaceCacheService::remember('home_banners_home', function () {
            return Banner::where('is_active', true)
                ->where('page', 'home')
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        });

        $featuredProducts = MarketplaceCacheService::remember('home_featured_products', function () {
            return Product::with(['vendor', 'category', 'primaryImage'])
                ->whereHas('vendor', function ($q) {
                    $q->active();
                })
                ->active()
                ->featured()
                ->take(8)
                ->get();
        });

        $categories = MarketplaceCacheService::remember('home_categories', function () {
            return Category::active()->where('parent_id', null)->get();
        });

        $vendors = MarketplaceCacheService::remember('home_vendors', function () {
            return Vendor::active()
                ->with('user')
                ->take(6)
                ->get();
        });

        // SEO structured data
        $structuredData = [
            'organization' => StructuredData::organization(),
            'website' => StructuredData::website(),
        ];

        return Inertia::render('Home', [
            'banners' => $banners,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'vendors' => $vendors,
            'structuredData' => $structuredData,
        ]);
    }

    public function about()
    {
        return Inertia::render('About');
    }
}
