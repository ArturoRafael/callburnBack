<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model {


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
	protected $table = 'notifications';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'text', 'text_data', 'route','other_data', 'section', 'params', 'progressbar_data', 'status', 'progressbar', 'other_data'];
}
