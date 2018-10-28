<?php

namespace App\Http\Controllers;

use App\Checkout;
use Illuminate\Http\Request;
use Validator;
use Response;

class CheckoutController extends Controller
{
    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email_address' => 'required|unique:checkouts'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'request' => $request,
                'status' => 'failed',
                'status_code' => 400,
                'message' => 'Email address is either not unique or not valid'
            ]);
        }
        else
        {
            $checkout = new Checkout();
            $checkout->email_address = $request['email_address'];
            $checkout->save();

            session([
                'checkoutId' => $checkout->id
            ]);
    
            return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Checkout order has been created & email saved'
            ]);
        }
    }

    public function updateAddress(Request $request)
    {
        $checkoutId = session('checkoutId');
        if(!session()->has('checkoutId'))
        {
            return response()->json([
                'status' => 'failed',
                'status_code' => 400,
                'message' => 'checkoutId session stoken does not exist'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'address' => 'required|string',
            'suburb' => 'required|string',
            'city' => 'required|string',
            'postcode' => 'required|integer'
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
            $checkout = Checkout::find($checkoutId);
            $checkout->address = $request['address'];
            $checkout->suburb = $request['suburb'];
            $checkout->city = $request['city'];
            $checkout->postcode = $request['postcode'];
            $checkout->save();
    
            return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Checkout order has been created & email saved'
            ]);
        }
    }
}