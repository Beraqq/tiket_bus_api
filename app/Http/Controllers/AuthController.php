<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['user' => ['message' => 'Successfully logged out']]);
    }
}
