<?php

namespace App\Http\Controllers;

use App\UserBalance;
use App\UserBettingHistory;
use App\UserBettingHistoryDetail;
use App\UserDeposit;
use App\User;
use App\UserWithdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function deposit(Request $request) {
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $now = Carbon::now();
        $remarks = 'Deposit Balance';
        try {
            $user = User::find($user_id);
            $balanceAfterTrans = $user->balance + $amount;
            DB::beginTransaction();
            UserDeposit::create([
                'transaction_at' => $now,
                'user_id' => $user->id,
                'amount' => $amount,
            ]);
            UserBalance::create([
                'transaction_at' => $now,
                'user_id' => $user->id,
                'deposit' => $amount,
                'withdraw' => 0,
                'debit' => 0,
                'credit' => 0,
                'balance' => $balanceAfterTrans
            ]);
            $user = User::find($user->id);
            $user->balance = $balanceAfterTrans;
            $user->save();
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
                    'debit_credit' => $amount,
                    'commission' => 0
                ]);
            }
            else {
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
            DB::commit();
        }
        catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e);
        }
        return response()->json($user);
    }

    public function withdraw(Request $request) {
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $now = Carbon::now();
        $remarks = 'Withdraw Balance';
        try {
            $user = User::find($user_id);
            $balanceAfterTrans = $user->balance - $amount;
            DB::beginTransaction();
            UserWithdraw::create([
                'transaction_at' => $now,
                'user_id' => $user->id,
                'amount' => $amount,
            ]);
            UserBalance::create([
                'transaction_at' => $now,
                'user_id' => $user->id,
                'deposit' => 0,
                'withdraw' => $amount,
                'debit' => 0,
                'credit' => 0,
                'balance' => $balanceAfterTrans
            ]);
            $user = User::find($user->id);
            $user->balance = $balanceAfterTrans;
            $user->save();
            // Creating User Betting History Detail Report
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
                    'commission' => 0
                ]);
            }
            else {
                $user_betting_history_detail->debit_credit = $user_betting_history_detail->debit_credit - $amount;
                $user_betting_history_detail->save();
            }
            // Creating User Betting History Report
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
            DB::commit();
        }
        catch(\Exception $e) {
            DB::rollBack();
            return response()->json($e);
        }
        return response()->json($user);
    }
}
