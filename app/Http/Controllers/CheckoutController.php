<?php

namespace App\Http\Controllers;

use App\Checkout;
use App\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Traits\CartData;
use DB;
use Mail;
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
        $cartData = CartData::getCartData($request->cookie('cart'));

        if(!$request->session()->has('checkoutId') || !@$cartData)
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
            $charge = \Stripe\Charge::create(['amount' => ($cartData['overview']['totalPrice'] * 100), 'currency' => 'nzd', 'source' => $request['tokenId']]);
            
            if(@$charge['paid'])
            {
                $checkout = Checkout::find($checkoutId);
                $checkout->status = "Success";
                $checkout->save();

                if(@$cartData['products'])
                {
                    foreach($cartData['products'] as $product)
                    {
                        $fullProduct = Product::find($product['_cart']['productId']);

                        DB::table('checkouts_items')->insert([
                            'checkoutId' => $checkoutId,
                            'productId' => $product['_cart']['productId'],
                            'quantity' => $product['_cart']['quantity'],
                            'shipped' => false
                        ]);

                        DB::table('products')
                        ->where('id', $fullProduct['id'])
                        ->update([
                            'inventory' => $fullProduct['inventory'] - $product['_cart']['quantity']
                        ]);
                    }
                }

                if(@$checkout->email_address)
                {
                    Mail::to($checkout->email_address)
                    ->queue(new \App\Mail\OrderCompleted($checkout));

                    Mail::to(explode(',', env('MAIL_ADMIN')))
                    ->queue(new \App\Mail\OrderCompletedAdmin($checkout));
                }

                return response()->json([
                    'status' => 'success',
                    'status_code' => 201,
                    'message' => 'Checkout order has been created & email saved'
                ])->withCookie(Cookie::forget('cart'));
            }
            else {
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'message' => 'Payment method has failed'
                ]);    
            }
        }

        return response()->json([
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
            'fullname' => 'required|string',
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
            $checkout->fullname = $request['fullname'];
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