<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\User;
use App\Models\Vendor;
use App\Services\GoogleShoppingFeedService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GoogleShoppingFeedTest extends TestCase
{
    use RefreshDatabase;

    protected Vendor $vendor;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget(GoogleShoppingFeedService::CACHE_KEY);

        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Aquawa Vendor',
        ]);

        $this->vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Aquawa Store Pro',
            'slug' => 'aquawa-store-pro',
            'status' => 'active',
            'shipping_price' => 8.00,
        ]);

        $this->category = Category::create([
            'name' => 'Électronique & High-Tech',
            'slug' => 'electronique',
            'is_active' => true,
        ]);
    }

    /**
     * Helper to create a fully eligible product with image.
     */
    protected function createProduct(array $attributes = [], array $images = []): Product
    {
        $product = Product::create(array_merge([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Produit Test Aquawa',
            'slug' => 'produit-test-aquawa-' . uniqid(),
            'description' => 'Description détaillée pour le produit test.',
            'price' => 50.00,
            'discount_price' => null,
            'stock' => 10,
            'sku' => 'SKU-' . strtoupper(uniqid()),
            'status' => 'active',
        ], $attributes));

        if (empty($images)) {
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'products/sample-test-image.jpg',
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        } else {
            foreach ($images as $index => $img) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $img['path'],
                    'is_primary' => $img['primary'] ?? ($index === 0),
                    'sort_order' => $index,
                ]);
            }
        }

        return $product->fresh(['vendor', 'category', 'images', 'primaryImage', 'variations']);
    }

    /**
     * Test endpoint returns 200 OK with correct XML content-type and cache headers.
     */
    public function test_feed_endpoint_returns_successful_xml_response(): void
    {
        $this->createProduct();

        $response = $this->get('/google-shopping.xml');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('UTF-8', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    /**
     * Test feed XML is valid RSS 2.0 with Google namespace and channel info.
     */
    public function test_feed_xml_structure_is_valid_rss_with_google_namespace(): void
    {
        $this->createProduct();

        $response = $this->get('/google-shopping.xml');
        $xmlContent = $response->getContent();

        $xml = simplexml_load_string($xmlContent);
        $this->assertNotFalse($xml, 'XML should be syntactically valid');

        $this->assertEquals('2.0', (string) $xml['version']);
        $namespaces = $xml->getNamespaces(true);
        $this->assertArrayHasKey('g', $namespaces);
        $this->assertEquals('http://base.google.com/ns/1.0', $namespaces['g']);

        $this->assertEquals('AquawaSouk', (string) $xml->channel->title);
        $this->assertNotEmpty((string) $xml->channel->link);
        $this->assertEquals('AquawaSouk Product Catalog', (string) $xml->channel->description);
    }

    /**
     * Test feed contains required Google Shopping attributes.
     */
    public function test_feed_contains_active_products_with_required_google_fields(): void
    {
        $product = $this->createProduct([
            'name' => 'Filtre Aquarium Externe 1000L/H',
            'slug' => 'filtre-aquarium-externe',
            'description' => 'Filtre puissant pour aquarium d\'eau douce.',
            'price' => 120.00,
            'stock' => 15,
            'sku' => 'FLT-1000',
        ]);

        $response = $this->get('/google-shopping.xml');
        $xml = simplexml_load_string($response->getContent());

        $found = false;
        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            if ((string) $g->id === (string) $product->id) {
                $found = true;
                $this->assertEquals('Filtre Aquarium Externe 1000L/H', (string) $g->title);
                $this->assertEquals('Filtre puissant pour aquarium d\'eau douce.', (string) $g->description);
                $this->assertEquals('https://aquawasouk.com/products/filtre-aquarium-externe', (string) $g->link);
                $this->assertEquals('https://aquawasouk.com/uploads/products/sample-test-image.jpg', (string) $g->image_link);
                $this->assertEquals('in_stock', (string) $g->availability);
                $this->assertEquals('120.000 TND', (string) $g->price);
                $this->assertEquals('new', (string) $g->condition);
                $this->assertEquals('Aquawa Store Pro', (string) $g->brand);
                $this->assertEquals('FLT-1000', (string) $g->mpn);
                $this->assertEquals('yes', (string) $g->identifier_exists);
                $this->assertEquals('222', (string) $g->google_product_category);
                $this->assertEquals('Électronique & High-Tech', (string) $g->product_type);

                // Shipping
                $shipping = $g->shipping;
                $this->assertEquals('TN', (string) $shipping->country);
                $this->assertEquals('Standard', (string) $shipping->service);
                $this->assertEquals('8.000 TND', (string) $shipping->price);
                break;
            }
        }

        $this->assertTrue($found, 'Product item should be present in feed');
    }

    /**
     * Test price and discount formatting with 3 decimal places for TND.
     */
    public function test_feed_formats_prices_with_three_decimal_places_for_tnd(): void
    {
        $discountedProduct = $this->createProduct([
            'name' => 'Smartphone Promo',
            'price' => 1000.00,
            'discount_price' => 850.50,
        ]);

        $response = $this->get('/google-shopping.xml');
        $xml = simplexml_load_string($response->getContent());

        $found = false;
        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            if ((string) $g->id === (string) $discountedProduct->id) {
                $found = true;
                $this->assertEquals('1000.000 TND', (string) $g->price);
                $this->assertEquals('850.500 TND', (string) $g->sale_price);
                break;
            }
        }

        $this->assertTrue($found);
    }

    /**
     * Test stock availability normalization: in_stock vs out_of_stock.
     */
    public function test_feed_normalizes_availability(): void
    {
        $inStockProduct = $this->createProduct(['name' => 'Item In Stock', 'stock' => 5]);
        $outOfStockProduct = $this->createProduct(['name' => 'Item Out of Stock', 'stock' => 0]);

        $response = $this->get('/google-shopping.xml');
        $xml = simplexml_load_string($response->getContent());

        $availabilities = [];
        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            $availabilities[(string) $g->id] = (string) $g->availability;
        }

        $this->assertEquals('in_stock', $availabilities[(string) $inStockProduct->id]);
        $this->assertEquals('out_of_stock', $availabilities[(string) $outOfStockProduct->id]);
    }

    /**
     * Test exclusion of inactive products, zero price products, and products without images.
     */
    public function test_feed_excludes_ineligible_products(): void
    {
        $inactiveProduct = $this->createProduct(['name' => 'Inactive Item', 'status' => 'inactive']);
        $zeroPriceProduct = $this->createProduct(['name' => 'Zero Price Item', 'price' => 0.00]);

        // Product with NO image
        $noImageProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'No Image Item',
            'slug' => 'no-image-item',
            'price' => 20.00,
            'stock' => 5,
            'status' => 'active',
        ]);

        $response = $this->get('/google-shopping.xml');
        $xml = simplexml_load_string($response->getContent());

        $idsInFeed = [];
        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            $idsInFeed[] = (string) $g->id;
        }

        $this->assertNotContains((string) $inactiveProduct->id, $idsInFeed);
        $this->assertNotContains((string) $zeroPriceProduct->id, $idsInFeed);
        $this->assertNotContains((string) $noImageProduct->id, $idsInFeed);
    }

    /**
     * Test product variants handling with item_group_id, color, size.
     */
    public function test_feed_handles_product_variations_correctly(): void
    {
        $parent = $this->createProduct([
            'name' => 'T-shirt Sport Aquawa',
            'slug' => 't-shirt-sport-aquawa',
            'price' => 45.00,
            'stock' => 30,
        ]);

        $var1 = ProductVariation::create([
            'product_id' => $parent->id,
            'color' => 'Bleu',
            'size' => 'M',
            'sku' => 'TSH-BLU-M',
            'price' => 45.00,
            'stock' => 20,
            'status' => 'active',
        ]);

        $var2 = ProductVariation::create([
            'product_id' => $parent->id,
            'color' => 'Noir',
            'size' => 'L',
            'sku' => 'TSH-BLK-L',
            'price' => 48.00,
            'stock' => 0,
            'status' => 'active',
        ]);

        $response = $this->get('/google-shopping.xml');
        $xml = simplexml_load_string($response->getContent());

        $variant1Found = false;
        $variant2Found = false;

        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            if ((string) $g->id === "{$parent->id}_{$var1->id}") {
                $variant1Found = true;
                $this->assertEquals((string) $parent->id, (string) $g->item_group_id);
                $this->assertEquals('Bleu', (string) $g->color);
                $this->assertEquals('M', (string) $g->size);
                $this->assertEquals('TSH-BLU-M', (string) $g->mpn);
                $this->assertEquals('in_stock', (string) $g->availability);
                $this->assertEquals('45.000 TND', (string) $g->price);
                $this->assertStringContainsString('variant=' . $var1->id, (string) $g->link);
            }

            if ((string) $g->id === "{$parent->id}_{$var2->id}") {
                $variant2Found = true;
                $this->assertEquals((string) $parent->id, (string) $g->item_group_id);
                $this->assertEquals('Noir', (string) $g->color);
                $this->assertEquals('L', (string) $g->size);
                $this->assertEquals('out_of_stock', (string) $g->availability);
                $this->assertEquals('48.000 TND', (string) $g->price);
            }
        }

        $this->assertTrue($variant1Found, 'Variant 1 must exist');
        $this->assertTrue($variant2Found, 'Variant 2 must exist');
    }

    /**
     * Test Arabic and French character encoding and XML escaping.
     */
    public function test_feed_escapes_xml_and_supports_utf8_arabic_and_french(): void
    {
        $product = $this->createProduct([
            'name' => 'حوض أسماك & Décoration Épurée <Luxe>',
            'description' => 'Un aquarium d\'exception avec 100% de clarté & design «Moderne» pour poissons.',
        ]);

        $response = $this->get('/google-shopping.xml');
        $xml = simplexml_load_string($response->getContent());

        $found = false;
        foreach ($xml->channel->item as $item) {
            $g = $item->children('http://base.google.com/ns/1.0');
            if ((string) $g->id === (string) $product->id) {
                $found = true;
                $this->assertStringContainsString('حوض أسماك', (string) $g->title);
                $this->assertStringContainsString('Décoration Épurée', (string) $g->title);
                $this->assertStringContainsString('&', (string) $g->title);
                $this->assertStringContainsString('poissons', (string) $g->description);
                break;
            }
        }

        $this->assertTrue($found);
    }

    /**
     * Test generated product links return HTTP 200 on public web routes.
     */
    public function test_product_show_url_returns_http_200(): void
    {
        $product = $this->createProduct([
            'name' => 'Lampe LED Aquarium',
            'slug' => 'lampe-led-aquarium-show-test',
        ]);

        $response = $this->get('/products/' . $product->slug);
        $response->assertStatus(200);
    }

    /**
     * Test cache invalidation when a product is saved.
     */
    public function test_cache_invalidation_when_product_is_updated(): void
    {
        $this->createProduct(['name' => 'Initial Cached Product']);

        $service = app(GoogleShoppingFeedService::class);
        $service->getFeedXml();

        $this->assertTrue(Cache::has(GoogleShoppingFeedService::CACHE_KEY));

        $product = $this->createProduct(['name' => 'New Product Trigger']);

        // Product saved triggers ProductObserver which calls MarketplaceCacheService::clearProductCache
        $this->assertFalse(Cache::has(GoogleShoppingFeedService::CACHE_KEY));
    }
}

