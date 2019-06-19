<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class Contact extends Eloquent
{
    
    public $timestamps = false;
  
    protected $table = 'contact';

    protected $casts = [        
        'born_date' => 'date'
    ];
    
    protected $fillable = [
        'id', 'email', 'phone', 'first_name', 'last_name', 'born_date', 'status','gender', 'user_email'
    ];
    
    
   
    public function usuario()
    {
        return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
    }
}
