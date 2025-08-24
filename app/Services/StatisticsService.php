<?php
// app/Services/StatisticsService.php

namespace App\Services;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatisticsService
{
    public function getStatistics(): array
    {
        return Cache::tags(['statistics'])->remember('statistics_general', 3600, function () {
            return [
                'total_products' => $this->getTotalProducts(),
                'total_by_category' => $this->getTotalByCategory(),
                'average_price' => $this->getAveragePrice(),
                'top_5_expensive' => $this->getTop5Expensive()
            ];
        });
    }

    private function getTotalProducts(): int
    {
        return Product::count();
    }

    private function getTotalByCategory(): array
    {
        
        $results = DB::select("
            SELECT 
                c.name as category_name,
                COUNT(p.id) as total_products
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id, c.name
            ORDER BY total_products DESC
        ");

        return collect($results)->map(function ($item) {
            return [
                'category' => $item->category_name,
                'total' => (int) $item->total_products
            ];
        })->toArray();
    }

    private function getAveragePrice(): float
    {
        
        $result = DB::select("
            SELECT AVG(price) as average_price 
            FROM products
        ");

        return round((float) $result[0]->average_price, 2);
    }

    private function getTop5Expensive(): array
    {
        return Product::with('category')
            ->orderBy('price', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'title' => $product->title,
                    'price' => $product->price,
                    'category' => $product->category->name ?? 'Sem categoria'
                ];
            })
            ->toArray();
    }
}