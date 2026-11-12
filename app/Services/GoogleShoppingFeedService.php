<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\Cache\MarketplaceCacheService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use XMLWriter;

class GoogleShoppingFeedService
{
    public const CACHE_KEY = 'google_shopping_feed_xml';
    public const CACHE_TTL = 3600; // 1 hour

    /**
     * Map internal category slugs to official Google Product Taxonomy numerical IDs.
     * Google accepts either numerical IDs or full category paths.
     */
    protected array $categoryTaxonomyMap = [
        // Aquariums & Fish
        'aquariums' => '3238', // Animals & Pet Supplies > Pet Supplies > Fish Supplies > Aquariums
        'aquarium-materials' => '505307', // Animals & Pet Supplies > Pet Supplies > Fish Supplies > Aquarium Accessories
        'fish' => '5047', // Animals & Pet Supplies > Live Animals > Fish
        'animaux-aquariophilie' => '2', // Animals & Pet Supplies

        // Electronics & Tech
        'electronique' => '222', // Electronics
        'electronique-high-tech' => '222',

        // Fashion & Apparel
        'mode-habillement' => '1604', // Apparel & Accessories

        // Home, Kitchen & Appliances
        'maison-cuisine' => '632', // Home & Garden
        'maison-cuisine-electromenager' => '632',

        // Beauty & Health
        'beaute-sante' => '469', // Health & Beauty
        'beaute-sante-parfums' => '469',

        // Sports & Fitness
        'sports-fitness' => '988', // Sporting Goods

        // Toys, Baby & Kids
        'bebe-enfants' => '1239', // Toys & Games
        'bebes-enfants-jouets' => '1239',

        // DIY, Hardware & Garden
        'bricolage-jardin' => '632', // Home & Garden
    ];

    /**
     * Get or build the cached Google Shopping XML feed.
     */
    public function getFeedXml(bool $forceRefresh = false): string
    {
        if ($forceRefresh) {
            $this->clearCache();
        }

        if (!$forceRefresh && Cache::has(self::CACHE_KEY)) {
            $cached = Cache::get(self::CACHE_KEY);
            // If cached value is valid and contains items, return it
            if (is_string($cached) && str_contains($cached, '<item>')) {
                return $cached;
            }
        }

        $xml = $this->generateXml();

        // Only cache when items are present to avoid locking an empty state
        if (str_contains($xml, '<item>')) {
            Cache::put(self::CACHE_KEY, $xml, self::CACHE_TTL);
        }

        return $xml;
    }

    /**
     * Invalidate the cached feed XML.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Generate the complete RSS 2.0 Google Shopping XML feed string.
     */
    public function generateXml(): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->setIndent(true);
        $xml->setIndentString('  ');

        // Root RSS element with Google Merchant Center namespace
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $xml->startElement('channel');
        $xml->writeElement('title', 'AquawaSouk');
        $xml->writeElement('link', $this->getBaseUrl());
        $xml->writeElement('description', 'AquawaSouk Product Catalog');

        $query = $this->getEligibleProductsQuery();

        // Process catalog in chunks of 200 to keep memory footprint minimal
        $query->chunkById(200, function ($products) use ($xml) {
            foreach ($products as $product) {
                $this->writeProductItems($xml, $product);
            }
        });

        $xml->endElement(); // channel
        $xml->endElement(); // rss
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /**
     * Write XML item(s) for a product (either variants or standalone).
     */
    protected function writeProductItems(XMLWriter $xml, Product $product): void
    {
        $images = $this->getProductImages($product);

        // Require at least a valid primary image to avoid Google Merchant Center rejection
        if (empty($images['primary'])) {
            return;
        }

        // Check if product has active variants
        $variations = $product->variations;

        if ($variations && $variations->isNotEmpty()) {
            foreach ($variations as $variation) {
                $this->writeVariantItem($xml, $product, $variation, $images);
            }
        } else {
            $this->writeStandaloneItem($xml, $product, $images);
        }
    }

    /**
     * Write a standalone product item to XML.
     */
    protected function writeStandaloneItem(XMLWriter $xml, Product $product, array $images): void
    {
        $xml->startElement('item');

        // Unique Offer ID
        $xml->writeElement('g:id', (string) $product->id);

        // Basic Product Data
        $xml->writeElement('g:title', $this->sanitizeTitle($product->name));
        $xml->writeElement('g:description', $this->sanitizeDescription($product->description, $product->name));
        $xml->writeElement('g:link', $this->productUrl($product));

        // Images
        $xml->writeElement('g:image_link', $images['primary']);
        foreach ($images['additional'] as $additionalImage) {
            $xml->writeElement('g:additional_image_link', $additionalImage);
        }

        // Availability
        $availability = $this->resolveAvailability((int) $product->stock, (string) $product->status);
        $xml->writeElement('g:availability', $availability);

        // Price & Sale Price
        $this->writePricing($xml, (float) $product->price, $product->hasDiscount() ? (float) $product->discount_price : null);

        // Condition & Brand
        $xml->writeElement('g:condition', 'new');
        $xml->writeElement('g:brand', $this->resolveBrand($product));

        // MPN & Product Identifiers
        if (!empty($product->sku)) {
            $xml->writeElement('g:mpn', (string) $product->sku);
            $xml->writeElement('g:identifier_exists', 'yes');
        } else {
            $xml->writeElement('g:identifier_exists', 'no');
        }

        // Categories
        $googleCategory = $this->getGoogleProductCategory($product->category);
        if ($googleCategory !== null) {
            $xml->writeElement('g:google_product_category', $googleCategory);
        }

        if ($product->category && !empty($product->category->name)) {
            $xml->writeElement('g:product_type', $this->sanitizeTitle($product->category->name));
        }

        // Shipping
        $this->writeShipping($xml, $product);

        $xml->endElement(); // item
    }

    /**
     * Write a product variant item to XML.
     */
    protected function writeVariantItem(XMLWriter $xml, Product $product, ProductVariation $variation, array $images): void
    {
        $xml->startElement('item');

        // Unique ID per variant + Item Group ID linking to parent
        $xml->writeElement('g:id', "{$product->id}_{$variation->id}");
        $xml->writeElement('g:item_group_id', (string) $product->id);

        // Title with variant details
        $variantDetails = array_filter([(string) $variation->color, (string) $variation->size]);
        $variantSuffix = !empty($variantDetails) ? ' - ' . implode(' / ', $variantDetails) : '';
        $title = $this->sanitizeTitle($product->name . $variantSuffix);

        $xml->writeElement('g:title', $title);
        $xml->writeElement('g:description', $this->sanitizeDescription($product->description, $product->name));
        $xml->writeElement('g:link', $this->productUrl($product, $variation));

        // Images
        $xml->writeElement('g:image_link', $images['primary']);
        foreach ($images['additional'] as $additionalImage) {
            $xml->writeElement('g:additional_image_link', $additionalImage);
        }

        // Availability based on variation stock
        $availability = $this->resolveAvailability((int) $variation->stock, (string) $variation->status);
        $xml->writeElement('g:availability', $availability);

        // Price
        $effectivePrice = (float) ($variation->price ?? $product->price);
        $this->writePricing($xml, $effectivePrice, null);

        // Condition & Brand
        $xml->writeElement('g:condition', 'new');
        $xml->writeElement('g:brand', $this->resolveBrand($product));

        // MPN & Identifiers
        $sku = !empty($variation->sku) ? $variation->sku : $product->sku;
        if (!empty($sku)) {
            $xml->writeElement('g:mpn', (string) $sku);
            $xml->writeElement('g:identifier_exists', 'yes');
        } else {
            $xml->writeElement('g:identifier_exists', 'no');
        }

        // Variant attributes (Color / Size)
        if (!empty($variation->color)) {
            $xml->writeElement('g:color', $this->sanitizeTitle((string) $variation->color));
        }

        if (!empty($variation->size)) {
            $xml->writeElement('g:size', $this->sanitizeTitle((string) $variation->size));
        }

        // Categories
        $googleCategory = $this->getGoogleProductCategory($product->category);
        if ($googleCategory !== null) {
            $xml->writeElement('g:google_product_category', $googleCategory);
        }

        if ($product->category && !empty($product->category->name)) {
            $xml->writeElement('g:product_type', $this->sanitizeTitle($product->category->name));
        }

        // Shipping
        $this->writeShipping($xml, $product);

        $xml->endElement(); // item
    }

    /**
     * Write price and optional sale price with 3 decimal places for TND.
     */
    protected function writePricing(XMLWriter $xml, float $regularPrice, ?float $salePrice): void
    {
        $xml->writeElement('g:price', $this->formatPrice($regularPrice));

        if ($salePrice !== null && $salePrice > 0 && $salePrice < $regularPrice) {
            $xml->writeElement('g:sale_price', $this->formatPrice($salePrice));
        }
    }

    /**
     * Write shipping details.
     */
    protected function writeShipping(XMLWriter $xml, Product $product): void
    {
        $shippingPrice = 0.0;
        if ($product->vendor && $product->vendor->shipping_price !== null) {
            $shippingPrice = (float) $product->vendor->shipping_price;
        }

        $xml->startElement('g:shipping');
        $xml->writeElement('g:country', 'TN');
        $xml->writeElement('g:service', 'Standard');
        $xml->writeElement('g:price', $this->formatPrice($shippingPrice));
        $xml->endElement(); // g:shipping
    }

    /**
     * Format price to 3 decimal places for Tunisian Dinar (TND).
     */
    public function formatPrice(float $price): string
    {
        return number_format($price, 3, '.', '') . ' TND';
    }

    /**
     * Normalize stock availability according to Google specifications.
     */
    public function resolveAvailability(int $stock, string $status): string
    {
        return ($status === 'active' && $stock > 0) ? 'in_stock' : 'out_of_stock';
    }

    /**
     * Resolve brand name.
     */
    public function resolveBrand(Product $product): string
    {
        if ($product->vendor && !empty($product->vendor->shop_name)) {
            return $this->sanitizeTitle($product->vendor->shop_name);
        }

        return 'AquawaSouk';
    }

    /**
     * Match product category to Google Product Taxonomy ID.
     */
    public function getGoogleProductCategory(?Category $category): ?string
    {
        if (!$category) {
            return null;
        }

        if (!empty($category->slug) && isset($this->categoryTaxonomyMap[$category->slug])) {
            return $this->categoryTaxonomyMap[$category->slug];
        }

        if ($category->parent && !empty($category->parent->slug) && isset($this->categoryTaxonomyMap[$category->parent->slug])) {
            return $this->categoryTaxonomyMap[$category->parent->slug];
        }

        return null;
    }

    /**
     * Extract and resolve absolute HTTPS image URLs.
     * Returns an array with 'primary' and 'additional' (up to 10).
     */
    public function getProductImages(Product $product): array
    {
        $primaryUrl = null;
        $additionalUrls = [];

        // 1. Determine primary image
        $primaryImg = $product->primaryImage ?: ($product->images ? $product->images->first() : null);
        if ($primaryImg && !empty($primaryImg->image_path)) {
            $primaryUrl = $this->resolveImageUrl($primaryImg->image_path);
        }

        // If primary image resolved to placeholder or null, return empty
        if (empty($primaryUrl)) {
            return ['primary' => null, 'additional' => []];
        }

        // 2. Determine additional images
        if ($product->images && $product->images->isNotEmpty()) {
            foreach ($product->images as $img) {
                if (empty($img->image_path)) {
                    continue;
                }

                $resolved = $this->resolveImageUrl($img->image_path);
                if (!empty($resolved) && $resolved !== $primaryUrl && !in_array($resolved, $additionalUrls, true)) {
                    $additionalUrls[] = $resolved;
                    if (count($additionalUrls) >= 10) {
                        break;
                    }
                }
            }
        }

        return [
            'primary' => $primaryUrl,
            'additional' => $additionalUrls,
        ];
    }

    /**
     * Convert an image path or URL into a clean, absolute HTTPS URL.
     */
    public function resolveImageUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Reject default placeholder cover image to ensure Google compliance
        if (str_ends_with($path, 'cover.jpeg') || str_ends_with($path, 'cover.jpg')) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if (str_starts_with($path, 'http://')) {
                return 'https://' . substr($path, 7);
            }
            return $path;
        }

        $cleanPath = ltrim($path, '/');
        if (!str_starts_with($cleanPath, 'uploads/')) {
            $cleanPath = 'uploads/' . $cleanPath;
        }

        return $this->formatUrl($cleanPath);
    }

    /**
     * Generate canonical product URL.
     */
    public function productUrl(Product $product, ?ProductVariation $variation = null): string
    {
        $url = rtrim($this->getBaseUrl(), '/') . '/products/' . $product->slug;

        if ($variation !== null) {
            $url .= '?variant=' . $variation->id;
        }

        return $url;
    }

    /**
     * Build an absolute HTTPS URL.
     */
    public function formatUrl(string $path): string
    {
        $cleanPath = ltrim($path, '/');
        return rtrim($this->getBaseUrl(), '/') . '/' . $cleanPath;
    }

    /**
     * Determine canonical base URL (ensures HTTPS).
     */
    public function getBaseUrl(): string
    {
        // 1. Explicit configuration if defined
        $configUrl = config('services.google_shopping.base_url');
        if (!empty($configUrl)) {
            return $this->ensureHttps($configUrl);
        }

        // 2. Production request host if available
        if (request() && request()->getHost() && !in_array(request()->getHost(), ['localhost', '127.0.0.1'], true)) {
            return 'https://' . request()->getHost();
        }

        // 3. APP_URL from .env if valid non-localhost domain
        $appUrl = config('app.url');
        if (!empty($appUrl)) {
            $host = parse_url($appUrl, PHP_URL_HOST);
            if ($host && !in_array($host, ['localhost', '127.0.0.1'], true)) {
                return $this->ensureHttps($appUrl);
            }
        }

        // 4. Default production website URL
        return 'https://aquawasouk.com';
    }

    /**
     * Ensure URL has https:// protocol.
     */
    protected function ensureHttps(string $url): string
    {
        if (str_starts_with($url, 'http://')) {
            return 'https://' . substr($url, 7);
        }

        if (!str_starts_with($url, 'https://')) {
            return 'https://' . ltrim($url, '/');
        }

        return $url;
    }

    /**
     * Clean and truncate title for Google Merchant Center (max 150 chars).
     */
    public function sanitizeTitle(string $title): string
    {
        $clean = strip_tags($title);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        return mb_substr($clean, 0, 150, 'UTF-8');
    }

    /**
     * Clean and truncate description for Google Merchant Center (max 5000 chars).
     */
    public function sanitizeDescription(?string $description, string $fallbackTitle): string
    {
        if (empty($description)) {
            return $this->sanitizeTitle($fallbackTitle);
        }

        $clean = strip_tags($description);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $clean);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        if (empty($clean)) {
            return $this->sanitizeTitle($fallbackTitle);
        }

        return mb_substr($clean, 0, 5000, 'UTF-8');
    }

    /**
     * Base query for eligible products.
     */
    public function getEligibleProductsQuery(): Builder
    {
        return Product::query()
            ->with([
                'vendor:id,shop_name,shipping_price,status',
                'category:id,parent_id,name,slug',
                'category.parent:id,name,slug',
                'images:id,product_id,image_path,is_primary,sort_order',
                'primaryImage:id,product_id,image_path,is_primary',
                'variations' => function ($query) {
                    $query->where('status', 'active')->orderBy('id');
                },
            ])
            ->where('status', 'active')
            ->where('price', '>', 0)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->whereHas('vendor', function ($q) {
                $q->where('status', 'active');
            })
            ->whereHas('images')
            ->orderBy('id');
    }

    /**
     * Gather feed statistics for reporting / diagnostics.
     */
    public function getStats(): array
    {
        $query = $this->getEligibleProductsQuery();
        $totalProducts = $query->count();

        $inStockCount = 0;
        $outOfStockCount = 0;
        $discountedCount = 0;
        $variantsCount = 0;
        $totalItems = 0;

        $query->chunkById(200, function ($products) use (&$inStockCount, &$outOfStockCount, &$discountedCount, &$variantsCount, &$totalItems) {
            foreach ($products as $product) {
                $images = $this->getProductImages($product);
                if (empty($images['primary'])) {
                    continue;
                }

                $variations = $product->variations;
                if ($variations && $variations->isNotEmpty()) {
                    foreach ($variations as $variation) {
                        $totalItems++;
                        $variantsCount++;
                        if ($variation->status === 'active' && $variation->stock > 0) {
                            $inStockCount++;
                        } else {
                            $outOfStockCount++;
                        }
                    }
                } else {
                    $totalItems++;
                    if ($product->status === 'active' && $product->stock > 0) {
                        $inStockCount++;
                    } else {
                        $outOfStockCount++;
                    }
                    if ($product->hasDiscount()) {
                        $discountedCount++;
                    }
                }
            }
        });

        return [
            'total_eligible_products' => $totalProducts,
            'total_feed_items' => $totalItems,
            'variants_count' => $variantsCount,
            'in_stock_items' => $inStockCount,
            'out_of_stock_items' => $outOfStockCount,
            'discounted_items' => $discountedCount,
            'base_url' => $this->getBaseUrl(),
        ];
    }
}

