<?php

namespace App\Http\Controllers;

use App\Models\Preregistration;
use Illuminate\Http\Request;
use function Webmozart\Assert\Tests\StaticAnalysis\length;

class PreregistrationController extends Controller
{
    public function create(Request $request) {
        $validated = $request->validate([
            "phone_number" => [
                "required",
                "string",
                "regex:/^(09|9)\d{9}$/",  // Validates Iranian mobile numbers
                "unique:preregistrations"
            ],
        ], [
            'phone_number.required' => 'شماره تلفن الزامی است',
            'phone_number.regex' => 'شماره تلفن باید با 09 شروع شود و 11 رقم باشد',
            'phone_number.unique' => 'این شماره قبلا ثبت شده است',
        ]);

        // Normalize the phone number to start with 09
        if (strlen($validated['phone_number']) === 10) {
            $validated['phone_number'] = '0' . $validated['phone_number'];
        }

        $preregistration = Preregistration::create($validated);

        return response()->json([
            'message' => 'Preregistration created successfully',
            'data' => $preregistration
        ], 201);
    }

    public function list() {
        $count = Preregistration::count();
        return response()->json([
            'count' => $count
        ], 200);
    }
}
