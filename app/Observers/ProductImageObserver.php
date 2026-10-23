<?php

namespace App\Observers;

use App\Models\ProductImage;
use App\Services\Cache\MarketplaceCacheService;

class ProductImageObserver
{
    public function saved(ProductImage $image): void
    {
        if ($image->product) {
            MarketplaceCacheService::clearProductCache($image->product);
        }
    }

    public function deleted(ProductImage $image): void
    {
        if ($image->product) {
            MarketplaceCacheService::clearProductCache($image->product);
        }
    }
}
