<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSandboxToApiEndpointTablesAndFixNames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add is_sandbox to api_endpoint_counts if it doesn't exist
        if (!Schema::hasColumn('api_endpoint_counts', 'is_sandbox')) {
            Schema::table('api_endpoint_counts', function (Blueprint $table) {
                $table->boolean('is_sandbox')->default(false)->after('count');
                $table->index('is_sandbox');
                $table->index(['endpoint', 'is_sandbox']);
            });
        }

        // Add is_sandbox to api_endpoint_logs if it doesn't exist
        if (!Schema::hasColumn('api_endpoint_logs', 'is_sandbox')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                $table->boolean('is_sandbox')->default(false)->after('response_code');
                $table->index('is_sandbox');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove is_sandbox from api_endpoint_counts if it exists
        if (Schema::hasColumn('api_endpoint_counts', 'is_sandbox')) {
            Schema::table('api_endpoint_counts', function (Blueprint $table) {
                $table->dropIndex(['is_sandbox']);
                $table->dropIndex(['endpoint', 'is_sandbox']);
                $table->dropColumn('is_sandbox');
            });
        }

        // Remove is_sandbox from api_endpoint_logs if it exists
        if (Schema::hasColumn('api_endpoint_logs', 'is_sandbox')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                $table->dropIndex(['is_sandbox']);
                $table->dropColumn('is_sandbox');
            });
        }
    }
}
