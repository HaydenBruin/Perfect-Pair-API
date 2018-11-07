<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Checkout extends Model
{
    protected $fillable = [
        'email_address',
        'fullname',
        'address',
        'suburb',
        'city',
        'postcode'
    ];
}
