<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonenumberAction extends Model {

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
	protected $table = 'phonenumber_actions';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['phonenumber_id', 'call_status' ,'log_type', 'datetime', 'duration'];

	/**
	 * Get action of call
	 */
	public function billing()
	{
		return $this->hasOne('App\Models\Billing');
	}

}
