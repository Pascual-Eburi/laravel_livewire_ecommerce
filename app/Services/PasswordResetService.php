<?php
namespace App\Services;
use ConstDefaults;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PasswordResetService {
    public function generateResetToken($email): string
    {
        return base64_encode(Str::random(64));

    }

    /**
     * @param string $token
     * @return array
     */
    public function validateToken(string $token): array
    {
        $result = ['valid' => false, 'message' => ''];

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
     * @param string $token
     * @param string $email
     * @return bool
     */
    public function insertToken(string $token, string $email): bool
    {
        return DB::table('password_reset_tokens')
            ->insert([
                'email' => $email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
    }

    /**
     * @param string $token
     * @param string $email
     * @return int
     */
    public function updateToken(string $token, string $email): int
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->update([
                'token' => $token,
                'created_at' => Carbon::now()
            ]);
    }

    /**
     * @param string $email
     * @return object|null
     */
    public function getOldToken(string $email): object|null
    {
        return DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();
    }

    /**
     * @param string $email
     * @param string $token
     * @return int
     */
    public function deleteTokenRecord(string $email, string $token): int
    {
        return  DB::table('password_reset_tokens')
            ->where(['email' => $email, 'token' => $token])
            ->delete();
    }
}
