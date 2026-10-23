<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Get user's orders
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Order::with(['items.product', 'items.vendor'])
            ->where('user_id', $user->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $orders = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Get single order details
     */
    public function show(Request $request, Order $order)
    {
        // Verify ownership
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $order->load(['items.product.images', 'items.vendor']);

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Create a new order
     */
    public function create(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:cash_on_delivery,stripe,paypal',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $cart = Cart::with('items.product.vendor')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Group cart items by vendor
            $itemsByVendor = $cart->items->toBase()->groupBy('product.vendor_id');

            // Filter by specific vendor if requested
            if ($request->has('vendor_id')) {
                $itemsByVendor = $itemsByVendor->only($request->vendor_id);
                
                if ($itemsByVendor->isEmpty()) {
                     return response()->json([
                        'success' => false,
                        'message' => 'No items found for this vendor in cart',
                    ], 400);
                }
            }

            $orders = [];
            $processedItemIds = [];

            foreach ($itemsByVendor as $vendorId => $items) {
                // ... (existing logic)
                $vendor = Vendor::find($vendorId);
                if (!$vendor) continue; // Safety check

                // Calculate total
                $subtotal = $items->sum(function ($item) {
                    return $item->quantity * ($item->product->discount_price ?? $item->product->price);
                });
                
                $shippingCost = $vendor->shipping_price ?? 0;
                $total = $subtotal + $shippingCost;

                // Create order
                $order = Order::create([
                    'order_number' => $this->generateOrderNumber(),
                    'user_id' => $user->id,
                    'total_amount' => $total,
                    'shipping_cost' => $shippingCost,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_method' => $request->payment_method,
                    'shipping_address' => $request->shipping_address,
                    'shipping_city' => $request->shipping_city,
                    'shipping_postal_code' => $request->shipping_postal_code,
                    'shipping_country' => $request->shipping_country,
                    'phone' => $request->phone,
                    'notes' => $request->notes,
                ]);

                // Create order items
                foreach ($items as $cartItem) {
                    $product = $cartItem->product;
                    $price = $product->discount_price ?? $product->price;
                    $commission = ($price * $vendor->commission_rate) / 100;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'vendor_id' => $vendor->id,
                        'quantity' => $cartItem->quantity,
                        'price' => $price,
                        'commission' => $commission,
                        'status' => 'pending',
                        'variant_info' => json_encode([
                            'color' => $cartItem->color,
                            'size' => $cartItem->size,
                        ]),
                    ]);

                    // Decrease stock
                    $product->decrement('stock', $cartItem->quantity);
                    
                    // Track processed items to remove from cart
                    $processedItemIds[] = $cartItem->id;
                }

                // Send email to Vendor
                try {
                    if ($vendor->user) {
                         \Illuminate\Support\Facades\Mail::to($vendor->user->email)->send(new \App\Mail\VendorOrderReceived($order, $vendor));
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send vendor email: ' . $e->getMessage());
                }

                // Send email to Admin
                try {
                    \Illuminate\Support\Facades\Mail::to('contact@aquawasouk.com')->send(new \App\Mail\OrderPlaced($order));
                } catch (\Exception $e) {
                     \Illuminate\Support\Facades\Log::error('Failed to send admin email: ' . $e->getMessage());
                }

                $orders[] = $order;
            }

            // Clear processed items from cart
            if (!empty($processedItemIds)) {
                $cart->items()->whereIn('id', $processedItemIds)->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order(s) created successfully',
                'data' => $orders,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, Order $order)
    {
        // Verify ownership
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Only allow cancellation if order is pending
        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled',
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order',
            ], 500);
        }
    }

    /**
     * Get order invoice
     */
    public function invoice(Request $request, Order $order)
    {
        // Verify ownership
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $order->load(['items.product', 'items.vendor', 'user']);

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'invoice_number' => 'INV-' . $order->order_number,
                'invoice_date' => $order->created_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Track order
     */
    public function track(Request $request, Order $order)
    {
        // Verify ownership
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $tracking = [
            'order_number' => $order->order_number,
            'current_status' => $order->status,
            'payment_status' => $order->payment_status,
            'timeline' => [
                [
                    'status' => 'pending',
                    'label' => 'Order Placed',
                    'completed' => true,
                    'date' => $order->created_at,
                ],
                [
                    'status' => 'processing',
                    'label' => 'Processing',
                    'completed' => in_array($order->status, ['processing', 'shipped', 'delivered']),
                    'date' => null,
                ],
                [
                    'status' => 'shipped',
                    'label' => 'Shipped',
                    'completed' => in_array($order->status, ['shipped', 'delivered']),
                    'date' => null,
                ],
                [
                    'status' => 'delivered',
                    'label' => 'Delivered',
                    'completed' => $order->status === 'delivered',
                    'date' => null,
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $tracking,
        ]);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(Str::random(10));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Store a direct single-product order without cart requirement.
     * Supports both authenticated users and guests.
     */
    public function directOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'shipping_address' => 'required|string|max:500',
            'shipping_city' => 'required|string|max:100',
            'shipping_postal_code' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
        ]);

        $product = \App\Models\Product::with(['vendor.user', 'variations'])->findOrFail($request->product_id);

        // Check specific variation stock if size or color requested
        /** @var \App\Models\ProductVariation|null $variation */
        $variation = null;
        if ($request->filled('size') || $request->filled('color')) {
            $varQuery = $product->variations()->where('status', 'active');
            if ($request->filled('size')) {
                $varQuery->where('size', $request->size);
            }
            if ($request->filled('color')) {
                $varQuery->where('color', $request->color);
            }
            $variation = $varQuery->first();

            if ($variation && $variation->stock < $request->quantity) {
                $detail = trim(($request->size ? "Taille {$request->size} " : "") . ($request->color ? "Couleur {$request->color}" : ""));
                return response()->json([
                    'success' => false,
                    'message' => "Stock insuffisant pour cette déclinaison ($detail). Stock restant: {$variation->stock}",
                ], 400);
            }
        }

        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant pour ce produit',
            ], 400);
        }

        // Authenticated or guest
        $user = auth('sanctum')->user() ?? auth()->user();
        if (!$user) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
            $email = 'client_' . ($cleanPhone ?: \Illuminate\Support\Str::random(8)) . '@aquawasouk.com';

            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $request->customer_name,
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                    'role' => 'customer',
                ]
            );
        }

        $unitPrice = ($variation && $variation->price > 0)
            ? (float) $variation->price
            : ($product->discount_price > 0 ? (float) $product->discount_price : (float) $product->price);

        $vendor = $product->vendor;
        $shippingCost = $vendor && $vendor->shipping_price ? (float) $vendor->shipping_price : 0;
        $subtotal = $unitPrice * (int) $request->quantity;
        $total = $subtotal + $shippingCost;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            $order = \App\Models\Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'total_amount' => $total,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cash_on_delivery',
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code ?? '1000',
                'shipping_country' => 'Tunisie',
                'phone' => $request->phone,
                'notes' => $request->notes,
            ]);

            $totalAdminMargin = $vendor ? $vendor->calculateAdminMargin((float) $subtotal) : 0;

            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'variation_id' => $variation?->id,
                'color' => $request->color,
                'size' => $request->size,
                'quantity' => $request->quantity,
                'price' => $unitPrice,
                'commission' => $totalAdminMargin,
                'status' => 'pending',
            ]);

            // Decrement variation stock if applicable
            if ($variation instanceof \App\Models\ProductVariation) {
                $variation->decrement('stock', $request->quantity);
            }
            $product->decrement('stock', $request->quantity);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Commande passée avec succès',
                'data' => $order->load('items.product'),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage(),
            ], 500);
        }
    }
}

