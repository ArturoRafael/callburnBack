<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsResponse extends Model
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
    protected $table = 'sms_response';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status_code',
        'is_billed',
        'reference_id',
        'send_on',
        'delivered_on',
        'result_code',
        'error_code',
        'status_text',
        'error_text',
        'message_parts',
        'destination_number',
        'sender_name',
        'customer_reference',
        'department'
    ];
}