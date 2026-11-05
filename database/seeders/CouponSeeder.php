<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'BIENVENUE10'],
            [
                'description' => '10% de réduction de bienvenue sur tout le site',
                'type' => 'percent',
                'value' => 10.00,
                'min_order_amount' => 30.00,
                'max_discount' => 50.00,
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'AQUASOUK20'],
            [
                'description' => 'Remise de 20 DT pour toute commande supérieure à 100 DT',
                'type' => 'fixed',
                'value' => 20.00,
                'min_order_amount' => 100.00,
                'usage_limit' => 200,
                'usage_limit_per_user' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'FLASH5'],
            [
                'description' => 'Offre Flash : 5 DT offerts sans minimum d\'achat',
                'type' => 'fixed',
                'value' => 5.00,
                'min_order_amount' => 10.00,
                'usage_limit' => 1000,
                'usage_limit_per_user' => 2,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
            ]
        );
    }
}
