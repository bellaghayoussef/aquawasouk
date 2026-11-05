<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Banner;
use App\Observers\ProductObserver;
use App\Observers\ProductVariationObserver;
use App\Observers\ProductImageObserver;
use App\Observers\CategoryObserver;
use App\Observers\VendorObserver;
use App\Observers\BannerObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\SmsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Auto Cache-Invalidation Observers
        Product::observe(ProductObserver::class);
        ProductVariation::observe(ProductVariationObserver::class);
        ProductImage::observe(ProductImageObserver::class);
        Category::observe(CategoryObserver::class);
        Vendor::observe(VendorObserver::class);
        Banner::observe(BannerObserver::class);
    }
}
