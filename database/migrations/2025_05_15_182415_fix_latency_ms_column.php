<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('api_endpoint_logs')) {
            try {
                DB::statement('UPDATE api_endpoint_logs SET latency_ms = 0 WHERE latency_ms IS NULL');
            } catch (\Exception $e) {
                // Log the error but continue
                logger()->error('Failed to update latency_ms values: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is for fixing data, so down is a no-op
    }
};
