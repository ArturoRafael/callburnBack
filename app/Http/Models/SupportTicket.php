<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model {

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
	protected $table = 'support_tickets';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'type', 'subject', 'context', 'status', 'country_code'];


	/**
	 * Get the user who made reply
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}

	/**
	 * Get replies of the ticket
	 */
	public function replies()
	{
		return $this->hasMany('App\Models\SupportTicketReply', 'ticket_id')->with('user');
	}
}
