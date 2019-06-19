<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;;

class Reservation extends Eloquent
{
    public $timestamps = false;
  
    protected $table = 'reservation';
   
    protected $casts = [        
        'reservation_date' => 'date'
    ];
    
    protected $fillable = [
        'id', 'id_contact', 'reservation_date'
    ];

    public function contacto()
	{
		return $this->belongsTo(\App\Http\Models\Contact::class, 'id_contact');
	}

	
}
