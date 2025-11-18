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
            $table->string('type')->nullable()->after('slug'); // 'isp' or 'hotspot'
            $table->string('email')->nullable()->after('type');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['type', 'email', 'phone', 'address']);
        });
    }
};
