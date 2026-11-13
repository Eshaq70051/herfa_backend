<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

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
