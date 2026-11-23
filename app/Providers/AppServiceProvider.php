<?php

namespace App\Providers;

use App\Database\Connectors\NeonPostgresConnector;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('db.connector.pgsql', NeonPostgresConnector::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production') || isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        Gate::before(function (User $user, string $ability): ?bool {
            if (! str_starts_with($ability, 'admin.')) {
                return null;
            }

            if ($user->isAdmin() || $user->getAttribute('role') === 'admin') {
                return true;
            }

            if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
                return false;
            }

            return $user->roles()
                ->where('roles.is_active', true)
                ->whereHas('permissions', fn ($query) => $query
                    ->where('permissions.code', $ability)
                    ->where('permissions.is_active', true))
                ->exists();
        });

        try {
            if (Schema::hasTable('app_settings')) {
                View::composer('*', function ($view) {
                    $settings = AppSetting::pluck('setting_value', 'setting_key')->toArray();
                    $view->with('platformSettings', $settings);
                });
            }
        } catch (\Throwable $e) {
            // Silently pass during initial migration or CLI commands
        }
    }
}
