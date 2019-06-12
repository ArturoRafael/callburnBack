<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model {


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
	protected $table = 'calls';

	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['aserver_id', 'isp_id', 'service_cost', 'service_cost_per_minute','cost_per_minute'];

	/**
	 * Get action of call
	 */
	public function transfer()
	{
		return $this->hasOne('App\Models\PhonenumberAction')->where('call_status', 'TRANSFER_ENDED')
			->with('billing');
	}

}
