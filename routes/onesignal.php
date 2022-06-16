<?php


/*
|--------------------------------------------------------------------------
| Onesignal Routes
|--------------------------------------------------------------------------
|
| Here is where you can register notification through onesignal routes for your application.
|
*/

Route::get('/test',function(){
    dd("this is for one signal");
})->middleware('auth:api');

Route::post('/install/player-id','OnesignalController@getPlayerIds');
Route::post('/onesignal/send-notification','OnesignalController@sendNotification');
