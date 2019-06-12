<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model {


	/**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';

	protected $table = 'coupons';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['discount_percentage', 'type', 'user_id', 'code', 'type', 'minimum_amount'];

	/**
	 * get Users of the coupon.
	 *
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}
}
