<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * Apply a coupon code to the current cart session
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'vendor_id' => 'nullable|integer',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return back()->with('coupon_error', __('Code promo invalide.'));
        }

        // Calculate subtotal from cart
        $cart = null;
        if (auth()->check()) {
            $cart = Cart::with('items.product')->where('user_id', auth()->id())->first();
        } else {
            $cart = Cart::with('items.product')->where('session_id', session()->getId())->first();
        }

        $subtotal = 0.0;
        if ($cart && $cart->items->isNotEmpty()) {
            if ($request->filled('vendor_id')) {
                $subtotal = (float) $cart->items->filter(function ($item) use ($request) {
                    return $item->product && $item->product->vendor_id == $request->vendor_id;
                })->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
            } else {
                $subtotal = (float) $cart->items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
            }
        }

        $validation = $coupon->isValid($subtotal, auth()->id(), $request->vendor_id);
        if (!$validation['valid']) {
            return back()->with('coupon_error', $validation['message']);
        }

        $discount = $coupon->calculateDiscount($subtotal);

        session()->put('applied_coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount' => $discount,
            'vendor_id' => $coupon->vendor_id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Code promo appliqué avec succès !'),
                'coupon' => session('applied_coupon'),
            ]);
        }

        return back()->with('coupon_success', __('Code promo appliqué : -:discount DT', ['discount' => number_format($discount, 2)]));
    }

    /**
     * Remove the applied coupon from session
     */
    public function remove(Request $request)
    {
        session()->forget('applied_coupon');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Code promo retiré.'),
            ]);
        }

        return back()->with('success', __('Code promo retiré.'));
    }

    /**
     * API: Validate coupon
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
            'vendor_id' => 'nullable|integer',
        ]);

        $code = strtoupper(trim($request->code));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => __('Code promo invalide.'),
            ], 404);
        }

        $validation = $coupon->isValid((float)$request->subtotal, auth('sanctum')->id(), $request->vendor_id);

        if (!$validation['valid']) {
            return response()->json($validation, 422);
        }

        $discount = $coupon->calculateDiscount((float)$request->subtotal);

        return response()->json([
            'valid' => true,
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'discount' => $discount,
            'message' => __('Code promo valide !'),
        ]);
    }
}
