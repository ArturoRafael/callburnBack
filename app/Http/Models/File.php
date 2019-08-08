<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class File extends Eloquent {

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
	protected $table = 'files';

	/**
	 * Deactive timestamps columns
	 */
	public $timestamps = true;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'orig_filename', 
		'map_filename', 
		'extension', 
		'stripped_name', 
		'user_email', 
		'tts_text', 
		'tts_language', 
		'length', 
		'is_template', 
		'type', 
		'cost',
		'saved_from',
	];

	public function users()
	{
		return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
	}
}
