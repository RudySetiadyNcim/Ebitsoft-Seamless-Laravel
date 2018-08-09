<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBettingHistoryDetail extends Model
{
    protected $table = 'user_betting_histories_detail';

    protected $fillable = [
        'user_id', 'date', 'remarks', 'total_wager', 'turnover', 'debit_credit', 'commission'
    ];

}
