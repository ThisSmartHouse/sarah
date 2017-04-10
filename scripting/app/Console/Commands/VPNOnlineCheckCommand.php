<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VPNOnlineCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:vpn-online-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks to see if the VPN is active or not.';

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
        $ipAddress = file_get_contents('http://ipv4bot.whatismyipaddress.com/');
        
        $vpnCheckParams = [
            'ip' => $ipAddress,
            'showtype' => 4, // JSON
            'email' => 'john@coggeshall.org'
        ];
        
        $result = json_decode(file_get_contents('http://legacy.iphub.info/api.php?' . http_build_query($vpnCheckParams)), true);
        
        print $result['proxy'] ? "online" : "offline";
    }
}
