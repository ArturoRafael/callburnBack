<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tariff extends Model {


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
	protected $table = 'tariffs';

	/**
	 * Deactive timestamps columns
	 */
	//public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['prefix', 'country_id', 'city_id', 
		'is_blocked', 'standard_price', 'is_main_tariff' ];

    protected $hidden = [
        'best_isp_id','best_margin', 'billed_amount',
        'is_blocked','is_deleted', 'is_disabled',

    ];

	/**
	 * Create relation with cities.
	 *
	 */
	public function city()
	{
		return $this->belongsTo('App\Models\City');
	}

	/**
	 * get Tariffs with pivot.
	 */
	public function isps()
	{
		return $this->belongsToMany('App\Models\Isp', 'tariffs_isps')->withPivot('cost');
	}

	/**
	 * Get country of the tariff.
	 */
	public function country()
	{
		return $this->belongsTo('App\Models\Country');
	}
	
	/**
	 * Get active isp of the tariff
	 */
	public function bestIsp(){
		return $this->belongsTo('App\Models\Isp', 'best_isp_id', '_id');
	}

}
