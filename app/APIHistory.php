<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class APIHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'api_histories';

    protected $fillable = [
        'api', 'req', 'res'
    ];

}
