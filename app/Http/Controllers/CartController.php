<?php

namespace App\Http\Controllers;

use DB;
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
        $cartData = CartData::getCartData($request->cookie('cart'));
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => $cartData
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = json_decode($request->cookie('cart'),true);
        if(@$cart)
        {
            foreach($cart as $key => $value) {
                if (in_array($request['productId'], $value)) {
                    unset($cart[$key]);
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => CartData::getCartData($cart)
        ])->cookie('cart', json_encode($cart), time() + (86400 * 30));
    }

    public function addToCart(Request $request)
    {
        $cart = array();
        if(!$request->cookie('cart')) {
            $cart = array();
            $cart[$request['productId']] = array(
                'productId' => intval($request['productId']),
                'quantity' => intval($request['quantity'])
            );
        }
        else 
        {
            $cart = json_decode($request->cookie('cart'), true);
            $cart[$request['productId']] = array(
                'productId' => intval($request['productId']),
                'quantity' => intval($request['quantity'])
            );
        }
    
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => CartData::getCartData($cart)
        ])->cookie('cart', json_encode($cart), time() + (86400 * 30));
    }
}
