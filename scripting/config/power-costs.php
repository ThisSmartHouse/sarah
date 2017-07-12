<?php

return [
    'entity_id' => 'sensor.home_power_consumption',
    'billing_cycle_start_day' => 13,
    'billing_cycle_end_day' => 12,
    'rates' => [
        'peak-based' => [
            [
                'start_month' => 6,
                'end_month' => 10,
                'peak_start_hour' => 11,
                'peak_end_hour' => 19,
                'peak_rate' => 0.1339,      // KWh
                'offpeak_rate' => 0.04283,   // KWh
                'distribution_rate' => 0.074628 
            ],
            [
                'start_month' => 11,
                'end_month' => 5,
                'peak_start_hour' => 11,
                'peak_end_hour' => 19,
                'peak_rate' => 0.11263,     // KWh
                'offpeak_rate' => 0.0411,     // KWh
                'distribution_rate' => 0.074628 // KWh
            ]
        ],
        'range-based' => [
            'allotment' => 17, // KWh per day
            'base_price' => 0.086608, // Base price per KWh
            'overage' => 0.102248, // overage cost per KWh
            'distribution_rate' => 0.05497
        ]
    ]
];