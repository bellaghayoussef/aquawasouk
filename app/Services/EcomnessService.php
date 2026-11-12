<?php

namespace App\Services;

use App\Models\AssistantSetting;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EcomnessService
{
    public const VENDOR_NAME = 'Aquawasouk eco';
    public const VENDOR_SLUG = 'aquawasouk-eco';

    /**
     * Official 24 Governorates accepted by Ecomness API
     */
    public const GOVERNORATES = [
        'Ariana',
        'Béja',
        'Ben Arous',
        'Bizerte',
        'Gabès',
        'Gafsa',
        'Jendouba',
        'Kairouan',
        'Kasserine',
        'Kébili',
        'Kef',
        'Mahdia',
        'Manouba',
        'Médenine',
        'Monastir',
        'Nabeul',
        'Sfax',
        'Sidi Bouzid',
        'Siliana',
        'Sousse',
        'Tataouine',
        'Tozeur',
        'Tunis',
        'Zaghouan'
    ];

    /**
     * Default Category Mapping from Ecomness codes to AquawaSouk Category Names
     */
    public const DEFAULT_CATEGORY_MAPPING = [
        'FURNITURE' => 'Maison, Cuisine & Électroménager',
        'DECORATION' => 'Maison, Cuisine & Électroménager',
        'HOME_APPLIANCES' => 'Maison, Cuisine & Électroménager',
        'KITCHEN' => 'Maison, Cuisine & Électroménager',
        'ELECTRONICS' => 'Électronique & High-Tech',
        'CLOTHING' => 'Mode & Habillement',
        'BEAUTY' => 'Beauté, Santé & Parfums',
        'SPORTS' => 'Sports & Fitness',
        'TOYS' => 'Bébés, Enfants & Jouets',
        'JEWELRY' => 'Mode & Habillement',
        'GARDEN' => 'Bricolage & Jardin',
        'CAR_ACCESSORIES' => 'Bricolage & Jardin',
        'ACCESSORIES' => 'Mode & Habillement',
    ];

    public function getBaseUrl(): string
    {
        return rtrim(config('ecomness.base_url', 'https://ecomness.com/api/v1'), '/');
    }

    public function getApiKey(): ?string
    {
        return AssistantSetting::get('ecomness_api_key', config('ecomness.api_key'));
    }

    public function setApiKey(string $apiKey): void
    {
        AssistantSetting::set('ecomness_api_key', trim($apiKey));
    }

    public function hasApiKey(): bool
    {
        $key = $this->getApiKey();
        return !empty($key) && strlen($key) > 5;
    }

    /**
     * Make an authenticated HTTP request to Ecomness API
     */
    public function makeRequest(string $method, string $endpoint, array $data = [], array $query = []): array
    {
        $apiKey = $this->getApiKey();
        if (empty($apiKey)) {
            return [
                'success' => false,
                'status' => 401,
                'error' => 'Clé API Ecomness manquante. Veuillez configurer votre clé dans les paramètres.',
            ];
        }

        $url = $this->getBaseUrl() . '/' . ltrim($endpoint, '/');

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
                ->timeout(20)
                ->send($method, $url, [
                    'json' => !empty($data) ? $data : null,
                    'query' => !empty($query) ? $query : null,
                ]);

            // Handle rate limiting (HTTP 429)
            if ($response->status() === 429) {
                $retryAfter = (int) ($response->header('Retry-After') ?? 10);
                Log::warning("Ecomness API rate limited. Retry-After: {$retryAfter}s");
                return [
                    'success' => false,
                    'status' => 429,
                    'error' => "Limite de requêtes atteinte. Réessayez dans {$retryAfter} secondes.",
                    'retry_after' => $retryAfter,
                ];
            }

            $json = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $json['data'] ?? $json,
                ];
            }

            $errorMessage = $json['error']['message'] ?? $json['message'] ?? 'Erreur inconnue de l\'API Ecomness';
            $errorCode = $json['error']['code'] ?? 'UNKNOWN_ERROR';
            $details = $json['error']['details'] ?? [];

            Log::error("Ecomness API Error [{$errorCode}]: {$errorMessage}", ['details' => $details, 'endpoint' => $endpoint]);

            return [
                'success' => false,
                'status' => $response->status(),
                'code' => $errorCode,
                'error' => $errorMessage,
                'details' => $details,
            ];
        } catch (\Throwable $e) {
            Log::error("Ecomness Connection Exception: " . $e->getMessage(), ['endpoint' => $endpoint]);
            return [
                'success' => false,
                'status' => 500,
                'error' => 'Impossible de contacter l\'API Ecomness: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test connection to Ecomness API
     */
    public function testConnection(): array
    {
        $res = $this->makeRequest('GET', 'reference');
        if ($res['success']) {
            return [
                'success' => true,
                'message' => 'Connexion à l\'API Ecomness réussie !',
                'categories_count' => count($res['data']['productCategories'] ?? []),
                'colors_count' => count($res['data']['colors'] ?? []),
            ];
        }

        return [
            'success' => false,
            'message' => $res['error'] ?? 'Échec de connexion à Ecomness.',
        ];
    }

    /**
     * Get or create the Vendor record for "Aquawasouk eco"
     */
    public function getVendor(): Vendor
    {
        $vendor = Vendor::where('shop_name', self::VENDOR_NAME)->first();
        if ($vendor) {
            return $vendor;
        }

        // Search by slug fallback
        $vendor = Vendor::where('slug', self::VENDOR_SLUG)->first();
        if ($vendor) {
            return $vendor;
        }

        // Create vendor user if not exists
        $user = User::firstOrCreate(
            ['email' => 'ecomness@aquawasouk.com'],
            [
                'name' => self::VENDOR_NAME,
                'password' => bcrypt(Str::random(16)),
                'role' => 'vendor',
            ]
        );

        return Vendor::create([
            'user_id' => $user->id,
            'shop_name' => self::VENDOR_NAME,
            'slug' => self::VENDOR_SLUG,
            'description' => 'Boutique officielle des produits certifiés Ecomness DropShipping sur AquawaSouk.',
            'phone' => '+21656818880',
            'city' => 'Tunis',
            'country' => 'Tunisie',
            'status' => 'active',
            'commission_rate' => 10.00,
            'shipping_price' => 8.00,
        ]);
    }

    /**
     * Fetch Reference Data from Ecomness API (Governorates, Categories, Colors, Sources)
     */
    public function fetchReference(bool $forceRefresh = false): array
    {
        if (!$forceRefresh && Cache::has('ecomness_reference_data')) {
            return Cache::get('ecomness_reference_data');
        }

        $res = $this->makeRequest('GET', 'reference');
        if ($res['success']) {
            $data = $res['data'];
            Cache::put('ecomness_reference_data', $data, 86400); // 24 hours
            return $data;
        }

        return [
            'governorates' => self::GOVERNORATES,
            'productCategories' => array_keys(self::DEFAULT_CATEGORY_MAPPING),
            'colors' => [],
            'orderSources' => ['NA', 'FB', 'INSTAGRAM', 'TIKTOK'],
        ];
    }

    /**
     * Fetch Statuses descriptions from Ecomness API
     */
    public function fetchStatuses(bool $forceRefresh = false): array
    {
        if (!$forceRefresh && Cache::has('ecomness_statuses_data')) {
            return Cache::get('ecomness_statuses_data');
        }

        $res = $this->makeRequest('GET', 'statuses');
        if ($res['success']) {
            $data = $res['data'];
            Cache::put('ecomness_statuses_data', $data, 86400);
            return $data;
        }

        return [];
    }

    /**
     * Get or initialize the Category Mapping (Ecomness category code -> AquawaSouk category ID)
     */
    public function getCategoryMapping(): array
    {
        $saved = AssistantSetting::get('ecomness_category_mapping', []);
        if (!empty($saved) && is_array($saved)) {
            return $saved;
        }

        // Initialize default mapping
        $mapping = [];
        $localCategories = Category::pluck('id', 'name')->toArray();

        foreach (self::DEFAULT_CATEGORY_MAPPING as $ecomCat => $preferredName) {
            if (isset($localCategories[$preferredName])) {
                $mapping[$ecomCat] = $localCategories[$preferredName];
            } else {
                // Find first available or match by slug/part
                $firstCat = Category::first();
                $mapping[$ecomCat] = $firstCat ? $firstCat->id : 1;
            }
        }

        AssistantSetting::set('ecomness_category_mapping', $mapping);
        return $mapping;
    }

    /**
     * Save custom Category Mapping
     */
    public function saveCategoryMapping(array $mapping): void
    {
        AssistantSetting::set('ecomness_category_mapping', $mapping);

        // Also update categories table ecomness_category column
        foreach ($mapping as $ecomCat => $catId) {
            Category::where('id', $catId)->update(['ecomness_category' => $ecomCat]);
        }
    }

    /**
     * Resolve AquawaSouk Category ID for an Ecomness category string
     */
    public function resolveCategoryId(string $ecomnessCategory): int
    {
        $mapping = $this->getCategoryMapping();
        $code = strtoupper(trim($ecomnessCategory));

        if (isset($mapping[$code])) {
            return (int) $mapping[$code];
        }

        // Check if category has ecomness_category
        $cat = Category::where('ecomness_category', $code)->first();
        if ($cat) {
            return $cat->id;
        }

        // Fallback to preferred default name
        if (isset(self::DEFAULT_CATEGORY_MAPPING[$code])) {
            $preferred = self::DEFAULT_CATEGORY_MAPPING[$code];
            $cat = Category::where('name', $preferred)->first();
            if ($cat) {
                return $cat->id;
            }
        }

        // Absolute fallback to first category
        $fallback = Category::first();
        return $fallback ? $fallback->id : 1;
    }

    /**
     * Fetch a paginated list of products from Ecomness API
     */
    public function fetchProducts(int $page = 1, int $pageSize = 50, ?string $category = null, ?string $search = null): array
    {
        $query = [
            'page' => $page,
            'pageSize' => min(100, max(1, $pageSize)),
        ];

        if (!empty($category)) {
            $query['category'] = $category;
        }
        if (!empty($search)) {
            $query['search'] = $search;
        }
        $query['inStock'] = true;
        return $this->makeRequest('GET', 'products', [], $query);
    }

    /**
     * Synchronize a single product from Ecomness into AquawaSouk under vendor "Aquawasouk eco"
     */
    public function syncSingleProduct(array $p): Product
    {
        $vendor = $this->getVendor();
        $code = trim($p['code']);
        $categoryId = $this->resolveCategoryId($p['category'] ?? 'ACCESSORIES');

        $wholesalePrice = (float) ($p['price'] ?? 0);
        $minPrice = (float) ($p['minimumUnitPrice'] ?? $wholesalePrice);

        // Determine stock & variants price fallback
        $hasVariants = !empty($p['variants']);
        if ($hasVariants) {
            $totalStock = 0;
            $variantWholesale = [];
            $variantMins = [];
            foreach ($p['variants'] as $v) {
                $totalStock += (int) ($v['stock'] ?? 0);
                if (!empty($v['price']) && (float) $v['price'] > 0) {
                    $variantWholesale[] = (float) $v['price'];
                }
                if (!empty($v['minimumUnitPrice']) && (float) $v['minimumUnitPrice'] > 0) {
                    $variantMins[] = (float) $v['minimumUnitPrice'];
                }
            }
            if ($wholesalePrice <= 0 && !empty($variantWholesale)) {
                $wholesalePrice = min($variantWholesale);
            }
            if ($minPrice <= 0 && !empty($variantMins)) {
                $minPrice = min($variantMins);
            } elseif ($minPrice <= 0) {
                $minPrice = $wholesalePrice;
            }
        } else {
            $totalStock = (int) ($p['stock'] ?? 0);
        }

        // Calculate default selling price: at least minimumUnitPrice, with recommended profit margin
        $marginPercent = (float) config('ecomness.default_profit_margin_percent', 25);
        $calculatedPrice = round($wholesalePrice * (1 + ($marginPercent / 100)), 2);
        $sellingPrice = max($minPrice, $calculatedPrice);

        $status = $totalStock > 0 ? 'active' : 'out_of_stock';

        // Find or create product by ecomness_code or sku
        $product = Product::where('ecomness_code', $code)
            ->orWhere('sku', $code)
            ->first();

        if ($product && $totalStock > 5) {
            $product->update([
                'vendor_id' => $vendor->id,
                'category_id' => $categoryId,
                'name' => $p['name'] ?? $product->name,
                'description' => $p['description'] ?? $product->description,
                'ecomness_code' => $code,
                'ecomness_min_price' => $minPrice,
                'ecomness_category' => $p['category'] ?? null,
                'ecomness_raw_data' => $p,
                'stock' => $totalStock > 10 ? 10 : $totalStock,
                'status' => $status,
                // Only update price if current price is below minimum allowed or was 0
                'price' => $product->price > 0 ? max($product->price, $minPrice) : $sellingPrice,
            ]);
        } else {
            // Generate clean unique slug
            $baseSlug = Str::slug($p['name'] ?? $code);
            $slug = $baseSlug;
            $counter = 1;
            while (Product::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $product = Product::create([
                'vendor_id' => $vendor->id,
                'category_id' => $categoryId,
                'name' => $p['name'] ?? 'Produit Ecomness ' . $code,
                'slug' => $slug,
                'description' => $p['description'] ?? '',
                'price' => $sellingPrice,
                'discount_price' => null,
                'stock' => $totalStock,
                'sku' => $code,
                'ecomness_code' => $code,
                'ecomness_min_price' => $minPrice,
                'ecomness_category' => $p['category'] ?? null,
                'ecomness_raw_data' => $p,
                'is_featured' => false,
                'status' => $status,
                'views' => 0,
            ]);
        }

        // Sync Images
        if (!empty($p['media']) && is_array($p['media'])) {
            $existingImages = $product->images()->pluck('image_path')->toArray();
            $sort = 0;
            foreach ($p['media'] as $media) {
                $url = $media['url'] ?? null;
                if ($url && !in_array($url, $existingImages)) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $url,
                        'is_primary' => $sort === 0 && empty($existingImages),
                        'sort_order' => $sort,
                    ]);
                    $existingImages[] = $url;
                }
                $sort++;
            }
        }

        // Sync Variants
        if ($hasVariants) {
            $minVarSellingPrice = null;
            foreach ($p['variants'] as $v) {
                $vCode = trim($v['code']);
                $vMinPrice = (float) ($v['minimumUnitPrice'] ?? $v['price'] ?? $minPrice);
                $vWholesale = (float) ($v['price'] ?? $wholesalePrice);
                $vSellingPrice = max($vMinPrice, round($vWholesale * (1 + ($marginPercent / 100)), 2));
                $vStock = (int) ($v['stock'] ?? 0);

                if ($vSellingPrice > 0 && ($minVarSellingPrice === null || $vSellingPrice < $minVarSellingPrice)) {
                    $minVarSellingPrice = $vSellingPrice;
                }

                ProductVariation::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'ecomness_code' => $vCode,
                    ],
                    [
                        'sku' => $vCode,
                        'color' => $v['color'] ?? null,
                        'size' => $v['size'] ?? null,
                        'ecomness_min_price' => $vMinPrice,
                        'price' => $vSellingPrice,
                        'stock' => $vStock,
                        'status' => $vStock > 0 ? 'active' : 'inactive',
                    ]
                );
            }

            // Ensure the parent product price is set to the minimum variant price
            if ($minVarSellingPrice && ($product->price <= 0 || (float) $product->price == 0)) {
                $product->update(['price' => $minVarSellingPrice]);
            }
        }

        return $product;
    }

    /**
     * Synchronize all products from Ecomness API across all pages
     */
    public function syncAllProducts(?callable $onProgress = null): array
    {
        $firstPage = $this->fetchProducts(1, 50);
        if (!$firstPage['success']) {
            return [
                'success' => false,
                'error' => $firstPage['error'] ?? 'Échec lors de la récupération des produits Ecomness.',
                'synced' => 0,
            ];
        }

        $totalPages = (int) ($firstPage['data']['totalPages'] ?? 1);
        $totalItems = (int) ($firstPage['data']['total'] ?? 0);
        $syncedCount = 0;

        // Process first page
        $products = $firstPage['data']['products'] ?? [];
        foreach ($products as $p) {
            $this->syncSingleProduct($p);
            $syncedCount++;
            if ($onProgress) {
                $onProgress($syncedCount, $totalItems);
            }
        }

        // Process remaining pages
        for ($page = 2; $page <= $totalPages; $page++) {
            // Slight delay to stay well within 120 req/min rate limit
            usleep(300000); // 300ms

            $res = $this->fetchProducts($page, 50);
            if ($res['success']) {
                $pageProducts = $res['data']['products'] ?? [];
                foreach ($pageProducts as $p) {
                    $this->syncSingleProduct($p);
                    $syncedCount++;
                    if ($onProgress) {
                        $onProgress($syncedCount, $totalItems);
                    }
                }
            }
        }

        AssistantSetting::set('ecomness_last_sync_at', now()->toIso8601String());
        AssistantSetting::set('ecomness_synced_products_count', $syncedCount);

        return [
            'success' => true,
            'synced' => $syncedCount,
            'total' => $totalItems,
            'pages' => $totalPages,
        ];
    }

    /**
     * Normalize a Tunisian governorate name to match Ecomness API exact accepted values
     */
    public function normalizeGovernorate(?string $input): string
    {
        if (empty($input)) {
            return 'Tunis';
        }

        $clean = mb_strtolower(trim($input), 'UTF-8');

        // Direct fuzzy / common aliases mapping
        $aliases = [
            'tunis' => 'Tunis',
            'ariana' => 'Ariana',
            'ben arous' => 'Ben Arous',
            'benarous' => 'Ben Arous',
            'manouba' => 'Manouba',
            'nabeul' => 'Nabeul',
            'zaghouan' => 'Zaghouan',
            'bizerte' => 'Bizerte',
            'beja' => 'Béja',
            'béja' => 'Béja',
            'jendouba' => 'Jendouba',
            'kef' => 'Kef',
            'le kef' => 'Kef',
            'siliana' => 'Siliana',
            'sousse' => 'Sousse',
            'monastir' => 'Monastir',
            'mahdia' => 'Mahdia',
            'sfax' => 'Sfax',
            'kairouan' => 'Kairouan',
            'kasserine' => 'Kasserine',
            'sidi bouzid' => 'Sidi Bouzid',
            'gabes' => 'Gabès',
            'gabès' => 'Gabès',
            'medenine' => 'Médenine',
            'médenine' => 'Médenine',
            'djerba' => 'Médenine',
            'midoun' => 'Médenine',
            'houmt souk' => 'Médenine',
            'zarzis' => 'Médenine',
            'tataouine' => 'Tataouine',
            'gafsa' => 'Gafsa',
            'tozeur' => 'Tozeur',
            'kebili' => 'Kébili',
            'kébili' => 'Kébili',
        ];

        foreach ($aliases as $key => $official) {
            if (str_contains($clean, $key)) {
                return $official;
            }
        }

        // Check if exact match exists in official governorates
        foreach (self::GOVERNORATES as $gov) {
            if (strcasecmp($gov, $input) === 0) {
                return $gov;
            }
        }

        return 'Tunis';
    }

    /**
     * Normalize Tunisian phone number to strictly 8 digits for Ecomness API
     */
    public function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/[^\d]/', '', (string) $phone);

        // If prefixed with 216 country code (e.g. 21622123456)
        if (str_starts_with($digits, '216') && strlen($digits) === 11) {
            return substr($digits, 3);
        }

        // If prefixed with 00216
        if (str_starts_with($digits, '00216') && strlen($digits) === 13) {
            return substr($digits, 5);
        }

        // If exactly 8 digits
        if (strlen($digits) === 8) {
            return $digits;
        }

        // If longer than 8 digits, take the last 8 digits
        if (strlen($digits) > 8) {
            return substr($digits, -8);
        }

        // Fallback default placeholder if too short to prevent API rejection
        return str_pad($digits, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Forward an order to Ecomness API (POST /orders) if it contains Ecomness products
     */
    public function forwardOrderToEcomness(Order $order): ?array
    {
        // 1. If already forwarded and has an Ecomness code, avoid duplicate
        if (!empty($order->ecomness_order_code)) {
            Log::info("Order #{$order->order_number} already forwarded to Ecomness with code: {$order->ecomness_order_code}");
            return [
                'success' => true,
                'already_synced' => true,
                'code' => $order->ecomness_order_code,
            ];
        }

        // 2. Identify order items belonging to Ecomness
        $vendor = $this->getVendor();
        $order->loadMissing(['items.product', 'items.variation', 'user']);

        $ecomnessItems = [];
        foreach ($order->items as $item) {
            $prod = $item->product;
            if (!$prod)
                continue;

            $isEcomness = !empty($prod->ecomness_code) || $prod->vendor_id === $vendor->id;
            if ($isEcomness) {
                $productCode = $prod->ecomness_code ?: $prod->sku;
                $variantCode = null;

                if ($item->variation && !empty($item->variation->ecomness_code)) {
                    $variantCode = $item->variation->ecomness_code;
                }

                $minPrice = (float) ($item->variation?->ecomness_min_price ?? $prod->ecomness_min_price ?? $item->price);
                $unitPrice = max((float) $item->price, $minPrice);

                $line = [
                    'productCode' => $productCode,
                    'quantity' => (int) $item->quantity,
                    'unitPrice' => (float) $unitPrice,
                ];

                if (!empty($variantCode)) {
                    $line['variantCode'] = $variantCode;
                }

                $ecomnessItems[] = [
                    'item_id' => $item->id,
                    'payload' => $line,
                ];
            }
        }

        // If this order contains no Ecomness products, nothing to forward
        if (empty($ecomnessItems)) {
            return null;
        }

        // 3. Prepare Customer info
        $customerName = trim($order->user?->name ?? 'Client AquawaSouk');
        $nameParts = explode(' ', $customerName, 2);
        $firstName = strlen($nameParts[0]) >= 2 ? $nameParts[0] : 'Client';
        $lastName = isset($nameParts[1]) && strlen($nameParts[1]) >= 2 ? $nameParts[1] : 'AquawaSouk';

        $phone = $this->normalizePhone($order->phone);
        $email = $order->user?->email;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = "commande_{$order->id}@aquawasouk.com";
        }

        $state = $this->normalizeGovernorate($order->shipping_city);
        $city = strlen($order->shipping_city ?? '') >= 3 ? $order->shipping_city : $state;
        $address = strlen($order->shipping_address ?? '') >= 3 ? $order->shipping_address : "Adresse de livraison {$city}";

        // 4. Construct Ecomness order payload
        $payload = [
            'externalId' => 'aquo-' . $order->order_number,
            'customer' => [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'phone' => $phone,
                'email' => $email,
                'state' => $state,
                'city' => $city,
                'address' => $address,
            ],
            'items' => array_column($ecomnessItems, 'payload'),
            'comment' => !empty($order->notes) ? Str::limit($order->notes, 500) : 'Commande passée sur AquawaSouk',
            'openable' => true, // Autoriser ouverture du colis à la livraison
            'fragile' => false,
            'source' => 'NA',
            'manageConfirmation' => false,
        ];

        // 5. Send to Ecomness API
        $res = $this->makeRequest('POST', 'orders', $payload);

        if ($res['success']) {
            $data = $res['data'];
            $orderCode = $data['code'] ?? null;
            $subOrders = $data['subOrders'] ?? [];
            $firstSub = $subOrders[0] ?? [];
            $tracking = $firstSub['trackingNumber'] ?? null;
            $statusLabel = $firstSub['status']['label'] ?? 'Emballage en cours';

            // Update order
            $order->update([
                'ecomness_order_code' => $orderCode,
                'ecomness_status' => $statusLabel,
                'ecomness_tracking_number' => $tracking,
                'ecomness_payload' => $data,
                'ecomness_error' => null,
                'ecomness_synced_at' => now(),
            ]);

            // Update individual order items
            foreach ($ecomnessItems as $idx => $eItem) {
                OrderItem::where('id', $eItem['item_id'])->update([
                    'ecomness_code' => $eItem['payload']['productCode'],
                    'ecomness_variant_code' => $eItem['payload']['variantCode'] ?? null,
                    'ecomness_parcel_code' => $firstSub['code'] ?? null,
                ]);
            }

            Log::info("Order #{$order->order_number} successfully forwarded to Ecomness! Code: {$orderCode}");

            return [
                'success' => true,
                'code' => $orderCode,
                'status' => $statusLabel,
                'tracking' => $tracking,
            ];
        }

        // Handle failure
        $errorMsg = $res['error'] ?? 'Échec lors de l\'envoi de la commande à Ecomness';
        $order->update([
            'ecomness_error' => $errorMsg . ' (' . json_encode($res['details'] ?? []) . ')',
            'ecomness_synced_at' => now(),
        ]);

        Log::error("Failed to forward Order #{$order->order_number} to Ecomness: {$errorMsg}", ['res' => $res]);

        return [
            'success' => false,
            'error' => $errorMsg,
            'details' => $res['details'] ?? [],
        ];
    }

    /**
     * Track up to 50 orders on Ecomness and update local statuses
     */
    public function syncTrackOrders(?array $orderCodes = null): array
    {
        if (empty($orderCodes)) {
            $orderCodes = Order::whereNotNull('ecomness_order_code')
                ->whereNotIn('ecomness_status', ['Colis Livré', 'Livrée payée', 'Annulée', 'Retournée payée'])
                ->latest()
                ->take(50)
                ->pluck('ecomness_order_code')
                ->toArray();
        }

        if (empty($orderCodes)) {
            return ['success' => true, 'updated' => 0, 'message' => 'Aucune commande à mettre à jour.'];
        }

        $chunks = array_chunk($orderCodes, 50);
        $updatedCount = 0;

        foreach ($chunks as $chunk) {
            $res = $this->makeRequest('GET', 'orders', [], ['codes' => implode(',', $chunk)]);
            if ($res['success']) {
                $ordersList = $res['data']['orders'] ?? [];
                foreach ($ordersList as $ecOrder) {
                    $code = $ecOrder['code'] ?? null;
                    if (!$code)
                        continue;

                    $subOrders = $ecOrder['subOrders'] ?? [];
                    $firstSub = $subOrders[0] ?? [];
                    $statusLabel = $firstSub['status']['label'] ?? null;
                    $statusCode = $firstSub['status']['code'] ?? null;
                    $tracking = $firstSub['trackingNumber'] ?? null;

                    $localOrder = Order::where('ecomness_order_code', $code)->first();
                    if ($localOrder) {
                        $updateData = [];
                        if ($statusLabel) {
                            $updateData['ecomness_status'] = $statusLabel;
                        }
                        if ($tracking) {
                            $updateData['ecomness_tracking_number'] = $tracking;
                        }

                        // Status 107 = Colis Livré, EC02 = Livrée payée
                        if (in_array($statusCode, ['107', 'EC02'])) {
                            $updateData['status'] = 'delivered';
                        } elseif (in_array($statusCode, ['102', '104', '105'])) {
                            $updateData['status'] = 'shipped';
                        } elseif (in_array($statusCode, ['EC01', '44'])) {
                            $updateData['status'] = 'cancelled';
                        }

                        $localOrder->update($updateData);
                        $updatedCount++;
                    }
                }
            }
        }

        return [
            'success' => true,
            'updated' => $updatedCount,
        ];
    }
}

