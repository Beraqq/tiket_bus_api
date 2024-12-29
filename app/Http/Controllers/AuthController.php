<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|regex:/^[0-9]{10,15}$/|unique:users,phone',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if($validator->fails()) :
                $validatorMessage = $validator->errors()->first();
                return response()->json(['status' => 'failed', 'message' => $validatorMessage ]);

            else:

                $user = User::create([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                $token = JWTAuth::fromUser($user);

                return response()->json(['status' =>'success','user' => ['token' => $token, 'data' => $user]], 201);
            endif;

        }catch(\Throwable $error){
            return response()->json(['status' => 'failed', 'message' => 'Error '. $error]);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (empty($request->password)) {
            return response()->json(['error' => 'The Password field is required'], 422);
        }

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid Credentials'], 403);
        }

        $user = Auth::user();
        return response()->json(['user' => $user, 'token' => $token], 200);
    }


    public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users',
    ]);

    try {
        // Generate token
        $token = Str::random(64);

        // Simpan token ke database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]
        );

        // Kirim email
        Mail::send('emails.forgot-password', ['token' => $token], function($message) use($request){
            $message->to($request->email);
            $message->subject('Reset Password');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Link reset password telah dikirim ke email Anda'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat mengirim email reset password'
        ], 500);
    }
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users',
        'token' => 'required',
        'password' => 'required|min:8|confirmed',
    ]);

    try {
        $updatePassword = DB::table('password_reset_tokens')
            ->where([
                'email' => $request->email,
                'token' => $request->token
            ])
            ->first();

        if(!$updatePassword){
            return response()->json([
                'status' => 'error',
                'message' => 'Token tidak valid!'
            ], 400);
        }

        User::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Password berhasil direset!'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat reset password'
        ], 500);
    }
}
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['user' => ['message' => 'Successfully logged out']]);
    }
}
