<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTip extends Model
{
    protected $table = 'user_tips';

    protected  $fillable = [
        'game', 'transaction_at', 'user_id', 'amount', 'transaction_id', 'refId', 'product_id', 'tips', 'table_id', 'game_identifier'
    ];
}
