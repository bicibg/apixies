<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveRedundantColumnsFromApiEndpointLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('api_endpoint_logs', function (Blueprint $table) {
            if (Schema::hasColumn('api_endpoint_logs', 'user_name')) {
                $table->dropColumn('user_name');
            }

            // If we want to remove other redundant columns
            if (Schema::hasColumn('api_endpoint_logs', 'is_sandbox') &&
                Schema::hasColumn('api_endpoint_logs', 'sandbox')) {
                $table->dropColumn('is_sandbox');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('api_endpoint_logs', function (Blueprint $table) {
            $table->string('user_name')->nullable();
            $table->boolean('is_sandbox')->default(false);
        });
    }
}
