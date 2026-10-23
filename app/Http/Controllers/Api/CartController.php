<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get user's cart
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with(['items.product.vendor', 'items.product.images'])
            ->firstOrCreate(['user_id' => $user->id]);

        $cartTotal = $cart->items->sum(function ($item) {
            return $item->quantity * ($item->product->discount_price ?? $item->product->price);
        });
        
        $cart->total = $cartTotal;

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'color' => 'nullable|string',
            'size' => 'nullable|string',
        ]);

        $user = $request->user();
        $product = Product::findOrFail($request->product_id);

        // Check stock
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
            ], 400);
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Check if product with same size & color already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->where('color', $request->color)
            ->where('size', $request->size)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available',
                ], 400);
            }

            $cartItem->quantity = $newQuantity;
            $cartItem->save();
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->discount_price ?? $product->price,
                'color' => $request->color,
                'size' => $request->size,
            ]);
        }

        $cart->load(['items.product.vendor', 'items.product.images']);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'data' => $cart,
        ], 201);
    }

    /**
     * Update cart item
     */
    public function update(Request $request, CartItem $cartItem)
    {
        // Verify ownership
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Check stock
        if ($cartItem->product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available',
            ], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'data' => $cartItem->load('product'),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, CartItem $cartItem)
    {
        // Verify ownership
        if ($cartItem->cart->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully',
        ]);
    }

    /**
     * Get cart items count
     */
    public function count(Request $request)
    {
        $user = $request->user();
        $cart = Cart::where('user_id', $user->id)->first();
        
        $count = $cart ? $cart->items()->count() : 0;

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }
}
