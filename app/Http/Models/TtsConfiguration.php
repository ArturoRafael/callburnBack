<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TtsConfiguration extends Model {

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
	protected $table = 'tts_configurations';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['language_name', 'google_tts_code', 'google_tts_speed'];

}
