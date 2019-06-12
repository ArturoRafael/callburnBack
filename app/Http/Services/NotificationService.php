<?php

namespace App\Services;

use App\User;
use Auth;
use \App\Models\Notification;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Object of Notification Class for working with storage.
     *
     * @var Notification
     */
    private $notification;

    /**
     * Create new instance of NotificationService class.
     *
     * @return void
     */
    public function __construct()
    {
        $this->notification = new Notification();
    }

    public function createOnCampaignCreate($campaign)
    {
        if (!$campaign->is_pre_listen) {
            $user = Auth::user();
            $campaignName = $campaign->campaign_name ? $campaign->campaign_name : 'ctn_no_name';
            $textData = [
                "<strong class=vm_success_name>",
                $campaignName,
                "</strong>",
                "<span class=vm_success_status>",
                "ctn_is_on_delivery_just_now",
                "</span>",
            ];
            $params = [
                'campaign_batch' => $campaign->repeat_batch_grouping,
                'is_multiple' => false,
                'campaign_id' => $campaign->_id,
                'grouping_type' => $campaign->grouping_type,
                'serverTimeZone' => $campaign->timezone,
            ];
            $otherData = 'campaign_id_' . $campaign->_id;
            $this->notification->user_id = $user->_id;
            $this->notification->text_data = json_encode($textData);
            $this->notification->route = 'campaign.statistics';
            $this->notification->params = json_encode($params);
            $this->notification->status = 'info';
            $this->notification->progressbar = 1;
            $this->notification->other_data = $otherData;
            $this->notification->section = 'vm';
            $this->notification->save();

            $timezone = $user->timezone;
            if (!$timezone) {
                $timezone = 'UTC';
            }
            $time = Carbon::now($timezone);
            \DB::table('notifications')->where('_id',$this->notification->_id)->update( ['created_at' => $time,'updated_at' => $time ] );
            if($this->notification->user_id) {
                event(new \App\Events\UserDataUpdated( [
                    'user_id' => $this->notification->user_id] ));
            }
        }
    }

    public function createOnContactsFileUpload(array $job, $group)
    {
        if ($job['original_file_name']) {
            $user = Auth::user();
            $originalFileName = $job['original_file_name'];
            $textData = [
                "ctn_notification_contacts_upload_in_progress",
                "<span class=original-file-name-holder>",
                $originalFileName,
                "</span>",
                "ctn_notification_process",
            ];

            $otherData = 'bg_job_in_progress_' . $group->_id;

            $this->notification->user_id = $user->_id;
            $this->notification->text_data = json_encode($textData);
            $this->notification->route = 'addressbook.groups';
            $this->notification->params = ' ';
            $this->notification->status = 'info';
            $this->notification->progressbar = 1;
            $this->notification->other_data = $otherData;
            $this->notification->section = 'phonebook';
            $this->notification->save();

            $timezone = $user->timezone;
            if (!$timezone) {
                $timezone = 'UTC';
            }
            $time = Carbon::now($timezone);
            \DB::table('notifications')->where('_id',$this->notification->_id)->update( ['created_at' => $time,'updated_at' => $time ] );
            if($this->notification->user_id) {
                event(new \App\Events\UserDataUpdated( [
                    'user_id' => $this->notification->user_id] ));
            }
        }
    }

    public function createOnContactsFileUploadSuccess($job)
    {
        if ($job->original_file_name) {
            $user = $job->user ? $job->user : Auth::user();
            if ($user) {
                $jobData = json_decode($job->data);
                $validContactsCount = $jobData->valid;
                $invalidContactsCount = $jobData->invalid;

                $textData = [
                    'ctn_notification_successfully_uploaded',
                    '-',
                    '<span class=original-file-name-holder>',
                    $job->original_file_name,
                    '</span>',
                    '<br>',
                    'ctn_notification_your_contacts_were_uploaded',
                    '<br>',
                    'ctn_notification_invalid',
                    '-',
                    $invalidContactsCount,
                    '<br>',
                    'ctn_notification_success',
                    '-',
                    $validContactsCount,
                    '<br>',
                    'ctn_notification_duplicate',
                    '-',
                    $jobData->duplicate,
                    '<br>',
                ];
                $params = [
                    'group_id' => $job->group_id,
                ];
                $params = ($validContactsCount === 0) ? '' : json_encode($params);
                $route = ($validContactsCount === 0) ? 'addressbook.groups' : 'addressbook.contacts';
                $status = ($validContactsCount === 0) ? 'danger' : 'success';

                $this->notification->user_id = $user->_id;
                $this->notification->text_data = json_encode($textData);
                $this->notification->route = $route;
                $this->notification->params = $params;
                $this->notification->progressbar_data = ' ';
                $this->notification->status = $status;
                $this->notification->progressbar = 1;
                $this->notification->section = 'phonebook';
                $this->notification->save();

                $timezone = $user->timezone;
                if (!$timezone) {
                    $timezone = 'UTC';
                }
                $time = Carbon::now($timezone);
                \DB::table('notifications')->where('_id',$this->notification->_id)->update( ['created_at' => $time,'updated_at' => $time ] );
                if($this->notification->user_id) {
                    event(new \App\Events\UserDataUpdated( [
                        'user_id' => $this->notification->user_id] ));
                }
            }
        }
    }

    public function createOnContactsFileUploadFailure($job)
    {
        if ($job->original_file_name) {
            $user = $job->user ? $job->user : Auth::user();
            if ($user) {
                $textData = [
                    'ctn_notification_something_went_wrong_when_uploading_the_contacts',
                    '-',
                    '<span class=original-file-name-holder>',
                    $job->original_file_name,
                    '</span>',
                ];

                $this->notification->user_id = $user->_id;
                $this->notification->text_data = json_encode($textData);
                $this->notification->route = 'addressbook.contacts';
                $this->notification->progressbar_data = ' ';
                $this->notification->status = 'danger';
                $this->notification->progressbar = 1;
                $this->notification->section = 'phonebook';
                $this->notification->save();

                $timezone = $user->timezone;
                if (!$timezone) {
                    $timezone = 'UTC';
                }
                $time = Carbon::now($timezone);
                \DB::table('notifications')->where('_id',$this->notification->_id)->update( ['created_at' => $time,'updated_at' => $time ] );
                if($this->notification->user_id) {
                    event(new \App\Events\UserDataUpdated( [
                        'user_id' => $this->notification->user_id] ));
                }
            }
        }
    }

    public function deleteOnContactsFileUploadFinish($id, $user = null)
    {
        if (is_null($user)) {
            $user = Auth::user();
        }
        if ($user) {
            $otherData = 'bg_job_in_progress_' . $id;
            $notification = $user->notifications()->where(['other_data' => $otherData]);
            if ($notification->first()) {
                $notification->first()->delete();
            }
        }
        if($this->notification->user_id) {
            event(new \App\Events\UserDataUpdated( [
                'user_id' => $this->notification->user_id] ));
        }
    }

    public function createOnCampaignStart($campaign)
    {
        $userId = $campaign->user_id;
        $campaignName = $campaign->campaign_name ? $campaign->campaign_name : 'ctn_no_name';
        $textData = [
            "<strong class=vm_success_name>",
            $campaignName,
            "</strong>",
            "<span class=vm_success_status>",
            "ctn_is_on_delivery_just_now",
            "</span>",
        ];
        $params = [
            'campaign_batch' => $campaign->repeat_batch_grouping,
            'is_multiple' => false,
            'campaign_id' => $campaign->_id,
            'grouping_type' => $campaign->grouping_type,
            'serverTimeZone' => $campaign->timezone,
        ];
        $params = json_encode($params);

        $this->notification->user_id = $userId;
        $this->notification->text_data = json_encode($textData);
        $this->notification->route = 'campaign.statistics';
        $this->notification->params = $params;
        $this->notification->progressbar_data = ' ';
        $this->notification->status = 'info';
        $this->notification->progressbar = 1;
        $this->notification->section = 'vm';
        $this->notification->save();

        $user = User::find($userId);
        $timezone = $user->timezone;
        if (!$timezone) {
            $timezone = 'UTC';
        }
        $time = Carbon::now($timezone);
        \DB::table('notifications')->where('_id',$this->notification->_id)->update( ['created_at' => $time,'updated_at' => $time ] );
        if($this->notification->user_id) {
            event(new \App\Events\UserDataUpdated( [
                'user_id' => $this->notification->user_id] ));
        }
    }

    public function createCampaignNotEnoughBalance($campaign)
    {
        $userId = $campaign->user_id;
        $campaignName = $campaign->campaign_name ? $campaign->campaign_name : 'ctn_no_name';
        $textData = [
            "<strong class=vm_success_name>",
            $campaignName,
            "</strong>",
            "<span class=vm_success_status>",
            "ctn_notification_campaign_compose_compose_step_3_balance_will_not_be_enought_to_call_all",
            "</span>",
        ];

        $this->notification->user_id = $userId;
        $this->notification->text_data = json_encode($textData);
        $this->notification->route = '';
        $this->notification->params = '';
        $this->notification->progressbar_data = '';
        $this->notification->status = 'info';
        $this->notification->progressbar = 0;
        $this->notification->section = 'vm';
        $this->notification->save();

        $user = User::find($userId);
        $timezone = $user->timezone;
        if (!$timezone) {
            $timezone = 'UTC';
        }
        $time = Carbon::now($timezone);
        \DB::table('notifications')->where('_id',$this->notification->_id)->update( ['created_at' => $time,'updated_at' => $time ] );
        if($this->notification->user_id) {
            event(new \App\Events\UserDataUpdated( [
                'user_id' => $this->notification->user_id] ));
        }
    }
}
