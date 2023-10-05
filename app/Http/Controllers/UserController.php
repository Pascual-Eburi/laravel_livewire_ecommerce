<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use ConstGuards;
use ConstDefaults;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use PHPMailer\PHPMailer\Exception;

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
        return redirect()->route('auth.login');
    }


    /**
     * @throws Exception
     */
    public function sendPasswordResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => $this->rules['email']
        ]);
        // get the user
        $user = User::where('email', $request->email)->first();

        // generate token
        $token = base64_encode( Str::random(64) );

        // check for old tokens
        $old_token = DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->first();

        // generate reset link
        $reset_link = route('auth.resetPassword', [
            'token' => $token,
            'email' => $request->email
        ]);

        $data = [
            'reset_link' => $reset_link,
            'user' => $user,
            'tokenExpiredMinutes' => ConstDefaults::tokenExpiredMinutes
        ];

        $email_body = view('email-templates.forgot_password', $data)
                        ->render();

        $emailConfig = [
            'from_email' => env('EMAIL_FROM_ADDRESS'),
            'from_name' => env('EMAIL_FROM_NAME'),
            'recipient_email' => $user->email,
            'recipient_name' => $user->first_name . ' '. $user->last_name,
            'subject' => 'Reset Password',
            'body' => $email_body
        ];

        if ( sendEmail($emailConfig) ) {
            // if found token, then update, else, insert new token
            if ($old_token){
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->update([
                        'token' => $token,
                        'created_at' => Carbon::now()
                    ]);
            }else {
                DB::table('password_reset_tokens')
                    ->insert([
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => Carbon::now()
                    ]);
            }

            session()->flash('success', 'We have send you the link');
        }else {
            session()->flash('fail', 'Something went wrong!!');
        }

        return redirect()->route('auth.forgotPassword');
    }

    public function resetPassword(Request $request){

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
