<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsteriskServer extends Model {

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
	protected $table = 'asterisk_servers';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'ip', 'port', 'username', 'password', 'server_max_concurrent_calls',
							'remote_path', 'remote_conf_path', 'dial_out_context', 'context', 'home_path',
							'serverfarm_id'];

	public function location()
	{
		return $this->belongsTo('App\Models\Serverfarm', 'serverfarm_id');
	}

}
