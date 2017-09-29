<?php

namespace App\Models;

class KeyStoreData extends \Eloquent
{
    protected $table = "keystore";
    protected $primaryKey = 'key';
    public $incrementing = false;
    
    protected $dates = ['created_at', 'updated_at'];
    
    static public function set($key, $value)
    {
        $keyObj = static::where('key', '=', $key)->first();
        
        if(!$keyObj instanceof static) {
            $keyObj = new static();
            $keyObj->key = $key;
        }
        
        $keyObj->value = $value;
        
        $keyObj->save();
        
        return $keyObj;
    }
    
    static public function get($key)
    {
        $keyObj = static::where('key', '=', $key)->first();
        
        if(!$keyObj instanceof static) {
            return null;
        }
        
        return $keyObj;
    }
    
}