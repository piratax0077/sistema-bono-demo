<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Las vistas del demo usan Bootstrap 5. Sin esta configuración Laravel
        // genera el paginador Tailwind, cuyas clases no existen en estas pages.
        Paginator::useBootstrapFive();
    }
}
