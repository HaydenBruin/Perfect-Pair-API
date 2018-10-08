<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'image',
        'description',
        'price',
        'saleprice',
        'inventory',
        'enabled'
    ];
}
