<?php
Route::group(['namespace' => 'K3Net\NavInvoice\Http\Controllers', 'middleware' => ['web']], function(){
  Route::post('/navinvoice/send', 'NavInvoiceController@send');
  Route::get('/navinvoice/sho', 'NavInvoiceController@sho');
});