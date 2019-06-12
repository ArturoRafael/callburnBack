<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Isp extends Model {

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
	protected $table = 'isps';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'config', 'status'];

	/**
	 * get Tariffs with pivot.
	 */
	public function tariffs()
	{
		return $this->belongsToMany('App\Models\Tariff', 'tariffs_isps')->withPivot('cost');
	}

}
