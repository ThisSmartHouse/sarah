<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;

class CameraSnapshotCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:camera-snapshot {camera} {filesystem}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Do a Camera Snap-shot and store it in a filesystem';

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
        $cameraId = $this->argument('camera');
        $filesystem = $this->argument('filesystem');
        
        $cameraMeta = config("cameras.$cameraId", null);
        $filesystemMeta = config("filesystems.disks.$filesystem", null);
        
        if(is_null($cameraMeta)) {
            $this->error("Failed to locate configuration for camera '$cameraId'");
            return 1;
        }
        
        if(is_null($filesystemMeta)) {
            $this->error("Failed to locate filesystem '$filesystem'");
            return 1;
        }
        
        if(empty($cameraMeta['still_feed_url'])) {
            $this->error("No still feed URL specified for camera, cannot store snapshot");
            return 1;
        }
        
        $stillFeedUrl = $cameraMeta['still_feed_url'];
        
        $httpClient = new Client();
        $httpOptions = [];
        
        if(!empty($cameraMeta['user'])) {
            $httpOptions['auth'] = [
                $cameraMeta['user'],
                $cameraMeta['password']
            ];
        }
        
        $this->info("Downloading Snapshot...");
        $response = $httpClient->get($stillFeedUrl, $httpOptions);

        if($response->getStatusCode() != 200) {
            $this->error("Failed to retrieve snapshot: {$response->getReasonPhrase()}");
            return 1;
        }
        
        $this->info("Snapshot downloaded...");
        $imageData = $response->getBody()->__toString();
        
        $filename = $cameraId . "_" . date('m-d-Y_hia') . ".jpg";
        
        $this->info("Storing Snapshot in filesystem as '$filename'");
        
        \Storage::disk($filesystem)->put($filename, $imageData);
        
        $this->info("Complete!");
    }
}
