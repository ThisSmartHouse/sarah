<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KeyStoreData;

class KeyStoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:keystore {action} {key} {value?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get / Put a value in the Keystore';

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
            $action = strtolower($this->argument('action'));
            $key = strtolower($this->argument('key'));
            
            $dataObj = KeyStoreData::find($key);
            
            if(!$dataObj instanceof KeyStoreData) {
                $dataObj = new KeyStoreData();
            }
            
            switch($action) {
                case 'get':
                    print $dataObj->value;
                    return 0;
                case 'put':
                    $dataObj->value = $this->argument('value');
                    $dataObj->key = $key;
                    $dataObj->save();
                    return 0;
            }
            
            $this->error("Invalid Action");
            return 1;
        } catch(\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }
}
