<?php

namespace App\Http\Controllers;

use App\User;
use App\UserResetPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserResetPasswordController extends Controller
{

    public function findByEmailAndToken(Request $request) {
        $data = $this->validate($request, [
            'email' => 'required',
            'token' => 'required',
        ]);
        $user = DB::table('user_reset_passwords')
            ->join('users', 'user_reset_passwords.user_id', '=', 'users.id')
            ->where('user_reset_passwords.reset_password_token', $data['token'])
            ->where('users.email_address', $data['email'])
            ->where('user_reset_passwords.reset_password_expires', '>', Carbon::now())
            ->select('user_reset_passwords.reset_password_token', 'users.email_address')
            ->first();
        if($user != null) {
            return response()->json([
                'token' => $user->reset_password_token,
                'email_address' => $user->email_address
            ]);
        }
        else {
            return response()->json([
                'message' => 'Password reset token is invalid or has expired.'
            ]);
        }
    }

}
