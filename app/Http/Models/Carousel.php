<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carousel extends Model
{
    /**
     * The primary key name used by mongo database .
     *
     * @var string
     */
    protected $primaryKey = '_id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['slug'];

    /*
     * Get tags of the carousel
     */
    public function condition()
    {
        return $this->belongsTo(CarouselCondition::class, 'carousels_condition_id');
    }

    /*
     * Get tags of the carousel
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'carousel_tags');
    }

    /*
     * Get countries of the carousel
     */
    public function countries()
    {
        return $this->belongsToMany(Country::class, 'carousel_countries');
    }

    public function getSlugAttribute()
    {
        return "carousel_" . $this->_id . "_" . str_slug($this->text, "_");
    }
}
