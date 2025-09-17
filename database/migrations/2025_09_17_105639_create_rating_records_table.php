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
        Schema::create('rating_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();

            $table->string('billing_period'); // 2024-01, 2024-02, etc
            $table->date('period_start');
            $table->date('period_end');

            // Usage aggregations from radacct
            $table->unsignedBigInteger('total_input_octets')->default(0);
            $table->unsignedBigInteger('total_output_octets')->default(0);
            $table->unsignedBigInteger('total_session_time')->default(0);
            $table->unsignedInteger('session_count')->default(0);

            // Billing calculations
            $table->decimal('usage_charge', 10, 2)->default(0);
            $table->decimal('subscription_charge', 10, 2)->default(0);
            $table->decimal('total_charge', 10, 2)->default(0);

            $table->boolean('is_invoiced')->default(false);
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->unique(['service_id', 'billing_period']);
            $table->index(['tenant_id', 'billing_period', 'is_invoiced']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rating_records');
    }
};
