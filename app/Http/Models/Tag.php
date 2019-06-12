<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
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
    protected $table = 'tags';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['pivot'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get leads of the tag
     */
    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_tags');
    }

    /**
     * Get customers of the tag
     */
    public function customers()
    {
        return $this->belongsToMany(User::class, 'user_tags');
    }

    /**
     * Get email campaigns of the tag
     */
    public function emailCamapigns()
    {
        return $this->belongsToMany(EmailCampaign::class, 'email_campaign_tags');
    }
}
