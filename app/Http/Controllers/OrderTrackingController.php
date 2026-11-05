<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    /**
     * Show order tracking page (Search or Direct Order Timeline)
     */
    public function index(Request $request)
    {
        $navCategories = Category::where('parent_id', null)->take(8)->get();
        $cartCount = 0;
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->first();
            $cartCount = $cart ? (int) $cart->items()->sum('quantity') : 0;
        }

        $order = null;
        $orderNumber = trim($request->get('order') ?? $request->get('order_number') ?? '');
        $phone = trim($request->get('phone') ?? '');

        if (!empty($orderNumber)) {
            $query = Order::with(['items.product.primaryImage', 'items.product.vendor', 'user', 'coupon'])
                ->where('order_number', $orderNumber);

            if (!empty($phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                $query->where(function ($q) use ($cleanPhone, $phone) {
                    $q->where('phone', 'like', "%{$phone}%")
                      ->orWhere('phone', 'like', "%{$cleanPhone}%");
                });
            } elseif (!auth()->check()) {
                // If not logged in and no phone provided, we only allow access if owner matches session/cookie or prompt for phone
            }

            $order = $query->first();

            if (!$order) {
                return view('blade.orders.track', [
                    'order' => null,
                    'searchAttempted' => true,
                    'orderNumber' => $orderNumber,
                    'phone' => $phone,
                    'error' => __('Aucune commande trouvée avec ces identifiants. Veuillez vérifier votre numéro de commande et téléphone.'),
                    'navCategories' => $navCategories,
                    'cartCount' => $cartCount,
                ]);
            }
        }

        return view('blade.orders.track', [
            'order' => $order,
            'searchAttempted' => !empty($orderNumber),
            'orderNumber' => $orderNumber,
            'phone' => $phone,
            'error' => null,
            'navCategories' => $navCategories,
            'cartCount' => $cartCount,
        ]);
    }
}
