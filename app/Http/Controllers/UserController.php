<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    private $rules = [
        'email' => 'required|email|exists:users,email',
        'username' =>'required|exists:users,username',
        'password' =>'required|min:5|max:45',
    ];

    public  function  login (Request $request){
        // check if user login with email or username
        $login_id = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';


        // validate data
        $request->validate([
            'login_id' => $this->rules[$login_id],
            'password' => $this->rules['password'],
        ]);

    }


    public function logout(Request $request){

    }
}
