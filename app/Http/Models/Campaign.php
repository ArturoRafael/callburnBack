<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Auth;

class Campaign extends Model
{

    use SoftDeletes;

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
    protected $table = 'campaigns';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_name', 'caller_id', 'transfer_option', 'transfer_digit', 'callback_digit',
        'callback_digit_file_id', 'campaign_voice_file_id', 'replay_digit', 'do_not_call_digit',
        'disable_answering_machine_detection', 'answering_machine_only', 'live_answer_only', 'max_concurrent_channels',
        'daily_max_success_calls', 'max_success_calls', 'live_transfer_limit', 'retries', 'dial_plan_context',
        'customer_id', 'created_on', 'do_not_call_digit_file_id', 'user_id', 'status', 'total_phonenumbers_loaded',
        'dialed', 'live', 'no_ans', 'busy', 'transfer', 'dnc', 'error', 'misc', 'machine', 'success', 'remaining_successful_calls',
        'error_congestion', 'error_manual_reset', 'timezone', 'get_email_notifications',
        'calling_interval_minutes', 'retained_balance', 'retained_gift_balance', 'delivery_speed', 'aserver_id',
        'remaining_repeats', 'repeat_days_interval', 'repeat_batch_grouping', 'is_prototype', 'grouping_type',
        'schedulation_original_data', 'playback_count', 'first_scheduled_date', 'created_from', 'mobile_message_group_id',
        'should_use_all_contacts', 'is_first_run',
        'snippet_id', 'api_key_id',
        'created_group_id', 'is_archived','should_shuffle','type','sms_text','sender_name','parent_id'
    ];


    /**
     * Get the campaign voice file.
     *
     */
    public function voiceFile()
    {
        return $this->belongsTo('App\Models\File', 'campaign_voice_file_id', '_id');
    }

    /**
     * Get the callback file.
     *
     */
    public function callbackFile()
    {
        return $this->belongsTo('App\Models\File', 'callback_digit_file_id', '_id');
    }

    /**
     * Get the callback file.
     *
     */
    public function doNotCallFile()
    {
        return $this->belongsTo('App\Models\File', 'do_not_call_digit_file_id', '_id');
    }

    /**
     * Get user of the campaign.
     *
     */
    public function user()
    {
        return $this->belongsTo('App\User')->with('numbers');
    }

    /**
     * Get API key object of the campaign.
     *
     */
    public function apiKey()
    {
        return $this->belongsTo('App\Models\ApiKey');
    }

    /**
     * Get schedulations for this campaign
     */
    public function schedulations()
    {
        return $this->hasMany('App\Models\Schedulation')->orderBy('scheduled_date', 'ASC');
    }

    /**
     * Get phonenumbers of campaign
     */
//    public function phonenumbers()
//    {
//        //dd($this);
//        if(isset($this->is_archived)){
//            dd('mtav');
//        }
//        if($this->is_archived){
//            return $this->hasMany('App\Models\ArchivedPhonenumber')->with('tariff');
//        }
//        return $this->hasMany('App\Models\Phonenumber')->with('tariff');
//    }
    public function phonenumbers()
    {
//        if($this->is_archived){
//            return $this->hasMany('App\Models\ArchivedPhonenumber')->with('tariff');
//        }
        return $this->hasMany('App\Models\Phonenumber')->with('tariff');
    }

    /**
     * Get archivedPhonenumbers of campaign
     */
    public function archivedPhonenumbers()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumber')->with('tariff');
    }

    /**
     * Get phonenumbers of campaign
     */
    public function phonenumbersForApi()
    {

        if ($this->is_archived) {
            return $this->hasMany('App\Models\ArchivedPhonenumber')->select('_id', 'phone_no', 'status');
        }
        return $this->hasMany('App\Models\Phonenumber')->select('_id', 'phone_no', 'status');
    }

    /**
     * Get phonenumbers of batch campaign
     */
    public function batchPhonenumber()
    {
        if ($this->is_archived) {
            return $this->hasOne('App\Models\ArchivedPhonenumber', 'campaign_id', '_id')->with(['tariff', 'actions']);
        }
        return $this->hasOne('App\Models\Phonenumber', 'campaign_id', '_id')->with(['tariff', 'actions']);
    }

    /**
     * Get batch campaigns of campaign
     */
    public function batchRepeats()
    {
        return $this->hasMany('App\Models\Campaign', 'repeat_batch_grouping', 'repeat_batch_grouping')
            ->with(['successPhonenumbers', 'costPhonenumbers', 'totalPhonenumbers', 'schedulations' => function ($query) {
                $query->where('is_finished', 0);
            }])->where('is_prototype', 0);
    }

    /**
     * Get repeats of campaign
     */
    public function prototype()
    {
        return $this->hasOne('App\Models\Campaign', 'repeat_batch_grouping', 'repeat_batch_grouping')
            ->with(['schedulations'])->where('is_prototype', 1);
    }

    /**
     * Get success phonenumbers
     */
    public function successPhonenumbers()
    {
        return $this->phonenumbers()->whereIn('status', ['SUCCEED','CALL_FAILED_SMS_SUCCEED'])
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }

    /**
     * Get archived success phonenumbers
     */
    public function archivedSuccessPhonenumbers()
    {
        return $this->archivedPhonenumbers()->whereIn('status', ['SUCCEED','CALL_FAILED_SMS_SUCCEED'])
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }

    /**
     * Get success phonenumbers
     */
    public function totalPhonenumbers()
    {
        return $this->phonenumbers()
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }

    /**
     * Get archived total phonenumbers
     */
    public function archivedTotalPhonenumbers()
    {
        return $this->archivedPhonenumbers()
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }


    public function costPhonenumbers()
    {
        return $this->hasMany('App\Models\Billing')
            ->selectRaw('campaign_id, (SUM(billed_from_gift) + SUM(billed_from_purchased)) as sum')->groupBy('campaign_id');
    }

    /**
     * Get calls count
     */
    public function callsCount()
    {
        if ($this->is_archived) {
            return $this->hasMany('App\Models\ArchivedPhonenumber')
                ->join('archived_calls', 'archived_calls.phonenumber_id', '=', 'archived_phonenumbers.original_id')
                ->selectRaw('archived_phonenumbers.campaign_id, count(*) as count')->groupBy('archived_phonenumbers.campaign_id');
        }
        return $this->hasManyThrough('App\Models\Call', 'App\Models\Phonenumber')
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }

    public function smsCount()
    {
        return $this->phonenumbers()->has('smsAction')
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }

    public function archivedSmsCount()
    {
        return $this->archivedPhonenumbers()->has('smsAction')
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }


    public function archivedCallsCount()
    {
        return $this->hasMany('App\Models\ArchivedPhonenumber')
            ->join('archived_calls', 'archived_calls.phonenumber_id', '=', 'archived_phonenumbers.original_id')
            ->selectRaw('archived_phonenumbers.campaign_id, count(*) as count')->groupBy('archived_phonenumbers.campaign_id');
    }

    /**
     * Get not finished phonenumbers
     */
    public function unfinishedPhonenumbers()
    {
        return $this->phonenumbers()->where('status', 'IN_PROGRESS')
            ->where('retries', '<', 5)
            ->selectRaw('campaign_id, count(*) as count')->groupBy('campaign_id');
    }

    /**
     * Get groups of the campaign
     */
    public function groups()
    {
        return $this->belongsToMany('\App\Models\AddressBookGroup', 'campaign_groups');
    }

    /**
     * Get groups of the campaign with contacts
     */
    public function groupsWithContacts()
    {
        return $this->belongsToMany('\App\Models\AddressBookGroup', 'campaign_groups')
            ->with('contacts');
    }

    public function ctcPhonenumbers()
    {
        if ($this->is_archived) {
            return $this->hasOne('App\Models\ArchivedPhonenumber')->with(['tariff', 'retryActions']);
        }
        return $this->hasOne('App\Models\Phonenumber')->with(['tariff', 'retryActions']);
    }

    public function snippet()
    {
        return $this->belongsTo('App\Models\Snippet')->with(['country', 'callerId', 'calls']);
    }


    /***************** REGISTER ALL ATTRIBUTES ******************/
    public function getSuccessPhonenumbersCountAttribute()
    {
        return isset($this->successPhonenumbers[0]) ? $this->successPhonenumbers[0]->count : 0;
    }

    public function getTotalPhonenumbersCountAttribute()
    {
        return isset($this->totalPhonenumbers[0]) ? $this->totalPhonenumbers[0]->count : 0;
    }

    public function getUnfinishedPhonenumbersCountAttribute()
    {
        return isset($this->unfinishedPhonenumbers[0]) ? $this->unfinishedPhonenumbers[0]->count : 0;
    }

    public function getVoiceFileTextAttribute()
    {
        return $this->voiceFile ? $this->voiceFile->tts_text : '';
    }

    public function getVoiceFileLanguageAttribute()
    {
        return $this->voiceFile ? $this->voiceFile->tts_language : '';
    }

    public function previewPhonenumbers()
    {
        return $this->hasMany('App\Models\Phonenumber')->with('tariff','actionsLog');
    }

    public function previews()
    {
        return $this->hasMany(self::class,'parent_id','_id')->with('previewPhonenumbers','smsCount');
    }
}
