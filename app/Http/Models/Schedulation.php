<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedulation extends Model {

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
	protected $table = 'schedulations';

	/**
	 * Get campaign of the schedule.
	 */
	public function campaign()
	{
		return $this->belongsTo('App\Models\Campaign');
	}

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['scheduled_date', 'success_calls_limit' ,'calls_limit', 'is_finished', 
		'campaign_id', 'calling_interval_minutes', 'delivery_speed', 'recipients'];

}
