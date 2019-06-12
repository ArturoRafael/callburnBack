<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    /**
	 * The primary key name used by database .
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'billings';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
	    'user_id',
        'phonenumber_id',
        'archived_phonenumber_id',
        'archived_phonenumber_actions_id',
        'phonenumber_action_id',
        'invoice_id',
        'type',
        'billed_from_gift',
        'billed_from_purchased',
        'service_cost',
        'real_margin',
        'status'
    ];

    public function invoice()
    {
        return $this->hasOne('App\Models\Invoice','_id','invoice_id');
    }
}
