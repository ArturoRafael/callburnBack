<?php 

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

class GroupWorkflow extends Eloquent
{
    
    protected $primaryKey = 'id';    
    protected $table = 'group_workflow';
    public $incrementing = true;


    protected $casts = [
        'send_on' => 'date',
        'delivered_on' => 'date'
    ];
    
    protected $fillable = [
        'id',
        'id_workflow',
        'id_group',
        'status_code',        
        'reference_id',
        'send_on',
        'delivered_on',
        'result_code',
        'error_code',
        'status_text',        
        'message_parts',
        'destination_number',
        'sender_name',
        'cost'
    ];

    public function workflows_recurrent()
    {
        return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow')->where('filter_type', 3)->orWhere('filter_type', 4)->orWhere('filter_type', 5);
    }

    public function workflows()
    {
        return $this->belongsTo(\App\Http\Models\Workflow::class, 'id_workflow');
    }

    public function groups()
    {
        return $this->belongsTo(\App\Http\Models\Group::class, 'id_group');
    }
}