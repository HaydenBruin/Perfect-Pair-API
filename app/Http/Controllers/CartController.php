<?php

namespace App\Http\Controllers;

use App\Cart;
use Illuminate\Http\Request;
use Validator;
use Response;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'guestId' => 'required',
            'productId' => 'required',
            'quantity' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'status_code' => 400,
                'message' => 'Validation failed, please check your input'
            ]);
        }
        else
        {
            $cart = new Cart();
            $cart->guestId = $request['guestId'];
            $cart->productId = $request['productId'];
            $cart->quantity = $request['quantity'];
            $cart->save();
    
            return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Product has been added to cart'
            ]);
        }
    }
}
