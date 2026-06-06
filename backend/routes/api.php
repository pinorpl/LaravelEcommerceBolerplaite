<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\Admin\AdminProductController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Rate limiter definitions ──────────────────────────────────────────────────
// [SEC] Prevent brute-force on auth endpoints
RateLimiter::for('auth', function ($request) {
    return Limit::perMinute(10)->by($request->ip());
});

// [SEC] General API rate limit
RateLimiter::for('api', function ($request) {
    return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
});

// ── Public endpoints ──────────────────────────────────────────────────────────
Route::middleware('throttle:auth')->post('/register', [AuthController::class, 'register']);

Route::middleware('throttle:api')->group(function () {
    Route::get('/products',        [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
});

// ── Authenticated endpoints ───────────────────────────────────────────────────
Route::middleware(['auth:api', 'throttle:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'me']);

    // ── Cart ──────────────────────────────────────────────────────────────────
    Route::prefix('cart')->group(function () {
        Route::get('/',               [CartController::class, 'index']);
        Route::post('/items',         [CartController::class, 'addItem']);
        Route::put('/items/{id}',     [CartController::class, 'updateItem']);
        Route::delete('/items/{id}',  [CartController::class, 'removeItem']);
        Route::post('/checkout',      [CartController::class, 'checkout']);
    });

    // ── Admin (role:admin enforced server-side) ───────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users',                  [AdminUserController::class, 'index']);
        Route::get('/products',               [AdminProductController::class, 'index']);
        Route::post('/products',              [AdminProductController::class, 'store']);
        Route::put('/products/{id}',          [AdminProductController::class, 'update']);
        Route::delete('/products/{id}',       [AdminProductController::class, 'destroy']);
    });
});
