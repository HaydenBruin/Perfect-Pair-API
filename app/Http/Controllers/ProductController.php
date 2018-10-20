<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
 
class ProductController extends Controller
{
    public function getProducts()
    {
        return Product::paginate(10);
    }

    public function getProduct(Product $product)
    {
        return $product;
    }
}