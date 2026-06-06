<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Bunu eklemeyi unutma!

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Canlı sunucuda HTTPS'i zorunlu kıl
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
