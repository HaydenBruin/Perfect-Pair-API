<?php

use Illuminate\Http\Request;
Use App\Product;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// PRODUCTS
Route::get('products', 'ProductController@getProducts');
Route::get('products/{product}', 'ProductController@getProduct');

// GUEST 
Route::post('guest', 'GuestController@store');

// CART
Route::get('cart', 'CartController@getCart');
Route::post('cart/add', 'CartController@addToCart');
Route::post('cart/remove', 'CartController@removeFromCart');