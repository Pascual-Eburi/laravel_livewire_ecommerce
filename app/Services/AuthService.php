<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService {
    public function attemptLogin($loginData): bool
    {
        return Auth::attempt($loginData);
    }
}
