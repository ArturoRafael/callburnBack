<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model {

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
	protected $table = 'api_keys';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'description', 'type', 'key'];

	/**
	 * Get owner of the API key.
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * Get owner of the API key.
	 */
	public function campaigns()
	{
		return $this->hasMany('App\Models\Campaign');
	}

	/**
	 * Get services of the API key.
	 */
	public function services()
	{
		return $this->belongsToMany('App\Models\Service', 'api_key_services');
	}

}
