<?php
// app/Http/Controllers/SyncController.php

namespace App\Http\Controllers;

use App\Services\FakeStoreApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class SyncController extends Controller
{
    public function __construct(
        private FakeStoreApiService $fakeStoreApiService
    ) {}

    public function sync(Request $request): JsonResponse
    {
        try {
            Log::info('Iniciando sincronização e invalidação de cache');
            
            $result = $this->fakeStoreApiService->syncProducts();
            
            
            Log::debug('Invalidando cache de estatísticas');
            \App\Http\Controllers\StatisticsController::clearCache();
            
            Log::debug('Invalidando cache de produtos');
            \App\Http\Controllers\ProductController::clearCache();
            
            
            $cacheCheck = !Cache::has('products:index:') && !Cache::has('statistics:dashboard');
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'cache_cleared' => $cacheCheck,
                'cache_info' => [
                    'products_cleared' => !Cache::has('products:index:'),
                    'stats_cleared' => !Cache::has('statistics:dashboard'),
                    'timestamp' => now()->toISOString()
                ],
                'synced_at' => now()->toISOString()
            ], $result['success'] ? 200 : 500);
            
        } catch (\Exception $e) {
            Log::error('Erro durante a sincronização: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro durante a sincronização: ' . $e->getMessage(),
                'synced_at' => now()->toISOString()
            ], 500);
        }
    }
}