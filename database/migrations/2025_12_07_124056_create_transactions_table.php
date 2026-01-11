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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 15, 2); // Positive for income, negative for expenses
            $table->foreignId('currency_id')->constrained();
            $table->enum('transaction_type', ['income', 'expense', 'transfer', 'loan_given', 'loan_received']);
            $table->foreignId('silo_id')->constrained(); // Source account
            $table->foreignId('destination_silo_id')->nullable()->constrained('silos'); // For transfers
            $table->foreignId('category_id')->nullable()->constrained();
            $table->datetime('transaction_date');
            $table->string('receipt_path')->nullable();
            $table->foreignId('installment_plan_id')->nullable()->constrained();
            $table->unsignedBigInteger('recurring_transaction_id')->nullable();
            $table->foreignId('loan_id')->nullable()->constrained();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->decimal('converted_amount', 15, 2)->nullable();
            $table->foreignId('converted_currency_id')->nullable()->constrained('currencies');
            $table->timestamps();

            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'transaction_type']);
            $table->index(['user_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
