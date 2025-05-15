<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the sitemap generation to run daily at midnight
Schedule::command('sitemap:generate')
    ->daily()
    ->at('00:00')
    ->onOneServer();
