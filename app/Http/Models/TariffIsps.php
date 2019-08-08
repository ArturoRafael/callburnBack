<?php 

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class TariffIsps extends Eloquent {


	protected $primaryKey = 'id';
	
	protected $table = 'tariffs_isps';

	protected $fillable = [
		'tariff_id',
		'isp_id', 
		'cost',  
		'name', 
		'is_blocked'
	];

   		
	public function isp()
	{
		return $this->belongsTo(\App\Http\Models\Isp::class, 'isp_id');
	}

	public function tariff()
	{
		return $this->belongsTo(\App\Http\Models\Tariff::class, 'tariff_id');
	}
	
	

}
