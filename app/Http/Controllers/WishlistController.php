<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display the customer's wishlist page
     */
    public function index()
    {
        $navCategories = Category::where('parent_id', null)->take(8)->get();
        $cartCount = 0;
        if (auth()->check()) {
            $cart = Cart::where('user_id', auth()->id())->first();
            $cartCount = $cart ? (int) $cart->items()->sum('quantity') : 0;
        }

        $wishlists = collect();
        if (auth()->check()) {
            $wishlists = Wishlist::with(['product.vendor', 'product.primaryImage', 'product.category'])
                ->where('user_id', auth()->id())
                ->latest()
                ->get();
        }

        return view('blade.wishlist', [
            'wishlists' => $wishlists,
            'navCategories' => $navCategories,
            'cartCount' => $cartCount,
        ]);
    }

    /**
     * Toggle product in user's wishlist (AJAX or direct POST)
     */
    public function toggle(Request $request, Product $product)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'requires_login' => true,
                'message' => __('Veuillez vous connecter pour ajouter des produits à vos favoris.'),
            ], 401);
        }

        $userId = auth()->id();
        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
            $message = __('Produit retiré de vos favoris.');
        } else {
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $product->id,
            ]);
            $favorited = true;
            $message = __('Produit ajouté à vos favoris !');
        }

        $totalCount = Wishlist::where('user_id', $userId)->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'favorited' => $favorited,
                'count' => $totalCount,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
