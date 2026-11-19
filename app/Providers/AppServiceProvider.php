<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use App\View\Composers\AdminNotificationComposer;
use App\Models\Penggalang_Dana;
use App\Policies\PenggalangDanaPolicy;

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
        Gate::policy(Penggalang_Dana::class, PenggalangDanaPolicy::class);

        if (app()->environment('production', 'staging')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.admin', AdminNotificationComposer::class);

            app()->setLocale('id');

    }
}

