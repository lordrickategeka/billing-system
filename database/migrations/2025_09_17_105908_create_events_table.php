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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('event_type'); // coa_disconnect, speed_change, subscription_created, etc
            $table->string('entity_type'); // service, subscription, customer, etc
            $table->unsignedBigInteger('entity_id');

            $table->json('event_data'); // Flexible payload
            $table->json('metadata')->nullable(); // User, IP, source system, etc

            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('completed');
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'event_type']);
            $table->index(['entity_type', 'entity_id']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
