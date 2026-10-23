<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'user' => [
                    'name' => 'Karim Belhadj',
                    'email' => 'karim@electrotech.tn',
                    'password' => Hash::make('password123'),
                    'role' => 'vendor',
                ],
                'vendor' => [
                    'shop_name' => 'ElectroTech Plus',
                    'description' => 'Votre spécialiste en smartphones, ordinateurs portables et gadgets High-Tech certifiés avec garantie officielle.',
                    'phone' => '+216 22 100 200',
                    'address' => 'Centre Urbain Nord',
                    'slug' => 'electrotech-plus',
                    'city' => 'Tunis',
                    'country' => 'Tunisie',
                    'postal_code' => '1082',
                    'status' => 'active',
                    'commission_rate' => 8.00,
                    'shipping_price' => 7.00,
                ],
            ],
            [
                'user' => [
                    'name' => 'Sonia Mansour',
                    'email' => 'sonia@modachic.tn',
                    'password' => Hash::make('password123'),
                    'role' => 'vendor',
                ],
                'vendor' => [
                    'shop_name' => 'Moda Chic Boutique',
                    'description' => 'Prêt-à-porter moderne, maroquinerie italienne, chaussures et accessoires tendance pour femmes et hommes.',
                    'phone' => '+216 23 200 300',
                    'address' => 'Avenue Habib Bourguiba',
                    'slug' => 'moda-chic-boutique',
                    'city' => 'Sousse',
                    'country' => 'Tunisie',
                    'postal_code' => '4000',
                    'status' => 'active',
                    'commission_rate' => 10.00,
                    'shipping_price' => 8.00,
                ],
            ],
            [
                'user' => [
                    'name' => 'Mohamed Trabelsi',
                    'email' => 'mohamed@maisonconfort.tn',
                    'password' => Hash::make('password123'),
                    'role' => 'vendor',
                ],
                'vendor' => [
                    'shop_name' => 'Maison & Confort',
                    'description' => 'Tout pour votre intérieur : petit électroménager, ustensiles de cuisine modernes, literie et décoration.',
                    'phone' => '+216 24 300 400',
                    'address' => 'Route de Téniour Km 2',
                    'slug' => 'maison-et-confort',
                    'city' => 'Sfax',
                    'country' => 'Tunisie',
                    'postal_code' => '3002',
                    'status' => 'active',
                    'commission_rate' => 9.00,
                    'shipping_price' => 8.50,
                ],
            ],
            [
                'user' => [
                    'name' => 'Amira Chahed',
                    'email' => 'amira@bioglow.tn',
                    'password' => Hash::make('password123'),
                    'role' => 'vendor',
                ],
                'vendor' => [
                    'shop_name' => 'BioGlow Cosmétique',
                    'description' => 'Parfums de créateurs 100% originaux, cosmétiques bio, soins capillaires et dermocosmétique.',
                    'phone' => '+216 25 400 500',
                    'address' => 'Avenue Hédi Nouira, Ennasr 2',
                    'slug' => 'bioglow-cosmetique',
                    'city' => 'Ariana',
                    'country' => 'Tunisie',
                    'postal_code' => '2037',
                    'status' => 'active',
                    'commission_rate' => 10.00,
                    'shipping_price' => 7.00,
                ],
            ],
            [
                'user' => [
                    'name' => 'Yassine Dridi',
                    'email' => 'yassine@fitpro.tn',
                    'password' => Hash::make('password123'),
                    'role' => 'vendor',
                ],
                'vendor' => [
                    'shop_name' => 'FitPro Sport Store',
                    'description' => 'Équipements de fitness, tapis de course, appareils de musculation et vêtements de sport haute performance.',
                    'phone' => '+216 26 500 600',
                    'address' => 'Zone Touristique Hammamet Nord',
                    'slug' => 'fitpro-sport-store',
                    'city' => 'Nabeul',
                    'country' => 'Tunisie',
                    'postal_code' => '8050',
                    'status' => 'active',
                    'commission_rate' => 8.00,
                    'shipping_price' => 8.00,
                ],
            ],
        ];

        foreach ($vendors as $vendorData) {
            $user = User::updateOrCreate(
                ['email' => $vendorData['user']['email']],
                $vendorData['user']
            );

            $vendorData['vendor']['user_id'] = $user->id;

            $vendor = Vendor::updateOrCreate(
                ['slug' => $vendorData['vendor']['slug']],
                $vendorData['vendor']
            );

            // Seed conditional margin rules in TND
            if ($vendor->profitMargins()->count() === 0) {
                $vendor->profitMargins()->createMany([
                    ['min_order_price' => 0.00, 'max_order_price' => 50.00, 'margin_amount' => 4.00],
                    ['min_order_price' => 50.01, 'max_order_price' => 120.00, 'margin_amount' => 8.00],
                    ['min_order_price' => 120.01, 'max_order_price' => null, 'margin_amount' => 15.00],
                ]);
            }
        }

        // Create sample cash payout if none exist
        $firstVendor = Vendor::first();
        if ($firstVendor && \App\Models\VendorPayout::count() === 0) {
            \App\Models\VendorPayout::create([
                'vendor_id' => $firstVendor->id,
                'amount' => 50.00,
                'payout_date' => now()->subDays(2)->toDateString(),
                'description' => 'Acompte espèces remise en main propre bureau Tunis',
            ]);
        }

        // Create or update admin user
        User::updateOrCreate(
            ['email' => 'admin@aquado.com'],
            [
                'name' => 'Admin Marketplace',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        // Create or update customer demo user
        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Client Démo',
                'password' => Hash::make('password123'),
                'role' => 'customer',
            ]
        );
    }
}
