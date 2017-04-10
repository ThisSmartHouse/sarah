<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PollSmappeeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:poll-smappee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Return current power usage from Smappee';

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
        try {
            
            $client = app('Smappee');
            $instant = $client->getInstantaneous();
            
            print round($instant['phase2ActivePower'] / 1000, 0, PHP_ROUND_HALF_UP);
            
        } catch(\Exception $e) {
            \Log::error("Exception Polling Smappee: {$e->getMessage()}", ['exception' => $e]);
            print '-';
        }
        
    }
}
