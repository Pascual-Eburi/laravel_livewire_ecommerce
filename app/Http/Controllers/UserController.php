<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

# use ConstGuards;
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
    private array $rules = [
        'email' => 'required|email|exists:users,email',
        'username' =>'required|exists:users,username',
        'password' =>'required|min:5|max:45',
        'new_password' => 'required|min:5|max:45|required_with:password_confirm|same:password_confirm'
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
        $login_data = [
            $login_id => $request->login_id, // login_id => email | username
            'password' => $request->password
        ];

        if ( Auth::attempt($login_data)){
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

        if ( !sendEmail($emailConfig) ) {
            session()->flash('fail', 'Something went wrong!!');
            return redirect()->route('auth.forgotPassword');
        }

        // the email has been sent successfully
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
        return redirect()->route('auth.forgotPassword');
    }

    /**
     * Validate a token requested for reset password link
     * @param string|null $token
     * @return array
     */
    public function validate_token(string $token = null): array
    {
        $result = array('valid' => false, 'message' => '');

        $check_token = DB::table('password_reset_tokens')
            ->where(['token' => $token])
            ->first();

        if (!$check_token){
            $result['message'] = 'Invalid token!, request another link';
            return $result;
        }

        $diff_minutes = Carbon::createFromFormat(
                    'Y-m-d H:i:s', $check_token->created_at
                    )->diffInMinutes( Carbon::now());

        if ($diff_minutes > ConstDefaults::tokenExpiredMinutes){
            $result['message'] = 'Token expired!, request another link';
            return $result;
        }

        $result['valid'] = true;
        return $result;

    }


    /**
     * @param Request $request
     * @param string $token
     * @return Factory|Application|\Illuminate\Contracts\View\View|RedirectResponse|\Illuminate\Contracts\Foundation\Application
     */
    public function resetPassword(Request $request, string $token): Factory|Application|\Illuminate\Contracts\View\View|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {

        // check token and validate token
        $valid_token_result = $this->validate_token($token);
        if (!$valid_token_result['valid']){
            session()->flash('fail', $valid_token_result['message']);
            return redirect()->route('auth.forgotPassword', ['token' => $token]);
        }

        // ok, the token is valid
        return view('backend.pages.auth.reset_password')->with(['token' => $token]);
    }


    /**
     * @throws Exception
     */
    public function resetPasswordHandler(Request $request){
        $request->validate([
            'new_password' => $this->rules['new_password'],
            'password_confirm' => 'required'
        ]);

        $user_token = DB::table('password_reset_tokens')
                        ->where(['token' => $request->token])->first();

        $user = User::where('email', $user_token->email )->first();

        if (!$user_token || !$user){
            session()->flash('fail', 'Token invalid!, please, request another link');
            return redirect()->route('auth.forgotPassword');
        }

        // ok, Update user password
        $updated = User::where('email', $user->email)->update([
            'password' => Hash::make( $request->new_password),
        ]);

        if (!$updated){
            session()->flash('fail', 'We could not update your password!!');
            return redirect()->route('auth.forgotPassword',
                ['token' => $request->token]);
        }

        // Delete token record
        DB::table('password_reset_tokens')
                ->where([ 'email' => $user->email, 'token' => $request->token
                ])->delete();

        // Send email to notify user
        $data = [
            'user' => $user,
            'login_link' => route('auth.login')
        ];

        $email_body = view('email-templates.reset_password_notification', $data)->render();

        $email_config = [
            'from_email' => env('EMAIL_FROM_ADDRESS'),
            'from_name' => env('EMAIL_FROM_NAME'),
            'recipient_email' => $user->email,
            'recipient_name' => $user->first_name . ' '. $user->last_name,
            'subject' => 'Password Changed',
            'body' => $email_body
        ];

        if ( !sendEmail($email_config)) {
            return redirect()->route('auth.login')
                ->with('fail', 'Password changed. You can login, But we could not sent a confirmation email!!');
        }

        return redirect()->route('auth.login')
            ->with('success', 'Done!, Password changed. Use new password to login');

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
