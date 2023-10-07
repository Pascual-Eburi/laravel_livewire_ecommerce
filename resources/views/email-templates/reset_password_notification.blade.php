<div style="font-family: monospace">
    <p>
        Dear {{ $user->first_name }},
        <br>
    </p>
    <p>
        Your password on Laracomerce was updated successfully.
        You can now <a href="{{ $login_link }}" target="_blank"> log in to your account </a> with your email or username.
    </p>
    <p>
        Please, do not share your login details with anybody else.
        Laracomerce will not be liable for any misuse of your login data.
    </p>
    <br>
    <br>
    <p>
        This email was sent automatically, please, do not reply it.
    </p>
</div>
