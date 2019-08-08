<?php

namespace App\Http\Models\Local;

use Illuminate\Database\Eloquent\Model;

class CbTable extends Model
{
    /**
	 * Set default connection for the table
	 * This is local asterisk table , so connection will be local
	 *
	 * @var string
	 */
	protected $connection = 'local_mysql';

    /**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'cbtable';

    /**
	 * Disable timestamps for asterisk tables
	 *
	 * @var string
	 */
	public  $timestamps = false;
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['*'];

	public function ctcConnected()
	{
		return $this->hasOne('App\Models\Local\CbTable', 'recid', 'recid')->where('action', 'CTC CONNECTED');
	}

	public function ctcRequested()
	{
		return $this->hasOne('App\Models\Local\CbTable', 'recid', 'recid')->where('action', 'CTC REQUESTED');
	}

	public function liveConnected()
	{
		return $this->hasOne('App\Models\Local\CbLive', 'recid', 'recid');
	}

	public function call()
	{
		return $this->hasOne('App\Models\Call', '_id', 'recid')->with('phonenumber');
	}
}
