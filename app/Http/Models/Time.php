<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;;

class Time extends Eloquent
{
    public $timestamps = false;
  
    protected $table = 'time';
   	
   	protected $casts = [
		'start_time' => 'time',
		'end_time' => 'time'
	];
    
    protected $fillable = [
        'id', 'start_time', 'end_time', 'id_workflow'
    ];

    public function workflow()
	{
		return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow');
	}
}
