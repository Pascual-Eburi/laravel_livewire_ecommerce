<p style="font-family: monospace;"> Dear {{ $user->first_name }}, </p>
<p style="font-family: monospace;">
    We are received a request to reset the password for laracomerce account associated with {{ $user->email }}. You can reset your password by clicking the button below:
    <br />
    <a  href="{{ $reset_link }}" target="_blank"
    style="margin-top:.84rem;color: #fff;border-color:#22bc66;border-style:solid;border-width: 5px 10px; background-color: #22bc66;display:inline-block;text-decoration: none;border-radius: 3px;box-shadow: 0 2px 3px rgba(0,0,0,.16);-webkit-text-size-adjust: none;box-sizing: border-box">
        Reset Password
    </a>

</p>
<p style="font-family: monospace;">
    <b>NB:</b> This link valid within {{ $tokenExpiredMinutes }} minutes
    <br />
    If you did not request for a password reset, please ignore this message.
</p>
