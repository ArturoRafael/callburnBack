<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSmsCost extends Model
{
    /**
     * The primary key name used by database .
     *
     * @var string
     */
    protected $primaryKey = '_id';


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_sms_cost';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'country_id',
        'cost',
        'is_blocked',
    ];

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }
}