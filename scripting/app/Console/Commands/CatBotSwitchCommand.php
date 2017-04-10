<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CatBotSwitchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:catbot-switch {state?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Control the CatBot On/Off State';

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
        $state = $this->argument('state', null);
        
        $uri = "http://" . config('services.catbot.host') ."/";
        
        switch(strtolower($state)) {
            case 'on':
                $status = file_get_contents($uri . 'power/on');
                return;
            case 'off':
                $status = file_get_contents($uri . 'power/off');
                return;
        }
        
    }
}
