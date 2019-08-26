<?php 

namespace  App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Language extends Eloquent {


	/**
	 * The primary key name used by mongo database .
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'languages';

	/**
	 * Deactive timestamps columns
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'full_name', 'code', 'json' ];

	
	public function users()
    {
        return $this->hasMany(App\Http\Models\Users::class, 'language_id', 'id');
    }

}
