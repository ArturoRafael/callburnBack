<?php 

namespace App\Http\Services\Cache;

/**
* This calss is responsible for caching all data to show in metronic.
* All functions are structured the same way
* Getting param incrementBy which can also be negative (as redis works that way)
* and param countryCode for shwoing filtered data by each country manager
*/
class AdminsDashboardRedisCacheService
{
	function __construct()
	{
		$this->redis = \Redis::connection();
	}

	/**
	 * Increment the amount of total recharges.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTotalRecharges($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'total_recharges');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'total_recharges', $incrementBy);
		} else{
			$this->updateTotalRecharge($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'total_recharges', $incrementBy);
			} else{
				$this->updateTotalRecharge($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of total gift.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTotalGift($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'total_gift');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'total_gift', $incrementBy);
		} else{
			$this->updateTotalGift($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'total_gift', $incrementBy);
			} else{
				$this->updateTotalGift($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of total billed.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTotalBilled($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'total_billed');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'total_billed', $incrementBy);
		} else{
			$this->updateTotalBilled($hashKey, null);
		}
 
		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'total_billed', $incrementBy);
			} else{
				$this->updateTotalBilled($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of verification cost
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTotalVerificationCost($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'total_verification_cost');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'total_verification_cost', $incrementBy);
		} else{
			$this->updateTotalVerificationCost($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'total_verification_cost', $incrementBy);
			} else{
				$this->updateTotalVerificationCost($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of isp cost.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementIspCost($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'isp_cost');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'isp_cost', $incrementBy);
		} else{
			$this->updateIspCost($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'isp_cost', $incrementBy);
			} else{
				$this->updateIspCost($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of retained credits.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementReatinedCredits($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'retained_credits');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'retained_credits', $incrementBy);
		} else{
			$this->updateRetainedCredit($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'retained_credits', $incrementBy);
			} else{
				$this->updateRetainedCredit($hashKey, $countryCode);
			}
		}
		return true;
	}


	/**
	 * Increment live calls count
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementLiveCallsCount($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'live_calls_now');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'live_calls_now', $incrementBy);
		} else{
			$this->updateLiveCalls($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'live_calls_now', $incrementBy);
			} else{
				$this->updateLiveCalls($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of total calls
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTotalCallsMade($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'total_calls');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'total_calls', $incrementBy);
		} else{
			$this->updateTotalCalls($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'total_calls', $incrementBy);
			} else{
				$this->updateTotalCalls($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of billed calls.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTotalBilledCalls($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'total_billed_calls');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'total_billed_calls', $incrementBy);
		} else{
			$this->updateBilledCalls($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'total_billed_calls', $incrementBy);
			} else{
				$this->updateBilledCalls($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of verification calls.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTotalVerificationCalls($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'total_verification_calls');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'total_verification_calls', $incrementBy);
		} else{
			$this->updateTotalVerificationCalls($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'total_verification_calls', $incrementBy);
			} else{
				$this->updateTotalVerificationCalls($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of successful verification calls.
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementSuccessfulVerificationCalls($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'successful_verification_calls');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'successful_verification_calls', $incrementBy);
		} else{
			$this->updateSuccessfulVerificationCalls($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'successful_verification_calls', $incrementBy);
			} else{
				$this->updateSuccessfulVerificationCalls($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment the amount of replays count
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementReplaysCount($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'replays_count');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'replays_count', $incrementBy);
		} else{
			$this->updateReplaysCount($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'replays_count', $incrementBy);
			} else{
				$this->updateReplaysCount($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment transfers count
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementTransfersCount($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'transfers_count');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'transfers_count', $incrementBy);
		} else{
			$this->updateTransfersCount($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'transfers_count', $incrementBy);
			} else{
				$this->updateTransfersCount($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment callback count
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementCallbacksCount($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'callbacks_count');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'callbacks_count', $incrementBy);
		} else{
			$this->updateCallbacksCount($hashKey, null);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'callbacks_count', $incrementBy);
			} else{
				$this->updateCallbacksCount($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Increment do not calls count
	 *
	 * @param $countryCode
	 * @return bool
	 */
	public function incrementDoNotCallsCount($incrementBy, $countryCode = null)
	{
		$hashKey = 'dashboardData';
		$ifExists = $this->redis->hExists($hashKey, 'do_not_calls_count');
		if($ifExists){
			$this->redis->hIncrByFloat($hashKey, 'do_not_calls_count', $incrementBy);
		} else{
			$this->updateDoNotCallsCount($hashKey, $countryCode);
		}

		if($countryCode){
			$hashKey = $countryCode + '-dashboardData';
			if($ifExists){
				$this->redis->hIncrByFloat($hashKey, 'do_not_calls_count', $incrementBy);
			} else{
				$this->updateDoNotCallsCount($hashKey, $countryCode);
			}
		}
		return true;
	}

	/**
	 * Get dashboard data from cache.
	 * 
	 * @param string $from
	 * @param string $to
	 * @return array
	 */
	public function getDashboardDataFromCache($from, $to, $countryCode = null)
	{
		$hashKey = $countryCode ? $countryCode . '-dashboardData' : 'dashboardData';
		if($from){
			$hashKey .= '-from-' . $from;
		}
		if($to){
			$hashKey .= '-to-' . $to;
		}
		$dashboardData = $this->redis->hGetAll($hashKey);
		if(!isset($dashboardData['total_recharges'])){
			$dashboardData['total_recharges'] = $this->updateTotalRecharge($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['total_gift'])){
			$dashboardData['total_gift'] = $this->updateTotalGift($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['total_billed'])){
			$dashboardData['total_billed'] = $this->updateTotalBilled($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['total_verification_cost'])){
			$dashboardData['total_verification_cost'] = $this->updateTotalVerificationCost($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['isp_cost'])){
			$dashboardData['isp_cost'] = $this->updateIspCost($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['retained_credits'])){
			$dashboardData['retained_credits'] = $this->updateRetainedCredit($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['live_calls_now'])){
			$dashboardData['live_calls_now'] = $this->updateLiveCalls($hashKey, $countryCode);
		}
		if(!isset($dashboardData['total_calls'])){
			$dashboardData['total_calls'] = $this->updateTotalCalls($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['total_billed_calls'])){
			$dashboardData['total_billed_calls'] = $this->updateBilledCalls($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['total_verification_calls'])){
			$dashboardData['total_verification_calls'] = $this->updateTotalVerificationCalls($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['successful_verification_calls'])){
			$dashboardData['successful_verification_calls'] = $this->updateSuccessfulVerificationCalls($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['replays_count'])){
			$dashboardData['replays_count'] = $this->updateReplaysCount($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['transfers_count'])){
			$dashboardData['transfers_count'] = $this->updateTransfersCount($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['callbacks_count'])){
			$dashboardData['callbacks_count'] = $this->updateCallbacksCount($hashKey, $countryCode, $from, $to);
		}
		if(!isset($dashboardData['do_not_calls_count'])){
			$dashboardData['do_not_calls_count'] = $this->updateDoNotCallsCount($hashKey, $countryCode, $from, $to);
		}
		return $dashboardData;
	}


	/**
	 * Update cache of total recharges
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateTotalRecharge($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalRecharges = \App\Models\Invoice::where('is_paid', 1)
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalRecharges = \App\Models\Invoice::where('is_paid', 1);
		}

		if($from){
			$totalRecharges = $totalRecharges->where('created_at', '>', $from);
		}
		if($to){
			$totalRecharges = $totalRecharges->where('created_at', '<', $to);
		}
		$totalRecharges = $totalRecharges->sum('purchased_amount');
		$this->redis->hSet($hashKey, 'total_recharges', $totalRecharges);
		return $totalRecharges;
	}

	/**
	 * Update cache of total gift
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateTotalGift($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalGift = \App\Models\Invoice::where('is_paid', 1)
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalGift = \App\Models\Invoice::where('is_paid', 1);
		}
		
		if($from){
			$totalGift = $totalGift->where('created_at', '>', $from);
		}
		if($to){
			$totalGift = $totalGift->where('created_at', '<', $to);
		}
		$totalGift = $totalGift->sum('discount_amount');
		$this->redis->hSet($hashKey, 'total_gift', $totalGift);
		return $totalGift;
	}

	/**
	 * Update cache of total billed
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateTotalBilled($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalBilled = \App\Models\Phonenumber::where('action_type','<>', 'VERIFICATION_CALL')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalBilled = \App\Models\Phonenumber::where('action_type', '<>','VERIFICATION_CALL');
		}
		
		if($from){
			$totalBilled = $totalBilled->where('created_at', '>', $from);
		}
		if($to){
			$totalBilled = $totalBilled->where('created_at', '<', $to);
		}
		$totalBilled = $totalBilled->sum('cost');
		$this->redis->hSet($hashKey, 'total_billed', $totalBilled);
		return $totalBilled;
	}

	/**
	 * Update cache of total verification cost
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateTotalVerificationCost($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalVerificationCost = \App\Models\Phonenumber::where('action_type', "VERIFICATION_CALL")
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalVerificationCost = \App\Models\Phonenumber::where('action_type', 'VERIFICATION_CALL');
		}
		
		if($from){
			$totalVerificationCost = $totalVerificationCost->where('created_at', '>', $from);
		}
		if($to){
			$totalVerificationCost = $totalVerificationCost->where('created_at', '<', $to);
		}
		$totalVerificationCost = $totalVerificationCost->sum('cost');
		$this->redis->hSet($hashKey, 'total_verification_cost', $totalVerificationCost);
		return $totalVerificationCost;
	}

	/**
	 * Update cache of is cost
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateIspCost($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalIspCost = \App\Models\Phonenumber::whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalIspCost = new \App\Models\Phonenumber();
		}
		
		if($from){
			$totalIspCost = $totalIspCost->where('created_at', '>', $from);
		}
		if($to){
			$totalIspCost = $totalIspCost->where('created_at', '<', $to);
		}
		$totalIspCost = $totalIspCost->sum('service_cost');
		$this->redis->hSet($hashKey, 'isp_cost', $totalIspCost);
		return $totalIspCost;
	}

	/**
	 * Update cache of retained credit
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateRetainedCredit($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalRetainedCredits = \App\Models\Campaign::select(\DB::raw('DISTINCT repeat_batch_grouping'))
				->whereIn('status', ['scheduled', 'saved'])
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalRetainedCredits = \App\Models\Campaign::select(\DB::raw('DISTINCT repeat_batch_grouping'))
				->whereIn('status', ['scheduled', 'saved']);
		}
		
		if($from){
			$totalRetainedCredits = $totalRetainedCredits->where('created_at', '>', $from);
		}
		if($to){
			$totalRetainedCredits = $totalRetainedCredits->where('created_at', '<', $to);
		}
		$totalRetainedCredits = $totalRetainedCredits->sum('retained_balance');
		$this->redis->hSet($hashKey, 'retained_credits', $totalRetainedCredits);
		return $totalRetainedCredits;
	}

	/**
	 * Update cache of live calls
	 *
	 * @param string $hash
	 * @return integer
	 */
	private function updateLiveCalls($hashKey, $countryCode)
	{
		if($countryCode){
			$liveCallsNow = \App\Models\Phonenumber::where('call_status', 'DIALLED')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0)->count();
		} else{
			$aserverLoadCache = new \App\Services\Cache\AsteriskLoadCacheService();
			$liveCallsNow = $aserverLoadCache->getSumOfAllLiveCalls();
		}
		
		$this->redis->hSet($hashKey, 'live_calls_now', $liveCallsNow);
		return $liveCallsNow;
	}

	/**
	 * Update cache of total calls
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateTotalCalls($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalCallsMade = \App\Models\Phonenumber::where('call_status', '!=', 'NOT_DIALLED')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalCallsMade = \App\Models\Phonenumber::where('call_status', '!=', 'NOT_DIALLED');
		}
		
		if($from){
			$totalCallsMade = $totalCallsMade->where('dialled_datetime', '>', $from);
		}
		if($to){
			$totalCallsMade = $totalCallsMade->where('dialled_datetime', '<', $to);
		}
		$totalCallsMade = $totalCallsMade->count();
		$this->redis->hSet($hashKey, 'total_calls', $totalCallsMade);
		return $totalCallsMade;
	}

	/**
	 * Update cache of billed calls
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateBilledCalls($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalBilledCallsMade = \App\Models\Phonenumber::where('action_type', '<>','VERIFICATION_CALL')
				->whereIn('call_status', ['ANSWERED', 'TRANSFER'])
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalBilledCallsMade = \App\Models\Phonenumber::where('action_type', '<>','VERIFICATION_CALL')
			->whereIn('call_status', ['ANSWERED', 'TRANSFER']);
		}
		
		if($from){
			$totalBilledCallsMade = $totalBilledCallsMade->where('dialled_datetime', '>', $from);
		}
		if($to){
			$totalBilledCallsMade = $totalBilledCallsMade->where('dialled_datetime', '<', $to);
		}
		$totalBilledCallsMade = $totalBilledCallsMade->count();
		$this->redis->hSet($hashKey, 'total_billed_calls', $totalBilledCallsMade);
		return $totalBilledCallsMade;
	}

	/**
	 * Update cache of total verification calls
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateTotalVerificationCalls($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalVerificationCalls =  \App\Models\Phonenumber::where('action_type','VERIFICATION_CALL')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalVerificationCalls =  \App\Models\Phonenumber::where('action_type','VERIFICATION_CALL');
		}
		
		if($from){
			$totalVerificationCalls = $totalVerificationCalls->where('dialled_datetime', '>', $from);
		}
		if($to){
			$totalVerificationCalls = $totalVerificationCalls->where('dialled_datetime', '<', $to);
		}
		$totalVerificationCalls = $totalVerificationCalls->count();
		$this->redis->hSet($hashKey, 'total_verification_calls', $totalVerificationCalls);
		return $totalVerificationCalls;
	}

	/**
	 * Update cache of successful verification calls
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateSuccessfulVerificationCalls($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$totalSuccessfullVerificationCalls = \App\Models\Phonenumber::where('action_type','VERIFICATION_CALL')
				->whereIn('call_status', ['ANSWERED', 'TRANSFER'])
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$totalSuccessfullVerificationCalls = \App\Models\Phonenumber::where('action_type','VERIFICATION_CALL')
				->whereIn('call_status', ['ANSWERED', 'TRANSFER']);
		}
		
		if($from){
			$totalSuccessfullVerificationCalls = $totalSuccessfullVerificationCalls->where('dialled_datetime', '>', $from);
		}
		if($to){
			$totalSuccessfullVerificationCalls = $totalSuccessfullVerificationCalls->where('dialled_datetime', '<', $to);
		}
		$totalSuccessfullVerificationCalls = $totalSuccessfullVerificationCalls->count();
		$this->redis->hSet($hashKey, 'successful_verification_calls', $totalSuccessfullVerificationCalls);
		return $totalSuccessfullVerificationCalls;
	}

	/**
	 * Update cache of replays count
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateReplaysCount($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$replaysCount = \App\Models\PhonenumberAction::where('call_status', 'REPLAY_REQUESTED')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$replaysCount = \App\Models\PhonenumberAction::where('call_status', 'REPLAY_REQUESTED');
		}
		
		if($from){
			$replaysCount = $replaysCount->where('datetime', '>', $from);
		}
		if($to){
			$replaysCount = $replaysCount->where('datetime', '<', $to);
		}
		$replaysCount = $replaysCount->count();
		$this->redis->hSet($hashKey, 'replays_count', $replaysCount);
		return $replaysCount;
	}

	/**
	 * Update cache of transfers count
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateTransfersCount($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$transfersCount = \App\Models\PhonenumberAction::where('call_status', 'TRANSFER_REQUESTED')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$transfersCount = \App\Models\PhonenumberAction::where('call_status', 'TRANSFER_REQUESTED');
		}
		
		if($from){
			$transfersCount = $transfersCount->where('datetime', '>', $from);
		}
		if($to){
			$transfersCount = $transfersCount->where('datetime', '<', $to);
		}
		$transfersCount = $transfersCount->count();
		$this->redis->hSet($hashKey, 'transfers_count', $transfersCount);
		return $transfersCount;
	}

	/**
	 * Update cache of callback count
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateCallbacksCount($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$callbacksCount = \App\Models\PhonenumberAction::where('call_status', 'CALLBACK_REQUESTED')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$callbacksCount = \App\Models\PhonenumberAction::where('call_status', 'CALLBACK_REQUESTED');
		}
		
		if($from){
			$callbacksCount = $callbacksCount->where('datetime', '>', $from);
		}
		if($to){
			$callbacksCount = $callbacksCount->where('datetime', '<', $to);
		}
		$callbacksCount = $callbacksCount->count();
		$this->redis->hSet($hashKey, 'callbacks_count', $callbacksCount);
		return $callbacksCount;
	}

	/**
	 * Update cache of do not calls count
	 *
	 * @param string $hash
	 * @param string $from
	 * @param string $to
	 * @return integer
	 */
	private function updateDoNotCallsCount($hashKey, $countryCode, $from = null, $to = null)
	{
		if($countryCode){
			$doNotCallsCount = \App\Models\PhonenumberAction::where('call_status', 'DONOTCALL_REQUESTED')
				->whereHas('user', function($query) use($countryCode){
					$query->where('country_code', $countryCode);
				}, '>', 0);
		} else{
			$doNotCallsCount = \App\Models\PhonenumberAction::where('call_status', 'DONOTCALL_REQUESTED');
		}
		
		if($from){
			$doNotCallsCount = $doNotCallsCount->where('datetime', '>', $from);
		}
		if($to){
			$doNotCallsCount = $doNotCallsCount->where('datetime', '<', $to);
		}
		$doNotCallsCount = $doNotCallsCount->count();
		$this->redis->hSet($hashKey, 'do_not_calls_count', $doNotCallsCount);
		return $doNotCallsCount;
	}
}