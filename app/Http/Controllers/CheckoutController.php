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
        $cartData = CartData::getCartData($request->cookie('cart'));

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
            $checkout->checkout_value = number_format($cartData['overview']['totalPrice'],2);
            $checkout->checkout_data = $request->cookie('cart');
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

        if(!$request->session()->has('checkoutId') || !@$cartData || !@$checkoutId)
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
            try {
                \Stripe\Stripe::setApiKey(env('STRIPE_KEY'));
                $charge = \Stripe\Charge::create(['amount' => intval($cartData['overview']['totalPrice'] * 100), 'currency' => 'nzd', 'source' => $request['tokenId']]);     
                
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

                    if(@$checkoutId && @$checkout->email_address)
                    {
                        $orderCompleted = new \App\Mail\OrderCompleted($checkoutId);

                        Mail::to($checkout->email_address)
                        ->bcc(explode(',', env('MAIL_ADMIN')))
                        ->send($orderCompleted);
                    }

                    return response()->json([
                        'status' => 'success',
                        'status_code' => 201,
                        'message' => 'Checkout order has been created & email saved'
                    ])->withCookie(Cookie::forget('cart'));
                }
            } catch(\Stripe\Error\Card $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];
                
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'error_status' => @$e->getHttpStatus(),
                    'message' => @$err['message'],
                    'param' => @$err['param'],
                    'code' => @$err['code'],
                    'type' => @$err['type']
                ]); 

            } catch (\Stripe\Error\RateLimit $e) {
                // Too many requests made to the API too quickly
                $body = $e->getJsonBody();
                $err  = $body['error'];
                
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'error_status' => @$e->getHttpStatus(),
                    'message' => @$err['message'],
                    'param' => @$err['param'],
                    'code' => @$err['code'],
                    'type' => @$err['type']
                ]); 
            } catch (\Stripe\Error\InvalidRequest $e) {
                // Invalid parameters were supplied to Stripe's API
                $body = $e->getJsonBody();
                $err  = $body['error'];
                
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'error_status' => @$e->getHttpStatus(),
                    'message' => @$err['message'],
                    'param' => @$err['param'],
                    'code' => @$err['code'],
                    'type' => @$err['type']
                ]); 
            } catch (\Stripe\Error\Authentication $e) {
                // Authentication with Stripe's API failed
                // (maybe you changed API keys recently)
                $body = $e->getJsonBody();
                $err  = $body['error'];
                
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'error_status' => @$e->getHttpStatus(),
                    'message' => @$err['message'],
                    'param' => @$err['param'],
                    'code' => @$err['code'],
                    'type' => @$err['type']
                ]); 
            } catch (\Stripe\Error\ApiConnection $e) {
                // Network communication with Stripe failed
                $body = $e->getJsonBody();
                $err  = $body['error'];
                
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'error_status' => @$e->getHttpStatus(),
                    'message' => @$err['message'],
                    'param' => @$err['param'],
                    'code' => @$err['code'],
                    'type' => @$err['type']
                ]); 
            } catch (\Stripe\Error\Base $e) {
                // Display a very generic error to the user, and maybe send
                // yourself an email
                $body = $e->getJsonBody();
                $err  = $body['error'];
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'message' => 'Payment method has failed (GE)'
                ]); 
            } catch (Exception $e) {
                $body = $e->getJsonBody();
                $err  = $body['error'];
                return response()->json([
                    'status' => 'failed',
                    'status_code' => 201,
                    'message' => 'Payment method has failed (sf)'
                ]); 
            }
            
            return response()->json([
                'status' => 'failed',
                'status_code' => 201,
                'message' => 'Payment method has failed'
            ]); 
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