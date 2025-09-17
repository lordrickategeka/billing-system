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
        Schema::create('network_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('nas_ip_address');
            $table->string('secret');
            $table->string('vendor')->nullable(); // mikrotik, cisco, etc
            $table->enum('type', ['access_point', 'bng', 'olt', 'switch', 'router']);

            $table->string('site_name')->nullable();
            $table->json('location')->nullable(); // lat, lng, address
            $table->json('management')->nullable(); // SSH, SNMP, API endpoints
            $table->json('capabilities')->nullable(); // CoA, DM, features

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['nas_ip_address']);
            $table->index(['tenant_id', 'type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_devices');
    }
};
