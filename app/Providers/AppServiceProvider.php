<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Broadcast;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Broadcast::routes(['middleware' => ['auth:sanctum']]);

    // Load channels
    if (file_exists(base_path('routes/channels.php'))) {
        require base_path('routes/channels.php');
    }
    }
}
