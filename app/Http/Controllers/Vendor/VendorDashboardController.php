<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ImageService;

class VendorDashboardController extends Controller
{
    public function dashboard()
    {
        $vendor = auth()->user()->vendor;

        if (!$vendor) {
            return redirect()->route('dashboard')
                ->with('error', 'You need to create a vendor account first');
        }

        $stats = [
            'total_products' => $vendor->products()->count(),
            'active_products' => $vendor->products()->where('status', 'active')->count(),
            'total_orders' => OrderItem::where('vendor_id', $vendor->id)->count(),
            'total_sales' => round($vendor->getTotalSales(), 2),
            'total_views' => (int) $vendor->products()->sum('views'),
            'pending_orders' => OrderItem::where('vendor_id', $vendor->id)
                ->where('status', 'pending')
                ->count(),
        ];

        $financials = [
            'total_sales' => round($vendor->getTotalSales(), 2),
            'admin_profit_due' => round($vendor->getTotalAdminProfit(), 2),
            'pending_profit' => round($vendor->getPendingAdminProfit(), 2),
            'vendor_net_earnings' => round($vendor->getVendorNetEarnings(), 2),
            'total_paid_to_admin' => round($vendor->getTotalPayouts(), 2),
            'remaining_due_to_admin' => round($vendor->getRemainingProfitDue(), 2),
        ];

        $payoutHistory = $vendor->payouts()
            ->with('creator')
            ->latest('payout_date')
            ->latest('id')
            ->take(10)
            ->get();

        $recentOrders = OrderItem::with(['order.user', 'product'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Vendor/Dashboard', [
            'vendor' => $vendor,
            'stats' => $stats,
            'financials' => $financials,
            'payoutHistory' => $payoutHistory,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function products(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $query = $vendor->products()->with('category');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $products = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::all();

        return Inertia::render('Vendor/Products', [
            'products' => $products,
            'vendor' => $vendor,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'category', 'date_from', 'date_to']),
        ]);
    }

    public function createProduct()
    {
        // Get categories with parent-child hierarchy
        $categories = Category::with('parent')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->parent 
                        ? $category->parent->name . ' → ' . $category->name 
                        : $category->name,
                    'parent_id' => $category->parent_id,
                ];
            });

        return Inertia::render('Vendor/ProductCreate', [
            'categories' => $categories,
        ]);
    }

    public function storeProduct(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB each
             'is_featured' => 'boolean',
        ]);

        $validated['vendor_id'] = $vendor->id;
        $validated['slug'] = \Str::slug($validated['name']);
        $validated['status'] = 'active';

        $product = Product::create($validated);

        // Handle multiple images upload with WebP conversion
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = ImageService::storeAsWebp($image, 'products');
                $product->images()->create([
                    'image_path' => $imagePath,
                    'is_primary' => $index === 0, // First image is primary
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('vendor.products')->with('success', 'Product created successfully');
    }

    public function editProduct(Product $product)
    {
        // Ensure the product belongs to the vendor
        if ($product->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $product->load('images');

        // Get categories with parent-child hierarchy
        $categories = Category::with('parent')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->parent 
                        ? $category->parent->name . ' → ' . $category->name 
                        : $category->name,
                    'parent_id' => $category->parent_id,
                ];
            });

        return Inertia::render('Vendor/ProductEdit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        // Ensure the product belongs to the vendor
        if ($product->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'status' => 'required|in:active,inactive',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',
            'is_featured' => 'boolean',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        $product->update($validated);

        // Handle image deletions
        if ($request->has('delete_images') && is_array($request->delete_images)) {
            foreach ($request->delete_images as $imageId) {
                $image = $product->images()->find($imageId);
                if ($image) {
                    \Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }
            }
        }

        // Handle new images upload with WebP conversion
        if ($request->hasFile('images')) {
            $existingImagesCount = $product->images()->count();
            foreach ($request->file('images') as $index => $image) {
                $imagePath = ImageService::storeAsWebp($image, 'products');
                $product->images()->create([
                    'image_path' => $imagePath,
                    'is_primary' => $existingImagesCount === 0 && $index === 0, // First image is primary if no existing images
                    'sort_order' => $existingImagesCount + $index,
                ]);
            }
        }

        return redirect()->route('vendor.products')->with('success', 'Product updated successfully');
    }

    public function deleteProduct(Product $product)
    {
        // Ensure the product belongs to the vendor
        if ($product->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $product->delete();
        return back()->with('success', 'Product deleted successfully');
    }

    public function orders(Request $request)
    {
        $vendor = auth()->user()->vendor;

        // Get all order items for this vendor with relationships
        $query = OrderItem::with(['order.user', 'product.primaryImage'])
            ->where('vendor_id', $vendor->id);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('order', function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                })
                ->orWhereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->whereHas('order', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orderItems = $query->latest()->get();

        // Group items by order
        $groupedOrders = $orderItems->groupBy('order_id')->map(function ($items) {
            $firstItem = $items->first();
            return [
                'order' => $firstItem->order,
                'items' => $items,
                'total' => $items->sum(function ($item) {
                    return $item->price * $item->quantity;
                }),
                'commission' => $items->sum('commission'),
                'created_at' => $firstItem->created_at,
            ];
        })->values();

        // Paginate manually
        $page = $request->get('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $paginatedOrders = new \Illuminate\Pagination\LengthAwarePaginator(
            $groupedOrders->slice($offset, $perPage),
            $groupedOrders->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Vendor/Orders', [
            'orders' => $paginatedOrders,
            'vendor' => $vendor,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }



    public function showOrder(OrderItem $orderItem)
    {
        // Ensure the order item belongs to the vendor
        if ($orderItem->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $orderItem->load(['order.user']);

        // Get all items for this order and this vendor
        $vendorItems = OrderItem::with(['product.primaryImage'])
            ->where('order_id', $orderItem->order_id)
            ->where('vendor_id', auth()->user()->vendor->id)
            ->get();

        return Inertia::render('Vendor/OrderShow', [
            'order' => $orderItem->order,
            'items' => $vendorItems,
            'vendor' => auth()->user()->vendor,
        ]);
    }

    public function generateInvoice(OrderItem $orderItem)
    {
        // Ensure the order item belongs to the vendor
        if ($orderItem->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $orderItem->load(['order.user']);

        // Get all items for this order and this vendor
        $vendorItems = OrderItem::with(['product'])
            ->where('order_id', $orderItem->order_id)
            ->where('vendor_id', auth()->user()->vendor->id)
            ->get();

        $pdf = Pdf::loadView('vendor.invoice', [
            'order' => $orderItem->order,
            'items' => $vendorItems,
            'vendor' => auth()->user()->vendor,
        ]);

        return $pdf->download('invoice-' . $orderItem->order->order_number . '.pdf');
    }

    public function generateWaybill(OrderItem $orderItem)
    {
        // Ensure the order item belongs to the vendor
        if ($orderItem->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $orderItem->load(['order.user']);

        // Get all items for this order and this vendor
        $vendorItems = OrderItem::with(['product', 'variation'])
            ->where('order_id', $orderItem->order_id)
            ->where('vendor_id', auth()->user()->vendor->id)
            ->get();

        $pdf = Pdf::loadView('vendor.waybill', [
            'order' => $orderItem->order,
            'items' => $vendorItems,
            'vendor' => auth()->user()->vendor,
        ])->setPaper('a5', 'portrait');

        return $pdf->download('bordereau-' . $orderItem->order->order_number . '.pdf');
    }

    public function updateOrderStatus(OrderItem $orderItem, Request $request)
    {
        // Ensure the order item belongs to the vendor
        if ($orderItem->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'update_all' => 'boolean',
        ]);

        if ($request->boolean('update_all')) {
            // Update all items for this order and vendor
            OrderItem::where('order_id', $orderItem->order_id)
                ->where('vendor_id', auth()->user()->vendor->id)
                ->update(['status' => $request->status]);
                Order::where('id', $orderItem->order_id)->update(['status' => $request->status]);

            // Send email & in-app notification to customer
            try {
                $order = \App\Models\Order::with('user')->find($orderItem->order_id);
                if ($order && $order->user) {
                     \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdated($order));
                     $order->user->notify(new \App\Notifications\OrderStatusNotification($order, $orderItem->status, $request->status));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send order status notifications: ' . $e->getMessage());
            }
        } else {
            // Update only this item
            $orderItem->update(['status' => $request->status]);
        }

        return back()->with('success', 'Order status updated successfully');
    }

    public function settings()
    {
        $vendor = auth()->user()->vendor;

        return Inertia::render('Vendor/Settings', [
            'vendor' => $vendor,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $vendor = auth()->user()->vendor;

        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'shipping_price' => 'nullable|numeric|min:0',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

        $validated['slug'] = \Str::slug($validated['shop_name']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($vendor->logo) {
                \Storage::disk('public')->delete($vendor->logo);
            }
            
            $logoPath = $request->file('logo')->store('vendors/logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $vendor->update($validated);

        return back()->with('success', 'Settings updated successfully');
    }

    /**
     * Send SMS to order customer via TextBee Gateway
     */
    public function sendOrderSms(Request $request, OrderItem $orderItem)
    {
        // Ensure the order item belongs to the vendor
        if ($orderItem->vendor_id !== auth()->user()->vendor->id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'message' => 'required|string|max:500',
            'phone' => 'nullable|string|max:30',
        ]);

        $order = $orderItem->order;
        $phone = $request->filled('phone') ? $request->input('phone') : $order->phone;

        if (empty($phone)) {
            return back()->with('error', 'Aucun numéro de téléphone associé à cette commande.');
        }

        $smsService = app(\App\Services\SmsService::class);
        $result = $smsService->send(
            recipients: $phone,
            message: $request->input('message'),
            adminId: auth()->id()
        );

        if ($result['success']) {
            return back()->with('success', 'SMS envoyé au client avec succès !');
        }

        return back()->with('error', 'Échec de l\'envoi : ' . $result['message']);
    }
}
