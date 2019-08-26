<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class CallerId extends Eloquent {


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
	protected $table = 'caller_ids';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_email', 'phone_number', 'is_verified', 'name', 'is_verified'];

	protected $hidden = [        
        'tariff_id'
    ];

	/**
	 * Get owner of the caller id
	 */
	public function user()
	{
		return $this->belongsTo(App\Http\Models\Users::class, 'user_email', 'email');
	}

	/**
	 * Get tariff of the caller id
	 */
	public function tariff()
	{
		return $this->belongsTo(App\Http\Models\Tariff::class)->with('country');
	}
}
