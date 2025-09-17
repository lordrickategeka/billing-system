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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('service_number')->unique();
            $table->enum('service_type', ['hotspot', 'broadband']);

            // Service Location/Installation
            $table->json('installation_address')->nullable();
            $table->string('circuit_id')->nullable(); // For ISP services
            $table->string('ont_serial')->nullable(); // For fiber services
            $table->string('static_ip')->nullable();

            $table->enum('status', ['pending', 'active', 'suspended', 'terminated'])->default('pending');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id', 'status']);
            $table->index(['tenant_id', 'service_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
