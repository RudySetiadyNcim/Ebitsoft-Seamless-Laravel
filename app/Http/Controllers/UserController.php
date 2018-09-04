<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordDoneMail;
use App\Mail\ResetPasswordMail;
use App\UserResetPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\User;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use JWTAuth;
use Validator;

class UserController extends Controller
{
    use Auth;

    public function index(Request $request)
    {
        $data = $this->validate($request, [
            'sort' => 'required',
            'order' => 'required',
            'page' => 'required',
            'pageSize' => 'required',
        ]);

        $sort = $data['sort'];
        $order = $data['order'];
        $page = $data['page'];
        $pageSize = (int)$data['pageSize'];

        $skip = ($page - 1) * $pageSize;
        $take = $pageSize;

        $users = DB::table('users');
        if($sort != 'undefined' && $order != null) {
            $users = $users->orderBy($sort, $order);
        }

        $total_count = $users->count();
        $users = $users->skip($skip)->take($take)->get();

        return response()->json([
            'items' => $users,
            'total_count' => $total_count
        ], 200);
    }

    public function register(Request $request)
    {
        $user = new User();
        $data = $this->validate($request, [
            'username' => 'required',
            'first_name' => 'required',
            'last_name' => 'nullable',
            'email_address' => 'required',
            'password' => 'required',
            'mobile_number' => 'required',
        ]);
        $data['balance'] = 0;
        $data['country'] = 'ID';
        $data['currency'] = 'IDR';
        $data['language'] = 'id';
        $data['password'] = Hash::make($data['password']);
        DB::beginTransaction();
        $user->saveUser($data);
        DB::commit();
        return $user;
    }

    public function update(Request $request)
    {
        $data = $request->all();
        $bearer = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $bearer);

        JWTAuth::setToken($token);
        $user = JWTAuth::toUser();
        if($user != null) {

            $validator = Validator::make($data, [
                'first_name' => 'required',
                'last_name' => 'required',
                'email_address' => 'required',
                'mobile_number' => 'required',
            ]);

            if($validator->fails()) {
                return response()
                    ->json([
                        'success' => false,
                        'message' => 'Validation failed.',
                        'errors' => $validator->errors()
                    ], 422);
            }

            $userModel = new User();
            $data['id'] = $user->id;
            DB::beginTransaction();
            $user = $userModel->updateUser($data);
            DB::commit();

            return response()->json([
                'success' => true,
                'user' => $user
                ], 200);
        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorised'
            ], 401);
        }
    }

    public function sendPasswordResetEmail(Request $request) {
        $data = $this->validate($request, [
            'email_address' => 'required',
        ]);
        $user = User::where('email_address', $data['email_address'])->first();
        if($user != null) {
            $existing_user_reset_password = UserResetPassword::where('user_id', $user->id)
                ->where('reset_password_expires', '>', Carbon::now())->first();
            if($existing_user_reset_password == null) {
                $user_reset_password = UserResetPassword::create([
                    'user_id' => $user->id,
                    'reset_password_token' => str_random(60),
                    'reset_password_expires' => Carbon::now()->addHour()
                ]);
                $token = $user_reset_password->reset_password_token;
            }
            else {
                $token = $existing_user_reset_password->reset_password_token;
            }
            $this->sendResetPasswordMail($user->email_address, $user->first_name, $user->last_name, $token);
            if(count(Mail::failures()) > 0){
                return response()->json([
                    'message' => 'Your error message or whatever you want.'
                ]);
            }
            return response()->json([
                'message' => 'Kindly check your email for further instructions.'
            ]);
        }
        else {
            return response()->json([
                'message' => 'email doesn\'t found in our database.'
            ]);
        }
    }

    private function sendResetPasswordMail($email, $first_name, $last_name, $token) {
        Mail::to($email)->send(new ResetPasswordMail($email, $token, $first_name, $last_name));
    }

    public function resetPassword(Request $request, $token) {

        $data = $this->validate($request, [
            'email_address' => 'required',
            'new_password' => 'required',
        ]);
        $user = DB::table('user_reset_passwords')
            ->join('users', 'user_reset_passwords.user_id', '=', 'users.id')
            ->where('user_reset_passwords.reset_password_token', $token)
            ->where('users.email_address', $data['email_address'])
            ->select('users.*')
            ->first();
        if($user != null) {
            $user = User::find($user->id);
            $user->password = Hash::make($data['new_password']);
            $user->save();
            $this->sendResetPasswordDoneMail($user->email_address, $user->first_name, $user->last_name);
            if(count(Mail::failures()) > 0){
                return response()->json([
                    'message' => 'Your error message or whatever you want.'
                ]);
            }
            return response()->json([
                'message' => 'Password has been changed.'
            ]);
        }
        else {
            return response()->json([
                'message' => 'Email doesn\'t found in our database.'
            ]);
        }

    }

    public function sendResetPasswordDoneMail($email, $first_name, $last_name) {
        Mail::to($email)->send(new ResetPasswordDoneMail($first_name, $last_name));
    }

    public function findUsername(Request $request, $username)
    {
        $user = User::where('username', $username)->first();
        if($user != null) {
            return response()->json([
                'exists' => true,
                'username' => $user->username
            ]);
        }
        else {
            return response()->json([
                'exists' => false
            ]);
        }
    }

}
