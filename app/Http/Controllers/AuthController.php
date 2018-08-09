<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;

class AuthController extends Controller
{
    public function __construct()
    {

    }

    /**
     * Authenticate an user.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $validator = Validator::make($credentials, [
            'username' => 'required',
            'password' => 'required'
        ]);

        if($validator->fails()) {
            return response()
                ->json([
                    'code' => 1,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
        }

        $token = JWTAuth::attempt($credentials);

        // verify the credentials and create a token for the user
        if ($token) {
            JWTAuth::setToken($token);
            $user = JWTAuth::toUser();
            return response()->json([
                'user' => $user,
                'token' => $token,
                'success' => true
            ]);
        } else {
            return response()->json([
                'status' => false,
                'code' => 2, 
                'message' => 'Invalid credentials.'
            ], 401);
        }
    }

    /**
     * Get the user by token.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $user = JWTAuth::toUser();
        return response()->json($user);
    }
}
