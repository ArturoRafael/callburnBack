<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AddressBookContact;
use App\Models\AddressBookGroup;
use App\Models\Campaign;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\StripeCard;

use Auth;
use Carbon\Carbon;
use Dompdf\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends WebsiteController
{
    /**
     * Create a new instance of UsersController
     *
     * @return void
     */
    public function __construct()
    {
       
    }

    /**
     * Send request for getting users time
     * GET /users/users-time
     *
     * @return JSON
     */
    public function getUsersTime()
    {
        $user = Auth::user();
        $timezone = $user->timezone;
        if (!$timezone) {
            $timezone = 'UTC';
        }
        $time = Carbon::now($timezone)->format('H:i');
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'server__time',
            ],
            'time' => $time,
        ];

        return response()->json(['resource' => $response]);
    }

    /**
     * Send request to API for checking if code is valid and add caller id.
     * POST /users/add-caller-id
     *
     * @param Request $request
     * @return JSON
     */
    public function postAddCallerId(Request $request, NumberVerificationService $numVerificationRepo)
    {
        $callerIdsModel = new \App\Models\CallerId();
        $phonenumber = $request->get('phonenumber');
        $voiceCode = $request->get('voice_code');
        $name = $request->get('name');
        $errorNumber = -100;
        $errorMessage = '';
        $user = Auth::user();

        if (!$voiceCode) {
            $response = [
                'error' => [
                    'no' => -2,
                    'text' => 'verification_code_is_empty',
                ],
            ];
            return response()->json(['resource' => $response]);
        }
        $numberField = $numVerificationRepo->getNumberVerification($voiceCode, $phonenumber);
        if (!$numberField) {
            $response = [
                'error' => [
                    'no' => -1,
                    'text' => 'Invalid_verification_code',
                ],
            ];
            return response()->json(['resource' => $response]);
        }
        $data = [
            'user_id' => $user->_id,
            'phone_number' => $numberField->phone_number,
            'is_verified' => 1,
            'name' => $name,
            'tariff_id' => $numberField->tariff_id,
        ];
        $callerIdsWithOldOwner = $callerIdsModel->where('phone_number', $numberField->phone_number)->with('user')->first();
        if ($callerIdsWithOldOwner) {
            $oldUser = $callerIdsWithOldOwner->user;
            //$callerIdsWithOldOwner->snippet()->update(['is_blocked' => 1]);
            //$callerIdsWithOldOwner->snippet()->detach();
            if ($oldUser) {
                $callerIdRepo = new \App\Services\CallerIdsService();
                $callerIdRepo->handleRemovingCallerIdFromUser($oldUser, $callerIdsWithOldOwner, true);
            }
            /*if($oldUser->numbers()->count() == 1){
            $user->balance = $user->balance + $oldUser->balance;
            $oldUser->balance = 0;
            $oldUser->save();
            }*/
            //$callerIdsWithOldOwner->update($data);
            // If the caller id is registered to another user
            // and that user does not have any other caller id
            // and the user has not synchronized account with website
            // we are adding that users balance to new user
            // and removing the old user , because that account will be unaccessible
            $callerIdsWithOldOwner->update($data);

            //NOTE THIS CODE NEEDED FOR MOBILE APPLICATION USERS
            //AS WE DON"T HAVE IT NOW WE ARE COMMENTING THIS PART

            // if($oldUser->numbers()->count() == 1 && !$oldUser->email && !$oldUser->password){
            //     $user->balance = $user->balance + $oldUser->balance;
            //     $oldUser->balance = 0;
            //     $oldUser->is_deleted = 1;
            //     $oldUser->caller_id_country_code = NULL;
            //     $oldUser->deleted_at = Carbon::now();
            //     $oldUser->save();
            // }
        } else {
            $newCallerIdcallerIdsWithOldOwner = $callerIdsModel->create($data);
        }
        $newCallerId = $callerIdsModel->where('user_id',$user->_id)->where('phone_number',$numberField->phone_number)->first();
        $numVerificationRepo->removeNumberVerification($numberField->_id);

        $tariff = \App\Models\Tariff::with('country')->find($numberField->tariff_id);
        $user->caller_id_country_code = $tariff->country->code;
        if(!$user->country_code) {
            $user->country_code = $tariff->country->code;
        }
        \DB::beginTransaction();
        try {
            $bonusWithOtherUser = \App\User::where('caller_id_used_for_wlc_credit', $numberField->phone_number)->first();
            if (!$user->caller_id_used_for_wlc_credit && !$bonusWithOtherUser && !$user->bonus) {

                $user->caller_id_used_for_wlc_credit = $numberField->phone_number;
                $amount = isset($tariff->country->web_welcome_credit) ? $tariff->country->web_welcome_credit : 0;
                if ($amount) {
                    $user->balance += $amount;
                    $user->gift_amount += $amount;
                    $user->first_time_bonus += $amount;
                    $minimumMargin = 0;

                    $countryCode = $tariff ? $tariff->country->code : 'N/A';
                    $invoiceRepo = new \App\Services\InvoiceService();
                    $invoice = $invoiceRepo->createGiftInvoice($user, $amount, $countryCode, $minimumMargin);

                    $this->sendEmailRepo->giftAdded($user, $amount);

                    $logData = [
                        'user_id' => $user->_id,
                        'device' => 'CALLBURN',
                        'action' => 'BILLINGS',
                        'description' => 'Welcome gift added to user as first caller id added',
                    ];
                    $this->activityLogRepo->createActivityLog($logData);
                }
            }

            $user->save();
            \DB::commit();
        } catch (\Exception $e) {
            \Log::info($e);
            \DB::rollback();
            $response = [
                'error' => [
                    'no' => -100,
                    'text' => 'something_went_wrong',
                ],
                'message' => $e->getMessage(),
            ];
            return response()->json(['resource' => $response]);
        }

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'ACCOUNT',
            'description' => 'User has verified new caller id -  ' . $numberField->phone_number,
        ];
        $this->activityLogRepo->createActivityLog($logData);

        $this->sendEmailRepo->sendCallerIdNotificationEmail($user, $numberField->phone_number);

        event(new \App\Events\UserDataUpdated( [
            'user_id' => $user->_id] ));

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'phonenumber_verified_and_added.',
            ],
            '_id' => $newCallerId->_id,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request fot updating API key name
     * PUT /users/update-caller-id/{id}
     *
     * @param integer $id
     * @param Request $request
     * @return JSON
     */
    public function putUpdateCallerId(Request $request)
    {
        $name = $request->get('name');
        $id = $request->get('id');
        $user = Auth::user();
        $number = $user->numbers()->where('_id', $id)->first();
        // dd($id, $name);
        if (!$number) {
            $response = $this->createBasicResponse(-1, 'number_does_not_exist__or__not_belong_to_you.');
            return response()->json(['resource' => $response]);
        }
        $number->name = $name;
        $number->save();
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'caller__Id_updated',
            ],
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for removing users caller id.
     * POST /users/remove-number
     *
     * @param Request $request
     * @return JSON
     */
    public function postRemoveNumber(Request $request)
    {
        $numberId = $request->get('id');
        $user = Auth::user();
        $number = $user->numbers()->where('_id', $numberId)->first();
        if (!$number) {
            $response = $this->createBasicResponse(-1, 'number_does_not_exist__or__not_belong_to_you');
            return response()->json(['resource' => $response]);
        }
        $phoneNumber = $number->phone_number;
        // $campaign = $user->campaigns()->where(function($query) use($phoneNumber){
        //     $query->where('caller_id', $phoneNumber)
        //         ->orWhere('transfer_option', 'LIKE', '%' . $phoneNumber . '%');
        // })->where('status', '!=', 'dialing_completed')->first();

        // if ($campaign) {
        //     $response = $this->createBasicResponse(-2, 'this_caller_id_is_being__used__in__your__active_message');
        //     return response()->json(['resource' => $response]);
        // }
        $callerIdRepo = new \App\Services\CallerIdsService();
        $callerIdRepo->handleRemovingCallerIdFromUser($user, $number, false);

        $number->delete();
        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'ACCOUNT',
            'description' => 'User has removed ' . $phoneNumber . ' from caller ids',
        ];
        $this->activityLogRepo->createActivityLog($logData);
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'caller_Id__removed',
            ],
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for getting user using api key.
     * GET /users/show-user
     *
     * @return JSON
     */
    public function getShowUser()
    {   
        $user = Auth::user();
        if($user->crisp_history_token) {
            $responseToken = $user->crisp_history_token;
        } else {
            $responseToken = str_random(30);
            $user->crisp_history_token = $responseToken;
            $user->save();
        }
        $user = \App\User::where('_id', $user->_id)
            ->with([
                'numbers',
                'country',
                'tags',
                'language',
                'notifications' => function ($query) {
                    $query->orderBy('notifications.created_at', 'DESC')->take(10);
                },
            ])->first();
        $serverTimezone = config('app.timezone');
        $now = Carbon::now($serverTimezone)->format('Y-m-d H:i:s');
        $campaigns = \DB::table('campaigns')
            ->leftJoin('schedulations', 'campaigns._id',  '=', 'schedulations.campaign_id')
            ->select([
                'campaigns._id',
                'campaigns.campaign_name',
                'campaigns.status',
                'campaigns.first_scheduled_date',
                'campaigns.repeat_batch_grouping',
                'campaigns.grouping_type',
                'campaigns.schedulation_original_data',
                'campaigns.timezone',
                'schedulations.scheduled_date'
            ])
            ->where('campaigns.deleted_at', null)
            ->where('campaigns.user_id', $user->_id)
            ->where('campaigns.status', 'scheduled')
            ->where('schedulations.is_finished', 0)
            ->where('schedulations.scheduled_date', '>=', $now)
            ->orderBy('schedulations.scheduled_date', 'asc')
            ->get();


        $scheduledCampaigns = array();
        foreach ($campaigns as $campaign) {
            $tempScheduledCampaigns = array();
            $tempScheduledCampaigns['_id'] = $campaign->_id;
            $tempScheduledCampaigns['campaign_name'] = $campaign->campaign_name;
            $tempScheduledCampaigns['scheduled_date'] = $campaign->scheduled_date;
            $tempScheduledCampaigns['repeat_batch_grouping'] = $campaign->repeat_batch_grouping;
            $tempScheduledCampaigns['grouping_type'] = $campaign->grouping_type;
            $tempScheduledCampaigns['timezone'] = $campaign->timezone;
            array_push($scheduledCampaigns, $tempScheduledCampaigns);
        }
        $user->schedulations = $scheduledCampaigns;

        $user->invoice_transaction_count = $user->invoices()->where('type', 'TRANSACTION')->where('is_paid', 1)->count();

        try {
            $user->show_validate_now_your_phonenumber = $user->country->web_welcome_credit > 0 && $user->numbers()->count() == 0 && !$user->caller_id_used_for_wlc_credit;
        } catch (\Exception $e) {
            $user->show_validate_now_your_phonenumber = false;
        }

        if ($user->birthday) {
            $date = $user->birthday->format('Y-m-d');
        } else {
            $date = null;
        }

        try {
            $formated_date = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            $formated_date = null;
        }

        $user->birthday = $formated_date;

        $socialMails = [
            $user->facebook_email,
            $user->gmail_email,
            $user->github_email
        ];
        $socialMails = array_filter($socialMails);
        $socialMails = array_unique($socialMails);

        $user->social_mails = $socialMails;

        $user->retainedBalance = $this->userRepo->getRetainedBalance($user);
        $user->retainedGiftBalance = $this->userRepo->getRetainedGiftBalance($user);
        $user->giftWithCriteria = $this->userRepo->getUsersGiftWithCriteria($user);

        if ($user->image_name) {
            $user->image_name = $this->getAmazonS3Url($user->image_name);
        }

        if ($user->password) {
            $user->social = null;
        } else {
            $user->social = 1;
        }

        $vat = $user->vat;

        if($user->balance < 0) {
            $user->balance = 0;
        }

        if ($vat) {
            $user->vat = substr($vat, 2);
        }

        if (env('VOICE_CALLS')) {
            $user->can_access_beta = 1;
        }

        $response = [
            'error' => [
                'no' => 0,
                'text' => 'user_data_successfully_updated',
            ],
            'user_data' => $user,
            'crispToken' => $responseToken,
        ];
        $gravUrl = "http://www.gravatar.com/avatar/";
        $gravUrl .= md5(strtolower(trim($user->email)));
        $gravUrl .= "?d=" . urlencode(config('app.url') . '/assets/callburn/images/contacs-icon.png') . "&s=40";
        $response['user_data']['grav_image'] = $gravUrl;
        return response()->json(['resource' => $response]);
    }

    /**
     * Get background job of the user
     * GET /users/background-job
     *
     * @param Request $request
     * @return JSON
     */
    public function getBackgroundJob(Request $request)
    {
        $user = Auth::user();
        $jobId = $request->get('job_id');
        $job = \App\Models\BackgroundJob::where('_id', $jobId)->where('user_id', $user->_id)->first();
        if (!$job) {
            $response = $this->createBasicResponse(-1, 'job_not_exists_2');
            return response()->json($response);
        }
        $data = [
            'data' => $job->data,
            //'file_id' => $job->file_id,
        ];
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'job_data',
            ],
            'job' => $data,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get background jobs of the user
     * GET /users/background-jobs
     *
     * @param Request $request
     * @return JSON
     */
    public function getBackgroundJobs()
    {
        $user = Auth::user();
        $jobs = $user->jobs;
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'job_data',
            ],
            'jobs' => $jobs,
        ];
        return response()->json(['resource' => $response]);
    }

    /**
     * Get background jobs of the user
     * DELETE /users/background-job
     *
     * @param Request $request
     * @return JSON
     */
    public function deleteBackgroundJob($id)
    {
        $user = Auth::user();
        $user->jobs()->where('_id', $id)->delete();
        $response = $this->createBasicResponse(0, 'Removed__1');
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request to api for updating users main data.
     * POST /users/update-main-data
     *
     * @param Request $request
     * @return JSON
     */
    public function postUpdateMainData(Request $request, SnippetService $snippetService)
    {
        $user = Auth::user();
        $availableValues = [
            'first_name', 'personal_name', 'vat', 'address', 'timezone',
            'language_id', 'country_code', 'postal_code',
            'city_id', 'send_newsletter', 'birthday', 'city',
            'newsletter_email', 'company_name'];
        $updateData = $request->all();

        $city = $request->get('city', null);

        if ($request->timezone) {

            $snippets = $user->snippets;

            $offset = Carbon::now($request->timezone)->offset / (3600);

            //dd($snippets[1]->custom_date_times);

            if (count($snippets)) {

                foreach ($snippets as $snippet) {

                    $dateRange = $snippet->custom_date_times;

                    if ($dateRange) {

                        $weekDates = SnippetService::createCustomSnippetDateRange($dateRange, $offset, $user->timezone, true, true);
                        $finalJson = $snippetService->mergedDataToJson($weekDates);
                        $snippet->custom_date_times = $finalJson;
                        $snippet->save();
                    }
                }
            }
        }

        $newUpdateData = [];

        $newUpdateData['city'] = $city;

        foreach ($updateData as $key => $value) {
            if (in_array($key, $availableValues)) {
                $newUpdateData[$key] = $value;
            }
        }

        if ($request->get('vat')) {
            $vatId = str_replace(' ', '', $request->get('vat'));
            if (preg_match("/^[a-zA-Z]{2}$/", substr($vatId, 0, 2))) {
                if (substr($vatId, 0, 2) === strtoupper($request->get('country_code')) || substr($vatId, 0, 2) === strtoupper($user->country_code)) {
                    $newUpdateData['vat'] = strtoupper(substr($vatId, 0, 2)) . substr($vatId, 2);
                } else {
                    $newUpdateData['vat'] = $request->get('country_code') ? strtoupper($request->get('country_code')) . $vatId : strtoupper($user->country_code) . $vatId;
                }
            } else {
                $newUpdateData['vat'] = $request->get('country_code') ? strtoupper($request->get('country_code')) . $vatId : strtoupper($user->country_code) . $vatId;
            }
        }
        if ($request->get('language_id')) {
            $newUpdateData['language_id'] = $request->get('language_id');
        }

        $newUpdateData['birthday'] = $request->get('birthday') ? Carbon::parse($request->get('birthday')) : null;

        $newUpdateData['newsletter_email'] = $request->get('newsletter_email');

        $oldPassword = $request->get('old_password');
        $newPassword = $request->get('new_password');
        $newPasswordConfirmation = $request->get('new_password_confirmation');
        $email = $request->get('email');
        $emailConfirmation = $request->get('email_confirmation');

        if ($newPassword) {
            if (!$user->password) {
                $response = $this->createBasicResponse(-5, 'access_denied');
                return response()->json(['resource' => $response]);
            }
            if (!\Hash::check($oldPassword, $user->password)) {
                $response = $this->createBasicResponse(-3, 'old__password_is__wrong');
                return response()->json(['resource' => $response]);
            }
            if ($newPassword != $newPasswordConfirmation) {
                $response = $this->createBasicResponse(-4, 'passwords__not__matching');
                return response()->json(['resource' => $response]);
            }
            $newUpdateData['password'] = bcrypt($newPassword);
        }
        if ($emailConfirmation && $user->email != $emailConfirmation) {
            if ($email != $emailConfirmation) {
                $response = $this->createBasicResponse(-1, 'emails__are__not__matching');
                return response()->json(['resource' => $response]);
            }
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                $response = $this->createBasicResponse(-2, 'email__is__not__valid');
                return response()->json(['resource' => $response]);
            }
            $existingUser = \App\User::where('email', $email)->first();
            if ($existingUser) {
                $response = $this->createBasicResponse(-3, 'email__is_already__registered');
                return response()->json(['resource' => $response]);
            }
            $token = str_random(20);
            $newUpdateData['new_email'] = $email;
            $newUpdateData['email_confirmation_token'] = $token;

            $this->sendEmailRepo->sendConfirmNewEmailAddressEmail($user, $email, $token);
        }

        $user->update($newUpdateData);

        $updateDescription = '';
        foreach ($newUpdateData as $key => $value) {
            if ($value == null || $user->$key == $value || $key == 'key' && $key == 'password') {continue;}
            $updateDescription .= $key . ' => ' . $value . '<br>';
        }
        if ($updateDescription) {
            $updateDescription = 'User has updated his main data: <br>' . $updateDescription;
            $logData = [
                'user_id' => $user->_id,
                'device' => 'WEBSITE',
                'action' => 'ACCOUNT',
                'description' => $updateDescription,
            ];
            $this->activityLogRepo->createActivityLog($logData);
        }

        $response = $this->createBasicResponse(0, 'data__updated_1');
        return response()->json(['resource' => $response]);
    }

    /**
     * Send request for enable/disableing low balance notifications
     * POST /users/enable-disable-low-balance-alerts
     *
     * @param Request $request
     * @return JSON
     */
    public function postEnableDisableLowBalanceAlerts(Request $request)
    {
        $user = Auth::user();
        $updateData = $request->only(['send_low_balance_notifications', 'notify_when_balance_is_low']);

        $user->update($updateData);

        $updateDescription = 'User has updated his low balance data: <br>';
        foreach ($updateData as $key => $value) {
            if ($value == null) {continue;}
            $updateDescription .= $key . ' => ' . $value . '<br>';
        }

        $logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'ACCOUNT',
            'description' => $updateDescription,
        ];
        $this->activityLogRepo->createActivityLog($logData);

        $response = $this->createBasicResponse(0, 'data__updated_1');
        return response()->json(['resource' => $response]);
    }

    /**
     * Get all languages supported by TTS API.
     * GET /users/language
     *
     * @return JSON
     */
    public function getLanguage()
    {
        $user = Auth::user();
        $language = $user->language;
        $response = [
            'error' => [
                'no' => 0,
                'text' => 'list_of_languages',
            ],
            'language' => $language,
        ];
        return response()->json(['resource' => $response]);
    }

    public function postDeleteAccount(Request $request)
    {
        $currentUser = Auth::user();
        $password = $request->get('currentPassword');
        $user_id = $request->get('user_id');
        $user = \App\User::find($user_id);

        if(!$user) {
            return response()->json([
                'resource' => [
                    'status' => 'error',
                    'error' => 'User With Id:'.$user_id.' Not Found.',
                    'code' => -1
                ]
            ]);
        }

        if($currentUser->_id != $user->_id) {
            return response()->json([
                'resource' => [
                    'status' => 'error',
                    'error' => 'Permission denied.',
                    'code' => -11
                ]
            ]);
        }

        if(!Hash::check($password, $user->password )) {
            return response()->json([
                'resource' => [
                    'status' => 'error',
                    'error' => 'Wrong Password',
                    'code' => -111
                ]
            ]);
        }

        try {
            DB::beginTransaction();
            Notification::where('user_id',$user->_id)->delete();
            StripeCard::where('user_id',$user->_id)->delete();
            AddressBookGroup::where('user_id',$user->_id)->delete();
            AddressBookContact::where('user_id',$user->_id)->delete();
            DB::table('temp_contacts')->where('user_id',$user->_id)->delete();

            Auth::logout();
            $user->delete();

            DB::commit();



            $response = [
                'resource' => [
                    'status' => 'success',
                    'error' => '',
                    'code' => 1
                ]
            ];
        } catch (Exception $exception) {
            \Log::error($exception);
            DB::rollback();
            $response = [
                'resource' => [
                    'status' => 'error',
                    'error' => 'Something went wrong.',
                    'code' => -100
                ]
            ];
        }

        return response()->json($response);
    }

}
