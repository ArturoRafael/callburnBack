<?php 

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class Invoice extends Eloquent {
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
	protected $table = 'invoices';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

    protected $dates = ['deleted_at'];

	protected $fillable = [
		'user_email',
		'id_workflow',
		'customer_name',
		'customer_address',
		'customer_postal_code',
		'customer_city',
		'customer_country_code',
		'invoice_date',		
		'order_number',
		'receipt_url',		
		'total_amount',		
		'currency',
		'method',		
		'type',	
		'description',
		'deleted_at'	
	];
	
	protected $hidden = [];


	public function users()
	{
		return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
	}


	public function workflows()
	{
		return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow');
	}


}
