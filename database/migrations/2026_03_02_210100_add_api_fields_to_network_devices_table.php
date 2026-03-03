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
        Schema::table('network_devices', function (Blueprint $table) {
            $table->string('api_username')->nullable()->after('management');
            $table->string('api_password')->nullable()->after('api_username');
            $table->integer('api_port')->default(8728)->after('api_password');
            $table->boolean('api_ssl')->default(false)->after('api_port');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('network_devices', function (Blueprint $table) {
            $table->dropColumn(['api_username', 'api_password', 'api_port', 'api_ssl']);
        });
    }
};
