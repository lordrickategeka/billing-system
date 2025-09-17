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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('service_type', ['hotspot', 'broadband']);
            $table->enum('access_type', ['voucher', 'pppoe', 'ipoe']);
            $table->string('class')->nullable(); // bronze, silver, gold

            // Speed & Limits
            $table->unsignedBigInteger('speed_up_kbps')->nullable(); // Upload speed in kbps
            $table->unsignedBigInteger('speed_down_kbps')->nullable(); // Download speed in kbps
            $table->unsignedBigInteger('quota_mb')->nullable(); // Data quota in MB
            $table->unsignedInteger('session_timeout')->nullable(); // Session timeout in seconds
            $table->unsignedInteger('idle_timeout')->nullable(); // Idle timeout in seconds

            // FUP (Fair Usage Policy)
            $table->json('fup_rules')->nullable(); // FUP configuration
            $table->json('burst_config')->nullable(); // Burst allowances

            // Pricing
            $table->decimal('price', 10, 2);
            $table->enum('billing_cycle', ['hourly', 'daily', 'weekly', 'monthly', 'yearly', 'one_time']);
            $table->unsignedInteger('term_days')->nullable(); // Product validity period

            $table->boolean('is_active')->default(true);

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'service_type', 'is_active']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
