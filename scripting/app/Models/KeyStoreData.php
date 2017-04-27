<?php

namespace App\Models;

class KeyStoreData extends \Eloquent
{
    protected $table = "keystore";
    protected $primaryKey = 'key';
    public $incrementing = false;
    
    protected $dates = ['created_at', 'updated_at'];
}