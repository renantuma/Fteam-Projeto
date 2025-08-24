<?php
// app/Services/ProductSyncService.php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class ProductSyncService
{
    private FakeStoreApiService $apiService;

    public function __construct(FakeStoreApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function syncAll(): array
    {
        $stats = [
            'categories_processed' => 0,
            'categories_created' => 0,
            'categories_updated' => 0,
            'products_processed' => 0,
            'products_created' => 0,
            'products_updated' => 0,
            'errors' => []
        ];

        DB::beginTransaction();
        
        try {
            
            $this->syncCategories($stats);
            
           
            $this->syncProducts($stats);
            
            DB::commit();
            
            
            $this->invalidateCache();
            
            Log::info('Sincronização concluída com sucesso', $stats);
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro na sincronização', [
                'error' => $e->getMessage(),
                'stats' => $stats
            ]);
            throw $e;
        }

        return $stats;
    }

    private function syncCategories(array &$stats): void
    {
        try {
            $categories = $this->apiService->getCategories();
            
            foreach ($categories as $index => $categoryName) {
                try {
                    $stats['categories_processed']++;
                    
                    $category = Category::where('external_id', $categoryName)->first();
                    
                    if ($category) {
                        if ($category->name !== $categoryName) {
                            $category->update(['name' => $categoryName]);
                            $stats['categories_updated']++;
                        }
                    } else {
                        Category::create([
                            'name' => $categoryName,
                            'external_id' => $categoryName
                        ]);
                        $stats['categories_created']++;
                    }
                    
                } catch (Exception $e) {
                    $error = "Erro ao processar categoria '{$categoryName}': " . $e->getMessage();
                    $stats['errors'][] = $error;
                    Log::warning($error);
                }
            }
        } catch (Exception $e) {
            throw new Exception('Falha ao buscar categorias da API: ' . $e->getMessage());
        }
    }

    private function syncProducts(array &$stats): void
    {
        try {
            $products = $this->apiService->getAllProducts();
            
            foreach ($products as $productData) {
                try {
                    $stats['products_processed']++;
                    $this->syncSingleProduct($productData, $stats);
                    
                } catch (Exception $e) {
                    $error = "Erro ao processar produto ID {$productData['id']}: " . $e->getMessage();
                    $stats['errors'][] = $error;
                    Log::warning($error, ['product_data' => $productData]);
                }
            }
        } catch (Exception $e) {
            throw new Exception('Falha ao buscar produtos da API: ' . $e->getMessage());
        }
    }

    private function syncSingleProduct(array $productData, array &$stats): void
    {
       
        if (!isset($productData['id'], $productData['title'], $productData['price'], $productData['category'])) {
            throw new Exception('Dados obrigatórios ausentes no produto');
        }

        
        $category = Category::where('external_id', $productData['category'])->first();
        if (!$category) {
            throw new Exception("Categoria '{$productData['category']}' não encontrada");
        }

        
        $data = [
            'title' => $productData['title'],
            'price' => $productData['price'],
            'description' => $productData['description'] ?? null,
            'image' => $productData['image'] ?? null,
            'category_id' => $category->id,
            'rating_rate' => $productData['rating']['rate'] ?? null,
            'rating_count' => $productData['rating']['count'] ?? null,
        ];

        
        $product = Product::where('external_id', $productData['id'])->first();
        
        if ($product) {
            $product->update($data);
            $stats['products_updated']++;
        } else {
            Product::create(array_merge($data, ['external_id' => $productData['id']]));
            $stats['products_created']++;
        }
    }

    private function invalidateCache(): void
    {
        
        Cache::tags(['products', 'statistics'])->flush();
        
        Log::info('Cache invalidado após sincronização');
    }
}