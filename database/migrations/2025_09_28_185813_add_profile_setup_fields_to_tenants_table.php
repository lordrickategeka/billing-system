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
        Schema::table('tenants', function (Blueprint $table) {
            // Update status column to include new statuses
            $table->string('status')->default('pending_setup')->change();

            // Add index for better performance
            $table->index(['status']);

            // Add profile completion timestamp
            $table->timestamp('profile_completed_at')->nullable();

            // Add setup skipped flag
            $table->boolean('setup_skipped')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['profile_completed_at', 'setup_skipped']);
        });
    }
};
