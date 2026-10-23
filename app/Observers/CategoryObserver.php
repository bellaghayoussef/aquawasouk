<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\Cache\MarketplaceCacheService;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        MarketplaceCacheService::clearCategoryCache($category);
    }

    public function deleted(Category $category): void
    {
        MarketplaceCacheService::clearCategoryCache($category);
    }
}
