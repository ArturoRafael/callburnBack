<?php

namespace App\Http\Models\Local;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
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
	protected $table = 'queue';

    /**
	 * Disable timestamps for asterisk tables
	 *
	 * @var string
	 */
	public  $timestamps = false;

    /**
	 * The database table primary key
	 *
	 * @var string
	 */
	protected $primaryKey = 'name';


	/**
	 * Extensions will not use auto incrementing primary key
	 *
	 * @var bool
	 */
	public $incrementing = false;
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['*'];
}
