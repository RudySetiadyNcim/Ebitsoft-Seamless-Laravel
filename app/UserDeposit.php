<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDeposit extends Model
{
    protected $table = 'user_deposits';

    protected  $fillable = [
        'transaction_at', 'user_id', 'amount'
    ];

}
