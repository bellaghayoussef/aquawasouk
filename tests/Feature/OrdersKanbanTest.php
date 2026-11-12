<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdersKanbanTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_orders_page()
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Orders/Index')
            ->has('orders')
            ->has('filters')
        );
    }

    public function test_kanban_view_request_returns_orders()
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get(route('orders.index', [
            'view' => 'kanban',
            'per_page' => 50,
        ]));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Orders/Index')
            ->where('filters.view', 'kanban')
        );
    }

    public function test_admin_can_update_order_status()
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::factory()->create(['role' => 'admin']);
        }

        $order = Order::first();
        if (!$order) {
            $order = Order::create([
                'order_number' => 'TEST-001',
                'user_id' => $admin->id,
                'total_amount' => 120.00,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cod',
                'shipping_address' => 'Rue de test',
                'shipping_city' => 'Tunis',
                'shipping_postal_code' => '1000',
                'shipping_country' => 'TN',
                'phone' => '+216 20 000 000',
            ]);
        }

        $response = $this->actingAs($admin)
            ->patchJson(route('orders.updateStatus', $order->id), [
                'status' => 'processing',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertEquals('processing', $order->fresh()->status);
    }

    public function test_order_status_validation_rejects_invalid_status()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'order_number' => 'TEST-002',
            'user_id' => $admin->id,
            'total_amount' => 50.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => 'Test',
            'shipping_city' => 'Tunis',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'TN',
            'phone' => '+216 20 000 000',
        ]);

        $response = $this->actingAs($admin)
            ->patchJson(route('orders.updateStatus', $order->id), [
                'status' => 'invalid_status_xyz',
            ]);

        $response->assertStatus(422);
    }

    public function test_customer_can_cancel_own_pending_order()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'CUST-001',
            'user_id' => $customer->id,
            'total_amount' => 80.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => 'Customer address',
            'shipping_city' => 'Sousse',
            'shipping_postal_code' => '4000',
            'shipping_country' => 'TN',
            'phone' => '+216 22 222 222',
        ]);

        $response = $this->actingAs($customer)
            ->patchJson(route('orders.updateStatus', $order->id), [
                'status' => 'cancelled',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_customer_cannot_mark_order_as_delivered()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'order_number' => 'CUST-002',
            'user_id' => $customer->id,
            'total_amount' => 80.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => 'Customer address',
            'shipping_city' => 'Sousse',
            'shipping_postal_code' => '4000',
            'shipping_country' => 'TN',
            'phone' => '+216 22 222 222',
        ]);

        $response = $this->actingAs($customer)
            ->patchJson(route('orders.updateStatus', $order->id), [
                'status' => 'delivered',
            ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->status);
    }

    public function test_admin_can_view_admin_orders_page_in_table_and_kanban()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Table view
        $response = $this->actingAs($admin)->get(route('admin.orders'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Orders')
            ->has('orders')
            ->has('filters')
        );

        // Kanban view
        $responseKanban = $this->actingAs($admin)->get(route('admin.orders', [
            'view' => 'kanban',
            'per_page' => 100,
        ]));
        $responseKanban->assertStatus(200);
        $responseKanban->assertInertia(fn ($page) => $page
            ->component('Admin/Orders')
            ->where('filters.view', 'kanban')
        );
    }

    public function test_admin_can_update_status_via_admin_orders_status_endpoint()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::create([
            'order_number' => 'ADMIN-TEST-001',
            'user_id' => $admin->id,
            'total_amount' => 150.00,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'shipping_address' => 'Admin address',
            'shipping_city' => 'Tunis',
            'shipping_postal_code' => '1000',
            'shipping_country' => 'TN',
            'phone' => '+216 20 111 222',
        ]);

        $response = $this->actingAs($admin)
            ->patchJson(route('admin.orders.status', $order->id), [
                'status' => 'shipped',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertEquals('shipped', $order->fresh()->status);
    }

    public function test_vendor_can_view_vendor_orders_in_table_and_kanban()
    {
        $vendorUser = User::factory()->create(['role' => 'vendor']);
        $vendor = \App\Models\Vendor::create([
            'user_id' => $vendorUser->id,
            'name' => 'Boutique Test',
            'shop_name' => 'Boutique Test',
            'slug' => 'boutique-test',
            'status' => 'active',
        ]);

        // Table view
        $response = $this->actingAs($vendorUser)->get(route('vendor.orders'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Vendor/Orders')
            ->has('orders')
            ->has('filters')
        );

        // Kanban view
        $responseKanban = $this->actingAs($vendorUser)->get(route('vendor.orders', [
            'view' => 'kanban',
            'per_page' => 100,
        ]));
        $responseKanban->assertStatus(200);
        $responseKanban->assertInertia(fn ($page) => $page
            ->component('Vendor/Orders')
            ->where('filters.view', 'kanban')
        );
    }
}
