<?php

namespace App\Http\Controllers;

use App\Guest;
use Illuminate\Http\Request;
use Response;

class GuestController extends Controller
{
    public function store(Request $request)
    {
        $guest = new Guest();
        $guest->token = $request['token'];
        $guest->ip_address = $request['ip_address'];
        $guest->save();

        return $this->respond([
            'status' => 'success',
            'message' => 'Guest profile has been created'
        ]);

    }
}
