<?php

namespace App\Models\HomeAssistant;

class States extends \Eloquent
{
    protected $connection = 'homeassistant';
    protected $table = 'states';
    protected $primaryKey = 'state_id';
    protected $dates = ['created', 'last_changed', 'last_updated'];
    public $timestamps = false;
    
}