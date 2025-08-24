<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SyncController;
use App\Http\Controllers\StatisticsController;


Route::get('/test', function () {
    return response()->json([
        'message' => 'API funcionando!',
        'laravel_version' => app()->version(),
        'timestamp' => now()
    ]);
});


Route::middleware(['integration'])->group(function () {
    Route::get('/test-middleware', function () {
        return response()->json([
            'message' => 'Middleware funcionando!',
            'client_id' => request()->header('X-Client-Id')
        ]);
    });
});


Route::get('/debug', function () {
    return response()->json([
        'routes_loaded' => true,
        'middleware_available' => class_exists(\App\Http\Middleware\IntegrationMiddleware::class),
        'laravel_version' => app()->version(),
        'environment' => app()->environment()
    ]);
});


Route::middleware(['integration'])->post('/integracoes/fakestore/sync', [SyncController::class, 'sync']);
Route::middleware(['integration'])->get('/integracoes/fakestore/sync', function () {
    return response()->json([
        'success' => true,
        'message' => 'Status da sincronização',
        'method' => request()->method(),
        'client_id' => request()->header('X-Client-Id'),
        'timestamp' => now()
    ]);
});


Route::middleware(['integration'])->get('/products', [ProductController::class, 'index']);
Route::middleware(['integration'])->get('/products/filter-options', [ProductController::class, 'filterOptions']);
Route::middleware(['integration'])->get('/products/{id}', [ProductController::class, 'show']);


Route::middleware(['integration'])->get('/categories', [CategoryController::class, 'index']);
Route::middleware(['integration'])->get('/categories/{id}', [CategoryController::class, 'show']);
Route::middleware(['integration'])->get('/categories/{slug}/products', [CategoryController::class, 'byCategory']);


Route::middleware(['integration'])->get('/statistics/dashboard', [StatisticsController::class, 'dashboard']);
Route::middleware(['integration'])->get('/statistics/advanced', [StatisticsController::class, 'advancedStats']);


Route::middleware(['integration'])->get('/products-by-category/{category}', [ProductController::class, 'byCategorySlug']);