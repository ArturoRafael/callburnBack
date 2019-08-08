<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Country extends Eloquent 
{
	
	protected $primaryKey = '_id';
	
	protected $table = 'countries';
	
	public $timestamps = false;

	protected $fillable = [
		'code',
		'name',
		'phonenumber_prefix',
		'original_name',
		'phonenumber_example',
        'customer_price',
        'sms_customer_price'
	];

    protected $hidden = [

        'is_blocked', 
        'minimum_margin',
        'minimum_margin_for_sms',
        'is_eu_member',
        'mobile_welcome_credit', 
        'tts_price',
        'free_tts_count_per_day',
        'verification_call_callerid', 
        'verification_call_language_code', 
        'web_welcome_credit',
        
        


    ];

   
}
