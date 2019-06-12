<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model {

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
	protected $table = 'countries';

	/**
	 * Deactive timestamps columns
	 */
	public $timestamps = false;


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];

    protected $hidden = [

        'api_free_message_max_duration', 'api_free_message_promo', 'api_free_messages_count_per_day', 'free_call_minimum_margin',
        'free_tts_count_per_day', 'is_blocked', 'minimum_margin', 'mobile_free_message_max_duration',
        'mobile_free_message_promo', 'mobile_free_messages_count_per_day', 'mobile_welcome_credit', 'tts_configuration_id',
        'tts_price', 'verification_call_callerid', 'verification_call_language_code', 'web_free_message_max_duration',
        'web_free_message_promo', 'web_free_messages_count_per_day', 'web_welcome_credit','sms_customer_price','best_sms_isp_id'


    ];

    /**
     * get Sms Tariffs with pivot.
     */
    public function smsTariffs()
    {
        return $this->belongsToMany('App\Models\Isp', 'sms_tariffs','country_id','isp_id')
            ->withPivot(['_id','cost', 'is_blocked', 'is_disabled', 'is_deleted', 'name'])->withTimestamps();
    }

}
