<?php

namespace App\Http\Models;

use Reliese\Database\Eloquent\Model as Eloquent;


class Rol extends Eloquent
{
    
    public $timestamps = false;
  
    protected $table = 'rol';
   
    
    protected $fillable = [
        'id', 'description'
    ];
    
    public function users()
    {
        return $this->hasMany(\App\Http\Models\Users::class, 'idrol');
    }
   
}
