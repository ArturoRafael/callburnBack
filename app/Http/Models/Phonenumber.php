<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Carbon\Carbon;

class Phonenumber extends Eloquent {


	/**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'phonenumbers';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];


	/************************* Define all scopes for the phonenumbers table *************/

	/**
     * Scope a query to only include the scheduled ones .
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('first_scheduled_date')->where('phonenumbers.status', 'IN_PROGRESS');
    }

	/**
	 * Get only the phonenumbers which are in progress .
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
	public function scopeInProgress($query)
	{
		return $query->where('phonenumbers.status', 'IN_PROGRESS');
	}


	/**
	 * Get only the phonenumbers which are not locked.
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
	public function scopeNotLocked($query)
	{
		return $query->where(function($newQuery) {
			$currPass1 = Carbon::now()->subSeconds(45);
			$newQuery->where('is_locked', 0)
				->orWhere('locked_at', '<', $currPass1);
		});
	}

	/**
	 * Get only the phonenumbers which should be called now
	 *
	 * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
	public function scopeToBeCalledNow($query)
	{
		return $query->where(function($newQuery) {
			$currTime = Carbon::now();
			$newQuery->whereNull('to_be_called_at')
				->orWhere('to_be_called_at', '<', $currTime);
		});
	}



	/**
	 * Get tariff of the contact
	 */
	public function tariff()
	{
		return $this->belongsTo('App\Http\Models\Tariff')->with('country', 'bestIsp', 'isps');
	}

	/**
	 * Get calls of the phonenumber
	 */
	public function calls()
	{
		return $this->hasMany('App\Http\Models\Calls');
	}

	
	public function user_balance()
	{
		return $this->belongsTo('App\Http\Models\BalanceUser');
	}

	/**
	 * Get isp of the contact
	 */
	public function isp()
	{
		return $this->belongsTo('App\Http\Models\Isp');
	}

	/**
	 * Get campaign of the contact
	 */
	public function user()
	{
		return $this->belongsTo('App\Http\Models\Users');
	}


	/**
	 * Get campaign of the contact
	 */
	public function workflow()
	{
		return $this->belongsTo('App\Http\Models\Workflow');
	}

	// /**
	//  * campaignCron
	//  */
	// public function cron()
	// {
	// 	return $this->belongsTo('App\Http\Models\Workflow', 'workflow_id', 'id')
	// 			->with(['voiceFile']);
	// }
	

	/**
	 * Get all aservers of the campaign
	 */
	public function aservers()
	{
		return $this->belongsToMany('App\Http\Models\AsteriskServer', 'aserver_phonenumber');
	}

    // /**
    //  * Get all sms actions of the phonenumber
    //  */
    // public function smsAction()
    // {
    //     return $this->hasOne('App\Http\Models\SmsAction','phonenumber_id','id');
    // }


}
