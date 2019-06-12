<?php

namespace App\Models;

use App\Services\InvoiceService;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class InvitationParam extends Model
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
    protected $table = 'invitation_params';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'lead_id', 'token', 'status', 'bonus', 'bonus_criteria', 'bonus_expiration_date'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ["bonus_expiration_date" => 'date'];

    /*
     * Get customer of the invitation
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
     * Get lead of the invitation
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /*
     * Get lead of the invitation
     */
    public function getEmailAttribute()
    {
        if ($this->lead_id) {
            return $this->lead->email;
        } elseif ($this->user_id) {
            return $this->customer->email;
        } else {
            return null;
        }
    }

    public function makeAsAccepted(User $user)
    {
        $this->status = 'COMPLETED';
        $this->token = null;
        $this->load(['lead.tags', 'lead.country']);
        if ($this->lead) {
            $lead = $this->lead;
            $tagIds = $lead->tags->lists('_id')->toArray();
            if ($lead->country) {
                $user->country_code = $lead->country->code;
                $user->save();
            }
            $lead->status = "REGISTERED";
            $lead->save();
            $user->tags()->sync($tagIds);
            $this->user_id = $user->_id;
        }
        $this->save();
        if ($this->bonus && (is_null($this->bonus_expiration_date) || $this->bonus_expiration_date->diffInDays(Carbon::today()) >= 0)) {
            $criteria = $this->bonus_criteria ? $this->bonus_criteria : 0;
            app(InvoiceService::class)->createGiftInvoice($user, $this->bonus, $user->country_code, $criteria);
        }
        session()->forget('invitation_token');
    }
}
