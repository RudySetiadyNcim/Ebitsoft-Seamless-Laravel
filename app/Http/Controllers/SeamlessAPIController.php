<?php

namespace App\Http\Controllers;

use App\APIHistory;
use App\UserBalance;
use App\UserBettingHistory;
use App\UserBettingHistoryDetail;
use App\UserCredit;
use App\UserDebit;
use App\UserTip;
use App\User;
use App\UserDetail;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeamlessAPIController extends Controller
{
    public function getRemarks($product_id) {
        switch($product_id) {
            case 1:
                return 'Evolution Live';
            case 2:
                return 'Porn Hub';
        }
    }

    public function auth(Request $request) {
        $currentUser = $request->input('user');
        $game = $request->input('game');
        $token = config('app.token');
        $hostname = config('app.hostname');
        $request_url = $hostname.'/auth';
        $client = new Client();
        $body = [
            'token' => $token,
            'user' => $currentUser,
            'game' => $game
        ];
        $apiHistory = APIHistory::create([
            'api' => 'auth',
            'req' => json_encode($body),
        ]);
        $json = $client->post($request_url, [
            'body' => json_encode($body)
        ])->getBody()->getContents();
        $apiHistory->res = $json;
        $apiHistory->save();
        $obj = json_decode($json);
        if($obj->{'success'} == 1) {
            UserDetail::updateOrCreate([
                'user_id' => $currentUser['id'],
                'token' => $token,
                'server_user_id' => $obj->{'userId'},
                'server_username' => $obj->{'username'}
            ]);
        }
        return response()->json($json);
    }

    public function debit(Request $request) {
        $game = $request->input('game');
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $transaction_id = $request->input('transaction_id');
        $refId = $request->input('refId');
        $product_id = $request->input('product_id');
        $table_id = $request->input('table_id');
        $game_identifier = $request->input('game_identifier');
        $now = Carbon::now();
        $apiHistory = APIHistory::create([
            'api' => 'debit',
            'req' => json_encode($request->all()),
        ]);
        try {
            $user = DB::table('users')
                ->join('user_details', 'users.id', '=', 'user_details.user_id')
                ->where('user_details.server_user_id', $user_id)
                ->select('users.*')
                ->first();
            if($user == null) {
                // USER_NOT_EXIST
                $json = [
                    'error' => '1',
                    'message' => 'USER_NOT_EXIST'
                ];
            }
            else {
                $lastDebit = UserDebit::where('transaction_id', $transaction_id)->first();
                if($lastDebit != null) {
                    // BET_ALREADY_EXIST
                    $json = [
                        'error' => '1',
                        'message' => 'BET_ALREADY_EXIST'
                    ];
                }
                else {
                    $remainingBalance = $user->balance - $amount;
                    if($remainingBalance < 0) {
                        // INSUFFICIENT_FUNDS
                        $json = [
                            'error' => '1',
                            'message' => 'INSUFFICIENT_FUNDS'
                        ];
                    }
                    else {
                        DB::beginTransaction();
                        UserDebit::create([
                            'game' => $game,
                            'transaction_at' => $now,
                            'user_id' => $user_id,
                            'amount' => $amount,
                            'transaction_id' => $transaction_id,
                            'refId' => $refId,
                            'product_id' => $product_id,
                            'table_id' => $table_id,
                            'game_identifier' => $game_identifier,
                        ]);
                        $json = [
                            'error' => '',
                            'balance' => $remainingBalance
                        ];
                        // Begin Creating User Betting History Detail Report
                        $user_betting_history_detail = UserBettingHistoryDetail::where('user_id', $user->id)
                            ->where('date', $now->toDateString())
                            ->where('remarks', $this->getRemarks($product_id))
                            ->first();
                        if($user_betting_history_detail == null) {
                            UserBettingHistoryDetail::create([
                                'user_id' => $user->id,
                                'date' => $now->toDateString(),
                                'remarks' => $this->getRemarks($product_id),
                                'total_wager' => 1,
                                'turnover' => 0,
                                'debit_credit' => -$amount,
                                'commission' => 0
                            ]);
                        }
                        else {
                            $user_betting_history_detail->total_wager = $user_betting_history_detail->total_wager + 1;
                            $user_betting_history_detail->debit_credit = $user_betting_history_detail->debit_credit - $amount;
                            $user_betting_history_detail->save();
                        }
                        // End Creating User Betting History Detail Report
                        // Begin Creating User Betting History Report
                        $user_betting_history = UserBettingHistory::where('user_id', $user->id)
                            ->where('date', $now->toDateString())
                            ->first();
                        if($user_betting_history == null) {
                            UserBettingHistory::create([
                                'user_id' => $user->id,
                                'date' => $now->toDateString(),
                                'balance' => $remainingBalance
                            ]);
                        }
                        else {
                            $user_betting_history->balance = $remainingBalance;
                            $user_betting_history->save();
                        }
                        // End Creating User Betting History Report
                        if($amount != 0) {
                            UserBalance::create([
                                'transaction_at' => $now,
                                'user_id' => $user->id,
                                'server_user_id' => $user_id,
                                'deposit' => 0,
                                'withdraw' => 0,
                                'debit' => $amount,
                                'credit' => 0,
                                'balance' => $remainingBalance
                            ]);
                        }
                        $user = User::find($user->id);
                        $user->balance = $remainingBalance;
                        $user->save();
                        DB::commit();
                    }
                }
            }
        }
        catch(\Exception $e) {
            dd($e);
            $json = [
                'error' => '1',
                'message' => 'UNKNOWN_ERROR'
            ];
            DB::rollBack();
        }
        $apiHistory->res = json_encode($json);
        $apiHistory->save();
        return response()->json($json);
    }

    public function credit(Request $request) {
        $game = $request->input('game');
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $transaction_id = $request->input('transaction_id');
        $refId = $request->input('refId');
        $product_id = $request->input('product_id');
        $table_id = $request->input('table_id');
        $game_identifier = $request->input('game_identifier');
        $now = Carbon::now();
        $apiHistory = APIHistory::create([
            'api' => 'credit',
            'req' => json_encode($request->all()),
        ]);
        try {
            $user = DB::table('users')
                ->join('user_details', 'users.id', '=', 'user_details.user_id')
                ->where('user_details.server_user_id', $user_id)
                ->select('users.*')
                ->first();
            if($user == null) {
                // USER_NOT_EXIST
                $json = [
                    'error' => '1',
                    'message' => 'USER_NOT_EXIST'
                ];
            }
            else {
                $lastDebit = UserDebit::where('refId', $refId)->first();
                if($lastDebit == null) {
                    // BET_DOES_NOT_EXIST
                    $json = [
                        'error' => '1',
                        'message' => 'BET_DOES_NOT_EXIST'
                    ];
                }
                else {
                    $lastCredit = UserCredit::where('refId', $refId)->first();
                    if($lastCredit != null) {
                        // BET_ALREADY_SETTLED
                        $json = [
                            'error' => '1',
                            'message' => 'BET_ALREADY_SETTLED'
                        ];
                    }
                    else {
                        $user = User::find($user->id);
                        DB::beginTransaction();
                        $balanceAfterTrans = $user->balance + $amount;
                        UserCredit::create([
                            'game' => $game,
                            'transaction_at' => $now,
                            'user_id' => $user_id,
                            'amount' => $amount,
                            'transaction_id' => $transaction_id,
                            'refId' => $refId,
                            'product_id' => $product_id,
                            'table_id' => $table_id,
                            'game_identifier' => $game_identifier,
                        ]);
                        $json = [
                            'error' => '',
                            'balance' => $balanceAfterTrans
                        ];
                        // Begin Creating User Betting History Detail Report
                        $user_betting_history_detail = UserBettingHistoryDetail::where('user_id', $user->id)
                            ->where('date', $now->toDateString())
                            ->where('remarks', $this->getRemarks($product_id))
                            ->first();
                        if($user_betting_history_detail == null) {
                            UserBettingHistoryDetail::create([
                                'user_id' => $user->id,
                                'date' => $now->toDateString(),
                                'remarks' => $this->getRemarks($product_id),
                                'total_wager' => 1,
                                'turnover' => 0,
                                'debit_credit' => $amount,
                                'commission' => 0
                            ]);
                        }
                        else {
                            $user_betting_history_detail->turnover = $user_betting_history_detail->turnover + abs($lastDebit->amount - $amount);
                            $user_betting_history_detail->debit_credit = $user_betting_history_detail->debit_credit + $amount;
                            $user_betting_history_detail->save();
                        }
                        // End Creating User Betting History Detail Report
                        // Begin Creating User Betting History Report
                        $user_betting_history = UserBettingHistory::where('user_id', $user->id)
                            ->where('date', $now->toDateString())
                            ->first();
                        if($user_betting_history == null) {
                            UserBettingHistory::create([
                                'user_id' => $user->id,
                                'date' => $now->toDateString(),
                                'balance' => $balanceAfterTrans
                            ]);
                        }
                        else {
                            $user_betting_history->balance = $balanceAfterTrans;
                            $user_betting_history->save();
                        }
                        // End Creating User Betting History Report
                        if($amount != 0) {
                            UserBalance::create([
                                'transaction_at' => $now,
                                'user_id' => $user->id,
                                'server_user_id' => $user_id,
                                'deposit' => 0,
                                'withdraw' => 0,
                                'debit' => 0,
                                'credit' => $amount,
                                'balance' => $balanceAfterTrans
                            ]);
                        }
                        $user->balance = $balanceAfterTrans;
                        $user->save();
                        DB::commit();
                    }
                }
            }
        }
        catch(\Exception $e) {
            $json = [
                'error' => '1',
                'message' => 'UNKNOWN_ERROR'
            ];
            DB::rollBack();
        }
        $apiHistory->res = json_encode($json);
        $apiHistory->save();
        return response()->json($json);
    }

    public function cancel(Request $request) {
        APIHistory::create([
            'api' => 'cancel',
            'req' => json_encode($request->all()),
        ]);
        return response()->json($request->all());
    }

    public function tips(Request $request) {
        $game = $request->input('game');
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $transaction_id = $request->input('transaction_id');
        $refId = $request->input('refId');
        $product_id = $request->input('product_id');
        $tips = $request->input('tips');
        $table_id = $request->input('table_id');
        $game_identifier = $request->input('game_identifier');
        $now = Carbon::now();
        $remarks = 'Tips';
        $apiHistory = APIHistory::create([
            'api' => 'tips',
            'req' => json_encode($request->all()),
        ]);
        try {
            $user = DB::table('users')
                ->join('user_details', 'users.id', '=', 'user_details.user_id')
                ->where('user_details.server_user_id', $user_id)
                ->select('users.*')
                ->first();
            if($user == null) {
                // USER_NOT_EXIST
                $json = [
                    'error' => '1',
                    'message' => 'USER_NOT_EXIST'
                ];
            }
            else {
                $remainingBalance = $user->balance - $amount;
                if($remainingBalance < 0) {
                    // INSUFFICIENT_FUNDS
                    $json = [
                        'error' => '1',
                        'message' => 'INSUFFICIENT_FUNDS'
                    ];
                }
                else {
                    DB::beginTransaction();
                    UserTip::create([
                        'game' => $game,
                        'transaction_at' => $now,
                        'user_id' => $user_id,
                        'amount' => $amount,
                        'transaction_id' => $transaction_id,
                        'refId' => $refId,
                        'product_id' => $product_id,
                        'tips' => $tips,
                        'table_id' => $table_id,
                        'game_identifier' => $game_identifier,
                    ]);
                    $json = [
                        'error' => '',
                        'balance' => $remainingBalance
                    ];
                    // Begin Creating User Betting History Detail Report
                    $user_betting_history_detail = UserBettingHistoryDetail::where('user_id', $user->id)
                        ->where('date', $now->toDateString())
                        ->where('remarks', $remarks)
                        ->first();
                    if($user_betting_history_detail == null) {
                        UserBettingHistoryDetail::create([
                            'user_id' => $user->id,
                            'date' => $now->toDateString(),
                            'remarks' => $remarks,
                            'total_wager' => 0,
                            'turnover' => 0,
                            'debit_credit' => -$amount,
                            'commission' => 0,
                        ]);
                    }
                    else {
                        $user_betting_history_detail->debit_credit = $user_betting_history_detail->debit_credit - $amount;
                        $user_betting_history_detail->save();
                    }
                    // End Creating User Betting History Detail Report
                    // Begin Creating User Betting History Report
                    $user_betting_history = UserBettingHistory::where('user_id', $user->id)
                        ->where('date', $now->toDateString())
                        ->first();
                    if($user_betting_history == null) {
                        UserBettingHistory::create([
                            'user_id' => $user->id,
                            'date' => $now->toDateString(),
                            'balance' => $remainingBalance
                        ]);
                    }
                    else {
                        $user_betting_history->balance = $remainingBalance;
                        $user_betting_history->save();
                    }
                    // End Creating User Betting History Report
                    if($amount != 0) {
                        UserBalance::create([
                            'transaction_at' => $now,
                            'user_id' => $user->id,
                            'server_user_id' => $user_id,
                            'deposit' => 0,
                            'withdraw' => 0,
                            'debit' => $amount,
                            'credit' => 0,
                            'balance' => $remainingBalance
                        ]);
                    }
                    $user = User::find($user->id);
                    $user->balance = $remainingBalance;
                    $user->save();
                    DB::commit();
                }
            }
        }
        catch(\Exception $e) {
            $json = [
                'error' => '1',
                'message' => 'UNKNOWN_ERROR'
            ];
            DB::rollBack();
        }
        $apiHistory->res = json_encode($json);
        $apiHistory->save();
        return response()->json($json);
    }

    public function getCurrentBalance(Request $request) {
        $user_id = $request->input('user_id');
        $apiHistory = APIHistory::create([
            'api' => 'get-current-balance',
            'req' => json_encode($request->all()),
        ]);
        $user = DB::table('users')
            ->join('user_details', 'users.id', '=', 'user_details.user_id')
            ->where('user_details.server_user_id', $user_id)
            ->select('users.*')
            ->first();
        if($user == null) {
            // USER_NOT_EXIST
            $json = [
                'error' => '1',
                'message' => 'USER_NOT_EXIST'
            ];
        }
        else {
            $json = [
                'error' => '',
                'balance' => $user->balance
            ];
        }
        $apiHistory->res = json_encode($json);
        $apiHistory->save();
        return response()->json($json);
    }
}
