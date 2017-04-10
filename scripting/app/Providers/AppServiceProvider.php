<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Smappee', function($app) {
            $client = new \Coogle\SmappeeLocal(config('services.smappee.host'), config('services.smappee.local_password'));
            return $client;
        });
        
        $this->app->bind('Mosquitto', function($app) {
            $client = new \Mosquitto\Client();
            return $client;
        });
    }
}
