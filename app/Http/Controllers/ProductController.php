<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Validar parâmetros de entrada
        $validator = Validator::make($request->all(), [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'category' => 'sometimes|string|exists:categories,slug',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
            'search' => 'sometimes|string|min:2|max:100',
            'sort' => ['sometimes', 'string', Rule::in(['id', 'title', 'price', 'created_at', 'rating'])],
            'order' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Parâmetros inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        
        $cacheKey = 'products:index:' . md5(serialize($request->all()));
        $cacheTtl = config('cache.ttl.products', 600);

        
        if (Cache::has($cacheKey)) {
            $products = Cache::get($cacheKey);
            $cached = true;
            Log::debug('Cache HIT', ['key' => $cacheKey]);
        } else {
            Log::debug('Cache MISS', ['key' => $cacheKey]);
            
            
            $query = Product::with('category');

            
            if ($request->has('category') && $request->category) {
                $query->whereHas('category', function($q) use ($request) {
                    $q->where('slug', $request->category);
                });
            }

            
            if ($request->has('min_price') && $request->min_price) {
                $query->where('price', '>=', (float) $request->min_price);
            }

            
            if ($request->has('max_price') && $request->max_price) {
                $query->where('price', '<=', (float) $request->max_price);
            }

            
            if ($request->has('search') && $request->search) {
                $searchTerm = '%' . $request->search . '%';
                $query->where('title', 'like', $searchTerm);
            }

           
            $sort = $request->get('sort', 'id');
            $order = $request->get('order', 'asc');
            
            
            if ($sort === 'rating') {
                $query->orderByRaw("CAST(JSON_EXTRACT(rating, '$.rate') AS DECIMAL(3,1)) $order");
            } else {
                $query->orderBy($sort, $order);
            }

            
            $perPage = $request->get('per_page', 20);
            $products = $query->paginate($perPage);
            
            
            Cache::put($cacheKey, $products, $cacheTtl);
            $cached = false;
        }

        
        $response = $products->toArray();
        $response['cache_info'] = [
            'cached' => $cached,
            'ttl' => $cacheTtl,
            'key' => $cacheKey,
            'timestamp' => now()->toISOString()
        ];

        return response()->json($response);
    }

    public function show($id): JsonResponse
    {
        $cacheKey = "products:show:{$id}";
        $cacheTtl = config('cache.ttl.products', 600);

        
        if (Cache::has($cacheKey)) {
            $product = Cache::get($cacheKey);
            $cached = true;
            Log::debug('Cache HIT', ['key' => $cacheKey]);
        } else {
            Log::debug('Cache MISS', ['key' => $cacheKey]);
            $product = Product::with('category')->findOrFail($id);
            Cache::put($cacheKey, $product, $cacheTtl);
            $cached = false;
        }

        return response()->json([
            'data' => $product,
            'cache_info' => [
                'cached' => $cached,
                'ttl' => $cacheTtl,
                'key' => $cacheKey,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    public function byCategory($categorySlug, Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|min:0',
            'search' => 'sometimes|string|min:2|max:100',
            'sort' => ['sometimes', 'string', Rule::in(['id', 'title', 'price', 'created_at', 'rating'])],
            'order' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Parâmetros inválidos',
                'details' => $validator->errors()
            ], 422);
        }

        $cacheKey = "products:by_category:{$categorySlug}:" . md5(serialize($request->all()));
        $cacheTtl = config('cache.ttl.products', 600);

        
        if (Cache::has($cacheKey)) {
            $products = Cache::get($cacheKey);
            $cached = true;
            Log::debug('Cache HIT', ['key' => $cacheKey]);
        } else {
            Log::debug('Cache MISS', ['key' => $cacheKey]);
            
            $query = Product::whereHas('category', function($query) use ($categorySlug) {
                $query->where('slug', $categorySlug);
            })->with('category');

            
            if ($request->has('min_price') && $request->min_price) {
                $query->where('price', '>=', (float) $request->min_price);
            }
            if ($request->has('max_price') && $request->max_price) {
                $query->where('price', '<=', (float) $request->max_price);
            }

            
            if ($request->has('search') && $request->search) {
                $searchTerm = '%' . $request->search . '%';
                $query->where('title', 'like', $searchTerm);
            }

            
            $sort = $request->get('sort', 'id');
            $order = $request->get('order', 'asc');
            
            if ($sort === 'rating') {
                $query->orderByRaw("CAST(JSON_EXTRACT(rating, '$.rate') AS DECIMAL(3,1)) $order");
            } else {
                $query->orderBy($sort, $order);
            }

            $perPage = $request->get('per_page', 20);
            $products = $query->paginate($perPage);
            
            Cache::put($cacheKey, $products, $cacheTtl);
            $cached = false;
        }

        
        $response = $products->toArray();
        $response['cache_info'] = [
            'cached' => $cached,
            'ttl' => $cacheTtl,
            'key' => $cacheKey,
            'timestamp' => now()->toISOString()
        ];

        return response()->json($response);
    }

    
    public function filterOptions(): JsonResponse
    {
        $cacheKey = 'products:filter_options';
        $cacheTtl = config('cache.ttl.statistics', 300);

        
        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            $cached = true;
            Log::debug('Cache HIT', ['key' => $cacheKey]);
        } else {
            Log::debug('Cache MISS', ['key' => $cacheKey]);
            
            $categories = Category::select('slug', 'name')->get();
            
            $priceRange = Product::selectRaw('MIN(price) as min_price, MAX(price) as max_price')
                ->first();

            $data = [
                'categories' => $categories,
                'price_range' => [
                    'min' => (float) $priceRange->min_price,
                    'max' => (float) $priceRange->max_price
                ],
                'sort_options' => [
                    'id', 'title', 'price', 'created_at', 'rating'
                ]
            ];
            
            Cache::put($cacheKey, $data, $cacheTtl);
            $cached = false;
        }

        return response()->json([
            'data' => $data,
            'cache_info' => [
                'cached' => $cached,
                'ttl' => $cacheTtl,
                'key' => $cacheKey,
                'timestamp' => now()->toISOString()
            ]
        ]);
    }

    
public static function clearCache(): void
{
    try {
        $driver = config('cache.default');
        Log::debug('Limpando cache de produtos', ['driver' => $driver]);
        
        
        self::clearCacheUniversal();
        
        Log::info('Cache de produtos limpo com sucesso', ['driver' => $driver]);
        
    } catch (\Exception $e) {
        Log::error('Erro ao limpar cache de produtos: ' . $e->getMessage());
    }
}

private static function clearCacheUniversal(): void
{
    try {
        $driver = config('cache.default');
        
        if ($driver === 'redis') {
            
            $redis = Cache::getRedis();
            
            
            $patterns = [
                'products:*',
                config('cache.prefix') . 'products:*'
            ];
            
            foreach ($patterns as $pattern) {
                $iterator = null;
                do {
                    $keys = $redis->scan($iterator, $pattern, 100);
                    if (!empty($keys)) {
                        $redis->del($keys);
                        Log::debug('Chaves Redis removidas', ['count' => count($keys), 'pattern' => $pattern]);
                    }
                } while ($iterator > 0);
            }
            
        } else {
            
            $keysToForget = [
                'products:filter_options',
                'products:index:*',
                'products:by_category:*',
                'products:show:*'
            ];
            
            foreach ($keysToForget as $keyPattern) {
                if ($driver === 'file') {
                    self::clearFileCacheByPattern($keyPattern);
                } else {
                    
                    Cache::forget('products:filter_options');
                }
            }
            
            
            if ($driver !== 'redis') {
                Cache::flush();
                Log::debug('Cache flush completo executado', ['driver' => $driver]);
            }
        }
        
    } catch (\Exception $e) {
        Log::error('Erro no clearCacheUniversal: ' . $e->getMessage());
        
        Cache::flush();
    }
}

    private static function clearFileCacheByPattern(string $pattern): void
    {
        try {
            $cachePath = storage_path('framework/cache/data');
            if (file_exists($cachePath)) {
                $files = glob($cachePath . '/' . $pattern);
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                        Log::debug('Arquivo de cache removido', ['file' => $file]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao limpar file cache: ' . $e->getMessage());
        }
    }
}