<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class PollPrinterStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:poll-printer {host}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Poll an Octoprint server by host';

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
        $octoprint = $this->argument('host');
        
        $endpoint = "http://{$octoprint}/api/job";
        
        $httpClient = new Client();
        
        try {
            $result = $httpClient->request('GET', $endpoint, [
                'headers' => [
                    'X-Api-Key' => config('services.octoprint.api_key')
                ]
            ]);
            
            print (string)$result->getBody();
        } catch(\Exception $e) {
            print "{}";
        }
        
        return;
    }
}
