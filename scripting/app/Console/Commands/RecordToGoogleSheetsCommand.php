<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RecordToGoogleSheetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:record-to-google-sheets {spreadsheetId} {data*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record data to a Google Spreadsheet';

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
        $spreadsheetId = $this->argument('spreadsheetId');
        $sheetData = $this->argument('data');
        
        $sheetData = array_merge([\Carbon\Carbon::now()->format('n/j/Y H:i')], $sheetData);
        
        \Sheets::setService(\Google::make('sheets'));
        \Sheets::spreadsheet($spreadsheetId);
        \Sheets::sheet('A1')->range('')->append([$sheetData]);
        
    }
}
