<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileMessageGroup extends Model {

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
	protected $table = 'mobile_message_groups';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'image', 'user_id'];

	/**
	 * Get owner of the API key.
	 */
	public function campaigns()
	{
		return $this->hasMany('App\Models\Campaign')
			->with(['voiceFile', 'successPhonenumbers', 'totalPhonenumbers',
					'costPhonenumbers', 
					'schedulations' => function($query){
						$query->where('is_finished', 0);
			}]);
	}


}
