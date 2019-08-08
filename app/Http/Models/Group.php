<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class Group extends Eloquent
{
    
    public $timestamps = false;
  
    protected $table = 'group';
   
    
    protected $fillable = [
        'id', 'description'
    ];
    

   
    public function contacts()
	{
		return $this->belongsToMany(\App\Http\Models\Contact::class, 'group_contact', 'id_contact', 'id_group');
	}

	public function workflows()
    {
        return $this->belongsToMany(\App\Http\Models\Workflow::class, 'group_workflow', 'id_workflow', 'id_group');
    }
}
