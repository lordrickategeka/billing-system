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
        Schema::create('policies', function (Blueprint $table) {
           $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();

            // RADIUS Attributes
            $table->json('radius_attributes'); // All RADIUS reply attributes

            // Network Configuration
            $table->string('qos_profile')->nullable();
            $table->unsignedInteger('vlan_id')->nullable();
            $table->string('address_pool')->nullable();
            $table->string('dns_servers')->nullable();

            // Advanced Features
            $table->json('firewall_rules')->nullable();
            $table->json('traffic_shaping')->nullable();
            $table->string('walled_garden_urls')->nullable(); // For dunning

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
