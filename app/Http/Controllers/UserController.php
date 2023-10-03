<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //
    private $rules = [
        'email' => 'required|email|exists:users,email',
        'username' =>'required|exists:users,username',
        'password' =>'required|min:5|max:45',
    ];

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public  function  login (Request $request): RedirectResponse
    {
        // check if user login with email or username
        $login_id = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // validate data
        $request->validate([
            'login_id' => $this->rules[$login_id],
            'password' => $this->rules['password'],
        ], [
            'login_id.required' => "Email or Username is required",
            'login_id.email' => "Invalid Email address",
            'login_id.exists' => $login_id === 'email' ? 'Wrong email' : 'Wrong Username',
            'password.required' => "Password is required"
        ]);

        //
        $logid_data = [
            $login_id => $request->login_id, // login_id => email | username
            'password' => $request->password
        ];

        if ( Auth::attempt($logid_data)){
            return redirect()->route('home');
        }

        session()->flash('fail', 'Incorrect credentials');
        return redirect()->route('login');
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        session()->flash('fail', 'Your are logged out');
        return redirect()->route('auth.login');
    }
}
