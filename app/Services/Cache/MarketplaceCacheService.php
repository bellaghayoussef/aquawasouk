<?php

namespace App\Services\Cache;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MarketplaceCacheService
{
    /**
     * Default Cache TTL: 1 Hour (3600 seconds)
     */
    public const DEFAULT_TTL = 3600;

    /**
     * Get or store item in cache with 1-hour expiration.
     */
    public static function remember(string $key, callable $callback, int $ttl = self::DEFAULT_TTL)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate all caches related to a Product.
     * Called automatically when a product or its variations/images change.
     */
    public static function clearProductCache($product = null): void
    {
        // Global product caches
        Cache::forget('home_featured_products');
        Cache::forget('api_featured_products');
        Cache::forget('home_all_products');
        Cache::forget('google_shopping_feed_xml');

        if ($product instanceof Product) {
            if ($product->slug) {
                Cache::forget("product_web_{$product->slug}");
                Cache::forget("api_product_{$product->slug}");
            }
            Cache::forget("product_related_{$product->id}");
            Cache::forget("product_related_{$product->category_id}");
        } elseif (is_string($product)) {
            Cache::forget("product_web_{$product}");
            Cache::forget("api_product_{$product}");
        }

        Log::info('[MarketplaceCache] Product cache invalidated on database change', [
            'product_id' => $product instanceof Product ? $product->id : $product,
        ]);
    }

    /**
     * Invalidate all caches related to Categories.
     */
    public static function clearCategoryCache($category = null): void
    {
        Cache::forget('home_categories');
        Cache::forget('api_categories_true');
        Cache::forget('api_categories_false');
        Cache::forget('categories_tree');

        if ($category instanceof Category && $category->slug) {
            Cache::forget("api_category_{$category->slug}");
            Cache::forget("category_web_{$category->slug}");
        }

        Log::info('[MarketplaceCache] Category cache invalidated on database change');
    }

    /**
     * Invalidate all caches related to Vendors.
     */
    public static function clearVendorCache($vendor = null): void
    {
        Cache::forget('home_vendors');
        Cache::forget('active_vendors_list');

        if ($vendor instanceof Vendor && $vendor->slug) {
            Cache::forget("vendor_show_{$vendor->slug}");
            Cache::forget("api_vendor_{$vendor->slug}");
        }

        Log::info('[MarketplaceCache] Vendor cache invalidated on database change');
    }

    /**
     * Invalidate all Banner caches.
     */
    public static function clearBannerCache($banner = null): void
    {
        Cache::forget('home_banners_home');
        Cache::forget('home_banners_products');
        Cache::forget('home_banners_product_detail');
        Cache::forget('home_banners_vendors');

        Log::info('[MarketplaceCache] Banner cache invalidated on database change');
    }

    /**
     * Complete flush of marketplace cache.
     */
    public static function flushAll(): void
    {
        self::clearProductCache();
        self::clearCategoryCache();
        self::clearVendorCache();
        self::clearBannerCache();

        Log::info('[MarketplaceCache] Complete marketplace cache purged');
    }
}
