<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberFile extends Model {


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
	protected $table = 'number_files';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'original_name', 'map_name'];

}
