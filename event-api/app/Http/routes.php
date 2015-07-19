<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

$app->get('/', function() use ($app) {
    return $app->welcome();
});

// Prefer to use route-based middleware!
$app->get('/track',
    ['middleware' => 'decode_data|validate_payload|validate_project',
    'App\Http\Controllers\EventController@event_new']);
//$app->get('/track','App\Http\Controllers\EventController@event_new');
$app->get('/pop','App\Http\Controllers\EventController@event_pump');
