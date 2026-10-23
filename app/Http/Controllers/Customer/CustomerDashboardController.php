<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerDashboardController extends Controller
{
    /**
     * Display customer dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Statistics
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'pending_orders' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'completed_orders' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'total_spent' => Order::where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
        ];

        return Inertia::render('Customer/Dashboard', [
            'recentOrders' => $recentOrders,
            'stats' => $stats,
        ]);
    }

    /**
     * Display all customer orders
     */
    public function orders(Request $request)
    {
        $query = Order::where('user_id', auth()->id())->with('items.product');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
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

        $orders = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Customer/Orders', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display single order details
     */
    public function orderShow($id)
    {
        $order = Order::where('user_id', auth()->id())
            ->where('id', $id)
            ->with(['items.product.primaryImage', 'items.vendor'])
            ->firstOrFail();

        return Inertia::render('Customer/OrderShow', [
            'order' => $order,
        ]);
    }

    /**
     * Display customer profile settings
     */
    public function settings()
    {
        return Inertia::render('Customer/Settings', [
            'user' => auth()->user(),
        ]);
    }
}
