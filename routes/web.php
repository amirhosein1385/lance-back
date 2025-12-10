    <?php

    use App\Http\Controllers\PreregistrationController;
    use Illuminate\Support\Facades\Route;
    use Inertia\Inertia;
    use Laravel\Fortify\Features;
    use App\Http\Controllers\AuthController;

    Route::get('/', function () {
        return Inertia::render('welcome', [
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    })->name('home');
    Route::get('/api/test', function () {
        return response()->json([
            'message' => 'API is working',
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'sanctum' => config('sanctum'),
            'session' => [
                'domain' => config('session.domain'),
                'secure' => config('session.secure'),
            ],
        ]);
    });

    Route::get('/test-auth', function () {
        return response()->json([
            'auth' => auth()->check(),
            'user' => auth()->user(),
        ]);
    })->middleware('auth:sanctum');
    Route::post("/api/create-preregistration", [PreregistrationController::class, "create"])->name('preregistration.create');
    Route::get("/api/list", [PreregistrationController::class, "list"])->name('preregistration.list');
    Route::post('/api/register', [AuthController::class, 'register']);
    Route::post('/api/login', [AuthController::class, 'login']);
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('dashboard', function () {
            return Inertia::render('dashboard');
        })->name('dashboard');
    });

    require __DIR__.'/settings.php';
