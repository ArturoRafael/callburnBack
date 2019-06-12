<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundJob extends Model {

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
	protected $table = 'background_jobs';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['type', 'file_path', 'original_file_name', 'user_id','group_id', 'status', 'complete_percentage', 'data'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['file_path'];


	public function user()
	{
		return $this->belongsTo('App\User');
	}


    public function group()
    {
        return $this->belongsTo('App\Models\AddressBookGroup','group_id');
    }
}
