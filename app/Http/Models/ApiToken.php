<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class ApiToken extends Model {

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
	protected $table = 'api_tokens';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'api_token', 'api_token_validity', 'session_id'];

	/**
	 * Get owner of the API key.
	 */
	public function user()
	{
		return $this->belongsTo('App\User')->where('is_deleted', '!=', 1)->where('role', 'client')
			->with(['numbers', 'city', 'country', 'language', 'apiTokens', 'notifications' => function($query){
				return $query->skip(0)->take(10);
			}]);
	}

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['session_id'];
}
