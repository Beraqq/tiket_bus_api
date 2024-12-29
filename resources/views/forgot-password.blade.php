<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
</head>
<body>
    <h2>Reset Password</h2>
    <p>
        Anda menerima email ini karena kami menerima permintaan reset password untuk akun Anda.
    </p>
    <p>
        Silakan klik link berikut untuk reset password Anda:
    </p>
    <a href="{{ env('APP_FRONTEND_URL') }}/reset-password?token={{ $token }}&email={{ request()->email }}">
        Reset Password
    </a>
    <p>
        Jika Anda tidak meminta reset password, abaikan email ini.
    </p>
    <p>
        Link reset password ini akan kadaluarsa dalam 60 menit.
    </p>
</body>
</html>
