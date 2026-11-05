<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get user's wishlist
     */
    public function wishlist(Request $request)
    {
        $userId = $request->user()->id;
        $items = Wishlist::with(['product.vendor', 'product.primaryImage'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => $item->product,
                    'created_at' => $item->created_at,
                ];
            }),
        ]);
    }

    /**
     * Add product to wishlist
     */
    public function addToWishlist(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $userId = $request->user()->id;

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $userId,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist',
            'data' => $wishlist,
        ]);
    }

    /**
     * Remove product from wishlist
     */
    public function removeFromWishlist(Request $request, $productId)
    {
        $userId = $request->user()->id;
        Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist',
        ]);
    }

    /**
     * Get user addresses
     */
    public function addresses(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    /**
     * Store new address
     */
    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:50',
            'recipient_name' => 'nullable|string|max:100',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'governorate' => 'nullable|string|max:100',
            'phone' => 'required|string|max:30',
            'is_default' => 'boolean',
        ]);

        $user = $request->user();
        if (!empty($validated['is_default']) || $user->addresses()->count() === 0) {
            $user->addresses()->update(['is_default' => false]);
            $validated['is_default'] = true;
        }

        if (empty($validated['recipient_name'])) {
            $validated['recipient_name'] = $user->name;
        }
        if (empty($validated['title'])) {
            $validated['title'] = 'Maison';
        }

        $address = $user->addresses()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address added successfully',
            'data' => $address,
        ], 201);
    }

    /**
     * Update address
     */
    public function updateAddress(Request $request, $addressId)
    {
        $address = $request->user()->addresses()->findOrFail($addressId);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:50',
            'recipient_name' => 'sometimes|string|max:100',
            'address' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'governorate' => 'nullable|string|max:100',
            'phone' => 'sometimes|string|max:30',
            'is_default' => 'boolean',
        ]);

        if (!empty($validated['is_default'])) {
            $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address,
        ]);
    }

    /**
     * Delete address
     */
    public function deleteAddress(Request $request, $addressId)
    {
        $address = $request->user()->addresses()->findOrFail($addressId);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully',
        ]);
    }

    /**
     * Set default address
     */
    public function setDefaultAddress(Request $request, $addressId)
    {
        $user = $request->user();
        $user->addresses()->update(['is_default' => false]);

        $address = $user->addresses()->findOrFail($addressId);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address set successfully',
            'data' => $address,
        ]);
    }
}
