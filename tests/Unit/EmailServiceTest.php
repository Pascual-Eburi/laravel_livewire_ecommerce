<?php

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Support\Facades\Config;
use PHPMailer\PHPMailer\Exception;
use Tests\TestCase;


describe('Send Password Reset Email', function (){
    beforeEach(function () {
        Config::set('mail.from.address', 'test@example.com');
        Config::set('mail.from.name', 'Test Sender');
    });

    it('sends a password reset email',
        /**
         * @throws Exception
         */
        function () {
        $user = User::factory()->create();
        $resetLink = 'https://example.com/reset-password';

        $emailService = new EmailService();
        $result = $emailService->sendPasswordResetEmail($user, $resetLink);

        expect($result)->toBeTrue();

    });
});


it( 'sends a password changed email',
    /**
     * @throws Exception
     */
    function () {
    $user = User::factory()->create();

    $emailService = new EmailService();
    $result = $emailService->sendPasswordChangedEmail($user);

    expect($result)->toBeTrue();

});


