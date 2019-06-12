<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model {

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
	protected $table = 'cities';

	/**
	 * Deactive timestamps columns
	 */
	public $timestamps = false;

}
