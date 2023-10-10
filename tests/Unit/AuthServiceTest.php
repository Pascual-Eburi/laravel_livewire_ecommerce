<?php
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
# use Mockery;
use Tests\TestCase;


describe('Attempt Login', function (){
    beforeEach(function () {
        $this->authService = new AuthService();
    });

    it('returns true when login succeeds', function () {
        // Simulates success authentication
        Auth::shouldReceive('attempt')->once()->andReturn(true);

        $loginData = ['email' => 'test@example.com', 'password' => 'password'];
        $result = $this->authService->attemptLogin($loginData);

        expect($result)->toBeTrue();
    });

    it('returns false when login fails', function () {
        // Simulates failed authentication
        Auth::shouldReceive('attempt')->once()->andReturn(false);

        $loginData = ['email' => 'test@example.com', 'password' => 'password'];
        $result = $this->authService->attemptLogin($loginData);

        expect($result)->toBeFalse();
    });

    afterEach(function () {
        Mockery::close();
    });
});



