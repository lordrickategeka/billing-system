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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('country', 2);
            $table->string('currency', 3)->default('UGX');
            $table->string('timezone')->default('UTC');
            $table->json('branding')->nullable(); // logo, colors, etc
            $table->json('tax_profile')->nullable(); // tax rates, numbers
            $table->json('settings')->nullable(); // feature flags, limits
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
