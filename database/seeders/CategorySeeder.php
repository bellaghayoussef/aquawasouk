<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Électronique & High-Tech',
                'slug' => 'electronique',
                'description' => 'Smartphones, ordinateurs portables, écouteurs, montres connectées et accessoires tech.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Mode & Habillement',
                'slug' => 'mode-habillement',
                'description' => 'Vêtements tendance, chaussures, sacs et accessoires de mode pour hommes et femmes.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Maison, Cuisine & Électroménager',
                'slug' => 'maison-cuisine',
                'description' => 'Mobilier moderne, petit électroménager, décoration intérieure et ustensiles de cuisine.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Beauté, Santé & Parfums',
                'slug' => 'beaute-sante',
                'description' => 'Soins du visage, parfums authentiques, maquillage et bien-être.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Sports & Fitness',
                'slug' => 'sports-fitness',
                'description' => 'Matériel de musculation, vélos, tapis de course et vêtements de sport.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Bébés, Enfants & Jouets',
                'slug' => 'bebe-enfants',
                'description' => 'Poussettes, jouets d\'éveil, vêtements et accessoires de puériculture.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Bricolage & Jardin',
                'slug' => 'bricolage-jardin',
                'description' => 'Outillage électroportatif, quincaillerie et équipements pour jardin.',
                'image' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Animaux & Aquariophilie',
                'slug' => 'animaux-aquariophilie',
                'description' => 'Nourriture, aquariums, filtres et accessoires pour vos animaux de compagnie.',
                'image' => null,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
