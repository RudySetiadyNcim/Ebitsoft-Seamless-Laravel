<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class AuthController extends Controller
{

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
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
        }

        $token = JWTAuth::attempt($credentials);

        // verify the credentials and create a token for the user
        if ($token) {
            JWTAuth::setToken($token);
            $user = JWTAuth::toUser();
            if($user != null) {
                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'token' => $token,
                ]);
            }
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorised'
                ], 401);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Username or password is incorrect'
            ], 400);
        }
    }

    // somewhere in your controller
    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }

}
