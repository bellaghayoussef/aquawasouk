<?php

namespace Tests\Feature;

use App\Models\AssistantSetting;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShoppingAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $vendorUser;
    protected Vendor $vendor;
    protected Category $category;
    protected Product $activeProduct;
    protected Product $outOfStockProduct;
    protected Product $inactiveProduct;

    protected function setUp(): void
    {
        parent::setUp();

        // Create category
        $this->category = Category::create([
            'name' => 'Électronique & Maison',
            'slug' => 'electronique-maison',
            'is_active' => true,
        ]);

        // Create vendor user & vendor
        $this->vendorUser = User::factory()->create([
            'role' => 'vendor',
            'name' => 'Tech Vendor',
            'email' => 'tech@vendor.tn',
        ]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Electro Store',
            'slug' => 'electro-store',
            'status' => 'active',
            'shipping_price' => 7.00,
        ]);

        // Create customer user
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Ahmed Customer',
            'email' => 'ahmed@customer.tn',
        ]);

        // Active Product
        $this->activeProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Écouteurs Bluetooth Pro',
            'slug' => 'ecouteurs-bluetooth-pro',
            'description' => 'Écouteurs sans fil avec réduction de bruit',
            'price' => 80.00,
            'discount_price' => 45.00,
            'stock' => 10,
            'status' => 'active',
        ]);

        // Out of Stock Product
        $this->outOfStockProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Montre Connectée Sport',
            'slug' => 'montre-connectee-sport',
            'description' => 'Montre intelligente étanche',
            'price' => 150.00,
            'stock' => 0,
            'status' => 'active',
        ]);

        // Inactive Product
        $this->inactiveProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Article Banni ou Suspendu',
            'slug' => 'article-suspendu',
            'description' => 'Ne doit jamais apparaître',
            'price' => 25.00,
            'stock' => 50,
            'status' => 'inactive',
        ]);
    }

    public function test_assistant_config_endpoint_returns_valid_settings(): void
    {
        $response = $this->getJson(route('shopping-assistant.config'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'enabled',
                'welcome_message',
                'suggested_actions',
                'cart',
                'currency',
            ]);
    }

    public function test_product_search_returns_only_active_published_products(): void
    {
        $response = $this->getJson(route('shopping-assistant.products', ['q' => 'Bluetooth']));

        $response->assertStatus(200);
        $products = $response->json('products');

        $this->assertNotEmpty($products);
        $this->assertEquals('Écouteurs Bluetooth Pro', $products[0]['name']);

        // Inactive product should never appear
        $inactiveSearch = $this->getJson(route('shopping-assistant.products', ['q' => 'Article Banni']));
        $this->assertEmpty($inactiveSearch->json('products'));
    }

    public function test_budget_filter_in_chat_returns_products_under_budget(): void
    {
        // Query under 50 DT should find the 45 DT product
        $response = $this->postJson(route('shopping-assistant.chat'), [
            'message' => 'Je cherche un produit à moins de 50 DT',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'products', 'quick_actions']);

        $products = $response->json('products');
        $this->assertNotEmpty($products);
        $this->assertEquals($this->activeProduct->id, $products[0]['id']);
        $this->assertLessThanOrEqual(50.00, $products[0]['current_price']);
    }

    public function test_multilingual_tunisian_arabic_budget_query(): void
    {
        $response = $this->postJson(route('shopping-assistant.chat'), [
            'message' => 'عندي ميزانية 50 دينار شنوة تنصحني',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('rtl', $response->json('direction'));
        $this->assertNotEmpty($response->json('products'));
    }

    public function test_out_of_stock_product_cannot_be_added_to_cart(): void
    {
        $response = $this->postJson(route('shopping-assistant.cart.add'), [
            'product_id' => $this->outOfStockProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_invalid_quantity_is_rejected(): void
    {
        $response = $this->postJson(route('shopping-assistant.cart.add'), [
            'product_id' => $this->activeProduct->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422);
    }

    public function test_active_product_can_be_added_to_cart_with_server_side_price(): void
    {
        $response = $this->postJson(route('shopping-assistant.cart.add'), [
            'product_id' => $this->activeProduct->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $cart = $response->json('cart');
        $this->assertEquals(2, $cart['total_items']);
        // 2 x 45.00 DT = 90.00 DT (discount price used, ignoring any manipulated frontend values)
        $this->assertEquals(90.00, $cart['subtotal']);
    }

    public function test_customer_cannot_track_another_users_order_idor_protection(): void
    {
        // Create an order for Customer B
        $otherCustomer = User::factory()->create([
            'role' => 'customer',
            'name' => 'Other Customer',
        ]);

        $order = Order::create([
            'order_number' => 'ORD-999999',
            'user_id' => $otherCustomer->id,
            'total_amount' => 120.00,
            'shipping_cost' => 7.00,
            'status' => 'shipped',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'shipping_address' => 'Avenue Habib Bourguiba',
            'shipping_city' => 'Tunis',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Tunisie',
            'phone' => '+216 99 999 999',
        ]);

        // Attempt to track as Ahmed (Customer A)
        $this->actingAs($this->customer);

        $response = $this->getJson(route('shopping-assistant.order-tracking', [
            'order_number' => 'ORD-999999',
        ]));

        $response->assertStatus(200);
        // Ownership verification must deny access
        $this->assertFalse($response->json('found'));
    }

    public function test_customer_can_track_their_own_order(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-123456',
            'user_id' => $this->customer->id,
            'total_amount' => 52.00,
            'shipping_cost' => 7.00,
            'status' => 'processing',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => 'Route de la plage',
            'shipping_city' => 'Sousse',
            'shipping_postal_code' => '4000',
            'shipping_country' => 'Tunisie',
            'phone' => '+216 22 222 222',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->activeProduct->id,
            'vendor_id' => $this->vendor->id,
            'quantity' => 1,
            'price' => 45.00,
            'commission' => 4.50,
            'status' => 'pending',
        ]);

        $this->actingAs($this->customer);

        $response = $this->getJson(route('shopping-assistant.order-tracking', [
            'order_number' => 'ORD-123456',
        ]));

        $response->assertStatus(200);
        $this->assertTrue($response->json('found'));
        $this->assertEquals('ORD-123456', $response->json('order_number'));
        $this->assertEquals('Sousse', $response->json('city'));
    }

    public function test_ai_disabled_mode_works_locally_without_api_key(): void
    {
        // Ensure AI is turned off
        AssistantSetting::set('ai_enabled', false);

        $response = $this->postJson(route('shopping-assistant.chat'), [
            'message' => 'Quels sont vos délais et frais de livraison ?',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'quick_actions']);

        $this->assertStringContainsString('livraison', mb_strtolower($response->json('message')));
    }
}
