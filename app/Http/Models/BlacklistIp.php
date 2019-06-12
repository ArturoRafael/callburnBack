<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistIp extends Model
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
    protected $table = 'blacklist_ips';

    protected $fillable = ['user_id', 'ip', 'json','phonenumber'];



    /**
     * Get BlacklistIp's json
     */
    public function getJsonAttribute($value)
    {
        return json_decode($value);
    }


//    /**
//     * Set BlacklistIp's json
//     */
//    public function setJsonAttribute($value)
//    {
//        $this->attributes['json'] = json_encode($value);
//    }
}
