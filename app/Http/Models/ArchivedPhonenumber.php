<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArchivedPhonenumber extends Model
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
    protected $table = 'archived_phonenumbers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'action_type', 'is_free', 'site_language',
        'user_id', 'original_id', 'tariff_id', 'campaign_id',
        'schedulation_id','snippet_id', 'retry_of', 'phone_no',
        'status', 'is_pending','is_current','retries','ip_address',
        'first_scheduled_date', 'is_from_not_eu_to_eu', 'is_locked', 'locked_at',
        'is_call_scheduled', 'last_called_at', 'to_be_called_at',
        'delivered_on', 'should_put_three_asterisks','total_duration','total_cost','type', 'comment'
    ];


    /**
     * Get tariff of the contact
     */
    public function tariff()
    {
        return $this->belongsTo('App\Models\Tariff')->with('country');
    }

    /**
     * Get campaign of the contact
     */
    public function campaign()
    {
        return $this->belongsTo('App\Models\Campaign')->with(['voiceFile']);
    }

    /**
     * Get all actions of the phonenumber
     */
    public function actions()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumberAction','phonenumber_id', 'original_id');
    }

    public function retryActions()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumberAction' ,'phonenumber_id', 'original_id')->where('log_type', 'RETRY')->orderBy('datetime', 'DESC');
    }

    /**
     * Get all log actions
     */
    public function actionsLog()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumberAction','phonenumber_id', 'original_id')->where('log_type', 'ACTION');
    }

    /**
     * Get all log retries
     */
    public function retryLog()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumberAction','phonenumber_id', 'original_id')->where('log_type', 'RETRY');
    }

    /**
     * Get all contacts
     */
    public function contacts()
    {
        return $this->belongsTo('App\Models\AddressBookContact', 'phone_no', 'phone_number');
    }


    public function calls()
    {
        return $this->hasMany('App\Models\ArchivedCall','phonenumber_id', 'original_id')->with('transfer');
    }

    /**
     * Get the retries of the phone number with its statuses
     */
    public function manualRetries()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumber', 'retry_of', 'original_id')->with('calls');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function totalCost()
    {
        return $this->hasMany('App\Models\Billing','archived_phonenumber_id', 'original_id')->selectRaw('archived_phonenumber_id, SUM(billed_from_gift) + SUM(billed_from_purchased) as totalCost')->groupBy('archived_phonenumber_id');
    }

    /**
     * Get all sms actions of the phonenumber
     */
    public function smsAction()
    {
        return $this->hasOne('App\Models\SmsAction','archived_phonenumber_id','original_id');
    }

    public function blackList()
    {
        return $this->hasMany('App\Models\BlackList','archived_phonenumber_id','original_id');
    }

}
