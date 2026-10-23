<?php

namespace App\Observers;

use App\Models\Vendor;
use App\Services\Cache\MarketplaceCacheService;

class VendorObserver
{
    public function saved(Vendor $vendor): void
    {
        MarketplaceCacheService::clearVendorCache($vendor);
    }

    public function deleted(Vendor $vendor): void
    {
        MarketplaceCacheService::clearVendorCache($vendor);
    }
}
