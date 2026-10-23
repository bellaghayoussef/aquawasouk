<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorProfitMargin;
use App\Models\VendorPayout;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $vendors = Vendor::with(['user', 'profitMargins', 'payouts'])->get();
        
        $vendorFinancials = $vendors->map(function ($vendor) {
            $totalSales = $vendor->getTotalSales();
            $adminProfit = $vendor->getTotalAdminProfit();
            $pendingProfit = $vendor->getPendingAdminProfit();
            $vendorEarnings = $vendor->getVendorNetEarnings();
            $totalPayouts = $vendor->getTotalPayouts();
            $balance = $vendor->getCashBalance();

            return [
                'id' => $vendor->id,
                'shop_name' => $vendor->shop_name,
                'owner_name' => $vendor->user ? $vendor->user->name : 'N/A',
                'owner_email' => $vendor->user ? $vendor->user->email : 'N/A',
                'status' => $vendor->status,
                'total_sales' => round($totalSales, 2),
                'admin_profit' => round($adminProfit, 2),
                'pending_profit' => round($pendingProfit, 2),
                'vendor_earnings' => round($vendorEarnings, 2),
                'total_payouts' => round($totalPayouts, 2),
                'balance' => round($balance, 2),
                'margins_count' => $vendor->profitMargins->count(),
            ];
        })->sortByDesc('admin_profit')->values();

        $totalAdminProfit = $vendorFinancials->sum('admin_profit');
        $totalVendorPayouts = $vendorFinancials->sum('total_payouts');
        $totalVendorBalance = $vendorFinancials->sum('balance');

        $stats = [
            'total_vendors' => Vendor::count(),
            'active_vendors' => Vendor::where('status', 'active')->count(),
            'pending_vendors' => Vendor::where('status', 'pending')->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
            'total_revenue' => Order::where('status', '!=', 'cancelled')->sum('total_amount'),
            'total_admin_profit' => round($totalAdminProfit, 2),
            'total_vendor_payouts' => round($totalVendorPayouts, 2),
            'total_vendor_balance' => round($totalVendorBalance, 2),
            'total_customers' => User::where('role', 'customer')->count(),
        ];

        $recentOrders = Order::with(['user', 'items.product'])
            ->latest()
            ->take(10)
            ->get();

        $recentProducts = Product::with(['vendor', 'category'])
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'recentProducts' => $recentProducts,
            'vendorFinancials' => $vendorFinancials,
            'vendors' => $vendors->map->only(['id', 'shop_name']),
        ]);
    }

    public function vendors(Request $request)
    {
        $query = Vendor::with('user')->withCount('products');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shop_name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $vendors = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Vendors', [
            'vendors' => $vendors,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function updateVendorStatus(Vendor $vendor, Request $request)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $vendor->update(['status' => $request->status]);

        return back()->with('success', 'Vendor status updated successfully');
    }

    public function products(Request $request)
    {
        $query = Product::with(['vendor', 'category', 'primaryImage']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhereHas('vendor', function($q) use ($search) {
                      $q->where('shop_name', 'like', "%{$search}%");
                  });
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

        return Inertia::render('Admin/Products', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'category', 'date_from', 'date_to']),
        ]);
    }

    public function orders(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Search filter (customer name or order number)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $orders->getCollection()->transform(function ($order) {
            $order->admin_profit = round($order->items->sum('commission'), 2);
            return $order;
        });

        return Inertia::render('Admin/Orders', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function showOrder(Order $order)
    {
        $order->load(['user', 'items.product.vendor', 'items.product.primaryImage']);
        $order->admin_profit = round($order->items->sum('commission'), 2);

        return Inertia::render('Admin/OrderShow', [
            'order' => $order,
        ]);
    }

    public function updateOrderStatus(Order $order, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $originalStatus = $order->status;
        $order->update(['status' => $request->status]);

        if ($originalStatus !== $request->status) {
            try {
                if ($order->user) {
                    \Illuminate\Support\Facades\Mail::to($order->user->email)->send(new \App\Mail\OrderStatusUpdated($order));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send order status email: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Order status updated successfully');
    }

    public function deleteVendor(Vendor $vendor)
    {
        $vendor->delete();
        return back()->with('success', 'Vendor deleted successfully');
    }

    public function deleteProduct(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted successfully');
    }

    // Category Management
    public function categories(Request $request)
    {
        $query = Category::with('parent')->withCount('products');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $categories = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search', 'date_from', 'date_to']),
        ]);
    }

    public function createCategory()
    {
        $categories = Category::whereNull('parent_id')->get();
        
        return Inertia::render('Admin/Categories/Create', [
            'categories' => $categories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        Category::create($validated);

        return redirect()->route('admin.categories')->with('success', 'Category created successfully');
    }

    public function editCategory(Category $category)
    {
        $categories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->get();
            
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = \Str::slug($validated['name']);

        $category->update($validated);

        return redirect()->route('admin.categories')->with('success', 'Category updated successfully');
    }

    public function deleteCategory(Category $category)
    {
        // Check if category has products
        if ($category->products()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing products');
        }

        $category->delete();
        return back()->with('success', 'Category deleted successfully');
    }

    // Contact Messages Management
    public function contactMessages(Request $request)
    {
        $query = ContactMessage::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $messages = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/ContactMessages', [
            'messages' => $messages,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function updateContactStatus(ContactMessage $message, Request $request)
    {
        $request->validate([
            'status' => 'required|in:new,read,replied',
            'admin_notes' => 'nullable|string',
        ]);

        $message->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Contact message status updated successfully');
    }

    public function deleteContactMessage(ContactMessage $message)
    {
        $message->delete();
        return back()->with('success', 'Contact message deleted successfully');
    }

    // User Management
    public function users(Request $request)
    {
        $query = User::withCount(['orders']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'date_from', 'date_to']),
        ]);
    }

    public function editUser(User $user)
    {
        return Inertia::render('Admin/UserEdit', [
            'user' => $user->load('vendor'),
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:customer,vendor,admin',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    public function deleteUser(User $user)
    {
        // Don't allow deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account');
        }

        // Delete associated vendor if exists
        if ($user->vendor) {
            $user->vendor->delete();
        }

        $user->delete();
        return back()->with('success', 'User deleted successfully');
    }

    // Vendor Edit/Update
    public function editVendor(Vendor $vendor)
    {
        $vendor->load(['user', 'profitMargins', 'payouts.creator']);

        $financials = [
            'total_sales' => round($vendor->getTotalSales(), 2),
            'admin_profit' => round($vendor->getTotalAdminProfit(), 2),
            'net_earnings' => round($vendor->getVendorNetEarnings(), 2),
            'total_payouts' => round($vendor->getTotalPayouts(), 2),
            'balance' => round($vendor->getCashBalance(), 2),
        ];

        return Inertia::render('Admin/VendorEdit', [
            'vendor' => $vendor,
            'financials' => $financials,
        ]);
    }

    public function saveVendorMargins(Request $request, Vendor $vendor)
    {
        $request->validate([
            'margins' => 'nullable|array',
            'margins.*.min_order_price' => 'required|numeric|min:0',
            'margins.*.max_order_price' => 'nullable|numeric|min:0',
            'margins.*.margin_amount' => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->has('commission_rate')) {
            $vendor->update(['commission_rate' => $request->commission_rate]);
        }

        // Replace existing margins
        $vendor->profitMargins()->delete();

        if (!empty($request->margins)) {
            foreach ($request->margins as $m) {
                if (isset($m['margin_amount']) && $m['margin_amount'] !== null && $m['margin_amount'] !== '') {
                    $vendor->profitMargins()->create([
                        'min_order_price' => (float) ($m['min_order_price'] ?? 0),
                        'max_order_price' => (!empty($m['max_order_price']) || $m['max_order_price'] === '0') ? (float)$m['max_order_price'] : null,
                        'margin_amount' => (float) $m['margin_amount'],
                    ]);
                }
            }
        }

        return back()->with('success', 'Conditions de marge de profit enregistrées en Dinars Tunisiens pour ' . $vendor->shop_name);
    }

    public function cashRegister(Request $request)
    {
        $vendors = Vendor::with(['user'])->orderBy('shop_name')->get();

        // Total Marge Profit Admin collected from DELIVERED orders ONLY (Cash on Delivery)
        $totalAdminProfit = (float) \App\Models\OrderItem::whereHas('order', function ($q) {
            $q->where('status', 'delivered');
        })->sum('commission');

        // Total Pending Admin Profit from orders in progress (not delivered yet)
        $totalPendingProfit = (float) \App\Models\OrderItem::whereHas('order', function ($q) {
            $q->whereNotIn('status', ['delivered', 'cancelled']);
        })->sum('commission');

        // Total Withdrawals from Admin Profit Cash Register
        $totalWithdrawals = (float) VendorPayout::sum('amount');

        // Solde Caisse Marge Admin (from delivered orders)
        $cashBalance = round($totalAdminProfit - $totalWithdrawals, 2);

        // Breakdown of Admin Profit per Vendor and Cash Balance
        $vendorSummaries = $vendors->map(function ($v) {
            $totalSales = round($v->getTotalSales(), 2);
            $adminProfit = round($v->getTotalAdminProfit(), 2);
            $pendingProfit = round($v->getPendingAdminProfit(), 2);
            $totalPayouts = round($v->getTotalPayouts(), 2);
            $remainingDue = round($v->getRemainingProfitDue(), 2);

            return [
                'id' => $v->id,
                'shop_name' => $v->shop_name,
                'owner_name' => $v->user ? $v->user->name : 'N/A',
                'owner_email' => $v->user ? $v->user->email : 'N/A',
                'total_sales' => $totalSales,
                'admin_profit' => $adminProfit,
                'pending_profit' => $pendingProfit,
                'total_payouts' => $totalPayouts,
                'balance' => $remainingDue,
                'margins_count' => $v->profitMargins()->count(),
            ];
        })->sortByDesc('admin_profit')->values();

        // Recent Orders showing their Admin Profit Margin contribution
        $recentOrderProfits = Order::with(['user', 'items.product.vendor'])
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->user ? $order->user->name : 'Client',
                    'created_at' => $order->created_at,
                    'total_amount' => round($order->total_amount, 2),
                    'admin_profit' => round($order->items->sum('commission'), 2),
                    'status' => $order->status,
                    'is_delivered' => $order->status === 'delivered',
                    'vendor_names' => $order->items->map(fn($i) => $i->product?->vendor?->shop_name)->filter()->unique()->values()->join(', '),
                ];
            });

        // Withdrawals Query from Cash Register
        $query = VendorPayout::with(['vendor', 'creator'])->latest('payout_date')->latest('id');

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payout_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payout_date', '<=', $request->date_to);
        }

        $withdrawals = $query->paginate(20)->withQueryString();

        $caisseTotals = [
            'total_admin_profit' => round($totalAdminProfit, 2),
            'total_pending_profit' => round($totalPendingProfit, 2),
            'total_withdrawals' => round($totalWithdrawals, 2),
            'cash_balance' => $cashBalance,
        ];

        return Inertia::render('Admin/CashRegister', [
            'withdrawals' => $withdrawals,
            'vendors' => $vendors->map->only(['id', 'shop_name']),
            'vendorSummaries' => $vendorSummaries,
            'recentOrderProfits' => $recentOrderProfits,
            'caisseTotals' => $caisseTotals,
            'filters' => $request->only(['vendor_id', 'date_from', 'date_to']),
        ]);
    }

    public function storePayout(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'amount' => 'required|numeric|min:0.01',
            'payout_date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $vendor = Vendor::findOrFail($validated['vendor_id']);
        $validated['created_by'] = auth()->id();

        VendorPayout::create($validated);

        return back()->with('success', "Retrait de " . number_format($validated['amount'], 2) . " DT enregistré avec succès pour la boutique \"{$vendor->shop_name}\" sur ses commandes livrées.");
    }

    public function deletePayout(VendorPayout $payout)
    {
        $payout->delete();

        return back()->with('success', 'Ligne de retrait supprimée de la caisse.');
    }

    public function updateVendor(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255|unique:vendors,shop_name,' . $vendor->id,
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
            'phone' => 'required|string',
            'status' => 'required|in:active,inactive,pending',
        ]);

        $vendor->update($validated);

        return redirect()->route('admin.vendors')->with('success', 'Vendor updated successfully');
    }

    // Product Edit/Update
    public function editProduct(Product $product)
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
        
        return Inertia::render('Admin/ProductEdit', [
            'product' => $product->load(['vendor.user', 'category', 'images']),
            'categories' => $categories,
        ]);
    }

    public function updateProduct(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->route('admin.products')->with('success', 'Product updated successfully');
    }
}
