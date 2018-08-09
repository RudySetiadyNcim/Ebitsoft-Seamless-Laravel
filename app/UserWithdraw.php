<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWithdraw extends Model
{
    protected $table = 'user_withdraws';

    protected  $fillable = [
        'transaction_at', 'user_id', 'amount'
    ];

}
