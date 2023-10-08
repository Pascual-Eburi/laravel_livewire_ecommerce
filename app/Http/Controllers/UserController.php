<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\Exception;

use App\Services\AuthService;
use App\Services\PasswordResetService;
use App\Services\EmailService;

class UserController extends Controller {
    //
    private array $rules = [
        'email' => 'required|email|exists:users,email',
        'username' =>'required|exists:users,username',
        'password' =>'required|min:5|max:45',
        'new_password' => 'required|min:5|max:45|required_with:password_confirm|same:password_confirm'
    ];
    private AuthService $authService;
    private PasswordResetService $passwordResetService;
    private EmailService $emailService;

    public function __construct(
        AuthService $authService,
        PasswordResetService $passwordResetService,
        EmailService $emailService
    ) {
        $this->authService = $authService;
        $this->passwordResetService = $passwordResetService;
        $this->emailService = $emailService;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    final public function login (Request $request): RedirectResponse
    {
        // check if user is login with email or username
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
        $loginData = [
            $login_id => $request->login_id, // login_id => email | username
            'password' => $request->password
        ];


        if ($this->authService->attemptLogin($loginData)) {
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
        $request->validate([ 'email' => $this->rules['email'] ]);

        // get the user
        $user = User::where('email', $request->email)->first();

        // generate token
        $token = $this->passwordResetService->generateResetToken($request->email);

        // check for old tokens
        $old_token = $this->passwordResetService->getOldToken($request->email);

        // generate reset link
        $reset_link = route('auth.resetPassword', [
            'token' => $token,
            'email' => $request->email
        ]);

        $email_sent = $this->emailService->sendPasswordResetEmail($user, $reset_link);

        if ( !$email_sent ) {
            session()->flash('fail', 'We could´t sent you the email!!');
            return redirect()->route('auth.forgotPassword');
        }

        // the email has been sent successfully
        // if found token, then update, else, insert new token
        if ($old_token){
            $this->passwordResetService->updateToken($token, $request->email);
        }else {
            $this->passwordResetService->insertToken($token, $request->email);
        }

        session()->flash('success', 'We have send you the link');
        return redirect()->route('auth.forgotPassword');
    }


    /**
     * @param Request $request
     * @param string $token
     * @return Factory|Application|View|RedirectResponse|\Illuminate\Contracts\Foundation\Application
     */
    public function resetPassword(Request $request, string $token): Factory|Application|View|RedirectResponse|\Illuminate\Contracts\Foundation\Application
    {

        // check token and validate token
        $valid_token_result = $this->passwordResetService->validateToken($token);
        if (!$valid_token_result['valid']){
            session()->flash('fail', $valid_token_result['message']);
            return redirect()->route('auth.forgotPassword', ['token' => $token]);
        }

        // ok, the token is valid
        return view('backend.pages.auth.reset_password')->with(['token' => $token]);
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function resetPasswordHandler(Request $request): RedirectResponse
    {
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
        $this->passwordResetService->deleteTokenRecord($user->email, $request->token);

        // send email
        $email_sent = $this->emailService->sendPasswordChangedEmail($user);

        if ( !$email_sent) {
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
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        session()->flash('fail', 'Your are logged out');
        return redirect()->route('auth.login');
    }
}
