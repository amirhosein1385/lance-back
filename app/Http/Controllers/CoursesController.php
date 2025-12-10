<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CoursesController extends Controller
{
    public function index($profileId)
    {
        $profile = Profile::findOrFail($profileId);
        $courses = $profile->courses()->orderBy('completion_date', 'desc')->get();

        return response()->json($courses);
    }

    /**
     * Get a single course
     */
    public function show($profileId, $courseId)
    {
        $course = Course::where('profile_id', $profileId)->findOrFail($courseId);

        return response()->json($course);
    }

    /**
     * Create a new course
     */
    public function store(Request $request, $profileId)
    {
        $profile = Profile::findOrFail($profileId);

        // Check authorization
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'certificate_url' => 'nullable|url|max:500',
            'price' => 'nullable|numeric',
            'discounted_price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $course = $profile->courses()->create($request->all());

        return response()->json($course, 201);
    }

    /**
     * Update a course
     */
    public function update(Request $request, $profileId, $courseId)
    {
        $profile = Profile::findOrFail($profileId);

        // Check authorization
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course = Course::where('profile_id', $profileId)->findOrFail($courseId);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'certificate_url' => 'nullable|url|max:500',
            'price' => 'nullable|numeric',
            'discounted_price' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $course->update($request->all());

        return response()->json($course);
    }

    /**
     * Delete a course
     */
    public function destroy(Request $request, $profileId, $courseId)
    {
        $profile = Profile::findOrFail($profileId);

        // Check authorization
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course = Course::where('profile_id', $profileId)->findOrFail($courseId);
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }
}
