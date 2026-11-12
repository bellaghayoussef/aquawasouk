<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MainCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Auto & Moto',
                'slug' => 'auto-moto',
                'description' => 'Pièces détachées, accessoires auto & moto, entretien, outillage et équipements pour véhicules.',
                'image_url' => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
            [
                'name' => 'Alimentation & Épicerie',
                'slug' => 'alimentation-epicerie',
                'description' => 'Produits alimentaires, épicerie salée et sucrée, boissons, produits frais et spécialités du terroir.',
                'image_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
            [
                'name' => 'Fournitures scolaires & Bureau',
                'slug' => 'fournitures-scolaires-bureau',
                'description' => 'Cahiers, stylos, cartables, papeterie, organisation et fournitures professionnelles pour bureau.',
                'image_url' => 'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
            [
                'name' => 'Livres & Loisirs',
                'slug' => 'livres-loisirs',
                'description' => 'Romans, mangas, bandes dessinées, manuels, jeux de société et activités de loisirs créatifs.',
                'image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
            [
                'name' => 'Bagagerie & Voyage',
                'slug' => 'bagagerie-voyage',
                'description' => 'Valises, sacs à dos, sacs de voyage, trousses de toilette et accessoires pour voyageurs.',
                'image_url' => 'https://images.unsplash.com/photo-1565026057447-bc90a3dceb87?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
            [
                'name' => 'Meubles & Décoration',
                'slug' => 'meubles-decoration',
                'description' => 'Canapés, tables, luminaires, miroirs, textiles de maison et objets de décoration intérieure.',
                'image_url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
            [
                'name' => 'Puériculture & Maternité',
                'slug' => 'puericulture-maternite',
                'description' => 'Poussettes, sièges auto, lits bébé, biberons, vêtements de maternité et soins pour nouveau-nés.',
                'image_url' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
            [
                'name' => 'Musique & Instruments',
                'slug' => 'musique-instruments',
                'description' => 'Guitares, pianos, claviers, percussions, microphones et accessoires pour musiciens.',
                'image_url' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=800&q=85',
                'is_active' => true,
            ],
        ];

        // Ensure directories exist
        $publicDir = public_path('uploads/categories');
        $storageDir = storage_path('app/public/categories');

        if (!File::isDirectory($publicDir)) {
            File::makeDirectory($publicDir, 0755, true, true);
        }
        if (!File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true, true);
        }

        foreach ($categories as $data) {
            $slug = $data['slug'];
            $remoteUrl = $data['image_url'];
            $fileName = $slug . '.jpg';
            $publicFilePath = $publicDir . DIRECTORY_SEPARATOR . $fileName;
            $storageFilePath = $storageDir . DIRECTORY_SEPARATOR . $fileName;
            $relativeImagePath = 'categories/' . $fileName;

            $imagePathToSave = $remoteUrl;

            // Check if file already exists locally
            if (File::exists($publicFilePath) && File::size($publicFilePath) > 1000) {
                $imagePathToSave = $relativeImagePath;
            } else {
                // Try to download and cache locally
                try {
                    $response = Http::withoutVerifying()
                        ->timeout(10)
                        ->get($remoteUrl);

                    if ($response->successful() && strlen($response->body()) > 1000) {
                        File::put($publicFilePath, $response->body());
                        File::put($storageFilePath, $response->body());
                        $imagePathToSave = $relativeImagePath;
                        $this->command?->info("Image téléchargée localement pour [{$data['name']}].");
                    } else {
                        $this->command?->warn("Utilisation URL distante pour [{$data['name']}].");
                    }
                } catch (\Throwable $e) {
                    // Fallback to remote URL
                    $imagePathToSave = $remoteUrl;
                    $this->command?->warn("Échec du téléchargement pour [{$data['name']}], fallback vers URL distante.");
                }
            }

            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'],
                    'image' => $imagePathToSave,
                    'is_active' => $data['is_active'],
                    'parent_id' => null,
                ]
            );

            $this->command?->info("Catégorie synchronisée: {$data['name']}");
        }
    }
}

