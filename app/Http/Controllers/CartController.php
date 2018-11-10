<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Product;
use App\Traits\CartData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Validator;
use Response;

class CartController extends Controller
{
    public function getCart(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => CartData::getCartData()
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = json_decode(Cookie::get('cart'),true);
        foreach($cart as $key => $value) {
            if (in_array($request['productId'], $value)) {
                unset($cart[$key]);
            }
        }
        Cookie::make('cart', json_encode($cart), time() + (86400 * 30));
    
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => CartData::getCartData($cart)
        ]);
    }

    public function addToCart(Request $request)
    {
        $cart = array();
        if(!Cookie::get('cart')) {
            $cart = array();
            $cart[$request['productId']] = array(
                'productId' => intval($request['productId']),
                'quantity' => intval($request['quantity'])
            );
        }
        else 
        {
            $cart = json_decode(Cookie::get('cart'), true);
            $cart[$request['productId']] = array(
                'productId' => intval($request['productId']),
                'quantity' => intval($request['quantity'])
            );
        }
        Cookie::queue('cart', json_encode($cart), time() + (86400 * 30));
    
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => CartData::getCartData($cart)
        ]);
    }
}
