<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Cek jika aplikasi diakses lewat Ngrok
        if (str_contains(request()->header('host'), 'ngrok-free')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            
            // Memaksa Laravel percaya pada header yang dikirim Ngrok
            request()->server->set('HTTPS', 'on');
        }
    }
}