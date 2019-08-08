<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Calls extends Eloquent {


	protected $primaryKey = 'id';	
	protected $table = 'calls';
	public $timestamps = true;
	public $incrementing = true;
	protected $fillable = [
		'phonenumber',
		'id_workflow', 
		'user_email', 		 
		'call_status',
		'duration',
		'dialled_datetime',
		'cost'
	];

    protected $hidden = [
        'isp_id', 
        'aserver_id',
        'service_cost',
        'cost_per_minute',
        'service_cost_per_minute',
    ];

    public function user()
	{
		return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
	}

}
