<?php
// database/migrations/2024_01_01_000002_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('external_id')->unique(); 
            $table->string('title');
            $table->decimal('price', 10, 2);
            $table->text('description');
            $table->string('image');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->json('rating')->nullable(); 
            $table->timestamps();
            
            
            $table->index(['external_id']);
            $table->index(['category_id']);
            $table->index(['price']);
            $table->index(['title']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};