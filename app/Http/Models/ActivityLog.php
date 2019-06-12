<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model {

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
	protected $table = 'activity_logs';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['ip_address', 'user_id', 'campaign_id', 'device', 'action', 'description'];

}
