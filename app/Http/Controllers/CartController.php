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
        $cart = json_decode(@$_COOKIE['cart'], true);
        $products = array();

        foreach($cart as $cartProduct)
        {
            $product = array();
            $getProduct = Product::find($cartProduct['productId']);
            $product['_cart']['productId'] = $cartProduct['productId'];
            $product['_cart']['quantity'] = $cartProduct['quantity'];
            $product['id'] = $getProduct->id;
            $product['title'] = $getProduct->title;
            $product['image'] = $getProduct->image;
            $product['description'] = $getProduct->description;
            $product['price'] = $getProduct->price;
            $product['salePrice'] = $getProduct->salePrice;
            $product['inventory'] = $getProduct->inventory;
            $product['slug'] = $getProduct->slug;
            $products[] = $product;
        }

        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'cart' => $products
        ]);
    }

    public function addToCart(Request $request)
    {
        if(!isset($_COOKIE['cart'])) {
            $cart = array();
            $cart[$request['productId']] = array(
                'productId' => $request['productId'],
                'quantity' => $request['quantity']
            );
            setcookie('cart',json_encode($cart),time() + (86400 * 30));
        }
        else 
        {
            $cart = json_decode($_COOKIE['cart'], true);
            $cart[$request['productId']] = array(
                'productId' => $request['productId'],
                'quantity' => $request['quantity']
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
