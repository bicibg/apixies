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
        Schema::create('sandbox_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 100)->unique();
            $table->integer('calls')->default(0);
            $table->integer('quota')->default(100);
            $table->integer('expires_in')->default(1800);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sandbox_tokens');
    }
};
