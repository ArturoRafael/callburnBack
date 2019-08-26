<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;


class Contact extends Eloquent
{
    
    public $timestamps = false;
  
    protected $table = 'contact';

    protected $casts = [        
        'born_date' => 'date'
    ];
    
    protected $fillable = [
        'id', 'email', 'phone', 'first_name', 'last_name', 'born_date', 'status','gender', 'info_file','user_email'
    ];
    
    

   
    public function usuario()
    {
        return $this->belongsTo(\App\Http\Models\Users::class, 'user_email');
    }

    public function workflows_key()
    {
        return $this->hasMany(\App\Http\Models\WorkflowContactKey::class, 'id_contact_workflow');
    }

    public function reservations()
    {
        return $this->hasMany(\App\Http\Models\Reservation::class, 'id_contact');
    }

    public function groups()
    {
        return $this->belongsToMany(\App\Http\Models\Group::class, 'group_contact', 'id_contact', 'id_group');
    }
}
