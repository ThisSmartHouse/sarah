<?php

namespace App\Http\Controllers\Api\Aws;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SesController extends Controller
{
    public function processIncoming(Request $request)
    {
        $data = $request->all();

        \Log::info("NEW USPS EMAIL");
        \Log::info(var_export($data, true));
        
        var_dump($request->all()); 
    }
}