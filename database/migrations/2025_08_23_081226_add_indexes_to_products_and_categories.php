<?php
// database/migrations/2025_08_23_xxxxxx_add_indexes_to_products_and_categories.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::table('products', function (Blueprint $table) {
            
            if (!$this->indexExists('products', 'products_external_id_unique')) {
                $table->unique('external_id');
            }
            
            if (!$this->indexExists('products', 'products_category_id_index')) {
                $table->index('category_id');
            }
            
            if (!$this->indexExists('products', 'products_price_index')) {
                $table->index('price');
            }
            
            if (!$this->indexExists('products', 'products_title_index')) {
                $table->index('title');
            }
            
            if (!$this->indexExists('products', 'products_created_at_index')) {
                $table->index('created_at');
            }
        });

       
        Schema::table('categories', function (Blueprint $table) {
            if (!$this->indexExists('categories', 'categories_name_index')) {
                $table->index('name');
            }
            
            if (!$this->indexExists('categories', 'categories_slug_index')) {
                $table->index('slug');
            }
        });
    }

    public function down(): void
    {
        
        Schema::table('products', function (Blueprint $table) {
            if ($this->indexExists('products', 'products_external_id_unique')) {
                $table->dropUnique(['external_id']);
            }
            
            if ($this->indexExists('products', 'products_category_id_index')) {
                $table->dropIndex(['category_id']);
            }
            
            if ($this->indexExists('products', 'products_price_index')) {
                $table->dropIndex(['price']);
            }
            
            if ($this->indexExists('products', 'products_title_index')) {
                $table->dropIndex(['title']);
            }
            
            if ($this->indexExists('products', 'products_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            if ($this->indexExists('categories', 'categories_name_index')) {
                $table->dropIndex(['name']);
            }
            
            if ($this->indexExists('categories', 'categories_slug_index')) {
                $table->dropIndex(['slug']);
            }
        });
    }

   
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = DB::getDatabaseName();
        $result = DB::select("
            SELECT COUNT(*) as count
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$connection, $table, $indexName]);

        return $result[0]->count > 0;
    }
};