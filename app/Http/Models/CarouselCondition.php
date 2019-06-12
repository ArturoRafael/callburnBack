<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarouselCondition extends Model
{
    /**
     * The primary key name used by mongo database .
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'carousel_conditions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['_id', 'name'];
}
