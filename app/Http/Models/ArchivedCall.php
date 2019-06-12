<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedCall extends Model
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
    protected $table = 'archived_calls';

    protected $fillable = [
        '_id',
        'phonenumber_id',
        'original_id',
        'user_id',
        'isp_id',
        'call_status',
        'aserver_id',
        'duration',
        'dialled_datetime',
        'cost',
        'service_cost',
        'cost_per_minute',
        'service_cost_per_minute',
    ];

    /**
     * Get tariff of the call
     */
    public function aserver()
    {
        return $this->belongsTo('App\Models\AsteriskServer', 'aserver_id', '_id');
    }

    /**
     * Get isp of the call
     */
    public function isp()
    {
        return $this->belongsTo('App\Models\Isp');
    }

    /**
     * Get phonenumber of the call
     */
    public function phonenumber()
    {
        return $this->belongsTo('App\Models\ArchivedPhonenumber','original_id','phonenumber_id')->with('user');
    }

    /**
     * Get a user of the call
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get action of call
     */
    public function transfer()
    {
        return $this->hasOne('App\Models\ArchivedPhonenumberAction','call_id', 'original_id')->where('call_status', 'TRANSFER_ENDED')
            ->with('billing');
    }
}
