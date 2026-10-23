<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::all();
        $categories = Category::all()->keyBy('slug');

        $sampleImages = [
            'products/688lZyoOdE5qszvpNDNVhOwKzMZHEpXB0LFOAXNR.png',
            'products/DKF0uA6ZOPH0Dj6WQu3Ox3gkZXQSADyOIyIPbW6b.png',
            'products/IaiKSNF0tx1C70NcCtX7DaofW4IUqRq2k5Y2T8oq.jpg',
            'products/Q4xSfU2zthrPhBiwOYsz9YVAQYNjRVuYcYewqUhn.png',
            'products/RHe9QvbejiwgiFVEgBCCB8vkkDO1HVbNvHCJ0Cxt.jpg',
            'products/V65AFkXJzIYXtHD6Cn53hUQoYf23gSNnTSJBD77U.png',
            'products/Vx9tlVTpuDTX8g88567CcqKH4o4qGFAxKZ57ZdBL.png',
        ];

        $productsData = [
            // 1. Électronique & High-Tech
            [
                'category_slug' => 'electronique',
                'vendor_slug' => 'electrotech-plus',
                'name' => 'Samsung Galaxy S24 Ultra 256GB - Gris Titane',
                'description' => "Le fleuron de Samsung doté de Galaxy AI, d'un châssis en titane résistant, d'un appareil photo 200MP et du S Pen intégré. Écran Dynamic AMOLED 2X 6.8\" 120Hz.",
                'specifications' => [
                    'Écran' => '6.8 pouces Dynamic AMOLED 2X 120Hz',
                    'Stockage' => '256 Go',
                    'Mémoire RAM' => '12 Go',
                    'Processeur' => 'Snapdragon 8 Gen 3',
                    'Batterie' => '5000 mAh avec charge ultra-rapide',
                    'Garantie' => '1 an constructeur officiel',
                ],
                'price' => 3899.00,
                'discount_price' => 3599.00,
                'stock' => 12,
                'is_featured' => true,
                'slug' => 'samsung-galaxy-s24-ultra-256gb',
                'status' => 'active',
            ],
            [
                'category_slug' => 'electronique',
                'vendor_slug' => 'electrotech-plus',
                'name' => 'Apple iPhone 15 Pro Max 256GB - Titane Naturel',
                'description' => "Design en titane de qualité aérospatiale, puce A17 Pro révolutionnaire, bouton Action personnalisable et téléobjectif 5x ultra-performant.",
                'specifications' => [
                    'Écran' => '6.7 pouces Super Retina XDR OLED',
                    'Stockage' => '256 Go',
                    'Puce' => 'A17 Pro avec GPU 6 cœurs',
                    'Connectique' => 'USB-C haute vitesse',
                    'Autonomie' => "Jusqu'à 29 heures de lecture vidéo",
                ],
                'price' => 4899.00,
                'discount_price' => 4599.00,
                'stock' => 8,
                'is_featured' => true,
                'slug' => 'apple-iphone-15-pro-max-256gb',
                'status' => 'active',
            ],
            [
                'category_slug' => 'electronique',
                'vendor_slug' => 'electrotech-plus',
                'name' => 'PC Portable Gamer Lenovo Legion 5 Pro 16" QHD',
                'description' => "Ordinateur portable gaming ultra-puissant pour les gamers et créateurs de contenu exigeants. Clavier rétroéclairé RGB et refroidissement Coldfront 5.0.",
                'specifications' => [
                    'Processeur' => 'Intel Core i7-13700HX (16 cœurs)',
                    'Carte Graphique' => 'NVIDIA GeForce RTX 4070 8GB GDDR6',
                    'RAM' => '32 Go DDR5 5200MHz',
                    'Stockage' => '1 To SSD NVMe PCIe 4.0',
                    'Écran' => '16 pouces WQXGA (2560x1600) 240Hz IPS',
                ],
                'price' => 3450.00,
                'discount_price' => 3199.00,
                'stock' => 6,
                'is_featured' => true,
                'slug' => 'pc-portable-lenovo-legion-5-pro',
                'status' => 'active',
            ],
            [
                'category_slug' => 'electronique',
                'vendor_slug' => 'electrotech-plus',
                'name' => 'Casque Sans Fil Sony WH-1000XM5 avec Réduction de Bruit',
                'description' => "La référence mondiale en réduction de bruit active. Son haute résolution sans fil, 30 heures d'autonomie et appels d'une clarté cristalline.",
                'specifications' => [
                    'Autonomie' => '30 heures avec ANC activé',
                    'Connexion' => 'Bluetooth 5.2 multipoint',
                    'Poids' => '250 g ultra-confortable',
                    'Microphones' => '8 micros avec 2 processeurs dédiés',
                ],
                'price' => 980.00,
                'discount_price' => 849.00,
                'stock' => 15,
                'is_featured' => false,
                'slug' => 'casque-sans-fil-sony-wh-1000xm5',
                'status' => 'active',
            ],
            [
                'category_slug' => 'electronique',
                'vendor_slug' => 'electrotech-plus',
                'name' => 'Montre Connectée Apple Watch Series 9 GPS 45mm',
                'description' => "Puce S9 plus puissante, geste toucher deux fois magique, écran deux fois plus lumineux et suivi complet de votre santé et activité sportive.",
                'specifications' => [
                    'Boîtier' => 'Aluminium 45 mm',
                    'Capteurs' => 'ECG, Oxygène sanguin, Température, Sommeil',
                    'Résistance à l\'eau' => 'Jusqu\'à 50 mètres',
                ],
                'price' => 1450.00,
                'discount_price' => 1290.00,
                'stock' => 10,
                'is_featured' => false,
                'slug' => 'apple-watch-series-9-gps-45mm',
                'status' => 'active',
            ],

            // 2. Mode & Habillement
            [
                'category_slug' => 'mode-habillement',
                'vendor_slug' => 'moda-chic-boutique',
                'name' => 'Veste en Cuir Véritable Homme - Coupe Slim Fit',
                'description' => "Veste classique et intemporelle confectionnée en cuir d'agneau souple de première qualité. Fermetures zippées robustes et doublure satinée.",
                'specifications' => [
                    'Matière' => '100% Cuir d\'agneau véritable',
                    'Tailles disponibles' => 'M, L, XL, XXL',
                    'Couleur' => 'Noir profond',
                    'Origine' => 'Fabriqué en Tunisie',
                ],
                'price' => 340.00,
                'discount_price' => 280.00,
                'stock' => 25,
                'is_featured' => true,
                'slug' => 'veste-cuir-homme-slim-fit',
                'status' => 'active',
            ],
            [
                'category_slug' => 'mode-habillement',
                'vendor_slug' => 'moda-chic-boutique',
                'name' => 'Sneakers de Running Nike Air Zoom Pegasus 40',
                'description' => "Amorti réactif Zoom Air pour une foulée énergique et confortable au quotidien ou lors de vos entraînements de course à pied.",
                'specifications' => [
                    'Pointures' => '40 à 45',
                    'Amorti' => 'Technologie Nike React + 2 unités Zoom Air',
                    'Usage' => 'Route, entraînement quotidien',
                ],
                'price' => 380.00,
                'discount_price' => 329.00,
                'stock' => 30,
                'is_featured' => false,
                'slug' => 'nike-air-zoom-pegasus-40',
                'status' => 'active',
            ],
            [
                'category_slug' => 'mode-habillement',
                'vendor_slug' => 'moda-chic-boutique',
                'name' => 'Sac à Main Femme en Cuir Italien Élégant',
                'description' => "Grand sac cabas chic et fonctionnel avec bandoulière amovible. Finition dorée haut de gamme et multiples compartiments organisateurs.",
                'specifications' => [
                    'Dimensions' => '32 x 26 x 14 cm',
                    'Matière' => 'Cuir véritable grainé',
                    'Fermeture' => 'Zippée sécurisée',
                ],
                'price' => 260.00,
                'discount_price' => 199.00,
                'stock' => 18,
                'is_featured' => true,
                'slug' => 'sac-a-main-femme-cuir-italien',
                'status' => 'active',
            ],
            [
                'category_slug' => 'mode-habillement',
                'vendor_slug' => 'moda-chic-boutique',
                'name' => 'Montre Homme Chronographe Acier Inoxydable',
                'description' => "Design raffiné avec cadran bleu soleillé, verre saphir inrayable et mouvement quartz de haute précision.",
                'specifications' => [
                    'Diamètre cadran' => '42 mm',
                    'Étanchéité' => '10 ATM (100 m)',
                    'Bracelet' => 'Acier inoxydable 316L',
                ],
                'price' => 420.00,
                'discount_price' => 349.00,
                'stock' => 14,
                'is_featured' => false,
                'slug' => 'montre-homme-chronographe-acier',
                'status' => 'active',
            ],

            // 3. Maison, Cuisine & Électroménager
            [
                'category_slug' => 'maison-cuisine',
                'vendor_slug' => 'maison-et-confort',
                'name' => 'Friteuse Sans Huile Air Fryer XL 6.2L Tactile 1800W',
                'description' => "Cuisinez croustillant et sain avec 90% d'huile en moins ! 8 programmes de cuisson automatiques, panier antiadhésif lavable au lave-vaisselle.",
                'specifications' => [
                    'Capacité' => '6.2 Litres (idéal pour 4 à 6 personnes)',
                    'Puissance' => '1800 Watts',
                    'Température' => '80°C à 200°C',
                    'Écran' => 'Tactile digital LED',
                ],
                'price' => 390.00,
                'discount_price' => 319.00,
                'stock' => 40,
                'is_featured' => true,
                'slug' => 'friteuse-sans-huile-air-fryer-xl',
                'status' => 'active',
            ],
            [
                'category_slug' => 'maison-cuisine',
                'vendor_slug' => 'maison-et-confort',
                'name' => 'Machine à Café Espresso Automatique avec Buse Vapeur',
                'description' => "Dégustez un véritable espresso italien à la maison ! Pression professionnelle 20 bars, chauffe rapide Thermoblock et buse pour cappuccinos onctueux.",
                'specifications' => [
                    'Pression' => '20 Bars',
                    'Réservoir' => '1.5 Litre amovible',
                    'Compatibilité' => 'Café moulu et dosettes ESE',
                ],
                'price' => 450.00,
                'discount_price' => 389.00,
                'stock' => 20,
                'is_featured' => false,
                'slug' => 'machine-a-cafe-espresso-automatique',
                'status' => 'active',
            ],
            [
                'category_slug' => 'maison-cuisine',
                'vendor_slug' => 'maison-et-confort',
                'name' => 'Robot Aspirateur & Laveur Connecté Laser LiDAR',
                'description' => "Navigation intelligente avec cartographie 3D des pièces, aspiration puissante 4000Pa et serpillère vibrante pour un nettoyage impeccable.",
                'specifications' => [
                    'Aspiration' => '4000 Pa',
                    'Autonomie' => '180 minutes (Jusqu\'à 200m²)',
                    'Application' => 'Contrôle WiFi via Smartphone (iOS/Android)',
                ],
                'price' => 890.00,
                'discount_price' => 749.00,
                'stock' => 12,
                'is_featured' => true,
                'slug' => 'robot-aspirateur-laveur-laser',
                'status' => 'active',
            ],
            [
                'category_slug' => 'maison-cuisine',
                'vendor_slug' => 'maison-et-confort',
                'name' => 'Batterie de Cuisine Granit Antiadhésif 10 Pièces',
                'description' => "Set complet comprenant faitouts, poêles et couvercles en verre trempé. Revêtement en pierre de granit sans PFOA pour une cuisine saine.",
                'specifications' => [
                    'Composition' => '3 faitouts + 2 poêles + 4 couvercles + 1 spatule',
                    'Feux' => 'Compatible tous feux dont induction',
                    'Matière' => 'Aluminium forgé revêtu de granit',
                ],
                'price' => 290.00,
                'discount_price' => 239.00,
                'stock' => 35,
                'is_featured' => false,
                'slug' => 'batterie-cuisine-granit-10-pieces',
                'status' => 'active',
            ],

            // 4. Beauté, Santé & Parfums
            [
                'category_slug' => 'beaute-sante',
                'vendor_slug' => 'bioglow-cosmetique',
                'name' => 'Eau de Parfum Homme Sauvage 100ml - Flacon Rechargeable',
                'description' => "Un sillage boisé et frais aux notes de bergamote de Calabre, de poivre de Sichuan et d'ambroxan noble. 100% authentique garanti.",
                'specifications' => [
                    'Contenance' => '100 ml',
                    'Famille olfactive' => 'Boisé aromatique',
                    'Concentration' => 'Eau de Parfum',
                ],
                'price' => 395.00,
                'discount_price' => 340.00,
                'stock' => 18,
                'is_featured' => true,
                'slug' => 'eau-de-parfum-sauvage-100ml',
                'status' => 'active',
            ],
            [
                'category_slug' => 'beaute-sante',
                'vendor_slug' => 'bioglow-cosmetique',
                'name' => 'Sérum Éclat Visage Acide Hyaluronique Pur & Vitamine C',
                'description' => "Formule concentrée antioxydante pour hydrater intensément, estomper les ridules et redonner de l'éclat aux peaux ternes et fatiguées.",
                'specifications' => [
                    'Volume' => '50 ml',
                    'Type de peau' => 'Toutes peaux, même sensibles',
                    'Formule' => 'Sans paraben, sans parfum artificiel',
                ],
                'price' => 85.00,
                'discount_price' => 69.00,
                'stock' => 50,
                'is_featured' => false,
                'slug' => 'serum-visage-acide-hyaluronique-vitamine-c',
                'status' => 'active',
            ],
            [
                'category_slug' => 'beaute-sante',
                'vendor_slug' => 'bioglow-cosmetique',
                'name' => 'Lisseur Professionnel à Plaques Larges en Titane',
                'description' => "Chauffe ultra-rapide en 15 secondes, contrôle précis de la température et technologie ionique anti-frisottis pour un lissage baguette parfait.",
                'specifications' => [
                    'Plaques' => 'Titane poli miroir ultra-glisse',
                    'Température' => 'De 150°C à 230°C',
                    'Cordon' => 'Rotatif 360° de 2.5 mètres',
                ],
                'price' => 220.00,
                'discount_price' => 179.00,
                'stock' => 22,
                'is_featured' => false,
                'slug' => 'lisseur-professionnel-plaques-titane',
                'status' => 'active',
            ],

            // 5. Sports & Fitness
            [
                'category_slug' => 'sports-fitness',
                'vendor_slug' => 'fitpro-sport-store',
                'name' => 'Tapis de Course Pliable Motorisé 2.5 HP avec Écran LCD',
                'description' => "Idéal pour le cardio à domicile ! Vitesse jusqu'à 14 km/h, inclinaison réglable, système d'amorti anti-choc et connectivité Bluetooth.",
                'specifications' => [
                    'Moteur' => '2.5 HP silencieux',
                    'Vitesse' => '1.0 à 14.0 km/h',
                    'Poids max utilisateur' => '120 kg',
                    'Affichage' => 'Temps, Distance, Vitesse, Calories, Pulsations',
                ],
                'price' => 1590.00,
                'discount_price' => 1349.00,
                'stock' => 7,
                'is_featured' => true,
                'slug' => 'tapis-de-course-pliable-motorise',
                'status' => 'active',
            ],
            [
                'category_slug' => 'sports-fitness',
                'vendor_slug' => 'fitpro-sport-store',
                'name' => 'Kit Haltères de Musculation Réglables 20kg en Valise',
                'description' => "Ensemble complet avec disques en fonte, barres ergonomiques antidérapantes et barre d'extension pour transformer en haltère longue.",
                'specifications' => [
                    'Poids total' => '20 kg',
                    'Disques' => 'Fonte robuste avec bagues de serrage étoiles',
                    'Rangement' => 'Valise de transport rigide incluse',
                ],
                'price' => 190.00,
                'discount_price' => 155.00,
                'stock' => 30,
                'is_featured' => false,
                'slug' => 'kit-halteres-musculation-20kg',
                'status' => 'active',
            ],

            // 6. Bébés, Enfants & Jouets
            [
                'category_slug' => 'bebe-enfants',
                'vendor_slug' => 'moda-chic-boutique',
                'name' => 'Poussette Bébé Trio Confort Pliable Ultra-Compacte',
                'description' => "Pack poussette 3-en-1 avec nacelle, cosi groupe 0+ et assise réversible. Châssis en aluminium léger et suspension 4 roues tout-terrain.",
                'specifications' => [
                    'Âge' => 'De la naissance à 4 ans (0-22 kg)',
                    'Poids' => '7.8 kg ultra-légère',
                    'Pliage' => 'À une main en format compact',
                ],
                'price' => 780.00,
                'discount_price' => 649.00,
                'stock' => 10,
                'is_featured' => true,
                'slug' => 'poussette-bebe-trio-confort',
                'status' => 'active',
            ],

            // 7. Bricolage & Jardin
            [
                'category_slug' => 'bricolage-jardin',
                'vendor_slug' => 'electrotech-plus',
                'name' => 'Perceuse Visseuse Sans Fil 20V avec 2 Batteries & Coffret',
                'description' => "Moteur brushless sans charbon haute performance, couple puissant de 50 Nm, 2 vitesses mécaniques et mandrin auto-serrant 13mm.",
                'specifications' => [
                    'Batteries' => '2 x 20V Lithium-ion 2.0 Ah',
                    'Couple max' => '50 Nm avec 21 positions de réglage',
                    'Accessoires' => 'Chargeur rapide + 30 embouts et forêts + coffret',
                ],
                'price' => 240.00,
                'discount_price' => 199.00,
                'stock' => 25,
                'is_featured' => false,
                'slug' => 'perceuse-visseuse-sans-fil-20v',
                'status' => 'active',
            ],

            // 8. Animaux & Aquariophilie
            [
                'category_slug' => 'animaux-aquariophilie',
                'vendor_slug' => 'maison-et-confort',
                'name' => 'Aquarium Panoramique Verre Épuré 50L avec Éclairage LED',
                'description' => "Cuve en verre poli extra-clair avec système de filtration complet silencieux et éclairage LED haute intensité avec mode jour/nuit.",
                'specifications' => [
                    'Capacité' => '50 Litres',
                    'Dimensions' => '50 x 30 x 35 cm',
                    'Équipement inclus' => 'Filtre cascade + Rampe LED tactile + Chauffage 50W',
                ],
                'price' => 280.00,
                'discount_price' => 229.00,
                'stock' => 15,
                'is_featured' => true,
                'slug' => 'aquarium-panoramique-verre-50l',
                'status' => 'active',
            ],
            [
                'category_slug' => 'animaux-aquariophilie',
                'vendor_slug' => 'maison-et-confort',
                'name' => 'Distributeur Automatique de Nourriture Connecté WiFi',
                'description' => "Programmez et contrôlez les repas de vos animaux depuis votre smartphone ! Caméra HD intégrée et enregistrement vocal personnalisable.",
                'specifications' => [
                    'Capacité réservoir' => '4 Litres de croquettes sèches',
                    'Connectivité' => 'WiFi 2.4 GHz avec application mobile gratuite',
                    'Alimentation' => 'USB avec batterie de secours en cas de coupure',
                ],
                'price' => 210.00,
                'discount_price' => 169.00,
                'stock' => 18,
                'is_featured' => false,
                'slug' => 'distributeur-automatique-croquettes-wifi',
                'status' => 'active',
            ],
        ];

        $imageIndex = 0;
        foreach ($productsData as $data) {
            $category = $categories[$data['category_slug']] ?? null;
            $vendor = $vendors->firstWhere('slug', $data['vendor_slug']) ?? $vendors->first();

            if (!$category || !$vendor) {
                continue;
            }

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'specifications' => $data['specifications'],
                    'price' => $data['price'],
                    'discount_price' => $data['discount_price'] ?? null,
                    'stock' => $data['stock'],
                    'is_featured' => $data['is_featured'] ?? false,
                    'status' => $data['status'] ?? 'active',
                    'category_id' => $category->id,
                    'vendor_id' => $vendor->id,
                ]
            );

            // Assign sample images so each product has multiple images (3 images)
            $existingCount = $product->images()->count();
            if ($existingCount < 3 && !empty($sampleImages)) {
                $numToCreate = 3 - $existingCount;
                for ($i = 0; $i < $numToCreate; $i++) {
                    $imagePath = $sampleImages[($imageIndex + $i) % count($sampleImages)];
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imagePath,
                        'is_primary' => ($existingCount === 0 && $i === 0),
                        'sort_order' => $existingCount + $i + 1,
                    ]);
                }
                $imageIndex += $numToCreate;
            }
        }
    }
}
