<?php

namespace App\Providers;

use App\Policies\ActivityPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use App\Policies\RolePolicy;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);

        // Verifica se a requisição veio do domínio público
        if (isset($_SERVER['HTTP_HOST']) && 
            ($_SERVER['HTTP_HOST'] === 'sisvtec.garanhuns.ifpe.edu.br' || 
             str_ends_with($_SERVER['HTTP_HOST'], 'garanhuns.ifpe.edu.br'))) {
            URL::forceScheme('https');
        }
    }
}