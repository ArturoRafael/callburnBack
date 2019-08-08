<?php 
namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class City extends Eloquent {

	
	protected $primaryKey = 'id';

	protected $table = 'cities';

	public $timestamps = false;

	 protected $fillable = [
        'country_code',
        'city',
        'region_code',
        'latitude',
        'longitude',
        'timezone'
    ];
}
