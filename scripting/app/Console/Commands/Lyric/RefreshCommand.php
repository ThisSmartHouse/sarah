<?php

namespace App\Console\Commands\Lyric;

use Illuminate\Console\Command;

class RefreshCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:lyric:refresh';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Data from Honeywell Lyric in the System';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $accessTokenData = \App\Models\KeyStoreData::get('lyric_access_token');
        
        if(!$accessTokenData instanceof \App\Models\KeyStoreData) {
            $this->info("Cannot refresh, no authorization! Visit http://home.coogle.org/oauth2/lyric to authorize.");
            return 1;
        }
        
        $accessTokenData = json_decode($accessTokenData->value, true);
        
        $accessToken = new \League\OAuth2\Client\Token\AccessToken($accessTokenData);
        
        $provider = app('Lyric\OAuth2\Provider');
        
        if($accessToken->hasExpired()) {
            $accessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $accessToken->getRefreshToken()
            ]);
            
            \App\Models\KeyStoreData::set('lyric_access_token', json_encode($accessToken->jsonSerialize()));
        }
        
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'https://api.honeywell.com/v2/locations',
            $accessToken
        );
        
        $client = new \GuzzleHttp\Client();
        
        $response = $client->send($request, [
            'timeout' => 5,
            'query' => [
                'apikey' => config('services.lyric.client_id')
            ]
        ]);
        
        $lyricData = json_decode($response->getBody()->__toString(), true);
        
        $payloads = [];
        
        foreach($lyricData[0]['devices'] as $device) {
            $payloads[$device['deviceID']] = $device;
        }
        
        $mqttClient = app('Mosquitto');
        
        $self = $this;
        
        $mqttClient->onConnect(function() use ($mqttClient, $payloads, $self) {
            
            foreach($payloads as $deviceID => $payload) {
                $topic = "/status/lyric/$deviceID";
                $mqttClient->publish($topic, json_encode($payload), 0, true);
                $self->info("Publishing Device to $topic");
            }
        });
            
        $mqttClient->connect(
            config('services.mqtt.host'),
            config('services.mqtt.port', 1883)
        );
        
        for ($i = 0; $i < 100; $i++) {
            $mqttClient->loop(1);
        }
        
        
    }
}
