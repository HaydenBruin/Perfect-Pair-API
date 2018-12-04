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


use App\Checkout;

Route::get('/mailable', function () {
    

    //Mail::to(explode(',', env('MAIL_ADMIN')))
    //->queue(new App\Mail\OrderCompleted());
    $checkoutId = 28;
    $email_address = "bruinhayden@gmail.com";
    $orderCompleted = new \App\Mail\OrderCompleted($checkoutId);

    return Mail::to($email_address)
    ->bcc(explode(',', env('MAIL_ADMIN')))
    ->send($orderCompleted);
});