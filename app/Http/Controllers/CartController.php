<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cart->load(['items.product.vendor', 'items.product.primaryImage']);

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:50',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $product = Product::with('variations')->findOrFail($request->product_id);
        
        $variation = null;
        if ($request->filled('variation_id')) {
            $variation = $product->variations()->find($request->variation_id);
        }

        $stockToCheck = $variation ? $variation->stock : $product->stock;
        if ($stockToCheck < $request->quantity) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => __('messages.out_of_stock')], 422);
            }
            return back()->with('error', __('messages.out_of_stock'));
        }

        $cart = $this->getOrCreateCart();

        $color = $request->input('color') ?: ($variation ? $variation->color : null);
        $size = $request->input('size') ?: ($variation ? $variation->size : null);

        // Check if product with same variation already in cart
        $query = $cart->items()->where('product_id', $product->id);
        if ($color) {
            $query->where('color', $color);
        } else {
            $query->whereNull('color');
        }
        if ($size) {
            $query->where('size', $size);
        } else {
            $query->whereNull('size');
        }
        $cartItem = $query->first();

        $price = ($variation && $variation->price > 0)
            ? (float) $variation->price
            : ($product->discount_price > 0 ? (float) $product->discount_price : (float) $product->price);

        if ($cartItem) {
            $cartItem->update([
                'quantity' => $cartItem->quantity + $request->quantity,
                'price' => $price,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $price,
                'color' => $color,
                'size' => $size,
            ]);
        }

        $totalCount = $cart->items()->sum('quantity');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('messages.cart_added_toast_title'),
                'cart_count' => $totalCount,
                'product_name' => $product->name,
                'product_price' => $price,
            ]);
        }

        return back()->with('cart_added_toast', [
            'name' => $product->name,
            'price' => $price,
        ])->with('success', __('messages.cart_added_toast_title'));
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($cartItem->product->stock < $request->quantity) {
            return back()->with('error', 'Insufficient stock available');
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return back()->with('success', 'Cart updated');
    }

    public function remove(CartItem $cartItem)
    {
        $cartItem->delete();

        return back()->with('success', 'Item removed from cart');
    }

    private function getOrCreateCart()
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        }

        $sessionId = session()->getId();
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }
}
