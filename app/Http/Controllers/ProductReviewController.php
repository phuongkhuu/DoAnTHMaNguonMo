<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductReview;
use App\Models\Product;

class ProductReviewController extends Controller
{
    /**
     * List all reviews for a given product
     */
    public function index(Product $product)
    {
        // eager load user info for display
        $reviews = $product->reviews()
            ->with('user')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    /**
     * Store a new review for a product
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string',
        ]);

        $review = ProductReview::create([
            'product_id' => $data['product_id'],
            'user_id'    => auth()->id(),
            'rating'     => $data['rating'],
            'comment'    => $data['comment'] ?? null,
        ]);

        return response()->json($review, 201);
    }

    /**
     * Optional: allow users to update their own review
     */
    public function all()
    {
        $reviews = ProductReview::with('product','user')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    public function update(Request $request, ProductReview $review)
    {
        $this->authorize('update', $review); // add policy if needed

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review->update($data);

        return response()->json($review);
    }

    /**
     * Optional: allow users to delete their own review
     */
    public function destroy(ProductReview $review)
    {

        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
