<?php

//Paytm
Route::get('/paytm/index', 'PaytmController@index');
Route::post('/paytm/callback', 'PaytmController@callback')->name('paytm.callback');
Route::get('/paytm/update_transaction_status','PaytmController@updateTransactionStatus')->name('paytm.update-transaction-status');

//Admin
Route::group(['prefix' =>'admin', 'middleware' => ['auth', 'admin']], function(){
    Route::get('/paytm_configuration', 'PaytmController@credentials_index')->name('paytm.index');
    Route::post('/paytm_configuration_update', 'PaytmController@update_credentials')->name('paytm.update_credentials');
});
