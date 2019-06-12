<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallerId extends Model {


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
	protected $table = 'caller_ids';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'phone_number', 'is_verified', 'name', 'tariff_id'];

	public static function boot() {

	    parent::boot();

	    static::created(function($callerId) {
	    	$user = $callerId->user;
	    	if(!$user->caller_id_country_code){
				$countryCode = $callerId->tariff->country->code;
		    	$user->caller_id_country_code = $countryCode;
		    	$user->save();
	    	}
	    });
	}

	/**
	 * Get owner of the caller id
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * Get tariff of the caller id
	 */
	public function tariff()
	{
		return $this->belongsTo('App\Models\Tariff')->with('country');
	}


    public function snippet()
    {
        return $this->belongsToMany('App\Models\Snippet','snippet_transfer_numbers','caller_id','snippet_id');
    }


}
