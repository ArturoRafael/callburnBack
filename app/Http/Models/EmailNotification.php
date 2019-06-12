<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    /**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'type', 'is_sent', 'other_data', 'campaign_id', 'file_format'];

	/**
	 * get Users of the notification.
	 *
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * get Users of the coupon.
	 *
	 */
	public function campaign()
	{
		return $this->belongsTo('App\Models\Campaign');
	}

}
