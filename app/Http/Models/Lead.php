<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{

    protected $primaryKey = '_id';

    protected $fillable = ['email', 'country_id', 'type', 'status', 'sent_emails', 'views'];

    protected $hidden = ['pivot'];

    /*
     * get country of the lead
     */
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    /*
     * Get tags of the lead
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'lead_tags');
    }
}
