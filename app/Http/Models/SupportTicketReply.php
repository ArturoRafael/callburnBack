<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketReply extends Model {

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
	protected $table = 'support_ticket_replies';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'ticket_id', 'message'];

	/**
	 * Get the user who made reply
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}

}
