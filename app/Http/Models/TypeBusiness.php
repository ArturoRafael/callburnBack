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
    
    
    public function users()
    {
        return $this->hasMany(\App\Http\Models\TypeBusiness::class, 'id_type_business');
    }
   
}
