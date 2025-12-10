<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Skill;
use Dotenv\Validator;
use Illuminate\Http\Request;

class ProfileSkillsController extends Controller
{
    public function index($profileId) {
        $profile = Profile::findOrFail($profileId);
        $skills = $profile->skills()->withPivot('proficiency_level', 'years_of_experience')->get();
        return response()->json($skills);
    }

    public function show($profileId, $skillId) {

    }

    public function store(Request $request , $profileId, $skillId) {
      $profile = Profile::findOrFail($profileId);

      if ($profile->user_id != $request->user()->id) {
          return response()->json(['message' => 'Unauthorized'], 403);
      }
       $validator = Validator::make($request->all(), [
           "skill_id" => "required",
           "proficiency_level" => "required",
           "years_of_experience" => "required"
       ]) ;

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if skill already attached
        if ($profile->skills()->where('skill_id', $request->skill_id)->exists()) {
            return response()->json(['message' => 'Skill already added'], 409);
        }

        $profile->skills()->attach($request->skill_id, [
            'proficiency_level' => $request->proficiency_level,
            'years_of_experience' => $request->years_of_experience,
        ]);

        return response()->json(['message' => 'Skill added successfully'], 201);
    }

    public function update(Request $request, $profileId, $skillId)
    {
        $profile = Profile::findOrFail($profileId);

        // Check authorization
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'proficiency_level' => 'required|integer|min:1|max:5',
            'years_of_experience' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile->skills()->updateExistingPivot($skillId, [
            'proficiency_level' => $request->proficiency_level,
            'years_of_experience' => $request->years_of_experience,
        ]);

        return response()->json(['message' => 'Skill updated successfully']);
    }

    public function destroy(Request $request, $profileId, $skillId)
    {
        $profile = Profile::findOrFail($profileId);

        // Check authorization
        if ($profile->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $profile->skills()->detach($skillId);

        return response()->json(['message' => 'Skill removed successfully']);
    }
}
