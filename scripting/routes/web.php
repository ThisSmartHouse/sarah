<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ml/classify/{bucket}', [
    'as' => 'ml.classify.view',
    'uses' => '\App\Http\Controllers\MachineLearning\ClassifyBucketController@view'
])->where('bucket', '^([a-z]|(\d(?!\d{0,2}\.\d{1,3}\.\d{1,3}\.\d{1,3})))([a-z\d]|(\.(?!(\.|-)))|(-(?!\.))){1,61}[a-z\d\.]$');

Route::post('/ml/classify/{bucket}', [
    'as' => 'ml.classify.submit',
    'uses' => '\App\Http\Controllers\MachineLearning\ClassifyBucketController@submit'
])->where('bucket', '^([a-z]|(\d(?!\d{0,2}\.\d{1,3}\.\d{1,3}\.\d{1,3})))([a-z\d]|(\.(?!(\.|-)))|(-(?!\.))){1,61}[a-z\d\.]$');

Route::any('/oauth2/lyric', [
    'as' => 'oauth2.lyric',
    'uses' => '\App\Http\Controllers\Lyric\OAuth2Controller@authenticate'
]);

Route::any('/mperks', [
    'as' => 'mperks.keywords',
    'uses' => '\App\Http\Controllers\MPerks\KeywordsController@showForm'
]);

Route::post('/mperks/submit', [
    'as' => 'mperks.keywords.submit',
    'uses' => '\App\Http\Controllers\MPerks\KeywordsController@saveKeywords'
]);
