<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Snippet extends Model
{
    use SoftDeletes;

    protected $primaryKey = '_id';

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    public function country()
    {
        return $this->belongsToMany('App\Models\Country','snippet_countries','snippet_id','country_id')
            ->select([
                'countries._id',
                'countries.code',
                'countries.name',
                'countries.phonenumber_prefix',
            ]);
    }

    public function callerId()
    {
        return $this->belongsToMany('App\Models\CallerId','snippet_transfer_numbers','snippet_id','caller_id');
    }

    public function ctcPhonenumbers()
    {
        return $this->hasMany('App\Models\Phonenumber')->with(['tariff', 'calls', 'manualRetries']);
    }

    public function files()
    {
        return $this->belongsTo('App\Models\File','file_id');
    }

    public function calls()
    {
        return $this->hasManyThrough('App\Models\Call', 'App\Models\Phonenumber');
    }

    public function callsCost()
    {
        return $this->hasManyThrough('App\Models\Call', 'App\Models\Phonenumber')
            ->selectRaw('snippet_id, sum(cost) as sum')->groupBy('snippet_id');
    }

    public function pendingCount()
    {
        return $this->hasMany('App\Models\Phonenumber')
                    ->current()
                    ->pending()
                    ->validCTC()
                    ->selectRaw('snippet_id, count(*) as count')->groupBy('snippet_id');
    }

    public function inTimeCount()
    {
        return $this->hasMany('App\Models\Phonenumber')
                    ->current()
                    ->validCTC()
                    ->whereNull('first_scheduled_date')
                    ->selectRaw('snippet_id, count(*) as count')->groupBy('snippet_id');
    }

    public function totalClicks()
    {
        return $this->hasMany('App\Models\Phonenumber')
                    ->current()
                    ->validCTC()
                    ->selectRaw('snippet_id, count(*) as count')->groupBy('snippet_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', '_id');
    }

    public function addressBookGroup()
    {
        return $this->hasOne('App\Models\AddressBookGroup');
    }

    public function scopeCurrentUser($query)
    {
        return $query->where('user_id',\Auth::user()->_id);
    }

    public function scopeNotBlocked($query)
    {
        return $query->where('is_blocked',0);
    }

    public function scopeIsPublished($query,$type)
    {
        return $query->where('is_published',$type);
    }

    public function scopeIsActive($query,$type)
    {
        return $query->where('is_active',$type);
    }

}
