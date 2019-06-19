<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class TypeBusiness extends Eloquent
{
    
    public $timestamps = false;
  
    protected $table = 'type_business';
   
    
    protected $fillable = [
        'id', 'description'
    ];
    
   
   
}
