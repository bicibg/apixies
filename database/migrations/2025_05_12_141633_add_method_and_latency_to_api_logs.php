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
        Schema::table('api_endpoint_logs', function (Blueprint $table) {
            $table->string('method', 8)->after('endpoint');
            $table->unsignedInteger('latency_ms')->after('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_endpoint_logs', function (Blueprint $table) {
            //
        });
    }
};
