<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Day extends Eloquent
{
    public $timestamps = false;
  
    protected $table = 'days';
    
    protected $fillable = [
        'id', 'id_day', 'id_workflow'
    ];

    public function workflow()
	{
		return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow');
	}
}
