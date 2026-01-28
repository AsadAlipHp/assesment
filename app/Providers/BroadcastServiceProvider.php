<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Routes for broadcasting
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        // Load your channels
        require base_path('routes/channels.php');
    }
}
