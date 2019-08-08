<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Key extends Eloquent
{
    public $timestamps = false;  
    protected $table = 'key';
   	
   	protected $casts = [
		'id_workflow' => 'int',
		'id_key_event_type' => 'int',
		'id_parent_key' => 'int',
		'has_sub_key_menu' => 'bool'
	];
    
    protected $fillable = [
        'id', 
        'id_workflow', 
        'keypad_value',
        'id_key_event_type',
        'label',
        'first_action_text',
        'first_action_audio',
        'post_action_text',
        'post_action_audio',
        'phone_number',
        'simultaneus_transfer_limit',
        'name_text',
        'transfer_text',
        'transfer_audio',
        'has_sub_key_menu',
        'id_parent_key'
    ];

    public function workflow()
	{
		return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow');
	}

	public function key_event_type()
	{
		return $this->belongsTo(\App\Http\Models\KeyEventType::class, 'id_key_event_type');
	}

	public function parent_key()
	{
		return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_parent_key');
	}

    public function workflows_contacts()
    {
        return $this->hasMany(\App\Http\Models\WorkflowContactKey::class, 'id_key');
    }
}
