<?php 

namespace App\Http\Services;

use Redis;
use Auth;

class RequestRateLimitService{

	/**
	 * Object of X class for working with cache
	 *
	 * @var X
	 */
	private $cacheDriver;

	/**
	 * Create a new instance of SnippetService
	 *
	 * @param Snippet $snippet
	 * @return void
	 */
	public function __construct()
	{
		$this->cacheDriver = LaravelRedis::connection();
		$this->whiteList = [
			'89.249.203.114',
			'151.52.89.207',
			'77.230.116.123'
		];

		$this->recipientsLimiterCount = 5;
	}

	/**
	 * Check if the same call for the same snippet was done more than 5 time today
	 * or if same ip more than 15 times
	 *
	 * @param integer $snippetId
	 * @param string $phonenumber
	 * @return bool
	 */
	public function canCallRecipient($snippetId, $phonenumber, $ipAddress)
	{
		if(in_array($ipAddress, $this->whiteList)) {
			return true;
		}


		$recipientKey = $this->createRedisKeyForPhoneNumbers($snippetId, $phonenumber);
		$ipKey = $this->createRedisKeyForIp($snippetId, $ipAddress);
		if($this->cacheDriver->hGet('limiter', $recipientKey) >= $this->recipientsLimiterCount){ return false; }
		if($this->cacheDriver->hGet('limiter', $ipKey) >= 3){ return false; }
		return true;
	}


	/**
	 * Check if the verification call can be done
	 *
	 * @param string $phonenumber
	 * @param string $ipAddress
	 * @return bool
	 */
	public function canMakeVerification($phonenumber, $ipAddress)
	{
		if(in_array($ipAddress, $this->whiteList)) {
			return true;
		}
		$recipientKey = $this->createRedisKeyForVerificationPhoneNumbers($phonenumber);
		$ipKey = $this->createRedisKeyForVerificationIp($ipAddress);
		if(Auth::check()){
            if($this->cacheDriver->hGet('verification-limiter', $recipientKey) >= $this->recipientsLimiterCount){ return false; }
        }else{
            if($this->cacheDriver->hGet('verification-limiter', $ipKey) >= 3){ return false; }
        }
		return true;
	}

	/**
	 * Increment the cache for ip adn phonenumber
	 *
	 * @param integer $snippetId
	 * @param string $phonenumber
	 * @return bool
	 */
	public function inrementCallerInfo($snippetId, $phonenumber, $ipAddress)
	{
		$recipientKey = $this->createRedisKeyForPhoneNumbers($snippetId, $phonenumber);
		$ipKey = $this->createRedisKeyForIp($snippetId, $ipAddress);
		$this->cacheDriver->hIncrBy('limiter', $recipientKey, 1);
		$this->cacheDriver->hIncrBy('limiter', $ipKey, 1);
		return true;
	}


	/**
	 * Increment the cache for ip and phonenumber for verification
	 *
	 * @param string $phonenumber
	 * @param string $ipAddress
	 * @return bool
	 */
	public function inrementVerificationCache($phonenumber, $ipAddress)
	{
		$recipientKey = $this->createRedisKeyForVerificationPhoneNumbers($phonenumber);
		$ipKey = $this->createRedisKeyForVerificationIp($ipAddress);
		$this->cacheDriver->hIncrBy('verification-limiter', $recipientKey, 1);
		$this->cacheDriver->hIncrBy('verification-limiter', $ipKey, 1);
		return true;
	}


	/**
	 * Get the key for keeping counter in redis
	 *
	 * @param integer $snippetId
	 * @param string $phonenumber
	 * @return string
	 */
	private function createRedisKeyForPhoneNumbers($snippetId, $phonenumber)
	{
		$date = date('Y-m-d');
		return 'limit-number-' . $date . '-' . $snippetId . '-' . $phonenumber;
	}



	/**
	 * Get the key for keeping counter in redis ofr verification call
	 *
	 * @param string $phonenumber
	 * @return string
	 */
	private function createRedisKeyForVerificationPhoneNumbers($phonenumber)
	{
		$date = date('Y-m-d');
	//	return 'limit-verification-number-' . $date  . '-' . $phonenumber;
		return 'limit-verification-number-' . $phonenumber;
	}

	/**
	 * Get the key for keeping counter in redis
	 *
	 * @param integer $snippetId
	 * @param string $ipAddress
	 * @return string
	 */
	private function createRedisKeyForIp($snippetId, $ipAddress)
	{
		$date = date('Y-m-d');
		//return 'limit-ip-' . $date . '-' . $snippetId . '-' . $ipAddress;
		return 'limit-ip-' . $snippetId . '-' . $ipAddress;
	}

	/**
	 * Get the key for keeping counter in redis ofr verification ip
	 *
	 * @param string $ipAddress
	 * @return string
	 */
	private function createRedisKeyForVerificationIp($ipAddress)
	{
		$date = date('Y-m-d');
		return 'limit-verification-ip-' . $ipAddress;
	}
}