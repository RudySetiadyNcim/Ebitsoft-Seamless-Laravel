<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_details';

    protected $fillable = [
        'user_id', 'token', 'server_user_id', 'server_username'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
