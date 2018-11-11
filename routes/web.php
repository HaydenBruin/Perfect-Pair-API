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



Route::get('/mailable', function () {
    

    //Mail::to(explode(',', env('MAIL_ADMIN')))
    //->queue(new App\Mail\OrderCompleted());

    return new App\Mail\OrderCompleted();
});