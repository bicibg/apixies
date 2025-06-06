<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sandbox_tokens', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('quota');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sandbox_tokens', function (Blueprint $table) {
            $table->dropColumn('ip_address');
        });
    }
};
