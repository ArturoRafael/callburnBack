<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model  as Eloquent;

class WorkflowContact extends Eloquent
{
    public $timestamps = false;  
    public $incrementing = false;
    protected $table = 'workflow_contact';
    protected $primaryKey = 'id_workflow', 'id_contact';
    protected $casts = [
		'id_workflow' => 'int',
		'id_contact' => 'int'
	];

	protected $fillable = [
        'id_workflow', 
        'id_contact'        
    ];

    public function workflow()
	{
		return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow');
	}

	public function contact()
	{
		return $this->belongsTo(\App\Http\Models\Contact::class, 'id_contact');
	}
}
