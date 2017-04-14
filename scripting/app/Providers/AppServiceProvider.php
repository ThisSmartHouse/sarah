<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Coogle\SmappeeLocal;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Smappee', function($app) {
            $client = new SmappeeLocal(config('services.smappee.host'), config('services.smappee.local_password'));
            return $client;
        });
        
        $this->app->bind('Mosquitto', function($app) {
            $client = new \Mosquitto\Client();
            return $client;
        });
    }
}
