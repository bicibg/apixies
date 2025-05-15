<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixApiEndpointNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:fix-endpoint-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix endpoint names in api_endpoint_counts and api_endpoint_logs tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Fixing API endpoint names...');

        // Fix api/v1/test to test in api_endpoint_counts
        $countRows = DB::table('api_endpoint_counts')
            ->where('endpoint', 'api/v1/test')
            ->update(['endpoint' => 'test']);

        $this->info("Updated {$countRows} records in api_endpoint_counts.");

        // Fix api/v1/test to test in api_endpoint_logs
        $logRows = DB::table('api_endpoint_logs')
            ->where('endpoint', 'api/v1/test')
            ->update(['endpoint' => 'test']);

        $this->info("Updated {$logRows} records in api_endpoint_logs.");

        // Fix any other routes that might have dots instead of slashes
        $otherEndpoints = DB::table('api_endpoint_counts')
            ->where('endpoint', 'like', 'api.%')
            ->pluck('endpoint')
            ->unique()
            ->toArray();

        if (!empty($otherEndpoints)) {
            $this->info('Found additional endpoints to fix:');

            foreach ($otherEndpoints as $endpoint) {
                $fixedEndpoint = str_replace('.', '/', $endpoint);

                // Update in api_endpoint_counts
                $countUpdated = DB::table('api_endpoint_counts')
                    ->where('endpoint', $endpoint)
                    ->update(['endpoint' => $fixedEndpoint]);

                // Update in api_endpoint_logs
                $logsUpdated = DB::table('api_endpoint_logs')
                    ->where('endpoint', $endpoint)
                    ->update(['endpoint' => $fixedEndpoint]);

                $this->info("Fixed '{$endpoint}' to '{$fixedEndpoint}': {$countUpdated} count records, {$logsUpdated} log records.");
            }
        }

        $this->info('Endpoint names fixed successfully!');
        return 0;
    }
}
