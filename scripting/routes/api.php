<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'aws'
], function() {
    
    Route::group([
        'prefix' => 'ses'
    ], function() {
        
        Route::post('process-incoming', [
            'as' => 'api.aws.ses.process-incoming',
            'uses' => 'Api\Aws\SesController@processIncoming'
        ]);
        
    });
    
});

#Route::middleware('auth:api')->get('/user', function (Request $request) {
#    return $request->user();
#});
