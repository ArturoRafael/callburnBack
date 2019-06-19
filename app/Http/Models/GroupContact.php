<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class GroupContact extends Eloquent
{
    protected $table = 'group_contact';
    public $incrementing = false;
    public $timestamps = false;  
    
   	
   	protected $casts = [
		'id_contact' => 'int',
		'id_group' => 'int'
	];
    
    protected $fillable = [
        'id_contact', 'id_group'
    ];
    
    public function contacto()
	{
		return $this->belongsTo(\App\Http\Models\Contact::class, 'id_contact');
	}

	public function grupo()
	{
		return $this->belongsTo(\App\Http\Models\Group::class, 'id_group');
	}
   
   
}
