<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $vendorUser;
    protected Vendor $vendor;
    protected Category $category;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);

        $this->vendorUser = User::factory()->create(['role' => 'vendor']);
        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Test Boutique',
            'slug' => 'test-boutique',
            'status' => 'active',
            'shipping_price' => 7.00,
            'commission_rate' => 10.00,
        ]);

        $this->category = Category::create([
            'name' => 'Aquarium',
            'slug' => 'aquarium',
        ]);

        $this->product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'name' => 'Pompe Aquarium Pro',
            'slug' => 'pompe-aquarium-pro',
            'price' => 50.00,
            'stock' => 10,
            'status' => 'active',
            'is_published' => true,
        ]);
    }

    public function test_customer_can_place_direct_cod_order_and_stock_is_decremented(): void
    {
        $response = $this->actingAs($this->customer)->post(route('orders.direct'), [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'customer_name' => 'Ahmed Ben Salah',
            'phone' => '21650123456',
            'shipping_address' => 'Avenue Habib Bourguiba',
            'shipping_city' => 'Tunis',
            'shipping_postal_code' => '1000',
        ]);

        $response->assertRedirect();
        
        // Assert order created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->customer->id,
            'shipping_city' => 'Tunis',
            'payment_method' => 'cod',
            'total_amount' => 107.00, // (50 * 2) + 7 shipping
        ]);

        // Assert order item created
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        // Assert stock decremented from 10 to 8
        $this->assertEquals(8, $this->product->fresh()->stock);
    }

    public function test_cannot_order_more_quantity_than_available_stock(): void
    {
        $response = $this->actingAs($this->customer)->post(route('orders.direct'), [
            'product_id' => $this->product->id,
            'quantity' => 999, // Exceeds stock (10)
            'customer_name' => 'Ahmed Ben Salah',
            'phone' => '21650123456',
            'shipping_address' => 'Avenue Habib Bourguiba',
            'shipping_city' => 'Tunis',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertEquals(10, $this->product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_customer_cannot_view_another_customers_order_idor_protection(): void
    {
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $order = Order::create([
            'order_number' => 'ORD-TEST1234',
            'user_id' => $otherCustomer->id,
            'total_amount' => 57.00,
            'shipping_cost' => 7.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => 'Sousse Centre',
            'shipping_city' => 'Sousse',
            'shipping_postal_code' => '4000',
            'shipping_country' => 'Tunisie',
            'phone' => '21650000000',
        ]);

        // Attempt to access other customer's order
        $response = $this->actingAs($this->customer)->get(route('orders.show', $order));

        $response->assertStatus(403);
    }

    public function test_customer_can_view_their_own_order(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-OWN1234',
            'user_id' => $this->customer->id,
            'total_amount' => 57.00,
            'shipping_cost' => 7.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => 'Tunis Centre',
            'shipping_city' => 'Tunis',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'Tunisie',
            'phone' => '21650000000',
        ]);

        $response = $this->actingAs($this->customer)->get(route('orders.show', $order));

        $response->assertOk();
    }
}
