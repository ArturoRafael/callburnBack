<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{

    protected $primaryKey = '_id';

    public $timestamps = false;

	protected $fillable = ['user_id', 'token'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
