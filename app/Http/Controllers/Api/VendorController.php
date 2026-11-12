<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageService;

class VendorController extends Controller
{
    /**
     * Get vendor dashboard data
     */
    public function dashboard(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        $totalProducts = $vendor->products()->count();
        $activeProducts = $vendor->products()->where('status', 'active')->count();
        $totalOrders = $vendor->orderItems()->count();
        $pendingOrders = $vendor->orderItems()->where('status', 'pending')->count();
        
        $totalRevenue = $vendor->getTotalSales();
        if ($totalRevenue <= 0) {
            $totalRevenue = (float) ($vendor->orderItems()
                ->where('status', '!=', 'cancelled')
                ->selectRaw('SUM(price * quantity) as total')
                ->value('total') ?? 0.00);
        }

        $monthlyRevenue = (float) ($vendor->orderItems()
            ->where('status', '!=', 'cancelled')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->selectRaw('SUM(price * quantity) as total')
            ->value('total') ?? 0.00);

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => $vendor,
                'stats' => [
                    'total_products' => $totalProducts,
                    'active_products' => $activeProducts,
                    'total_orders' => $totalOrders,
                    'pending_orders' => $pendingOrders,
                    'total_revenue' => round($totalRevenue, 2),
                    'total_sales' => round($totalRevenue, 2),
                    'monthly_revenue' => round($monthlyRevenue, 2),
                ],
            ],
        ]);
    }

    /**
     * Get vendor statistics
     */
    public function statistics(Request $request)
    {
        $vendor = $request->user()->vendor;
        
        $period = $request->get('period', '30days'); // 7days, 30days, 90days, year

        $days = match($period) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            'year' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days);

        $stats = [
            'total_sales' => $vendor->orderItems()
                ->whereHas('order', function ($q) {
                    $q->where('payment_status', 'paid');
                })
                ->where('created_at', '>=', $startDate)
                ->sum('price'),
            
            'total_orders' => $vendor->orderItems()
                ->where('created_at', '>=', $startDate)
                ->count(),
            
            'avg_order_value' => $vendor->orderItems()
                ->where('created_at', '>=', $startDate)
                ->avg('price'),
            
            'product_views' => $vendor->products()
                ->where('updated_at', '>=', $startDate)
                ->sum('views'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get sales chart data
     */
    public function salesChart(Request $request)
    {
        $vendor = $request->user()->vendor;
        $days = $request->get('days', 30);

        $salesData = $vendor->orderItems()
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(price) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $salesData,
        ]);
    }

    /**
     * Get vendor profile
     */
    public function profile(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor profile not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vendor,
        ]);
    }

    /**
     * Update vendor profile
     */
    public function updateProfile(Request $request)
    {
        $vendor = $request->user()->vendor;

        $request->validate([
            'shop_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'country' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'shipping_price' => 'sometimes|numeric|min:0',
        ]);

        $vendor->update($request->only([
            'shop_name', 'description', 'phone', 'address',
            'city', 'country', 'postal_code', 'shipping_price'
        ]));

        // Update slug if shop name changed
        if ($request->has('shop_name')) {
            $vendor->slug = Str::slug($request->shop_name);
            $vendor->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $vendor,
        ]);
    }

    /**
     * Update vendor logo
     */
    public function updateLogo(Request $request)
    {
        $vendor = $request->user()->vendor;

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old logo
        if ($vendor->logo) {
            Storage::disk('public')->delete($vendor->logo);
        }

        $logoPath = $request->file('logo')->store('vendors/logos', 'public');
        $vendor->update(['logo' => $logoPath]);

        return response()->json([
            'success' => true,
            'message' => 'Logo updated successfully',
            'data' => ['logo' => $logoPath],
        ]);
    }

    /**
     * Update vendor banner
     */
    public function updateBanner(Request $request)
    {
        $vendor = $request->user()->vendor;

        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        // Delete old banner
        if ($vendor->banner) {
            Storage::disk('public')->delete($vendor->banner);
        }

        $bannerPath = $request->file('banner')->store('vendors/banners', 'public');
        $vendor->update(['banner' => $bannerPath]);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully',
            'data' => ['banner' => $bannerPath],
        ]);
    }

    /**
     * Get vendor products
     */
    public function products(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor profile not found'], 404);
        }

        $query = Product::with(['category', 'images', 'primaryImage', 'variations'])
            ->where('vendor_id', $vendor->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Store a new product
     */
    public function storeProduct(Request $request)
    {
        $vendor = $request->user()->vendor;

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'specifications' => 'nullable|array',
            'is_featured' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

$slug = Str::slug($request->name);
$originalSlug = $slug;
$count = 1;

while (\App\Models\Product::where('slug', $slug)->exists()) {
    $slug = $originalSlug . '-' . $count;
    $count++;
}


        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'specifications' => $request->specifications,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock' => $request->stock,
            'is_featured' => $request->is_featured ?? false,
            'status' => 'active',
        ]);

        // Handle images with WebP conversion
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = ImageService::storeAsWebp($image, 'products');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }
$pr = $product->load('images');
$pr->reviews_count = 0;
$pr->stock = (int)$pr->stock;
$pr->category_id = (int) $pr->category_id;
        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' =>$pr,
        ], 201);
    }

    /**
     * Show a single product
     */
    public function showProduct(Request $request, Product $product)
    {
        // Verify ownership
        if ($product->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $product->load(['category', 'images', 'primaryImage', 'variations', 'reviews']);

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Update a product
     */
    public function updateProduct(Request $request, Product $product)
    {
        // Verify ownership
        if ($product->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'specifications' => 'nullable|array',
            'is_featured' => 'boolean',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $product->update($request->only([
            'category_id', 'name', 'description', 'price',
            'discount_price', 'stock', 'specifications',
            'is_featured', 'status'
        ]));

        // Update slug if name changed
        if ($request->has('name')) {
$slug = Str::slug($request->name);
$originalSlug = $slug;
$count = 1;

while (\App\Models\Product::where('slug', $slug)->exists()) {
    $slug = $originalSlug . '-' . $count;
    $count++;
}

            $product->slug =$slug;

            $product->save();
        }
$pr = $product;
$pr->reviews_count = 0;
$pr->stock = (int)$pr->stock;
$pr->category_id = (int) $pr->category_id;


        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Request $request, Product $product)
    {
        // Verify ownership
        if ($product->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Delete product images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    /**
     * Add product image
     */
    public function addProductImage(Request $request, Product $product)
    {
        // Verify ownership
        if ($product->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_primary' => 'boolean',
        ]);

        $path = ImageService::storeAsWebp($request->file('image'), 'products');
        
        // If this is primary, unset others
        if ($request->is_primary) {
            ProductImage::where('product_id', $product->id)
                ->update(['is_primary' => false]);
        }

        $lastOrder = ProductImage::where('product_id', $product->id)->max('sort_order') ?? 0;

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path,
            'is_primary' => $request->is_primary ?? false,
            'sort_order' => $lastOrder + 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image added successfully',
            'data' => $image,
        ], 201);
    }

    /**
     * Delete product image
     */
    public function deleteProductImage(Request $request, ProductImage $image)
    {
        // Verify ownership
        if ($image->product->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Toggle product status
     */
    public function toggleProductStatus(Request $request, Product $product)
    {
        // Verify ownership
        if ($product->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $product->status = $product->status === 'active' ? 'inactive' : 'active';
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product status updated',
            'data' => ['status' => $product->status],
        ]);
    }

    /**
     * Toggle product featured
     */
    public function toggleProductFeatured(Request $request, Product $product)
    {
        // Verify ownership
        if ($product->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $product->is_featured = !$product->is_featured;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product featured status updated',
            'data' => ['is_featured' => $product->is_featured],
        ]);
    }

    /**
     * Get vendor orders
     */
    public function orders(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor profile not found'], 404);
        }

        $query = OrderItem::with(['product.primaryImage', 'product.images', 'order.user'])
            ->where('vendor_id', $vendor->id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * Show order details
     */
    public function showOrder(Request $request, OrderItem $orderItem)
    {
        // Verify ownership
        if ($orderItem->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $orderItem->load(['product.primaryImage', 'product.images', 'order.user', 'order.items.product.primaryImage']);

        return response()->json([
            'success' => true,
            'data' => $orderItem,
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus(Request $request, OrderItem $orderItem)
    {
        // Verify ownership
        if ($orderItem->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $orderItem->update(['status' => $request->status]);

        // Update main order status if all items have same status
        $order = $orderItem->order;
        $allStatuses = $order->items->pluck('status')->unique();
        
        if ($allStatuses->count() === 1) {
            $order->update(['status' => $allStatuses->first()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'data' => $orderItem,
        ]);
    }

    /**
     * Generate invoice for order
     */
    public function generateInvoice(Request $request, OrderItem $orderItem)
    {
        // Verify ownership
        if ($orderItem->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $orderItem->load(['product', 'order.user', 'vendor']);

        return response()->json([
            'success' => true,
            'data' => [
                'order_item' => $orderItem,
                'invoice_number' => 'INV-' . $orderItem->order->order_number . '-' . $orderItem->id,
                'invoice_date' => $orderItem->created_at->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Generate waybill details for courier / delivery
     */
    public function generateWaybill(Request $request, OrderItem $orderItem)
    {
        // Verify ownership
        if ($orderItem->vendor_id !== $request->user()->vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $orderItem->load(['product', 'order.user', 'vendor', 'variation']);

        $vendorItems = OrderItem::with(['product', 'variation'])
            ->where('order_id', $orderItem->order_id)
            ->where('vendor_id', $request->user()->vendor->id)
            ->get();

        $vendorItemsTotal = $vendorItems->sum(fn($i) => $i->price * $i->quantity);
        $shipping = $orderItem->vendor->shipping_price ?? 0;
        $totalToCollect = $vendorItemsTotal + $shipping;

        return response()->json([
            'success' => true,
            'data' => [
                'waybill_number' => 'WB-' . $orderItem->order->order_number,
                'order_number' => $orderItem->order->order_number,
                'date' => $orderItem->order->created_at->format('d/m/Y H:i'),
                'recipient' => [
                    'name' => $orderItem->order->user?->name ?? 'Client',
                    'phone' => $orderItem->order->phone,
                    'governorate' => $orderItem->order->shipping_city,
                    'address' => $orderItem->order->shipping_address,
                    'postal_code' => $orderItem->order->shipping_postal_code,
                    'notes' => $orderItem->order->notes,
                ],
                'sender' => [
                    'shop_name' => $orderItem->vendor->shop_name,
                    'phone' => $orderItem->vendor->phone,
                    'city' => $orderItem->vendor->city,
                    'address' => $orderItem->vendor->address,
                ],
                'items' => $vendorItems->map(fn($item) => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => (float) $item->price,
                    'total' => (float) ($item->price * $item->quantity),
                    'size' => $item->size,
                    'color' => $item->color,
                ]),
                'financials' => [
                    'subtotal' => (float) $vendorItemsTotal,
                    'shipping_cost' => (float) $shipping,
                    'total_to_collect_cod' => (float) $totalToCollect,
                    'currency' => 'DT',
                ],
            ],
        ]);
    }

    /**
     * Get revenue analytics
     */
    public function revenue(Request $request)
    {
        $vendor = $request->user()->vendor;
        $period = $request->get('period', 'month'); // week, month, year

        $startDate = match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $revenue = $vendor->orderItems()
            ->whereHas('order', function ($q) {
                $q->where('payment_status', 'paid');
            })
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('SUM(price) as total_revenue'),
                DB::raw('SUM(commission) as total_commission'),
                DB::raw('SUM(price - commission) as net_revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->first();

        return response()->json([
            'success' => true,
            'data' => $revenue,
        ]);
    }

    /**
     * Get top selling products
     */
    public function topProducts(Request $request)
    {
        $vendor = $request->user()->vendor;
        $limit = $request->get('limit', 10);

        $topProducts = Product::where('vendor_id', $vendor->id)
            ->withCount('orderItems')
            ->orderBy('order_items_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $topProducts,
        ]);
    }

    /**
     * Get recent orders
     */
    public function recentOrders(Request $request)
    {
        $vendor = $request->user()->vendor;
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor profile not found'], 404);
        }

        $limit = $request->get('limit', 10);

        $recentOrders = OrderItem::with(['product.primaryImage', 'product.images', 'order.user'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $recentOrders,
        ]);
    }

    /**
     * Get customers
     */
    public function customers(Request $request)
    {
        $vendor = $request->user()->vendor;

        $customers = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('order_items.vendor_id', $vendor->id)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT orders.id) as total_orders'),
                DB::raw('SUM(order_items.price) as total_spent')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_spent', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Get vendor reviews
     */
    public function reviews(Request $request)
    {
        $vendor = $request->user()->vendor;

        $reviews = Review::with(['user', 'product'])
            ->whereHas('product', function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            })
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Reply to review (if you implement this feature)
     */
    public function replyToReview(Request $request, Review $review)
    {
        $vendor = $request->user()->vendor;

        // Verify the review is for vendor's product
        if ($review->product->vendor_id !== $vendor->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        // You would need to add a 'vendor_reply' column to reviews table
        // $review->update(['vendor_reply' => $request->reply]);

        return response()->json([
            'success' => true,
            'message' => 'Reply posted successfully',
        ]);
    }

    /**
     * Get notifications
     */
    public function notifications(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $notificationId)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }
}
