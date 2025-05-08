<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiEndpointCountsTable extends Migration
{
    public function up(): void
    {
        Schema::create('api_endpoint_counts', function (Blueprint $table) {
            $table->string('endpoint')->primary();
            $table->unsignedBigInteger('count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_endpoint_counts');
    }
}
