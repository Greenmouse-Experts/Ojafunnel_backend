<?php

namespace App\Models;

use App\Notifications\VerifyEmailPIN;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected  $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendApiEmailVerificationTokenNotification(){
        $token = rand(100000, 999999); //4 digits
        $emailVerification = new EmailVerification();
        $emailVerification->token = Hash::make($token);
        $emailVerification->email = $this->email;
        $emailVerification->used = 'no';
        $emailVerification->save();

        $delay = now()->addSeconds(5);
        $this->notify ((new VerifyEmailPIN ($token))->delay ($delay));
    }

    public function accounts()
    {
        return $this->hasMany('App\Models\Account');
    }

    public function cards()
    {
        return $this->hasMany('App\Models\Card');
    }

    public function userDetail()
    {
        return $this->hasOne('App\Models\UserDetail');
    }

    public function withdrawalAccount()
    {
        return $this->hasMany('App\Models\WithdrawalAccount');
    }

    public function userReferral()
    {
        return $this->hasMany(UserReferral::class);
    }
    
    public function userReferralRef()
    {
        return $this->hasMany(UserReferral::class, 'referred_by');
    }
    
    public function userReferralTransaction()
    {
        return $this->hasMany(UserReferralTransaction::class);
    }
    
    public function userReferralTransactionRef()
    {
        return $this->hasMany(UserReferralTransaction::class, 'user_id_ref');
    }
}
