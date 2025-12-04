<?php

use App\Http\Controllers\PreregistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::post("/api/create-preregistration", [PreregistrationController::class, "create"])->name('preregistration.create');
Route::get("/api/list", [PreregistrationController::class, "list"])->name('preregistration.list');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
