<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserImportation extends Model
{
    /**
	 * The primary key name used by database .
	 *
	 * @var string
	 */
	protected $primaryKey = '_id';


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user_importations';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['tag', 'welcome_bonus', 'bonus_criteria'];
}
