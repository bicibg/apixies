<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sitemap:generate', function () {
    $this->comment('Generating sitemap...');

    $controller = new SitemapController();
    $controller->generateFromConsole();

    $this->info('Sitemap generated successfully!');
})->purpose('Generate the website sitemap.xml file');

// Schedule the sitemap generation to run daily at midnight
Schedule::command('sitemap:generate')
    ->daily()
    ->at('00:00')
    ->onOneServer();
