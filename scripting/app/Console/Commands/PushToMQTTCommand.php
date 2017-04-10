<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PushToMQTTCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:push-to-mqtt {topic} {payload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push a Payload to a given topic in MQTT';

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
        $topic = $this->argument('topic');
        $payload = $this->argument('payload');
        
        $client = app('Mosquitto');
        
        $client->onConnect(function() use ($client, $topic, $payload) {
            $client->publish($topic, $payload);
        });
        
        $client->connect(
            config('services.mqtt.host'),
            config('services.mqtt.port', 1883)
        );
        
        for ($i = 0; $i < 100; $i++) {
            $client->loop(1);
        }
    }
}
