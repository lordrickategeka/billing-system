<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hp_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // basic, premium, daily
            $table->string('display_name'); // Quick Browse, Premium Access
            $table->integer('amount'); // Price in UGX
            $table->string('duration'); // 1h, 8h, 24h
            $table->string('speed_limit'); // 2M/2M, 5M/5M
            $table->string('mikrotik_profile'); // Basic, Premium, Daily
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hp_plans');
    }
};
