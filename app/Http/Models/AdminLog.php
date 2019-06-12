<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLog extends Model {


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
	protected $table = 'admin_logs';

}
