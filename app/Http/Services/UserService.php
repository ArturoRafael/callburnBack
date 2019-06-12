<?php 

namespace App\Http\Services;

use App\User;
use Carbon\Carbon;

class UserService
{

    /**
     * Object of User class for working with DB.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new instance of UserService.
     *
     * @param User $user
     * @return void
     */
    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Store user data into the DB .
     *
     * @param array $userData
     * @return User
     */
    public function createUser($userData)
    {
        return $this->user->create($userData);
    }

    /**
     * Get user by primary key.
     *
     * @param integer $id
     * @return $user
     */
    public function getUserByPK($id)
    {
        return $this->user->with(['numbers'])->find($id);
    }

    /**
     * Get user by email.
     *
     * @param string $email
     * @return User
     */
    public function getUserByEmail($email)
    {
        return $this->user->where('email', $email)->where('role', 'client')->with(['numbers', 'language'])->first();
    }

    /**
     * Update users data.
     *
     * @param integer $id
     * @param array $userData
     * @return bool
     */
    public function updateUser($id, $userData)
    {
        return $this->getUserByPK($id)->update($userData);
    }

    /**
     * Get user by api token.
     *
     * @param string $token
     * @return User
     */
    public function getUserByApiToken($token)
    {
    return \App\Http\Models\ApiToken::where('api_token', $token)->with('user')->first();
    }

    /**cd
     * Get user by api token.
     *
     * @param string $token
     * @return User
     */
    public function getUserByMobileApiToken($token)
    {
        return $this->user->where('mobile_api_token', $token)
            ->where('is_deleted', '!=', 1)->where('role', 'client')
            ->with(['numbers', 'language'])->first();
    }

    /**
     * Get user by upload contacts token.
     *
     * @param string $token
     * @return User
     */
    public function getUserByUploadContactToken($token)
    {
        $now = Carbon::now();
        return $this->user->where('mobile_contacts_upload_token',$token)
            ->where('mobile_contacts_upload_token_expiration','>',$now)
            ->where('is_deleted', '!=', 1)->where('role', 'client')
            ->with(['numbers', 'language'])->first();
    }

    /**
     * Get user by password token.
     *
     * @param string $token
     * @return User
     */
    public function getUserByPasswordToken($token)
    {
        return $this->user->where('password_reset', $token)->where('is_deleted', '!=', 1)->first();
    }

    /**
     * Get users retained credit
     *
     * @param User $user
     * @return float
     */
    public function getRetainedBalance($user)
    {
        $balance = $user->campaigns()
            ->where('is_prototype', 0)
            ->whereIn('status', ['scheduled', 'start'])->sum('retained_balance');
        return $balance ? $balance : 0;
    }

    /**
     * Get users retained credit from gift
     *
     * @param User $user
     * @return float
     */
    public function getRetainedGiftBalance($user)
    {
        $balance = $user->campaigns()
            ->where('is_prototype', 0)
            ->whereIn('status', ['scheduled', 'start'])->sum('retained_gift_balance');
        return $balance ? $balance : 0;
    }

    /**
     * Check if user can use gift
     *
     * @param mixed $phonenumber
     * @param User $user
     * @param float $maxGiftCost
     * @param float $cost
     */
    public function canUseGift($phonenumber, $user, $maxGiftCost, $cost)
    {
        if (!$user->bonus_criteria || $user->bonus_criteria > $phonenumber['tariff']['best_margin']) {
            return false;
        }
        return $user->bonus >= $maxGiftCost + $cost;
    }

    /**
     * Get the gift balance user has with its criteria
     * NOTE! later we will implement different gifts with different criterias
     *
     * @param User $user
     * @return object
     */
    public function getUsersGiftWithCriteria($user)
    {
        $giftInfo = (object) [
            'balance' => 0,
            'minimum_margin' => 0,
        ];
        $retainedGift = $this->getRetainedGiftBalance($user);
        $invoice = $this->getInvoiceWithMinimumMarginCriteria($user->_id);
        if ($invoice && $invoice->remaining_gift_amount > $retainedGift) {
            $giftInfo->balance = $invoice->remaining_gift_amount;
            $giftInfo->minimum_margin = $invoice->minimum_margin_criteria_for_gift;
        }
        return $giftInfo;
    }

    /**
     * Get invoice where minimum_margin_criteria_for_gift is greater than 0
     * We need to bill it first if the phonenumber criteria is satisfying us
     *
     * @param Integer $userId
     * @return Invoice
     */
    private function getInvoiceWithMinimumMarginCriteria($userId)
    {
        $invoiceModel = new \App\Http\Models\Invoice();
        return $invoiceModel->where('user_id', $userId)
            ->where('type', 'GIFT')
            ->where('remaining_gift_amount', '>', 0)
            ->where('minimum_margin_criteria_for_gift', '>', 0)
            ->first();
    }

}
