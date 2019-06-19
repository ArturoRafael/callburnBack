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
    
   
   
}
