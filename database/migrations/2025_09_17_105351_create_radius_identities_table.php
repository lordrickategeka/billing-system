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
        Schema::create('radius_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();

            // Universal identity fields
            $table->string('username')->unique();
            $table->string('password')->nullable();
            $table->string('token')->nullable(); // For token-based auth

            // Hotspot specific
            $table->string('mac_address')->nullable();
            $table->boolean('mac_binding')->default(false);

            // ISP specific
            $table->string('circuit_id')->nullable();
            $table->string('ont_serial')->nullable();
            $table->string('static_ip')->nullable();
            $table->string('address_pool')->nullable();

            $table->enum('auth_type', ['voucher', 'pppoe', 'ipoe', 'mac']);
            $table->enum('status', ['active', 'suspended', 'expired'])->default('active');

            $table->timestamp('last_auth_at')->nullable();
            $table->json('radius_attributes')->nullable(); // Cached attributes
            $table->timestamps();

            $table->index(['tenant_id', 'auth_type', 'status']);
            $table->index(['username', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radius_identities');
    }
};
