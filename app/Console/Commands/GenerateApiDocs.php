<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class GenerateApiDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'openapi:generate {--force : Force regeneration even in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate OpenAPI documentation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Generating OpenAPI documentation...');

        if (app()->environment('production') && !$this->option('force')) {
            $this->warn('In production environment, use --force flag to generate documentation');
            return CommandAlias::FAILURE;
        }

        $this->call('l5-swagger:generate');

        $this->info('OpenAPI documentation generated successfully! View at: /api/documentation');

        return CommandAlias::SUCCESS;
    }
}
