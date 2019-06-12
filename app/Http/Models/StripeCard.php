<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StripeCard extends Model {

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
	protected $table = 'stripe_cards';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'last_4_digits',
		'expiration_month',
		'expiration_year',
		'card_holder_name',
		'stripe_id',
		'is_default',
		'fails_count',
	];

}
