<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index()
    {
        
        $categoryStats = DB::select("
            SELECT 
                c.name as category_name,
                COUNT(p.id) as total_products,
                AVG(p.price) as average_price
            FROM products p
            JOIN categories c ON p.category_id = c.id
            GROUP BY c.id, c.name
            ORDER BY total_products DESC
        ");

        
        $totalProducts = DB::table('products')->count();
        $averagePrice = DB::table('products')->avg('price');
        
        $mostExpensiveProducts = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('products.title', 'products.price', 'categories.name as category_name')
            ->orderBy('products.price', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_products' => $totalProducts,
            'average_price' => round($averagePrice, 2),
            'category_stats' => $categoryStats,
            'most_expensive_products' => $mostExpensiveProducts
        ]);
    }
}