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
        Schema::table('api_endpoint_counts', function (Blueprint $table) {
            if (!Schema::hasColumn('api_endpoint_counts', 'is_sandbox')) {
                $table->boolean('is_sandbox')->default(false)->after('count');
            }

            // Check if index exists before adding
            $indexExists = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('api_endpoint_counts'))
                ->keys()
                ->contains('api_endpoint_counts_is_sandbox_index');

            if (!$indexExists) {
                $table->index('is_sandbox');
            }

            // Check if composite index exists
            $compositeIndexExists = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('api_endpoint_counts'))
                ->keys()
                ->contains('api_endpoint_counts_endpoint_is_sandbox_index');

            if (!$compositeIndexExists) {
                $table->index(['endpoint', 'is_sandbox']);
            }
        });

        // Add is_sandbox to api_endpoint_logs if it doesn't exist
        Schema::table('api_endpoint_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('api_endpoint_logs', 'is_sandbox')) {
                $table->boolean('is_sandbox')->default(false)->after('response_code');
            }

            // Check if index exists before adding
            $indexExists = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('api_endpoint_logs'))
                ->keys()
                ->contains('api_endpoint_logs_is_sandbox_index');

            if (!$indexExists) {
                $table->index('is_sandbox');
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
        // Remove is_sandbox from api_endpoint_counts if it exists
        if (Schema::hasColumn('api_endpoint_counts', 'is_sandbox')) {
            Schema::table('api_endpoint_counts', function (Blueprint $table) {
                // Check if index exists before dropping
                $indexExists = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('api_endpoint_counts'))
                    ->keys()
                    ->contains('api_endpoint_counts_is_sandbox_index');

                if ($indexExists) {
                    $table->dropIndex(['is_sandbox']);
                }

                // Check if composite index exists
                $compositeIndexExists = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('api_endpoint_counts'))
                    ->keys()
                    ->contains('api_endpoint_counts_endpoint_is_sandbox_index');

                if ($compositeIndexExists) {
                    $table->dropIndex(['endpoint', 'is_sandbox']);
                }

                $table->dropColumn('is_sandbox');
            });
        }

        // Remove is_sandbox from api_endpoint_logs if it exists
        if (Schema::hasColumn('api_endpoint_logs', 'is_sandbox')) {
            Schema::table('api_endpoint_logs', function (Blueprint $table) {
                // Check if index exists before dropping
                $indexExists = collect(Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('api_endpoint_logs'))
                    ->keys()
                    ->contains('api_endpoint_logs_is_sandbox_index');

                if ($indexExists) {
                    $table->dropIndex(['is_sandbox']);
                }

                $table->dropColumn('is_sandbox');
            });
        }
    }
}
