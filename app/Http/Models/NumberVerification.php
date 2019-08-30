<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class NumberVerification extends Model {

	/**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	protected $table = 'number_verifications';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id' ,
		'user_email', 
		'phone_number', 
		'code', 
		'retries', 
		'created_at', 
		'updated_at'
	];

	public function users()
	{
		return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
	}

	

}
