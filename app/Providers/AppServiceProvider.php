<?php

namespace App\Providers;

use \Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::macro('description', function (string $description) {
            $this->action['description'] = isset($this->action['description']) ?
                $this->action['description'].$description :
                $description;
            return $this;
        });

        Route::macro('requiredParams', function (array $params) {
            $this->action['required_params'] = $params;
            return $this;
        });

    }

}
