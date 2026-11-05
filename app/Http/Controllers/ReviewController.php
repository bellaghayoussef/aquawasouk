<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Services\Cache\MarketplaceCacheService;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Store a product review (Web & AJAX)
     */
    public function store(Request $request, Product $product)
    {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Veuillez vous connecter pour laisser un avis.'),
                ], 401);
            }
            return redirect()->route('login')->with('error', __('Veuillez vous connecter pour laisser un avis.'));
        }

        $userId = auth()->id();

        // Check if user has already reviewed
        $existing = Review::where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Vous avez déjà déposé un avis pour ce produit.'),
                ], 422);
            }
            return back()->with('error', __('Vous avez déjà déposé un avis pour ce produit.'));
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'comment' => 'required|string|max:1500',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Check if verified purchase (bought this product in an order that is delivered or paid)
        $hasPurchased = Order::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('status', 'delivered')
                  ->orWhere('payment_status', 'paid');
            })
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->exists();

        // Handle uploaded images
        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if (count($uploadedImages) >= 3) break; // max 3 images
                $filename = 'rev_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/reviews'), $filename);
                $uploadedImages[] = 'uploads/reviews/' . $filename;
            }
        }

        $review = Review::create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'rating' => (int) $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'verified_purchase' => $hasPurchased,
            'is_approved' => true, // Instant approval for smooth customer experience
        ]);

        // Clear product cache so new rating immediately appears
        MarketplaceCacheService::clearProductCache($product);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Merci ! Votre avis a été publié avec succès.'),
                'review' => $review->load('user'),
                'average_rating' => $product->fresh()->average_rating,
                'reviews_count' => $product->fresh()->reviews_count,
            ]);
        }

        return back()->with('success', __('Merci ! Votre avis a été publié avec succès.'));
    }

    /**
     * Vendor replies to a customer review
     */
    public function reply(Request $request, Review $review)
    {
        $vendor = auth()->user()->vendor;
        if (!$vendor || $review->product->vendor_id !== $vendor->id) {
            abort(403, 'Action non autorisée.');
        }

        $request->validate([
            'vendor_reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'vendor_reply' => $request->vendor_reply,
            'vendor_replied_at' => now(),
        ]);

        return back()->with('success', __('Votre réponse a été enregistrée.'));
    }
}
