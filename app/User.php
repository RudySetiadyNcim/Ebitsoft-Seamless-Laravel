<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'first_name', 'last_name', 'balance', 'country', 'currency', 'language', 'email_address', 'mobile_number'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function saveUser($data) {
        $this->username = $data['username'];
        $this->first_name = $data['first_name'];
        $this->last_name = $data['last_name'];
        $this->balance = $data['balance'];
        $this->country = $data['country'];
        $this->currency = $data['currency'];
        $this->language = $data['language'];
        $this->password = $data['password'];
        $this->email_address = $data['email_address'];
        $this->mobile_number = $data['mobile_number'];
        $this->save();
        return $this;
    }   

    public function updateUser($data) {
        $user = $this->find($data['id']);
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->email_address = $data['email_address'];
        $user->mobile_number = $data['mobile_number'];
        $user->save();
        return $user;
    }

    public function userDetails() {
        return $this->hasMany(UserDetail::class, 'user_id');
    }
}
