<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->with('items.product')
            ->latest()
            ->paginate(10);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order)
    {
        // Make sure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load(['user', 'items.product.vendor', 'items.product.primaryImage']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function checkout($vendorId)
    {
        $cart = Cart::where('user_id', auth()->id())->first();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty');
        }

        $cart->load(['items.product.vendor', 'items.product.primaryImage']);

        // Filter items for this vendor only
        $vendorItems = $cart->items->filter(function($item) use ($vendorId) {
            return $item->product->vendor_id == $vendorId;
        });

        if ($vendorItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'No items from this vendor');
        }

        // Get shipping price from the vendor
        $vendor = $vendorItems->first()->product->vendor;
        $shippingPrice = $vendor->shipping_price;

        return Inertia::render('Orders/Checkout', [
            'cart' => [
                'items' => $vendorItems->values(),
            ],
            'vendorId' => $vendorId,
            'shippingPrice' => $shippingPrice,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string',
            'shipping_postal_code' => 'required|string',
            'shipping_country' => 'required|string',
            'phone' => 'required|string',
            'vendor_id' => 'required|exists:vendors,id',
            'notes' => 'nullable|string',
        ]);

        $cart = Cart::where('user_id', auth()->id())->firstOrFail();

        if ($cart->items->isEmpty()) {
            return back()->with('error', 'Your cart is empty');
        }

        // Get only items from the specified vendor
        $cart->load(['items.product.vendor']);
        $vendorItems = $cart->items->filter(function($item) use ($request) {
            return $item->product->vendor_id == $request->vendor_id;
        });

        if ($vendorItems->isEmpty()) {
            return back()->with('error', 'No items from this vendor');
        }

        // Get vendor and shipping price
        $vendor = $vendorItems->first()->product->vendor;
        $shippingCost = $vendor->shipping_price;

        // Calculate subtotal (only for this vendor's items)
        $subtotal = $vendorItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        // Total amount includes shipping
        $total = $subtotal + $shippingCost;

        // Create order and items within atomic database transaction
        $order = DB::transaction(function () use ($request, $total, $shippingCost, $vendor, $subtotal, $vendorItems) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cod', // Force Cash on Delivery
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_country' => $request->shipping_country,
                'phone' => $request->phone,
                'notes' => $request->notes,
            ]);

            // Calculate admin profit margin for this vendor based on order subtotal
            $totalAdminMargin = $vendor->calculateAdminMargin((float) $subtotal);

            // Create order items (only for this vendor)
            foreach ($vendorItems as $item) {
                $itemTotal = $item->quantity * $item->price;
                $commission = $subtotal > 0 ? round(($itemTotal / $subtotal) * $totalAdminMargin, 2) : 0;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'vendor_id' => $item->product->vendor_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'commission' => $commission,
                    'status' => 'pending',
                ]);

                // Reduce stock
                $item->product->decrement('stock', $item->quantity);
                
                // Delete only this vendor's items from cart
                $item->delete();
            }

            return $order;
        });

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

        return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');
    }

    /**
     * Store a direct single-product order without using the cart.
     * Supports both authenticated users and guests.
     */
    public function directStore(Request $request)
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
        ]);

        $product = Product::with('vendor.user')->findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return back()->withErrors(['quantity' => __('low_stock')]);
        }

        // Handle user authentication or guest customer account
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
            $email = 'client_' . ($cleanPhone ?: Str::random(8)) . '@aquawasouk.com';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $request->customer_name,
                    'password' => bcrypt(Str::random(16)),
                    'role' => 'customer',
                ]
            );

            // Automatically log in the guest so they can access their order confirmation
            Auth::login($user);
        }

        $unitPrice = $product->discount_price > 0 ? (float) $product->discount_price : (float) $product->price;
        $vendor = $product->vendor;
        $shippingCost = $vendor && $vendor->shipping_price ? (float) $vendor->shipping_price : 0;
        $subtotal = $unitPrice * (int) $request->quantity;
        $total = $subtotal + $shippingCost;

        // Create Order and items within atomic database transaction
        $order = DB::transaction(function () use ($request, $user, $total, $shippingCost, $vendor, $subtotal, $product, $unitPrice) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'total_amount' => $total,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cod', // Cash on Delivery
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code ?? '1000',
                'shipping_country' => 'Tunisie',
                'phone' => $request->phone,
                'notes' => $request->notes,
            ]);

            // Calculate admin profit margin for this vendor based on subtotal
            $commission = $vendor ? $vendor->calculateAdminMargin($subtotal) : 0.00;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'vendor_id' => $product->vendor_id,
                'quantity' => (int) $request->quantity,
                'price' => $unitPrice,
                'commission' => $commission,
                'status' => 'pending',
            ]);

            // Decrement product stock
            $product->decrement('stock', (int) $request->quantity);

            return $order;
        });

        // Send email to Vendor
        try {
            if ($vendor && $vendor->user) {
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

        return redirect()->route('orders.show', $order)->with('success', 'Votre commande a été passée avec succès ! Notre équipe ou le vendeur vous contactera pour confirmer la livraison.');
    }
}
