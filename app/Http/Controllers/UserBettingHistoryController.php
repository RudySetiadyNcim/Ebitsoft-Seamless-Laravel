<?php

namespace App\Http\Controllers;

use App\UserBettingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use JWTAuth;

class UserBettingHistoryController extends Controller
{
    public function searchByDate(Request $request) {

        $data = $request->all();
        $bearer = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $bearer);

        JWTAuth::setToken($token);
        $user = JWTAuth::toUser();

        if($user != null) {

            $validator = Validator::make($data, [
                'game_ids' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
            ]);

            if($validator->fails()) {
                return response()
                    ->json([
                        'success' => false,
                        'message' => 'Validation failed.',
                        'errors' => $validator->errors()
                    ], 422);
            }

            $game_ids = explode(',', $data['game_ids']);
            array_push($game_ids, 'Deposit Balance');
            array_push($game_ids, 'Withdraw Balance');

            $query = DB::table('user_betting_histories')
                ->join('user_betting_histories_detail', function($join) {
                    $join->on('user_betting_histories.user_id', '=', 'user_betting_histories_detail.user_id');
                    $join->on('user_betting_histories.date', '=', 'user_betting_histories_detail.date');
                })
                ->where('user_betting_histories.user_id', $user->id)
                ->whereBetween('user_betting_histories.date', [$data['start_date'], $data['end_date']])
                ->whereIn('user_betting_histories_detail.remarks', $game_ids)
                ->select(
                    'user_betting_histories.date',
                    'user_betting_histories_detail.remarks',
                    'user_betting_histories_detail.total_wager',
                    'user_betting_histories_detail.turnover',
                    'user_betting_histories_detail.debit_credit',
                    'user_betting_histories_detail.commission',
                    'user_betting_histories.balance'
                );

            $row_count = $query->count();
            $result = $query->orderBy('date', 'desc')->get();

            return response()->json([
                'success' => true,
                'rowcount' => $row_count,
                'result' => [
                    'rows' => $result,
                    'metaData' => ['date', 'remarks', 'total_wager', 'turnover', 'debit_credit', 'commission', 'balance'],
                ]
            ]);

        }
        else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorised'
            ], 401);
        }

    }
}
