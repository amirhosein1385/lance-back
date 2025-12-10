<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Dotenv\Validator;
use Illuminate\Http\Request;

class SkillsController extends Controller
{
    public function index(Request $request)
    {
        $query = Skill::query();

        if ($request->has('category')) {
            $query->where('category' , $request->get('category'));
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        $skills = $query->orderBy('name')->get();
        return response()->json($skills);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'category' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $skill =  Skill::create([
            'name' => $request->get('name'),
            'category' => $request->get('category'),
            'slug' => $request->get('slug'),
        ]);


        return response()->json($skill , 201);
    }
}



