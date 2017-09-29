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
        
        $this->app->bind('Lyric\OAuth2\Provider', function($app) {
            $provider = new \App\Library\OAuth2\HoneywellProvider([
                'clientId' => config('services.lyric.client_id'),
                'clientSecret' => config('services.lyric.client_secret'),
                'redirectUri' => 'http://home.coogle.org/oauth2/lyric',
                'urlAuthorize' => 'https://api.honeywell.com/oauth2/authorize',
                'urlAccessToken' => 'https://api.honeywell.com/oauth2/token',
                'urlResourceOwnerDetails' => null
            ]);
            
            return $provider;
        });
        
    }
}
