<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Profile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReviewsController extends Controller
{

    public function index($profileId)
    {
        $reviews = Review::with('reviewer.profile')
            ->where('profile_id', $profileId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate average rating
        $averageRating = Review::where('profile_id', $profileId)->avg('rating');

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($averageRating, 2),
            'total_reviews' => $reviews->count()
        ]);
    }

    /**
     * Get a single review
     */
    public function show($profileId, $reviewId)
    {
        $review = Review::with('reviewer.profile')
            ->where('profile_id', $profileId)
            ->findOrFail($reviewId);

        return response()->json($review);
    }

    /**
     * Create a new review
     */
    public function store(Request $request, $profileId)
    {
        $profile = Profile::findOrFail($profileId);

        // Check if user is trying to review their own profile
        if ($profile->user_id === $request->user()->id) {
            return response()->json(['message' => 'Cannot review your own profile'], 403);
        }

        // Check if user already reviewed this profile
        $existingReview = Review::where('profile_id', $profileId)
            ->where('reviewer_id', $request->user()->id)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this profile'], 409);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'project_title' => 'nullable|string|max:255',
            'is_employer_review' => 'boolean',
            'is_verified_hire' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reviewData = $request->all();
        $reviewData['reviewer_id'] = $request->user()->id;

        $review = Review::create($reviewData);

        // Update profile rating
        $this->updateProfileRating($profileId);

        return response()->json($review->load('reviewer'), 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, $profileId, $reviewId)
    {
        $review = Review::where('profile_id', $profileId)->findOrFail($reviewId);

        // Check authorization
        if ($review->reviewer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'project_title' => 'nullable|string|max:255',
            'is_employer_review' => 'boolean',
            'is_verified_hire' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $review->update($request->all());

        // Update profile rating
        $this->updateProfileRating($profileId);

        return response()->json($review->load('reviewer'));
    }

    /**
     * Delete a review
     */
    public function destroy(Request $request, $profileId, $reviewId)
    {
        $review = Review::where('profile_id', $profileId)->findOrFail($reviewId);

        // Check authorization (reviewer or profile owner can delete)
        $profile = Profile::findOrFail($profileId);
        if ($review->reviewer_id !== $request->user()->id && $profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        // Update profile rating
        $this->updateProfileRating($profileId);

        return response()->json(['message' => 'Review deleted successfully']);
    }

    /**
     * Get reviews by the authenticated user
     */
    public function myReviews(Request $request)
    {
        $reviews = Review::with('profile.user')
            ->where('reviewer_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reviews);
    }

    /**
     * Helper method to update profile rating
     */
    private function updateProfileRating($profileId)
    {
        $averageRating = Review::where('profile_id', $profileId)->avg('rating');

        $profile = Profile::findOrFail($profileId);
        $profile->rating = round($averageRating ?? 0);
        $profile->save();
    }
}
