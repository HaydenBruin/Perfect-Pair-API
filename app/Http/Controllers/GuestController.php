<?php

namespace App\Http\Controllers;

use App\Guest;
use Illuminate\Http\Request;
use Validator;
use Response;

class GuestController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|unique:guests|size:15',
            'ip_address' => 'required|ip',
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
            $guest = new Guest();
            $guest->token = $request['token'];
            $guest->ip_address = $request['ip_address'];
            $guest->save();
    
            return response()->json([
                'status' => 'success',
                'status_code' => 201,
                'message' => 'Guest profile has been created'
            ]);
        }
    }
}
