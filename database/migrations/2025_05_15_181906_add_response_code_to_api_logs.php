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
        if (Schema::hasTable('api_endpoint_logs')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('api_endpoint_logs', 'response_code')) {
                    $table->integer('response_code')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('api_endpoint_logs')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                if (Schema::hasColumn('api_endpoint_logs', 'response_code')) {
                    $table->dropColumn('response_code');
                }
            });
        }
    }
};
