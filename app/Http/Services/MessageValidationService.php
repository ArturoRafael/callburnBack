<?php

namespace App\Services;

use App\Services\UserService;
use Carbon\Carbon;

class MessageValidationService
{

    /**
     * Check if user has enough balance to start the call
     *
     * @param string $messageStatus
     * @param float $maxCost
     * @param User $user
     * @return bool
     */
    public static function haveEnoughBalanceForCall($messageStatus, $maxCost, $maxGiftCost, $user)
    {
        $userRepo = new UserService();
        //We will always allow to save message even if not enough balance
        if ($messageStatus == 'saved') {return true;}
        $retainedBalance = $userRepo->getRetainedBalance($user);
        $giftBalance = $userRepo->getUsersGiftWithCriteria($user);
        $availableBalance = $user->balance - $retainedBalance - $giftBalance->balance;
        // return $availableBalance >= $maxCost;
        return $availableBalance > 0 ? true : false;
    }

    public static function getFinalSchedulation(
        $schedulations,
        $finalPhonenumbers,
        $timezone
    ) {
        $finalSchedulation = array();
        if ($schedulations) {
            // dd($schedulations);
            foreach ($schedulations as $schedulation) {
                $startDate = Carbon::parse($schedulation['start']);
                $today = Carbon::now($timezone)->subHour();

                if (
                    !isset($schedulation['recipients_count_to_call'])
                 || !$schedulation['recipients_count_to_call']
                 ||  (isset($schedulation['is_finished'])
                   &&  $schedulation['is_finished'])
                    || (isset($schedulation['is_pasted']) && $schedulation['is_pasted'])
                ) {
                    continue;
                }

                // $recipients = $schedulation->recipients_count_to_call;

                $isPassed = array_key_exists('is_pasted', $schedulation) ? $schedulation['is_pasted'] : false;

                $finalSchedulation[] = [
                    'date' => $startDate,
                    'max' => $schedulation['recipients_count_to_call'],
                    'sending_time' => isset($schedulation['delivery_speed']) ? $schedulation['delivery_speed'] : 0,
                    'is_past' => $isPassed,
                    // 'recipients' => $recipients
                ];
            }
        }

        return $finalSchedulation;
    }

    public static function Validator($validatorData, $saveAsDraft, $part, $type = null)
    {

        $rules = [

            'caller_id' => 'required',
            'transfer_digit' => 'required_with:transfer_options',
            'transfer_options' => 'required_with:transfer_digit',
            'callback_digit' => 'required_with:callback_voice_file_id',
            'do_not_call_digit' => 'required_with:do_not_call_voice_file_id',

        ];
        if ($part == 'create' && $type !== 'SMS') {

            if (!$saveAsDraft) {
                $rules['campaign_voice_file_id'] = 'required';
            }
            $rules['callback_voice_file_id'] = 'required_with:callback_digit';
            $rules['do_not_call_voice_file_id'] = 'required_with:do_not_call_digit';

            $voice = 'campaign_voice_file_id';
            $callback = 'callback_voice_file_id';
            $doNotCall = 'do_not_call_voice_file_id';

        } elseif ($part == 'update' && $type !== 'SMS') {

            if (!$saveAsDraft) {
                $rules['voice_message'] = 'required';
            }
            $rules['callback_digit_file_id'] = 'required_with:callback_digit';
            $rules['do_not_call_digit_file_id'] = 'required_with:do_not_call_digit';

            $voice = 'voice_message';
            $callback = 'callback_digit_file_id';
            $doNotCall = 'do_not_call_digit_file_id';
        } else {

            $rules['callback_voice_file_id'] = 'required_with:callback_digit';
            $rules['do_not_call_voice_file_id'] = 'required_with:do_not_call_digit';

            $callback = 'callback_voice_file_id';
            $doNotCall = 'do_not_call_voice_file_id';
        }


        $validator = \Validator::make($validatorData, $rules);

        if ($validator->fails()) {
            $errorNumber = -100;
            $errorMessage = 'Something went wrong';
            $failedRules = $validator->failed();

            if (isset($failedRules['caller_id']['Required'])) {
                $errorNumber = -1;
                $errorMessage = 'Caller Id is required';
            } elseif ($part != 'BatchSend') {
                if (isset($failedRules[$voice]['Required'])) {
                    $errorNumber = -2;
                    $errorMessage = 'voice file is required and should be mp3,wav or gsm';
                }
            } elseif (isset($failedRules['transfer_digit']['RequiredWith'])) {
                $errorNumber = -3;
                $errorMessage = 'Transfer Digit is required if transfer option is selected';
            } elseif (isset($failedRules['transfer_digit']['RequiredWith'])) {
                $errorNumber = -4;
                $errorMessage = 'Transfer Digit is required if transfer limit is set';
            } elseif (isset($failedRules['transfer_options']['RequiredWith'])) {
                $errorNumber = -5;
                $errorMessage = 'Transfer Options is required if transfer digit is selected';
            } elseif (isset($failedRules['callback_digit']['RequiredWith'])) {
                $errorNumber = -6;
                $errorMessage = 'Callback digit is required if callback fie is selected';
            } elseif (isset($failedRules[$callback]['RequiredWith'])) {
                $errorNumber = -7;
                $errorMessage = 'Callback File is required if callback digit selected';
            } elseif (isset($failedRules[$doNotCall]['RequiredWith'])) {
                $errorNumber = -8;
                $errorMessage = 'Do not call File is required if do not call digit selected';
            } elseif (isset($failedRules['do_not_call_digit']['RequiredWith'])) {
                $errorNumber = -9;
                $errorMessage = 'Do not call digit is required if do not call file is selected';
            }
            $Error = new \stdClass;
            $Error->errorNumber = $errorNumber;
            $Error->errorMessage = $errorMessage;
            return $Error;
        }

        return false;

    }

}
