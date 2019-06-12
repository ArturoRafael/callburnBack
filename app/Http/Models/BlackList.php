<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model {


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
    protected $table = 'blacklists';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['phonenumber', 'user_id','type','name', 'country_code','phonenumber_id','archived_phonenumber_id', 'campaign_id', 'blacklist_type'];

    public function campaign()
    {
        return $this->belongsTo('App\Models\Campaign', 'campaign_id', '_id');
    }
}