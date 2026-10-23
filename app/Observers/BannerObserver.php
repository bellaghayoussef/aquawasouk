<?php

namespace App\Observers;

use App\Models\Banner;
use App\Services\Cache\MarketplaceCacheService;

class BannerObserver
{
    public function saved(Banner $banner): void
    {
        MarketplaceCacheService::clearBannerCache($banner);
    }

    public function deleted(Banner $banner): void
    {
        MarketplaceCacheService::clearBannerCache($banner);
    }
}
