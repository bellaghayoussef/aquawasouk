<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Get product reviews
     */
    public function index(Product $product)
    {
        $reviews = $product->reviews()
            ->with('user')
            ->approved()
            ->latest()
            ->paginate(10);

        $avgRating = $product->reviews()->approved()->avg('rating');
        $ratingDistribution = $product->reviews()
            ->approved()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'average_rating' => round($avgRating, 1),
                'rating_distribution' => $ratingDistribution,
            ],
        ]);
    }

    /**
     * Store a new review
     */
    public function store(Request $request, Product $product)
    {
        $user = $request->user();

        // Check if user has already reviewed this product
        $existingReview = Review::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product',
            ], 400);
        }

        // Check if user has purchased this product
        $hasPurchased = $user->orders()
            ->whereHas('items', function ($q) use ($product) {
                $q->where('product_id', $product->id);
            })
            ->where('payment_status', 'paid')
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'success' => false,
                'message' => 'You can only review products you have purchased',
            ], 403);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => false, // Pending admin approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully and is pending approval',
            'data' => $review->load('user'),
        ], 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, Review $review)
    {
        // Verify ownership
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|max:1000',
        ]);

        $review->update($request->only(['rating', 'comment']));
        $review->is_approved = false; // Reset approval status

        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully and is pending approval',
            'data' => $review,
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, Review $review)
    {
        // Verify ownership
        if ($review->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Get user's reviews
     */
    public function myReviews(Request $request)
    {
        $user = $request->user();

        $reviews = Review::with(['product.images', 'product.vendor'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }
}
