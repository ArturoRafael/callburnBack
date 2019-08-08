<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class KeyEventType extends Eloquent
{
    public $timestamps = false;  
    protected $table = 'key_event_type';
   
    
    protected $fillable = [
        'id', 'name', 'description'
    ];

    public function keys()
    {
		return $this->hasMany(\App\Http\Models\Key::class, 'id_key_event_type');
	}
}
