<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class PollPetWhistleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:poll-whistle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll Whistle for Data about the Pets';

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
        $httpClient = new Client();
        
        $pets = $httpClient->request('GET', 'https://app.whistle.com/api/pets', [
            'headers' => [
                'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjozMTUzNDEsImV4cCI6MTUzMzIzNzk3Mn0._VQVSgR31Mtpd3Y-Pd099K-bUxm-k0VXABYJB8njgXM',
                'Accept' => 'application/vnd.whistle.com.v4+json',
                'User-Agent' => 'Winston/2.4.0 (iPhone; iOS 11.1.1; Build:1066; Scale/2.0)'
            ]
        ]);

        $results = json_decode((string)$pets->getBody(), true);
        
        $retval = [];
        
        foreach($results['pets'] as $pet)  {
            
            $retval[$pet['id']] = [
                'name' => $pet['name'],
                'photo' => $pet['profile_photo_url_sizes']['750x750'],
                'battery_level' => $pet['device']['battery_level'],
                'latitude' => $pet['last_location']['latitude'],
                'longitude' => $pet['last_location']['longitude'],
                'location_timestamp' => $pet['last_location']['timestamp']
            ];
        }
        
        
        $mqttClient = app('Mosquitto');
        
        $self = $this;
        
        $mqttClient->onConnect(function() use ($mqttClient, $retval) {
            
            foreach($retval as $deviceID => $payload) {
                $topic = "/status/pets/$deviceID";
                $mqttClient->publish($topic, json_encode($payload), 0, true);
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
