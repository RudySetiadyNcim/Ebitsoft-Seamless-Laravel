<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_balances';

    protected $fillable = [
        'transaction_at', 'user_id', 'server_user_id', 'deposit', 'withdraw', 'debit', 'credit', 'balance'
    ];

}
