<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\AddressBookService;

class Phonenumber extends Model
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
    protected $table = 'phonenumbers';

    protected $dates = [
        'first_scheduled_date',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = [
        'action_type', 'is_free', 'site_language',
        'user_id', 'tariff_id', 'campaign_id',
        'schedulation_id','snippet_id', 'retry_of', 'phone_no',
        'status', 'is_pending','is_current','retries','ip_address',
        'first_scheduled_date', 'is_from_not_eu_to_eu', 'is_locked', 'locked_at',
        'is_call_scheduled', 'last_called_at', 'to_be_called_at',
        'delivered_on', 'should_put_three_asterisks','total_duration','total_cost','type', 'comment'
    ];

    /**
     * Scope a query to only include the scheduled ones .
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeValidCTC($query)
    {
        return $query->whereNotNull('snippet_id')
            ->where(function ($query) {
                $query->whereNotNull('first_scheduled_date')
                    ->orWhereIn('status', ['TRANSFER_NOT_CONNECTED', 'TRANSFER_NOT_CONNECTED_FAILED', 'SUCCEED', 'CANCELLED', 'OUT_OF_DATE', 'FAILED'])
                    ->orWhereNotNull('retry_of')
                    ->orWhere('is_call_scheduled', 1);
            });
    }

    /**
     * Scope a query to only include the pending
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVoiceMessage($query)
    {
        return $query->whereNotNull('campaign_id');
    }

    /**
     * Scope a query to only include the scheduled ones .
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('first_scheduled_date')->where('status', 'IN_PROGRESS');
    }

    /**
     * Scope a query to only include the current call
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', 1);
    }

    /**
     * Scope a query to only include the pending
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where(function ($newQuery) {
            $newQuery->where('is_pending', 1)
                ->orWhere('status', 'TRANSFER_NOT_CONNECTED');
        });
    }

    /**
     * Get the phonenumbers's phone_no. 
     *
     * @param  string  $value
     * @return string
     */
    // public function getPhoneNoAttribute($value)
    // {
    //     // dd(AddressBookService::addThreeAsterisks($this->phone_no));
    //     // dd($value);
    //     return $this->should_put_three_asterisks
    //                 ? AddressBookService::addThreeAsterisks($value)
    //                 : $value;
    // }

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
        return $this->hasMany('App\Models\PhonenumberAction');
    }

    public function retryActions()
    {
        return $this->hasMany('App\Models\PhonenumberAction')->where('log_type', 'RETRY')->orderBy('datetime', 'DESC');
    }

    /**
     * Get all log actions
     */
    public function actionsLog()
    {
        return $this->hasMany('App\Models\PhonenumberAction')->where('log_type', 'ACTION');
    }

    /**
     * Get all log retries
     */
    public function retryLog()
    {
        return $this->hasMany('App\Models\PhonenumberAction')->where('log_type', 'RETRY');
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
        return $this->hasMany('App\Models\Call')->with('transfer');
    }

    /**
     * Get the retries of the phone number with its statuses
     */
    public function manualRetries()
    {
        return $this->hasMany('App\Models\Phonenumber', 'retry_of', '_id')->with('calls');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function totalCost()
    {
        return $this->hasMany('App\Models\Billing')->selectRaw('phonenumber_id, SUM(billed_from_gift) + SUM(billed_from_purchased) as totalCost')->groupBy('phonenumber_id');
    }

    /**
     * Get all sms actions of the phonenumber
     */
    public function smsAction()
    {
        return $this->hasOne('App\Models\SmsAction','phonenumber_id','_id');
    }

    public function blackList()
    {
        return $this->hasMany('App\Models\BlackList','phonenumber_id','_id');
    }

}
