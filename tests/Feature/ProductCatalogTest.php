<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected Vendor $vendor;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $vendorUser = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Store Owner',
        ]);

        $this->vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Mega Boutique',
            'slug' => 'mega-boutique',
            'status' => 'active',
            'shipping_price' => 7.00,
        ]);

        $this->category = Category::create([
            'name' => 'Électronique',
            'slug' => 'electronique',
            'is_active' => true,
        ]);

        // Cheap Product (15 DT)
        Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Câble USB Type-C',
            'slug' => 'cable-usb-type-c',
            'price' => 15.00,
            'stock' => 20,
            'status' => 'active',
        ]);

        // Mid Product (45 DT with discount from 60 DT)
        Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Écouteurs Pro',
            'slug' => 'ecouteurs-pro',
            'price' => 60.00,
            'discount_price' => 45.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        // Expensive Product (250 DT)
        Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Tablette Tactile',
            'slug' => 'tablette-tactile',
            'price' => 250.00,
            'stock' => 5,
            'status' => 'active',
        ]);
    }

    public function test_products_can_be_filtered_by_min_and_max_price(): void
    {
        // Query products between 20 DT and 50 DT
        // Should only return "Écouteurs Pro" (discount price 45 DT)
        $response = $this->get(route('products.index', [
            'min_price' => 20,
            'max_price' => 50,
        ]));

        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Écouteurs Pro')
            ->where('filters.min_price', '20')
            ->where('filters.max_price', '50')
        );
    }

    public function test_products_can_be_filtered_by_only_max_price(): void
    {
        // Query products with max_price = 20
        // Should return only "Câble USB Type-C" (15 DT)
        $response = $this->get(route('products.index', [
            'max_price' => 20,
        ]));

        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Câble USB Type-C')
        );
    }

    public function test_products_can_be_filtered_by_only_min_price(): void
    {
        // Query products with min_price = 100
        // Should return only "Tablette Tactile" (250 DT)
        $response = $this->get(route('products.index', [
            'min_price' => 100,
        ]));

        $response->assertStatus(200);

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Products/Index')
            ->has('products.data', 1)
            ->where('products.data.0.name', 'Tablette Tactile')
        );
    }
}
