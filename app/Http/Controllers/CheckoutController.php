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
            'email_address' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'status_code' => 400,
                'message' => 'Email address is not valid'
            ]);
        }
        else
        {
            $checkout = new Checkout();
            $checkout->email_address = $request['email_address'];
            $checkout->status = "Pending";
            $checkout->save();

            $request->session()->put('checkoutId', $checkout->id);
    
            return response()->json([
                'checkoutId' => $checkout->id,
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Checkout order has been created & email saved'
            ]);
        }
    }

    public function updatePayment(Request $request)
    {
        $checkoutId = $request->session()->get('checkoutId');
        if(!$request->session()->has('checkoutId'))
        {
            return response()->json([
                'status' => 'failed',
                'status_code' => 400,
                'message' => 'checkoutId session token does not exist'
            ]);
        }

        // TAKE PAYMENT
        if(@$request['tokenId'])
        {
            \Stripe\Stripe::setApiKey('sk_test_nQRGis4oGrHnzm9cVilHhrwf');
            $charge = \Stripe\Charge::create(['amount' => 2000, 'currency' => 'nzd', 'source' => $request['tokenId']]);
            
            $checkout = Checkout::find($checkoutId);
            $checkout->status = "Success";
            $checkout->save();

            return response()->json([
                'charge' => $charge,
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Checkout order has been created & email saved'
            ]);
        }

        return response()->json([
            'request' => $request,
            'status' => 'failed',
            'status_code' => 400,
            'message' => 'Payment was not processed'
        ]);
    }

    public function updateAddress(Request $request)
    {
        $checkoutId = session('checkoutId');
        if(!session()->has('checkoutId'))
        {
            return response()->json([
                'status' => 'failed',
                'status_code' => 400,
                'message' => 'checkoutId session token does not exist'
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
                'message' => 'Delivery address updated'
            ]);
        }
    }
}