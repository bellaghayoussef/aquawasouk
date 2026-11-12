<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Jobs (Cron)
// 1. Sync variant prices to product price at 23:55 (so products with variants get their proper price)
Schedule::command('ecomness:sync-variant-prices')
    ->dailyAt('23:55')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron-variant-prices.log'));

// 2. Daily removal of products with price <= 0 or null at midnight
Schedule::command('products:remove-zero-price --force')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/cron-products-zero-price.log'));

