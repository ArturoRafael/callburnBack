<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsAction extends Model
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
    protected $table = 'sms_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id',
        'isp_id',
        'phonenumber_id',
        'service_cost',
        'customer_cost',
        'status',
        'response_id',
    ];

    public function response()
    {
        return $this->hasOne('App\Models\SmsResponse','_id','response_id');
    }
}