<?php

use App\Services\PasswordResetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

beforeEach(function () {
    DB::shouldReceive('table')->andReturn(DB::shouldReceive('where'));
});

it('generates a reset token', function () {
    $passwordResetService = new PasswordResetService();
    $token = $passwordResetService->generateResetToken();

    expect($token)->toBeString()
        ->and(strlen(base64_decode($token)))->toBe(64);
});

it('validates a valid token', function () {
    $token = 'valid_token';
    DB::shouldReceive('first')->andReturn((object)['created_at' => Carbon::now()]);

    $passwordResetService = new PasswordResetService();
    $result = $passwordResetService->validateToken($token);

    expect($result['valid'])->toBeTrue();
});

it('validates an invalid token', function () {
    $token = 'invalid_token';
    DB::shouldReceive('first')->andReturn(null);

    $passwordResetService = new PasswordResetService();
    $result = $passwordResetService->validateToken($token);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toBe('Invalid token!, request another link');
});

it('validates an expired token', function () {
    $token = 'expired_token';
    $expiredDate = Carbon::now()->subMinutes(60); // Assuming a 60-minute expiration time
    DB::shouldReceive('first')->andReturn((object)['created_at' => $expiredDate]);

    $passwordResetService = new PasswordResetService();
    $result = $passwordResetService->validateToken($token);

    expect($result['valid'])->toBeFalse()
        ->and($result['message'])->toBe('Token expired!, request another link');
});

it('inserts a token record', function () {
    DB::shouldReceive('insert')->andReturn(true);

    $passwordResetService = new PasswordResetService();
    $result = $passwordResetService->insertToken('token', 'test@example.com');

    expect($result)->toBeTrue();
});

it('updates a token record', function () {
    DB::shouldReceive('update')->andReturn(1);

    $passwordResetService = new PasswordResetService();
    $result = $passwordResetService->updateToken('new_token', 'test@example.com');

    expect($result)->toBe(1);
});

it('gets an old token', function () {
    $token = 'old_token';
    DB::shouldReceive('first')->andReturn((object)['token' => $token]);

    $passwordResetService = new PasswordResetService();
    $result = $passwordResetService->getOldToken('test@example.com');

    expect($result)->toBeObject()
        ->and($result->token)->toBe($token);
});

it('deletes a token record', function () {
    DB::shouldReceive('delete')->andReturn(1);

    $passwordResetService = new PasswordResetService();
    $result = $passwordResetService->deleteTokenRecord('test@example.com', 'token');

    expect($result)->toBe(1);
});

