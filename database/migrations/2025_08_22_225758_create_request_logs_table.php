<?php
// database/migrations/2024_01_01_000003_create_request_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->string('route');
            $table->string('method');
            $table->integer('status_code');
            $table->integer('response_time_ms');
            $table->json('request_data')->nullable();
            $table->timestamps();
            
            $table->index(['client_id']);
            $table->index(['route']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_logs');
    }
};