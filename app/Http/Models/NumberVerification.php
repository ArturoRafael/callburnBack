<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NumberVerification extends Model {


	/**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';

	protected $table = 'number_verifications';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'phone_number', 'code', 'tariff_id'];


	/**
	 * Get tariff of the number verification
	 */
	public function tariff()
	{
		return $this->belongsTo('App\Models\Tariff')->with('country');
	}
}
