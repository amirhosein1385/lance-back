<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
class ProfilesController extends Controller
{
    public function index(Request $request)
    {
        $query = Profile::with(['user' , 'skills' , 'works' , 'courses' , 'reviews']);

        if ($request->has('is_employer')) {
            $query->where('is_employer', $request->is_employer);
        }

        // Filter by verified
        if ($request->has('verified')) {
            $query->where('verified', $request->verified);
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Search by skills
        if ($request->has('skills')) {
            $skillIds = explode(',', $request->skills);
            $query->whereHas('skills', function ($q) use ($skillIds) {
                $q->whereIn('skills.id', $skillIds);
            });
        }

        $sortBy = $request->get('sort_by');
        $sortOrder = $request->get('sort_order');
        $query->orderBy($sortBy, $sortOrder);

        $profiles = $query->paginate($request->get('per_page', 15));
        return response()->json($profiles);
    }


    public function show($id) {
        $profile = Profile::with(['user' , 'skills' , 'works.skills' , 'courses' , 'reviews.reviewer'])->find($id);
        $profile->increment('profile_views');
        return response()->json($profile);
    }

    public function me(Request $request)
    {
        $profile = Profile::with([
            'skills',
            'courses',
            'works',
            'reviews'
        ])->where('user_id', $request->user()->id)->first();

        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'is_employer' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if user already has a profile
        $existingProfile = Profile::where('user_id', $request->user()->id)->first();
        if ($existingProfile) {
            return response()->json(['message' => 'Profile already exists'], 409);
        }

        $profileData = $request->only(['description', 'location', 'is_employer']);
        $profileData['user_id'] = $request->user()->id;

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $profileData['profile_image'] = $path;
        }

        $profile = Profile::create($profileData);

        return response()->json($profile, 201);
    }

    public function update(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        // Check authorization
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:255',
            'is_employer' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profileData = $request->only(['description', 'location', 'is_employer']);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
            }
            $path = $request->file('profile_image')->store('profiles', 'public');
            $profileData['profile_image'] = $path;
        }

        $profile->update($profileData);

        return response()->json($profile);
    }


    public function toggleStatus(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile->status = !$profile->status;
        $profile->save();

        return response()->json([
            'message' => 'Profile status updated',
            'status' => $profile->status
        ]);
    }
}
