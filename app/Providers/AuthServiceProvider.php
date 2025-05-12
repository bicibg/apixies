<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Policy mappings for your models.
     * You can leave this empty if you’re only defining Gates.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // e.g. \App\Models\Post::class => \App\Policies\PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // This method exists here because this class extends
        // Illuminate\Foundation\Support\Providers\AuthServiceProvider
        $this->registerPolicies();

        // Define the 'viewApiStats' Gate:
        Gate::define('viewApiStats', function (User $user): bool {
            // Only allow if your User model has an is_admin flag
            return $user->email === 'bugraergin@gmail.com';
        });
        Gate::define('viewFilament', fn ($user) => (bool) $user->is_admin);
    }
}
