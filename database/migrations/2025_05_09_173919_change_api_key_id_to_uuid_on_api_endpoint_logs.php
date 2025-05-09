<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeApiKeyIdToUuidOnApiEndpointLogs extends Migration
{
    public function up()
    {
        Schema::table('api_endpoint_logs', function (Blueprint $table) {
            // drop the old integer column
            $table->dropColumn('api_key_id');

            // add a new utf8 UUID column (36 chars)
            $table->char('api_key_id', 36)->nullable()->after('user_name');
        });
    }

    public function down()
    {
        Schema::table('api_endpoint_logs', function (Blueprint $table) {
            $table->dropColumn('api_key_id');
            $table->unsignedBigInteger('api_key_id')->nullable()->after('user_name');
        });
    }
}
