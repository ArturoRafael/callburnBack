<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class Workflow extends Eloquent
{
    
    public $timestamps = true;
  
    protected $table = 'workflow';   

    protected $casts = [
		'event' => 'int',
		'type' => 'int',
        'status' => 'int',
        'activate_hours' => 'int',
        'filter_type' => 'int',
        'activate_before_hours' => 'bool'
	];

	// protected $dates = [
	// 	'date_register',		
	// 	'date_begin',
	// 	'date_end'
	// ];
    
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
        'filter_type',
        'call_id',
        'sender_name',
        'is_blocked',
        'status', 
        'user_email', 
        'date_register', 
        'date_begin', 
        'date_end',
        'activate_hours',
        'activate_before_hours',
        'created_at',
        'updated_at'
    ];
    
   
    public function usuario()
	{
		return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
	}

    public function call_id()
    {
        return $this->belongsTo(\App\Http\Models\CallerId::class, 'call_id');
    }

    public function keys()
    {
        return $this->hasMany(\App\Http\Models\Key::class, 'id_workflow');
    }

    public function times()
    {
        return $this->hasMany(\App\Http\Models\Time::class, 'id_workflow');
    }

    public function days()
    {
        return $this->hasMany(\App\Http\Models\Day::class, 'id_workflow');
    }

    public function contact_key()
    {
        return $this->hasMany(\App\Http\Models\WorkflowContactKey::class, 'id_workflow');
    }

     public function invoices()
    {
        return $this->hasMany(\App\Http\Models\Invoice::class, 'id_workflow');
    }   

    public function sms_recurrent()
    {
        return $this->hasMany(\App\Http\Models\GroupWorkflow::class, 'id_workflow');
    }

    public function call_recurrent()
    {
        return $this->hasMany(\App\Http\Models\Calls::class, 'id_workflow');
    }

    public function groups()
    {
        return $this->belongsToMany(\App\Http\Models\Group::class, 'group_workflow', 'id_workflow', 'id_group');
    }
}
