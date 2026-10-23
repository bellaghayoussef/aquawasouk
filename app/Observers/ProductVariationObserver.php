<?php

namespace App\Observers;

use App\Models\ProductVariation;
use App\Services\Cache\MarketplaceCacheService;

class ProductVariationObserver
{
    public function saved(ProductVariation $variation): void
    {
        if ($variation->product) {
            MarketplaceCacheService::clearProductCache($variation->product);
        }
    }

    public function deleted(ProductVariation $variation): void
    {
        if ($variation->product) {
            MarketplaceCacheService::clearProductCache($variation->product);
        }
    }
}
