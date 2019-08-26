<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Tariff extends Eloquent {


	protected $primaryKey = '_id';
	
	protected $table = 'tariffs';

	protected $fillable = [
		'name',
		'prefix', 
		'country_id', 		 
		'is_blocked',  
		'is_main_tariff' ];

    protected $hidden = [
        'best_margin', 
        'billed_amount',
        'is_blocked',
        'is_deleted', 
        'is_disabled',

    ];

		
	public function country()
	{
		return $this->belongsTo(\App\Http\Models\Country::class, 'country_id');
	}


	public function bestIsp(){
		return $this->belongsTo(App\Http\Models\Isp::class, 'best_isp_id', 'id');
	}
	
	

}
