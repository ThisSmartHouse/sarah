<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\HomeAssistant\States;

class CurrentPowerCostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ha:current-power-cost 
                               {mode=peak : The Mode, either "peak" calculation or "standard"}
                               {--month= : The billing cycle start-month to use for the calculation (default current month)}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Return current power cost for billing period';

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
        
        $startMonth = $this->option('month');
        
        if(is_null($startMonth)) {
            $startMonth = Carbon::now()->month;
        }
        
        switch(strtolower($this->argument('mode'))) {
            case 'peak':
                
                $result = $this->calculatePeakBasedCosts($startMonth);
                
                $retval = [
                    'cost' => number_format(round($result['totalCost'], 2), 2),
                    'total_kWh' => $result['totalKWh']
                ];
                break;
            case 'standard':
                
                $result = $this->calculateStandardCosts($startMonth);
                
                $retval = [
                    'cost' => number_format(round($result['totalCost'], 2), 2),
                    'total_kWh' => $result['totalKWh'],
                    'average_kWh_per_day' => $result['avgKWhPerDay']
                ];
                break;
        }
        
        print json_encode($retval, JSON_PRETTY_PRINT);
    }
    
    protected function calculateStandardCosts($startMonth)
    {
        $cycleStart = config('power-costs.billing_cycle_start_day');
        $cycleEnd = config('power-costs.billing_cycle_end_day');
    
        $entity_id = config('power-costs.entity_id');
    
        $cycleStartDate = Carbon::createFromDate(null, $startMonth, $cycleStart);
        $cycleEndDate = clone $cycleStartDate;
        $cycleEndDate->addMonth()->day = $cycleEnd;
    
        // $this->info("Processing {$cycleStartDate->format('r')} to {$cycleEndDate->format('r')}");
    
        $readings = States::where('entity_id', $entity_id)
                          ->whereBetween('created', [$cycleStartDate, $cycleEndDate])
                          ->orderBy('created')
                          ->get();
    
        //$this->info("Retrieved a total of {$readings->count()} readings for current billing cycle");
    
        $rates = config('power-costs.rates.range-based');
        
        $totalKWh = 0;
        $totalCost = 0;
        $readingData = [];
        
        foreach($readings as $reading) {
            
            if(!isset($readingData[$reading->created->format('Y-m-d')])) {
                $readingData[$reading->created->format('Y-m-d')] = [];
            } 
            
            if(!isset($readingData[$reading->created->format('Y-m-d')][$reading->created->format('G')])) {
                $readingData[$reading->created->format('Y-m-d')][$reading->created->format('G')] = [
                    $reading->state
                ];
            } else {
                $readingData[$reading->created->format('Y-m-d')][$reading->created->format('G')][] = $reading->state;
            }
            
        }

        foreach($readingData as $date => $dateData) {
            
            foreach($dateData as $hour => $readings) {
                
                $temp = 0;
                
                foreach($readings as $reading) {
                    $temp += $reading;
                }
                
                $readingData[$date][$hour] = ($temp / count($readings));
            }
            
        }
        
        foreach($readingData as $date => $hours) {
            
            $totalDailyKWh = 0;
            
            foreach($hours as $hourValue) {
                $totalDailyKWh += $hourValue;
            }
            
            $totalDailyKWh = $totalDailyKWh / 1000;
            
            if($totalDailyKWh <= $rates['allotment']) {
                $totalDailyCost = round($totalDailyKWh * $rates['base_price'], 2);
            } else {
                $totalStandardCost = round($rates['base_price'] * $rates['allotment'], 2);
                $totalOverageCost = round(($totalDailyKWh - $rates['allotment']) * $rates['overage'], 2);
                $totalDailyCost = $totalStandardCost + $totalOverageCost;
            }
            
            $totalKWh += $totalDailyKWh;
            $totalCost += $totalDailyCost;
        }
        
        $totalCost += $totalKWh * $rates['distribution_rate'];
        
        $avgKWhPerDay = round($totalKWh / $cycleEndDate->lastOfMonth()->day, 2);
        
        return compact('totalCost', 'totalKWh', 'avgKWhPerDay');
    }
    
    
    protected function calculatePeakBasedCosts($startMonth)
    {
        $cycleStart = config('power-costs.billing_cycle_start_day');
        $cycleEnd = config('power-costs.billing_cycle_end_day');
        
        $entity_id = config('power-costs.entity_id');
        
        $cycleStartDate = Carbon::createFromDate(null, $startMonth, $cycleStart);
        $cycleEndDate = clone $cycleStartDate;
        $cycleEndDate->addMonth()->day = $cycleEnd;
        
        // $this->info("Processing {$cycleStartDate->format('r')} to {$cycleEndDate->format('r')}");
        
        $readings = States::where('entity_id', $entity_id)
                          ->whereBetween('created', [$cycleStartDate, $cycleEndDate])
                          ->orderBy('created')
                          ->get();
        
        //$this->info("Retrieved a total of {$readings->count()} readings for current billing cycle");
        
        $rates = config('power-costs.rates.peak-based');
        
        $currentHour = null;
        $totalHourWattage = 0;
        $totalReadingsInHour = 0;
        $averageWattage = 0;
        $totalKWh = 0;
        
        $totalCost = 0;
        
        foreach($readings as $reading) {
        
            if(is_null($currentHour)) {
                $currentHour = $reading->created->hour;
            }
        
            if($currentHour != $reading->created->hour) {
        
                if($totalReadingsInHour == 0) {
                    $this->warn("Failed to find any readings for current hour. Something bad happened");
                    return 1;
                }
        
                $averageWattage = $totalHourWattage / $totalReadingsInHour;
                $currentRate = null;
        
                foreach($rates as $rate) {
        
                    $rateStart = Carbon::createFromDate(null, $rate['start_month'], 1)->subYear();
                    $rateEnd = clone $rateStart;
                    $rateEnd->addYear();
                    $rateEnd->month = $rate['end_month'];
                    $rateEnd = $rateEnd->lastOfMonth();
        
                    if($reading->created->between($rateStart, $rateEnd)) {
                        $currentRate = $rate;
                        break;
                    }
        
                    $rateStart = Carbon::createFromDate(null, $rate['start_month'], 1);
                    $rateEnd = clone $rateStart;
                    $rateEnd->month = $rate['end_month'];
                    $rateEnd = $rateEnd->lastOfMonth();
        
                    if($reading->created->between($rateStart, $rateEnd)) {
                        $currentRate = $rate;
                        break;
                    }
                }
        
                if(is_null($currentRate)) {
                    $this->warn("Failed to identify current rate for reading");
                    continue;
                }
        
                $isPeak = ($currentHour >= $currentRate['peak_start_hour']) &&
                ($currentHour < $currentRate['peak_end_hour']);
        
                $rate = $isPeak ? $currentRate['peak_rate'] : $currentRate['offpeak_rate'];
                $distributionRate = $currentRate['distribution_rate'];
        
                $kWh = $averageWattage / 1000;
        
                $totalKWh += $kWh;
                $hourlyCost = ($kWh * $rate);
        
                //$this->info("Calculated a cost for the hour {$currentHour} to be $hourlyCost (based on $totalReadingsInHour readings, $averageWattage W, rate: $rate)");
        
                $totalCost += $hourlyCost;
        
                $averageWattage = 0;
                $currentHour = $reading->created->hour;
                $totalHourWattage = $reading->state;
                $totalReadingsInHour = 1;
        
            } else {
                $totalHourWattage += $reading->state;
                $totalReadingsInHour++;
            }
        
        }
        
        $totalCost += $distributionRate * $totalKWh;
        
        return compact('totalCost', 'totalKWh');
    }
}
