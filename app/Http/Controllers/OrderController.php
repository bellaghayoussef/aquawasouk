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
    public function index(Request $request)
    {
        $user = auth()->user();

        // Scope query according to user role
        if ($user->isAdmin()) {
            $query = Order::query();
        } elseif ($user->isVendor() && $user->vendor) {
            $vendorId = $user->vendor->id;
            $query = Order::whereHas('items', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            });
        } else {
            $query = $user->orders();
        }

        $query->with([
            'user:id,name,email',
            'items.product.primaryImage',
            'items.product.vendor:id,user_id,shop_name',
            'items.vendor:id,user_id,shop_name',
        ]);

        // Search filter: order number, customer name/email, phone, city
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('shipping_city', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Payment status filter
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Per page: if kanban view is requested, fetch a larger batch for smooth board experience
        $view = $request->get('view', 'table');
        $perPage = $view === 'kanban' ? 100 : 15;
        if ($request->filled('per_page')) {
            $perPage = max(5, min(200, (int) $request->per_page));
        }

        $orders = $query->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'payment_status', 'date_from', 'date_to', 'view', 'per_page']),
            'canManageOrders' => $user->isAdmin() || $user->isVendor(),
            'userRole' => $user->role,
        ]);
    }

    public function show(Order $order)
    {
        $user = auth()->user();
        $isOwner = $order->user_id === $user->id;
        $isAdmin = $user->isAdmin();
        $isVendorWithItem = $user->isVendor() && $user->vendor && $order->items()->where('vendor_id', $user->vendor->id)->exists();

        if (!$isOwner && !$isAdmin && !$isVendorWithItem) {
            abort(403);
        }

        $order->load(['user', 'items.product.vendor', 'items.product.primaryImage']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $user = auth()->user();

        // Authorization check
        $canUpdate = false;
        if ($user->isAdmin()) {
            $canUpdate = true;
        } elseif ($user->isVendor() && $user->vendor) {
            $canUpdate = $order->items()->where('vendor_id', $user->vendor->id)->exists();
        } elseif ($order->user_id === $user->id) {
            // Customer can only cancel a pending order
            if ($request->status === 'cancelled' && $order->status === 'pending') {
                $canUpdate = true;
            }
        }

        if (!$canUpdate) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action non autorisée sur cette commande.',
                ], 403);
            }
            abort(403, 'Action non autorisée sur cette commande.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];

        // Business rules check
        if ($oldStatus === 'delivered' && $newStatus !== 'delivered' && !$user->isAdmin()) {
            $msg = 'Une commande déjà livrée ne peut pas être rétrogradée.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled' && !$user->isAdmin()) {
            $msg = 'Seul un administrateur peut réactiver une commande annulée.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        DB::transaction(function () use ($order, $newStatus, $user) {
            $order->status = $newStatus;
            if ($newStatus === 'delivered' && $order->payment_method === 'cod') {
                $order->payment_status = 'paid';
            }
            $order->save();

            // Sync item statuses if admin or vendor owns them
            if ($user->isAdmin()) {
                $order->items()->update(['status' => $newStatus]);
            } elseif ($user->isVendor() && $user->vendor) {
                $order->items()->where('vendor_id', $user->vendor->id)->update(['status' => $newStatus]);
            }
        });

        // Trigger notification email if status changed and user exists
        if ($oldStatus !== $newStatus && $order->user) {
            try {
                \Illuminate\Support\Facades\Mail::to($order->user->email)
                    ->send(new \App\Mail\OrderStatusUpdated($order));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Email dispatch failed: ' . $e->getMessage());
            }
        }

        $statusLabels = [
            'pending' => 'En attente',
            'processing' => 'En préparation',
            'shipped' => 'En cours d\'expédition',
            'delivered' => 'Livrée',
            'cancelled' => 'Annulée',
        ];

        $statusLabel = $statusLabels[$newStatus] ?? $newStatus;
        $message = "Commande #{$order->order_number} passée en statut « {$statusLabel} ».";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'order' => $order->fresh([
                    'user:id,name,email',
                    'items.product.primaryImage',
                    'items.product.vendor:id,user_id,shop_name',
                    'items.vendor:id,user_id,shop_name',
                ]),
            ]);
        }

        return back()->with('success', $message);
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

        // Check for applied coupon discount
        $discountAmount = 0.00;
        $couponId = null;
        $coupon = null;
        if (session()->has('applied_coupon')) {
            $applied = session('applied_coupon');
            $coupon = \App\Models\Coupon::find($applied['id'] ?? null);
            if ($coupon && $coupon->isValid((float) $subtotal, auth()->id(), $vendor->id)['valid']) {
                $discountAmount = $coupon->calculateDiscount((float) $subtotal);
                $couponId = $coupon->id;
            }
        }

        // Total amount includes shipping minus discount
        $total = max(0, ($subtotal - $discountAmount)) + $shippingCost;

        // Payment method
        $paymentMethod = $request->input('payment_method', 'cod');

        // Create order and items within atomic database transaction
        $order = DB::transaction(function () use ($request, $total, $shippingCost, $vendor, $subtotal, $vendorItems, $couponId, $discountAmount, $coupon, $paymentMethod) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => auth()->id(),
                'total_amount' => $total,
                'shipping_cost' => $shippingCost,
                'coupon_id' => $couponId,
                'discount_amount' => $discountAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
                'shipping_address' => $request->shipping_address,
                'shipping_city' => $request->shipping_city,
                'shipping_postal_code' => $request->shipping_postal_code,
                'shipping_country' => $request->shipping_country,
                'phone' => $request->phone,
                'notes' => $request->notes,
            ]);

            // Track coupon usage
            if ($coupon) {
                $coupon->increment('times_used');
                \App\Models\CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'user_id' => auth()->id(),
                    'order_id' => $order->id,
                    'discount_amount' => $discountAmount,
                ]);
                session()->forget('applied_coupon');
            }

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

        // Trigger in-app notification to customer
        try {
            auth()->user()->notify(new \App\Notifications\OrderStatusNotification($order, 'none', 'pending'));
        } catch (\Throwable $e) {}

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

        // Send SMS alert for new order to 56818880
        try {
            $smsService = app(\App\Services\SmsService::class);
            $customerName = auth()->user()->name ?? 'Client';
            $smsMessage = "AquawaSouk: Nouvelle commande #" . $order->order_number . "\n"
                . "Client: " . $customerName . " (" . $request->phone . ")\n"
                . "Articles: " . $vendorItems->count() . " article(s)\n"
                . "Total: " . number_format($total, 2) . " DT\n"
                . "Ville: " . $request->shipping_city;

            $smsService->send('56818880', trim($smsMessage));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send cart order SMS alert to 56818880: ' . $e->getMessage());
        }

        // Auto-forward order to Ecomness if it contains Ecomness products
        try {
            $ecomnessService = app(\App\Services\EcomnessService::class);
            if ($ecomnessService->hasApiKey()) {
                $ecomnessService->forwardOrderToEcomness($order);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Ecomness auto-forward failed for order #' . $order->order_number . ': ' . $e->getMessage());
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
            'payment_method' => 'nullable|string|in:cod',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:50',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $product = Product::with(['vendor.user', 'variations'])->findOrFail($request->product_id);

        $variation = null;
        if ($request->filled('variation_id')) {
            $variation = $product->variations()->find($request->variation_id);
        }

        $stockToCheck = $variation ? $variation->stock : $product->stock;
        if ($stockToCheck < $request->quantity) {
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

        $unitPrice = ($variation && $variation->price > 0)
            ? (float) $variation->price
            : ($product->discount_price > 0 ? (float) $product->discount_price : (float) $product->price);

        $vendor = $product->vendor;
        $shippingCost = $vendor && $vendor->shipping_price ? (float) $vendor->shipping_price : 0;
        $subtotal = $unitPrice * (int) $request->quantity;
        $total = $subtotal + $shippingCost;

        $paymentMethod = $request->input('payment_method', 'cod');

        // Create Order and items within atomic database transaction
        $order = DB::transaction(function () use ($request, $user, $total, $shippingCost, $vendor, $subtotal, $product, $unitPrice, $paymentMethod, $variation) {
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'total_amount' => $total,
                'shipping_cost' => $shippingCost,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $paymentMethod,
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
                'variation_id' => $variation ? $variation->id : null,
                'color' => $request->color ?: ($variation ? $variation->color : null),
                'size' => $request->size ?: ($variation ? $variation->size : null),
                'quantity' => (int) $request->quantity,
                'price' => $unitPrice,
                'commission' => $commission,
                'status' => 'pending',
            ]);

            // Decrement product stock
            $product->decrement('stock', (int) $request->quantity);

            // Also decrement variation stock if applicable
            if ($variation) {
                $variation->decrement('stock', (int) $request->quantity);
            }

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

        // Send SMS alert for new direct order to 56818880
        try {
            $smsService = app(\App\Services\SmsService::class);
            $productVariant = '';
            if ($variation && !empty($variation->name)) {
                $productVariant = ' (' . $variation->name . ')';
            } elseif ($request->filled('color') || $request->filled('size')) {
                $productVariant = ' (' . trim(($request->color ?? '') . ' ' . ($request->size ?? '')) . ')';
            }

            $smsMessage = "AquawaSouk: Nouvelle commande directe #" . $order->order_number . "\n"
                . "Client: " . $request->customer_name . " (" . $request->phone . ")\n"
                . "Produit: " . $product->name . $productVariant . " x" . $request->quantity . "\n"
                . "Total: " . number_format($total, 2) . " DT\n"
                . "Ville: " . $request->shipping_city;

            $smsService->send('56818880', trim($smsMessage));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send direct order SMS alert to 56818880: ' . $e->getMessage());
        }

        // Auto-forward order to Ecomness if it contains Ecomness products
        try {
            $ecomnessService = app(\App\Services\EcomnessService::class);
            if ($ecomnessService->hasApiKey()) {
                $ecomnessService->forwardOrderToEcomness($order);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Ecomness auto-forward failed for direct order #' . $order->order_number . ': ' . $e->getMessage());
        }

        return redirect()->route('orders.show', $order)->with('success', 'Votre commande a été passée avec succès ! Notre équipe ou le vendeur vous contactera pour confirmer la livraison.');
    }
}
