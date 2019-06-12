<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageLogs extends Model
{

    protected $primaryKey = '_id';


    protected $table = 'message_logs';

    protected $fillable = [
        'campaign_id',
        'phonenumber_id',
        'archived_phonenumber_id',
        'type',
        'status',
        'text',
        'data'
    ];

    public function campaign()
    {
        return $this->belongsTo('App\Models\Campaign');
    }


    public function phonenumber()
    {
        return $this->belongsTo('App\Models\Phonenumber');
    }

    public function archivedPhonenumber()
    {
        return $this->belongsTo('App\Models\ArchivedPhonenumber');
    }

}
