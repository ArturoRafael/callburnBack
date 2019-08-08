<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class WorkflowContactKey extends Eloquent
{
    public $timestamps = false;
    protected $table = 'workflow_contact_key';

    protected $casts = [
		'id_workflow' => 'int',
		'id_contact_workflow' => 'int',
		'id_key' => 'int'
	];

	protected $fillable = [
        'id',
        'id_workflow',
        'id_contact_workflow',
        'id_key', 
        'date_time'       
    ];


    public function workflow()
	{
		return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow');
	}

	public function contact_workflow()
	{
		return $this->belongsTo(\App\Http\Models\Contact::class, 'id_contact_workflow');
	}

	public function keys()
	{
		return $this->belongsTo(\App\Http\Models\Key::class, 'id_key');
	}
}
