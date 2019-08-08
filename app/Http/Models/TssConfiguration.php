<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class TssConfiguration extends Eloquent {


	protected $primaryKey = 'id';
	public $timestamps = true;
	protected $table = 'tts_configurations';

	protected $fillable = [
		'language_name',
		'google_tts_code', 
		'google_tts_speed', 		 
	];

	
}
