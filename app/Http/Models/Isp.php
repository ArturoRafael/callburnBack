<?php 

namespace  App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Isp extends Eloquent {

	
	protected $primaryKey = 'id';

	protected $table = 'isps';

	protected $fillable = [
		'name', 
		'config', 
		'status', 
		'can_call_from_not_eu_to_eu', 
		'is_active',
		'blocked_at'
	];


}
