<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\Cache\MarketplaceCacheService;

class ProductObserver
{
    /**
     * Handle the Product "saved" event (created or updated).
     */
    public function saved(Product $product): void
    {
        MarketplaceCacheService::clearProductCache($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        MarketplaceCacheService::clearProductCache($product);
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        MarketplaceCacheService::clearProductCache($product);
    }
}
