<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register with phone number
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|unique:users|regex:/^[0-9]{10,15}$/',
                'password' => 'required|string|min:8',
            ]);

            \Log::info('Registration attempt', $request->all());

            $user = User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'password' => Hash::make($request->password),
            ]);
            $profile = Profile::create([
                'user_id' => $user->id,
            ]);

            \Log::info('User created', ['user_id' => $user->id]);

            // Create token for immediate login
            $token = $user->createToken('auth_token')->plainTextToken;

            \Log::info('Token created for user', ['user_id' => $user->id]);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                    'created_at' => $user->created_at,
                ],
                'token' => $token,
                'profile' => $profile,
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Registration failed',
                'error' =>  $e->getMessage()
            ], 500);
        }
    }

    // Login with phone number
    public function login(Request $request)
    {
        try {
            $request->validate([
                'phone_number' => 'required|string',
                'password' => 'required|string',
            ]);

            \Log::info('Login attempt', ['phone' => $request->phone_number]);

            // Attempt to find user by phone number
            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user) {
                \Log::warning('User not found', ['phone' => $request->phone_number]);
                throw ValidationException::withMessages([
                    'phone_number' => ['کاربری با این شماره یافت نشد'],
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                \Log::warning('Invalid password', ['user_id' => $user->id]);
                throw ValidationException::withMessages([
                    'phone_number' => ['شماره یا رمز وارد شده اشتباه است.'],
                ]);
            }

            \Log::info('User authenticated', ['user_id' => $user->id]);

            // Delete existing tokens (optional - for single device login)
            // $user->tokens()->delete();

            // Create new token
            $token = $user->createToken('auth_token')->plainTextToken;

            \Log::info('Token created', ['user_id' => $user->id]);

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone_number' => $user->phone_number,
                ],
                'token' => $token,
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'message' => 'Login failed',
                'error' =>  $e->getMessage()
            ], 500);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        try {
            \Log::info('Logout attempt', ['user_id' => $request->user()->id]);

            $request->user()->currentAccessToken()->delete();

            \Log::info('User logged out', ['user_id' => $request->user()->id]);

            return response()->json(['message' => 'Logged out successfully']);

        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Logout failed',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // Get authenticated user
    public function user(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ]);

        } catch (\Exception $e) {
            \Log::error('Get user error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to get user',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
