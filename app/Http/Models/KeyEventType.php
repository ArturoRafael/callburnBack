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
}
