<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedPhonenumberAction extends Model
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
    protected $table = 'archived_phonenumber_actions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'phonenumber_id', 'original_id', 'call_status' ,'log_type', 'datetime', 'duration', 'transfered_to'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function billing()
    {
        return $this->hasOne('App\Models\Billing','archived_phonenumber_actions_id', 'original_id');
    }
}
