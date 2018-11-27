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
                'totalFullPrice' => 0.00,
                'totalProducts' => 0
            ],
            'coupons' => [],
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
                    $product['totalprice'] = number_format($getProduct->price * $cartProduct['quantity'],2);
                    $product['inventory'] = $getProduct->inventory;
                    $product['slug'] = $getProduct->slug;
                    $cart['products'][] = $product;

                    // UPDATE TOTAL PRICING
                    for($i = 0; $i < $cartProduct['quantity']; $i++)
                    {
                        $cart['overview']['totalPrice'] += $getProduct->price;
                        $cart['overview']['totalFullPrice'] += $getProduct->price;
                        $cart['overview']['totalSavings'] = 0;
                        $cart['overview']['totalProducts']++;
                    }
                }
            }

            $discounts = DB::table('discounts')->get();
            if(@$discounts)
            {
                foreach($discounts as $discount)
                {
                    $success = false;
                    if(@$discount->type && @$discount->discount_value)
                    {
                        if($discount->type === "fixed")
                        {
                            $cart['overview']['totalPrice'] = $cart['overview']['totalFullPrice'] - $discount->discount_value;
                            $cart['overview']['totalSavings'] = $cart['overview']['totalSavings'] + $discount->discount_value;
                            $success = true;
                        }
                        else if(@$discount->type === "percentage")
                        {
                            $cart['overview']['totalSavings'] = $cart['overview']['totalSavings'] + (($discount->discount_value * $cart['overview']['totalFullPrice']) / 100);
                            $cart['overview']['totalPrice'] = $cart['overview']['totalFullPrice'] - ($discount->discount_value * $cart['overview']['totalFullPrice']) / 100;
                            $success = true;
                        }
                        else if(@$discount->type === "cartpercentage" && @$discount->requirement_value) {
                            if($cart['overview']['totalProducts'] >= $discount->requirement_value)
                            {
                                $cart['overview']['totalPrice'] = $cart['overview']['totalPrice'] - ($discount->discount_value * $cart['overview']['totalFullPrice']) / 100;
                                $cart['overview']['totalSavings'] = $cart['overview']['totalSavings'] + (($discount->discount_value * $cart['overview']['totalFullPrice']) / 100);
                                $success = true;
                            }
                        }
                        else if(@$discount->type === "cartfixed" && @$discount->requirement_value) {
                            if($cart['overview']['totalProducts'] >= $discount->requirement_value)
                            {
                                $cart['overview']['totalPrice'] = $cart['overview']['totalPrice'] - $discount->discount_value;
                                $cart['overview']['totalSavings'] = $cart['overview']['totalSavings'] + $discount->discount_value;
                                $success = true;
                            }
                        }
                    }
                    if(@$success === true) {
                        $cart['coupons'][] = $discount;
                    }
                }
                if($cart['overview']['totalPrice'] <= 0) $cart['overview']['totalPrice'] = 0.00; 
                if($cart['overview']['totalSavings'] >= $cart['overview']['totalPrice']) $cart['overview']['totalSavings'] = $cart['overview']['totalPrice']; 
                if($cart['overview']['totalFullPrice'] <= 0) $cart['overview']['totalFullPrice'] = 0.00; 
            }

            $cart['overview']['totalPrice'] = number_format($cart['overview']['totalPrice'], 2);
            $cart['overview']['totalSavings'] = number_format($cart['overview']['totalSavings'], 2);
            $cart['overview']['totalFullPrice'] = number_format($cart['overview']['totalFullPrice'], 2);
        }

        return $cart;
    }
}