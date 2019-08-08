<?php

namespace App\Http\Models\Local;

use Illuminate\Database\Eloquent\Model;

class AudioFile extends Model
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
	protected $table = 'audio_files';

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
}
