<?php
// app/Services/FakeStoreApiService.php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

class FakeStoreApiService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('services.fakestore.url', 'https://fakestoreapi.com');
        $this->timeout = config('services.fakestore.timeout', 30);
    }

    public function syncProducts(): array
    {
        try {
            Log::info('Iniciando sincronização de produtos com FakeStore API');

            
            $externalProducts = $this->getAllProducts();
            
            $syncedCount = 0;
            $updatedCount = 0;
            $createdCount = 0;

            foreach ($externalProducts as $externalProduct) {
                
                $category = Category::firstOrCreate(
                    ['name' => $externalProduct['category']],
                    [
                        'slug' => Str::slug($externalProduct['category']),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                
                $productData = [
                    'title' => $externalProduct['title'],
                    'price' => $externalProduct['price'],
                    'description' => $externalProduct['description'],
                    'image' => $externalProduct['image'],
                    'category_id' => $category->id,
                    'rating' => json_encode($externalProduct['rating']),
                    'external_id' => $externalProduct['id'],
                    'updated_at' => now()
                ];

                
                $existingProduct = Product::where('external_id', $externalProduct['id'])->first();

                if ($existingProduct) {
                    
                    $existingProduct->update($productData);
                    $updatedCount++;
                } else {
                    
                    $productData['created_at'] = now();
                    Product::create($productData);
                    $createdCount++;
                }

                $syncedCount++;
            }

            Log::info('Sincronização concluída', [
                'total_processados' => $syncedCount,
                'criados' => $createdCount,
                'atualizados' => $updatedCount
            ]);

            return [
                'success' => true,
                'message' => "Sincronização concluída com sucesso. {$syncedCount} produtos processados ({$createdCount} novos, {$updatedCount} atualizados).",
                'data' => [
                    'total' => $syncedCount,
                    'created' => $createdCount,
                    'updated' => $updatedCount
                ]
            ];

        } catch (Exception $e) {
            Log::error('Erro durante a sincronização de produtos', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Falha na sincronização: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

   
    public function getAllProducts(): array
    {
        return $this->makeRequest('/products');
    }

    public function getProduct(int $id): ?array
    {
        try {
            return $this->makeRequest("/products/{$id}");
        } catch (Exception $e) {
            Log::warning('Produto não encontrado na API externa', [
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function getCategories(): array
    {
        return $this->makeRequest('/products/categories');
    }

    private function makeRequest(string $endpoint): array
    {
        $maxRetries = 3;
        $baseDelay = 1; 

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = Http::timeout($this->timeout)
                    ->retry(1, 0)
                    ->get($this->baseUrl . $endpoint);

                if ($response->successful()) {
                    return $response->json();
                }

                $statusCode = $response->status();
                $body = $response->body();
                
                Log::warning('Resposta de erro da API externa', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'status_code' => $statusCode,
                    'response_body' => $body
                ]);

                if ($statusCode >= 400 && $statusCode < 500) {
                    throw new Exception("Erro 4xx da API externa: {$statusCode}");
                }

                if ($attempt === $maxRetries) {
                    throw new Exception("Erro 5xx da API externa após {$maxRetries} tentativas: {$statusCode}");
                }

            } catch (Exception $e) {
                Log::error('Erro na requisição para API externa', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt === $maxRetries) {
                    throw $e;
                }

                $delay = $baseDelay * pow(2, $attempt - 1);
                sleep($delay);
            }
        }

        throw new Exception('Falha inesperada na requisição');
    }
}