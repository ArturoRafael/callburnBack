<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvalidContact extends Model
{
    /**
     * The primary key name used by mongo database .
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'invalid_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'group_id',
        'upload_id',
        'file_importation_id',
        'phone_number',
        'name'
    ];

}