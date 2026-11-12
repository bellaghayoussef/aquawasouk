<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapFeedTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleProducts(): void
    {
        $user = User::factory()->create();
        $category = \App\Models\Category::create([
            'name' => 'Équipements',
            'slug' => 'equipements',
            'description' => 'Catégorie de test',
        ]);
        $vendor = Vendor::create([
            'user_id' => $user->id,
            'name' => 'Test Vendor',
            'shop_name' => 'Test Shop',
            'slug' => 'test-shop',
            'status' => 'active',
        ]);

        // Product with relative image
        $product1 = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Produit Local Image',
            'slug' => 'produit-local-image',
            'description' => 'Description produit local',
            'price' => 50.00,
            'stock' => 10,
            'status' => 'active',
            'sku' => 'LOCAL-001',
        ]);
        ProductImage::create([
            'product_id' => $product1->id,
            'image_path' => 'products/local-image.jpg',
            'is_primary' => true,
        ]);

        // Product with absolute URL image (starts with http/https)
        $product2 = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Produit Remote Image',
            'slug' => 'produit-remote-image',
            'description' => 'Description produit remote',
            'price' => 75.00,
            'stock' => 5,
            'status' => 'active',
            'sku' => 'REMOTE-001',
        ]);
        ProductImage::create([
            'product_id' => $product2->id,
            'image_path' => 'https://cdn.externalsupplier.com/images/remote-item.jpg',
            'is_primary' => true,
        ]);
    }

    public function test_sitemap_feed_handles_relative_and_absolute_image_urls()
    {
        $this->createSampleProducts();

        $response = $this->get('/feed.xml');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Check relative image was wrapped in uploads
        $this->assertStringContainsString('/uploads/products/local-image.jpg', $content);

        // Check absolute image was preserved as-is without uploads prefix
        $this->assertStringContainsString('https://cdn.externalsupplier.com/images/remote-item.jpg', $content);
        $this->assertStringNotContainsString('/uploads/https://cdn.externalsupplier.com', $content);
    }

    public function test_sitemap_index_xml_handles_relative_and_absolute_images()
    {
        $this->createSampleProducts();

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('https://cdn.externalsupplier.com/images/remote-item.jpg', $content);
        $this->assertStringNotContainsString('/uploads/https://cdn.externalsupplier.com', $content);
    }
}
