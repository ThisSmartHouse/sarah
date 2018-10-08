<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\PollSmappeeCommand::class,
        Commands\PushToMQTTCommand::class,
        Commands\CatBotSwitchCommand::class,
        Commands\VPNOnlineCheckCommand::class,
        Commands\PollPrinterStatus::class,
        Commands\KeyStoreCommand::class,
        Commands\CurrentPowerCostCommand::class,
        Commands\CameraSnapshotCommand::class,
        Commands\RecordToGoogleSheetsCommand::class,
        Commands\Lyric\RefreshCommand::class,
        Commands\ClipMPerksCommand::class,
        Commands\PollPetWhistleCommand::class,
        Commands\MonitorRewardJetCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $time = Carbon::now();
        
        // This is, for the moment, just to capture data for machine learning of our backyard
        // so let's put a time limit on it so it doesn't blow up S3 costs
        if(($time->month == 7) && ($time->year = 2017)) {
            $schedule->command('ha:camera-snapshot backyard backyard-camera')->everyMinute();
        }
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
