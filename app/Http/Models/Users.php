<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;  
use Laravel\Cashier\Billable;  
use Illuminate\Foundation\Auth\User as Authenticatable;


class Users extends Authenticatable implements JWTSubject
{

    use Notifiable;
    use Billable;

    public $incrementing = false;
    public $timestamps = false;
  
    protected $table = 'user';

    
    protected $primaryKey = 'email';

    protected $casts = [        
        'idrol' => 'int',
        'id_type_business' => 'int',
        'confirmed' => 'bool',
        'is_blocked' => 'bool'
    ];

    
    protected $fillable = [
        'email', 
        'password', 
        'firstname', 
        'lastname', 
        'phone',
        'birthday', 
        'timezone',
        'idrol',
        'language_id',
        'address',
        'postal_code',
        'country_code',
        'city', 
        'businessname', 
        'id_type_business', 
        'email_business_user',
        'stripe_customer_id',
        'confirmation_code',
        'confirmed',
        'is_blocked',
        'remember_token'
    ];

    
    protected $hidden = [
        'password', 'remember_token', 'confirmation_code', 'stripe_customer_id'
    ];

    public function rol()
    {
        return $this->belongsTo(\App\Http\Models\Rol::class, 'idrol');
    }

    public function call_id()
    {
        return $this->hasMany(\App\Http\Models\CallerId::class,  'user_email', 'email')->where('is_verified', 1);
    }

    public function balance()
    {
        return $this->belongsTo(\App\Http\Models\BalanceUser::class, 'email', 'user_email');
    }

    public function language()
    {
        return $this->belongsTo(\App\Http\Models\Language::class, 'language_id', 'id');
    }

    public function type_business()
    {
        return $this->belongsTo(\App\Http\Models\TypeBusiness::class, 'id_type_business');
    }

    public function localCards()
    {
        return $this->hasMany(\App\Http\Models\StripeCard::class, 'user_email', 'email');
    }

    public function getJWTIdentifier()      
    {           
        return $this->getKey();     
    }           

    public function getJWTCustomClaims()        
    {           
        return [
             'users' => [ 
                'email' => $this->email,
             ]
        ];      
    }

   

}
