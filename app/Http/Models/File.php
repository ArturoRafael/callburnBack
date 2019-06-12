<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model {

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
	protected $fillable = ['orig_filename', 'map_filename', 'extension', 'stripped_name', 
						'user_id', 'tts_text', 'tts_language', 'length', 'is_template', 'type', 'cost', 'saved_from'];


	public function snippet()
	{
		return $this->hasOne('App\Models\Snippet');
	}
}
