<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\ProfileSkillsController;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\WorksController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {

    // Public profile routes
    Route::get('profiles', [ProfilesController::class, 'index']);
    Route::get('profiles/{id}', [ProfilesController::class, 'show']);

    // Public skills routes
    Route::get('skills', [SkillsController::class, 'index']);

    // Public profile skills routes
    Route::get('profiles/{profileId}/skills', [ProfileSkillsController::class, 'index']);

    // Public courses routes
    Route::get('profiles/{profileId}/courses', [CoursesController::class, 'index']);
    Route::get('profiles/{profileId}/courses/{courseId}', [CoursesController::class, 'show']);

    // Public works/portfolio routes
    Route::get('profiles/{profileId}/works', [WorksController::class, 'index']);
    Route::get('profiles/{profileId}/works/{workId}', [WorksController::class, 'show']);

    // Public reviews routes
    Route::get('profiles/{profileId}/reviews', [ReviewsController::class, 'index']);
    Route::get('profiles/{profileId}/reviews/{reviewId}', [ReviewsController::class, 'show']);
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Authenticated user's profile
    Route::get('/me/profile', [ProfilesController::class, 'me']);

    // Profile management
    Route::post('/profiles', [ProfilesController::class, 'store']);
    Route::put('/profiles/{id}', [ProfilesController::class, 'update']);
    Route::delete('profiles/{id}', [ProfilesController::class, 'destroy']);
    Route::patch('profiles/{id}/toggle-status', [ProfilesController::class, 'toggleStatus']);

    // Skills management (admin only - add middleware as needed)
    Route::post('/skills', [SkillsController::class, 'store']);

    // Profile skills management
    Route::post('/profiles/{profileId}/skills', [ProfileSkillsController::class, 'store']);
    Route::put('/profiles/{profileId}/skills/{skillId}', [ProfileSkillsController::class, 'update']);
    Route::delete('/profiles/{profileId}/skills/{skillId}', [ProfileSkillsController::class, 'destroy']);

    // Courses management
    Route::post('profiles/{profileId}/courses', [CoursesController::class, 'store']);
    Route::put('profiles/{profileId}/courses/{courseId}', [CoursesController::class, 'update']);
    Route::delete('profiles/{profileId}/courses/{courseId}', [CoursesController::class, 'destroy']);

    // Works/Portfolio management
    Route::post('profiles/{profileId}/works', [WorksController::class, 'store']);
    Route::put('profiles/{profileId}/works/{workId}', [WorksController::class, 'update']);
    Route::delete('profiles/{profileId}/works/{workId}', [WorksController::class, 'destroy']);
    Route::patch('profiles/{profileId}/works/{workId}/toggle-featured', [WorksController::class, 'toggleFeatured']);

    // Reviews management
    Route::post('profiles/{profileId}/reviews', [ReviewsController::class, 'store']);
    Route::put('profiles/{profileId}/reviews/{reviewId}', [ReviewsController::class, 'update']);
    Route::delete('profiles/{profileId}/reviews/{reviewId}', [ReviewsController::class, 'destroy']);
    Route::get('me/reviews', [ReviewsController::class, 'myReviews']);
});
