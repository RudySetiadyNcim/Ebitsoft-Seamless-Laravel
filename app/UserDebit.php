<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDebit extends Model
{
    protected $table = 'user_debits';

    protected  $fillable = [
        'game', 'transaction_at', 'user_id', 'amount', 'transaction_id', 'refId', 'product_id', 'table_id', 'game_identifier'
    ];

}
