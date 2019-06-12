<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable; 
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;


class Users extends Model implements AuthenticatableContract
{

    use Authenticatable, Notifiable;

    public $incrementing = false;
    public $timestamps = false;
  
    protected $table = 'user';

    
    protected $primaryKey = 'email';

    protected $casts = [        
        'idrol' => 'int',
        'id_type_business' => 'int'
    ];

    
    protected $fillable = [
        'email', 'password', 'firstname', 'lastname', 'phone', 'idrol', 'businessname', 'id_type_business', 
        'email_business_user'
    ];

    
    protected $hidden = [
        'password'
    ];

   

}
