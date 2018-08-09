<!DOCTYPE html>
<html>
    <head>
        <title>Forgot Password Email</title>
    </head>
    <body>
        <div>
            <h3>Dear {{ $name }},</h3>
            <p>
            You are receiving this because you (or someone else) have requested the reset of the password for your account.<br>
            Please click on the following link, or paste this into your browser to complete the process:
            </p>
            <a href="{{ $url }}">{{ $url }}</a>
            <p>If you did not request this, please ignore this email and your password will remain unchanged.</p>
            <br>
            <p>Cheers!</p>
        </div>
    </body>

</html>