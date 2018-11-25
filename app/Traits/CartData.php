<?php
namespace App\Traits;

use DB;
use App\Product;
use Illuminate\Support\Facades\Cookie;

trait CartData {
    public static function getCartData($cartProducts) {
        if(is_array(@$cartProducts)) $cartProducts = json_encode($cartProducts);
        $cartProducts = json_decode($cartProducts,true);

        $cart = array(
            'overview' => [
                'totalPrice' => 0.00,
                'totalSavings' => 0.00,
                'totalFullPrice' => 0.00
            ],
            'products' => []
        );
        if(@$cartProducts && is_array(@$cartProducts))
        {
            foreach($cartProducts as $cartProduct)
            {
                $product = array();
                $getProduct = Product::find($cartProduct['productId']);
                if(@$getProduct)
                {
                    $product['_cart']['productId'] = $cartProduct['productId'];
                    $product['_cart']['quantity'] = $cartProduct['quantity'];
                    $product['id'] = $getProduct->id;
                    $product['title'] = $getProduct->title;
                    $product['image'] = $getProduct->image;
                    $product['description'] = $getProduct->description;
                    $product['price'] = number_format($getProduct->price,2);
                    $product['saleprice'] = number_format($getProduct->saleprice,2);
                    $product['totalprice'] = number_format($getProduct->price * $cartProduct['quantity'],2);
                    $product['totalsaleprice'] = number_format($getProduct->saleprice * $cartProduct['quantity'],2);
                    $product['inventory'] = $getProduct->inventory;
                    $product['slug'] = $getProduct->slug;
                    $cart['products'][] = $product;

                    // UPDATE TOTAL PRICING
                    for($i = 0; $i < $cartProduct['quantity']; $i++)
                    {
                        $cart['overview']['totalPrice'] += $getProduct->price;
                        $cart['overview']['totalSavings'] = 0;
                        
                    }
                }
            }

            $discounts = DB::table('discounts')->get();
            if(@$discounts)
            {
                foreach($discounts as $discount)
                {
                    if(@$discount->amount)
                    {
                        $cart['overview']['totalPrice'] = $cart['overview']['totalPrice'] - $discount->amount;
                        $cart['overview']['totalSavings'] = $cart['overview']['totalSavings'] + $discount->amount;
                    }
                    else if(@$discount->percentage)
                    {
                        $cart['overview']['totalSavings'] = ($discount->percentage * $cart['overview']['totalPrice']) / 100;
                        $cart['overview']['totalPrice'] = $cart['overview']['totalPrice'] - ($discount->percentage * $cart['overview']['totalPrice']) / 100;
                    }
                }
                if($cart['overview']['totalPrice'] <= 0) $cart['overview']['totalPrice'] = 0.00; 
                if($cart['overview']['totalSavings'] <= 0) $cart['overview']['totalSavings'] = 0.00; 
                if($cart['overview']['totalFullPrice'] <= 0) $cart['overview']['totalFullPrice'] = 0.00; 
            }

            $cart['overview']['totalPrice'] = number_format($cart['overview']['totalPrice'], 2);
            $cart['overview']['totalSavings'] = number_format($cart['overview']['totalSavings'], 2);
            $cart['overview']['totalFullPrice'] = number_format($cart['overview']['totalSavings'] + $cart['overview']['totalPrice'], 2);
        }

        return $cart;
    }
}