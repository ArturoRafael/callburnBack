<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;    
use Illuminate\Foundation\Auth\User as Authenticatable;


class Users extends Authenticatable implements JWTSubject
{

    use Notifiable;

    public $incrementing = false;
    public $timestamps = false;
  
    protected $table = 'user';

    
    protected $primaryKey = 'email';

    protected $casts = [        
        'idrol' => 'int',
        'id_type_business' => 'int',
        'confirmed' => 'bool'
    ];

    
    protected $fillable = [
        'email', 
        'password', 
        'firstname', 
        'lastname', 
        'phone', 
        'idrol', 
        'businessname', 
        'id_type_business', 
        'email_business_user',
        'confirmation_code',
        'confirmed',
        'remember_token'
    ];

    
    protected $hidden = [
        'password', 'remember_token', 'confirmation_code'
    ];

    public function rol()
    {
        return $this->belongsTo(\App\Http\Models\Rol::class, 'idrol');
    }

    public function type_business()
    {
        return $this->belongsTo(\App\Http\Models\TypeBusiness::class, 'id_type_business');
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
