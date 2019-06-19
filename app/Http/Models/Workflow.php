<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class Workflow extends Eloquent
{
    
    public $timestamps = false;
  
    protected $table = 'workflow';   

    protected $casts = [
		'event' => 'int',
		'type' => 'int'
	];

	protected $dates = [
		'date_register',		
		'date_begin',
		'date_end'
	];
    
    protected $fillable = [
        'id', 
        'name', 
        'business_name', 
        'welcome_message', 
        'audio', 
        'sms', 
        'event', 
        'cost', 
        'type', 
        'user_email', 
        'date_register', 
        'date_begin', 
        'date_end'
    ];
    
   
    public function usuario()
	{
		return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
	}
}
