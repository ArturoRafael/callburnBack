<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model {

    use SoftDeletes;
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
	protected $table = 'invoices';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */

    protected $dates = ['deleted_at'];

	protected $fillable = [
		'user_id',
		'customer_name',
		'customer_address',
		'customer_postal_code',
		'customer_city',
		'customer_country_code',
		'invoice_date',
		'invoice_number',
		'order_number',
		'purchased_amount',
		'vat_amount',
		'vat_percentage',
		'vat_id',
		'total_amount',
		'discount_amount',
		'discount_percentage',
		'discount_coupon_code',
		'currency',
		'method',
		'is_paid',
		'type',
		'transaction_id',
		'refund_id',
		'yearmonth_id',
		'minimum_margin_criteria_for_gift',
		'remaining_gift_amount',
		'current_balance_after_billing',
        'is_manual'
	];
	
	protected $hidden = ['paypal_json', 'transaction_id', 'refund_id', 'yearmonth_id', 'minimum_margin_criteria_for_gift', 'remaining_gift_amount'];

	// public static function boot() {

	//     parent::boot();

	//     static::created(function($invoice) {
	//     	if($invoice->type == 'TRANSACTION') {
	//     		\App\User::where('_id', $invoice->user_id)
	// 	    		->update([
	// 	    			'is_autobilling_made' => 0,
	// 	    			'is_low_balance_notification_send' => 0,
	//     			]);
	//     	}
	    	
	//     });
	// }

}
