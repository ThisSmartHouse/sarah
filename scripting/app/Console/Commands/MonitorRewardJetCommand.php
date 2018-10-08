<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MonitorRewardJetCommand extends Command
{
    
    protected $deals = [
        '/deal/amazon-gift-card' => [
            'vendor' => 'amazon'
        ],
        '/deal/cvs-egift-card' => [
            'vendor' => 'cvs'
        ],
        '/deal/sephora-gift-cards' => [
            'vendor' => 'sephora'
        ],
        '/deal/lowes' => [
            'vendor' => 'lowes'
        ],
        '/deal/wholefoods-egift-card' => [
            'vendor' => 'wholefoods'
        ],
        '/deal/Target-eCards2018020612370420180208145042' => [
            'vendor' => 'target'
        ],
        '/deal/BBnB-gift-cards20180206123704' => [
            'vendor' => 'bedbathbeyond'
        ],
        '/deal/Gap-eCard' => [
            'vendor' => 'gap'
        ],
        '/deal/Petco' => [
            'vendor' => 'petco'
        ],
        '/deal/HomeDepot-gift-cards2018020612370420180206150744' => [
            'vendor' => 'homedepot'
        ]
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:pull-rewardjet {--dev}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically Monitor RewardJet';

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
        $client = new \GuzzleHttp\Client([
            'cookies' => true
        ]);
        
        $baseUrl = config('services.rewardjet.base_url');
        
        try {
            $response = $client->request('POST', "{$baseUrl}/login", [
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
                ],
                'form_params' => [
                    'LoginForm[email]' => config('services.rewardjet.username'),
                    'LoginForm[password]' => config('services.rewardjet.password')
                ]
            ]);
        } catch(\Throwable $e) {
            if($this->option('dev')) {
                throw $e;
            }
            return 1;
        }
        
        $loginResponse = json_decode($response->getBody()->__toString(), true);
        
        if(!is_array($loginResponse) || ($loginResponse['res'] != 'ok')) {
            return 1;
        }
        
        $mqttdeals = [];
        
        foreach($this->deals as $dealUrl => $dealData) {
            $dealUrl = "{$baseUrl}{$dealUrl}";
            
            try {
                $response = $client->request('GET', $dealUrl, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36'
                    ]
                ]);
            } catch(\Throwable $e) {
                if($this->option('dev')) {
                    $this->error($e->getMessage());
                }
                continue;
            }
            
            $qp = htmlqp($response->getBody()->__toString());
            
            $button = $qp->find('button.rjcart-add-item')->first();
            $attributes = $button->attr();

            $thisDeal = [
                'title' => $attributes['data-item-name'],
                'price' => $attributes['data-item-price'],
                'actual_value' => $attributes['data-item-user-price'],
                'discount_perc' => (1 - ($attributes['data-item-price'] / $attributes['data-item-user-price'])) * 100,
            ];
            
            $mqttdeals[$dealData['vendor']] = $thisDeal;
        }
       
       $mqttClient = app('Mosquitto');
       
       $self = $this;
       
       $mqttClient->onConnect(function() use ($mqttClient, $mqttdeals, $self) {
           
           foreach($mqttdeals as $vendor => $details) {
               $topic = "/status/rewardjet/$vendor";
               $mqttClient->publish($topic, json_encode($details), 0, true);
               $self->info("Publishing $vendor to $topic");
           }
       });
       
       $mqttClient->connect(
           config('services.mqtt.host'),
           config('services.mqtt.port', 1883)
       );
       
       for($i = 0; $i < 100; $i++) {
           $mqttClient->loop(1);
       }
       
    }
}
