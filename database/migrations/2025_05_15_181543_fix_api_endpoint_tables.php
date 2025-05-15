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
        // Fix the api_endpoint_counts table to allow the same endpoint with different sandbox flags
        if (Schema::hasTable('api_endpoint_counts')) {
            // Step 1: Create a backup of the current data
            $currentData = DB::table('api_endpoint_counts')->get();

            // Step 2: Drop the existing table
            Schema::dropIfExists('api_endpoint_counts');

            // Step 3: Recreate the table with a composite primary key
            Schema::create('api_endpoint_counts', function (Blueprint $table) {
                $table->string('endpoint');
                $table->boolean('is_sandbox')->default(false);
                $table->integer('count')->default(0);
                $table->timestamps();

                // Create a composite primary key
                $table->primary(['endpoint', 'is_sandbox']);
            });

            // Step 4: Restore the data
            foreach ($currentData as $record) {
                // Only insert if record has all required fields
                if (isset($record->endpoint) && isset($record->count)) {
                    // Default is_sandbox to false if it doesn't exist
                    $isSandbox = property_exists($record, 'is_sandbox') ? $record->is_sandbox : false;

                    DB::table('api_endpoint_counts')->insert([
                        'endpoint' => $record->endpoint,
                        'is_sandbox' => $isSandbox,
                        'count' => $record->count,
                        'created_at' => $record->created_at ?? now(),
                        'updated_at' => $record->updated_at ?? now(),
                    ]);
                }
            }
        }

        // Fix the api_endpoint_logs table to add the missing request_params column
        if (Schema::hasTable('api_endpoint_logs')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('api_endpoint_logs', 'request_params')) {
                    $table->text('request_params')->nullable();
                }

                if (!Schema::hasColumn('api_endpoint_logs', 'is_sandbox')) {
                    $table->boolean('is_sandbox')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is fixing data issues, so down doesn't make sense
        // but we can provide a path back to a standard schema
        if (Schema::hasTable('api_endpoint_counts')) {
            // In down, we could recreate without the composite key, but that would lose data distinction
            // This is just a placeholder
            Schema::table('api_endpoint_counts', function (Blueprint $table) {
                // No action needed
            });
        }

        if (Schema::hasTable('api_endpoint_logs')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                if (Schema::hasColumn('api_endpoint_logs', 'request_params')) {
                    $table->dropColumn('request_params');
                }
            });
        }
    }
};
