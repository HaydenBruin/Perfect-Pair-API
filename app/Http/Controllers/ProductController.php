<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
 
class ProductController extends Controller
{
    public function getProducts()
    {
        return Product::where([
            ['enabled','=','1'],
            ['inventory','>=','1']
        ])
        ->orderBy('display_order','desc')
        ->paginate(25);
    }

    public function getProduct(Product $product)
    {
        return $product;
    }
}