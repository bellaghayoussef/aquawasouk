<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Http\Helpers\StructuredData;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Banner;
use App\Services\Cache\MarketplaceCacheService;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with('user')
            ->active()
            ->paginate(12);

        $banners = Banner::where('is_active', true)
            ->where('page', 'vendors')
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Vendors/Index', [
            'vendors' => $vendors,
            'banners' => $banners,
        ]);
    }

    public function show($slug)
    {
        // 1-Hour Cached Vendor Profile (Auto-cleared on update/delete)
        $vendor = MarketplaceCacheService::remember("vendor_show_{$slug}", function () use ($slug) {
            return Vendor::with('user')
                ->where('slug', $slug)
                ->firstOrFail();
        });

        $products = $vendor->products()
            ->with(['category', 'primaryImage', 'reviews'])
            ->active()
            ->paginate(12);

        // Generate structured data for SEO
        $structuredData = StructuredData::vendor($vendor);

        // SEO metadata
        $seoData = [
            'title' => $vendor->shop_name . ' - Aquawasouk',
            'description' => $vendor->description
                ? substr(strip_tags($vendor->description), 0, 160)
                : "Découvrez les produits de {$vendor->shop_name} sur Aquawasouk",
            'keywords' => $vendor->shop_name . ', vendeur, aquarium, aquariophilie',
            'image' => $vendor->logo ? asset('uploads/' . $vendor->logo) : asset('logo/logo.png'),
            'type' => 'profile',
            'canonical' => route('vendors.show', $vendor->slug),
        ];

        return Inertia::render('Vendors/Show', [
            'vendor' => $vendor,
            'products' => $products,
            'structuredData' => $structuredData,
            'seo' => $seoData,
        ]);
    }
}
