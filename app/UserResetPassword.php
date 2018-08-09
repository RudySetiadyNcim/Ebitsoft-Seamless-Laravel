<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserResetPassword extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_reset_passwords';

    protected $fillable = [
        'user_id', 'reset_password_token', 'reset_password_expires',
    ];

}
