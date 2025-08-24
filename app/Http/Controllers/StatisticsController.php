<?php
// app/Http/Controllers/StatisticsController.php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use Illuminate\Support\Facades\Log;


class StatisticsController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $cacheKey = 'statistics:dashboard';
        $cacheTtl = config('cache.ttl.statistics', 300); // 5 minutos

        $data = Cache::remember($cacheKey, $cacheTtl, function () {
            
            $averagePrice = DB::selectOne('
                SELECT ROUND(AVG(price), 2) as average_price 
                FROM products
            ')->average_price;

            
            $totalProducts = Product::count();

            
            $productsByCategory = DB::select('
                SELECT c.name, COUNT(p.id) as total_products
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                GROUP BY c.id, c.name
                ORDER BY total_products DESC
            ');

           
            $top5MostExpensive = Product::with('category')
                ->orderBy('price', 'desc')
                ->take(5)
                ->get(['id', 'title', 'price', 'category_id']);

            return [
                'total_products' => $totalProducts,
                'average_price' => (float) $averagePrice,
                'products_by_category' => $productsByCategory,
                'top_5_most_expensive' => $top5MostExpensive,
                'generated_at' => now()->toISOString(),
                'cached' => false
            ];
        });

        
        if (!isset($data['cached'])) {
            $data['cached'] = true;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'cache_info' => [
                'cached' => $data['cached'],
                'ttl' => $cacheTtl
            ]
        ]);
    }

    
    public function advancedStats(): JsonResponse
    {
        $cacheKey = 'statistics:advanced';
        $cacheTtl = config('cache.ttl.statistics', 300);

        $data = Cache::remember($cacheKey, $cacheTtl, function () {
            
            $ratingStats = DB::selectOne('
                SELECT 
                    ROUND(AVG(CAST(JSON_EXTRACT(rating, "$.rate") AS DECIMAL(3,1))), 2) as avg_rating,
                    ROUND(MAX(CAST(JSON_EXTRACT(rating, "$.rate") AS DECIMAL(3,1))), 2) as max_rating,
                    ROUND(MIN(CAST(JSON_EXTRACT(rating, "$.rate") AS DECIMAL(3,1))), 2) as min_rating,
                    SUM(CAST(JSON_EXTRACT(rating, "$.count") AS UNSIGNED)) as total_reviews
                FROM products
            ');

            
            $priceRanges = DB::select('
                SELECT 
                    CASE 
                        WHEN price <= 50 THEN "0-50"
                        WHEN price <= 100 THEN "51-100" 
                        WHEN price <= 200 THEN "101-200"
                        ELSE "201+"
                    END as price_range,
                    COUNT(*) as product_count
                FROM products
                GROUP BY price_range
                ORDER BY MIN(price)
            ');

            return [
                'rating_statistics' => $ratingStats,
                'price_distribution' => $priceRanges,
                'generated_at' => now()->toISOString(),
                'cached' => false
            ];
        });

        if (!isset($data['cached'])) {
            $data['cached'] = true;
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'cache_info' => [
                'cached' => $data['cached'],
                'ttl' => $cacheTtl
            ]
        ]);
    }

    
public static function clearCache(): void
{
    try {
        Log::debug('Limpando cache de estatísticas');
        
        $keys = [
            'statistics:dashboard',
            'statistics:advanced',
            config('cache.prefix') . 'statistics:dashboard',
            config('cache.prefix') . 'statistics:advanced'
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Log::info('Cache de estatísticas limpo com sucesso');
        
    } catch (\Exception $e) {
        Log::error('Erro ao limpar cache de estatísticas: ' . $e->getMessage());
        
        Cache::flush();
    }
}
}