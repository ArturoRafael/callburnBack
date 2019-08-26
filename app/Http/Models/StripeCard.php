<?php 

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

class StripeCard extends Eloquent {

	/**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

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
		'user_email',
		'last_4_digits',
		'expiration_month',
		'expiration_year',
		'card_holder_name',
		'stripe_id',
		'is_default',
		'fails_count',
	];


	public function user()
    {
        return $this->belongsTo(\App\Http\Models\Users::class, 'user_email', 'email');
    }

}
