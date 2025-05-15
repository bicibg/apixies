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
        if (Schema::hasTable('api_endpoint_logs') && !Schema::hasColumn('api_endpoint_logs', 'sandbox_token_id')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                $table->string('sandbox_token_id')->nullable()->after('is_sandbox');
                $table->index('sandbox_token_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('api_endpoint_logs') && Schema::hasColumn('api_endpoint_logs', 'sandbox_token_id')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                $table->dropColumn('sandbox_token_id');
            });
        }
    }
};
