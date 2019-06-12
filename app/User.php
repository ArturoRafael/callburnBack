<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model implements AuthenticatableContract
{

    use Authenticatable, SoftDeletes;

    public $incrementing = false;
    public $timestamps = false;
    /**
     * Change timestamp field name.
     */
    //const CREATED_AT = 'post_date';
    //const UPDATED_AT = 'post_modified';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The primary key name used by mongo database .
     *
     * @var string
     */
    protected $primaryKey = 'email';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'firstname', 'lastname', 'phone', 'idrol', 'businessname', 'id_type_business', 
        'email_business_user'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

   

}
