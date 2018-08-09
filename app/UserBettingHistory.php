<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBettingHistory extends Model
{
    protected $table = 'user_betting_histories';

    protected $fillable = [
        'user_id', 'date', 'balance'
    ];

}
