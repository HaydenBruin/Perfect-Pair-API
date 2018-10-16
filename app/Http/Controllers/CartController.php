<?php

namespace App\Http\Controllers;

use App\Cart;
use App\Product;
use Illuminate\Http\Request;
use Validator;
use Response;

class CartController extends Controller
{
    public function getCart(Request $request)
    {
        $cartProducts = json_decode(@$_COOKIE['cart'], true);
        $cart = array(
            'overview' => [
                'totalPrice' => 0.00,
                'totalSavings' => 0.00
            ],
            'products' => []
        );

        if(@$cartProducts)
        {
            foreach($cartProducts as $cartProduct)
            {
                $product = array();
                $getProduct = Product::find($cartProduct['productId']);
                $product['_cart']['productId'] = $cartProduct['productId'];
                $product['_cart']['quantity'] = $cartProduct['quantity'];
                $product['id'] = $getProduct->id;
                $product['title'] = $getProduct->title;
                $product['image'] = $getProduct->image;
                $product['description'] = $getProduct->description;
                $product['price'] = number_format($getProduct->price,2);
                $product['saleprice'] = number_format($getProduct->saleprice,2);
                $product['inventory'] = $getProduct->inventory;
                $product['slug'] = $getProduct->slug;
                $cart['products'][] = $product;

                // UPDATE TOTAL PRICING
                $cart['overview']['totalPrice'] += $getProduct->saleprice ? $getProduct->saleprice : $getProduct->price;
                $cart['overview']['totalSavings'] += $getProduct->price - ($getProduct->saleprice ? $getProduct->saleprice : $getProduct->price);
            }
            $cart['overview']['totalPrice'] = number_format($cart['overview']['totalPrice'], 2);
            $cart['overview']['totalSavings'] = number_format($cart['overview']['totalSavings'], 2);
        }

        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => $cart
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = json_decode(@$_COOKIE['cart'],true);
        foreach($cart as $key => $value) {
            if (in_array($request['productId'], $value)) {
                unset($cart[$key]);
            }
        }
        setcookie('cart',json_encode($cart),time() + (86400 * 30));
    
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => $cart
        ]);
    }

    public function addToCart(Request $request)
    {
        if(!isset($_COOKIE['cart'])) {
            $cart = array();
            $cart[$request['productId']] = array(
                'productId' => intval($request['productId']),
                'quantity' => intval($request['quantity'])
            );
            setcookie('cart',json_encode($cart),time() + (86400 * 30));
        }
        else 
        {
            $cart = json_decode($_COOKIE['cart'], true);
            $cart[$request['productId']] = array(
                'productId' => intval($request['productId']),
                'quantity' => intval($request['quantity'])
            );
            setcookie('cart',json_encode($cart),time() + (86400 * 30));
        }
    
        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => $cart
        ]);
    }

    /*public function dbstore(Request $request)
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
    }*/
}
