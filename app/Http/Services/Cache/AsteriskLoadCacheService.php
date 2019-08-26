<?php 

namespace App\Http\Services\Cache;


class AsteriskLoadCacheService
{
	function __construct()
	{
		$this->redis = \Redis::connection();
	}

	/**
	 * Increment the amount of live calls.
	 *
	 * @param integer $incrementBy
	 * @param integer $aserverId
	 * @return bool
	 */
	public function incrementLiveCalls($incrementBy, $aserverId)
	{
		$hashKey = 'aserverLoadData:' . $aserverId;
		$ifExists = $this->redis->hExists($hashKey, 'live_calls');
		if($ifExists){
			$this->redis->hIncrBy($hashKey, 'live_calls', $incrementBy);
		} else{
			$this->redis->hSet($hashKey, 'live_calls', $incrementBy);
		}
	}

	/**
	 * Get live calls of the campaign
	 *
	 * @param integer $aserverId
	 * @return integer
	 */
	public function getLiveCalls($aserverId){
		$hashKey = 'aserverLoadData:' . $aserverId;
		$ifExists = $this->redis->hExists($hashKey, 'live_calls');
		if($ifExists){
			return $this->redis->hGet($hashKey, 'live_calls');
		} else{
			return 0;
		}
	}

	/**
	 * Get sum of all live calls
	 *
	 * @return integer
	 */
	public function getSumOfAllLiveCalls(){
		$lastAserver = \App\Models\AsteriskServer::select(['_id'])->orderBy('_id', 'DESC')->first();
		if(!$lastAserver){
			return 0;
		}
		$id = $lastAserver->_id;
		$sum = 0;
		for($i = 1; $i <= $id; $i++){
			$sum += $this->getLiveCalls($i);
		}
		return $sum;
	}

	/**
	 * Increment the amount of total calls.
	 *
	 * @param integer $incrementBy
	 * @param integer $aserverId
	 * @return bool
	 */
	public function incrementTotalCalls($incrementBy, $aserverId)
	{
		$hashKey = 'aserverLoadData:' . $aserverId;
		$ifExists = $this->redis->hExists($hashKey, 'total_calls');
		if($ifExists){
			$this->redis->hIncrBy($hashKey, 'total_calls', $incrementBy);
		} else{
			$this->redis->hSet($hashKey, 'total_calls', $incrementBy);
		}
	}


	/**
	 * Get live calls of the campaign
	 *
	 * @param integer $aserverId
	 * @return integer
	 */
	public function getTotalCalls($aserverId){
		$hashKey = 'aserverLoadData:' . $aserverId;
		$ifExists = $this->redis->hExists($hashKey, 'total_calls');
		if($ifExists){
			return $this->redis->hGet($hashKey, 'total_calls');
		} else{
			return 0;
		}
	}

	/**
	 * Increment the amount of second of calls
	 *
	 * @param integer $incrementBy
	 * @param integer $aserverId
	 * @return bool
	 */
	public function incrementCallsSecond($incrementBy, $aserverId)
	{
		$hashKey = 'aserverLoadData:' . $aserverId;
		$ifExists = $this->redis->hExists($hashKey, 'total_seconds');
		if($ifExists){
			$this->redis->hIncrBy($hashKey, 'total_seconds', $incrementBy);
		} else{
			$this->redis->hSet($hashKey, 'total_seconds', $incrementBy);
		}
	}


	/**
	 * Get total seconds
	 *
	 * @param integer $aserverId
	 * @return integer
	 */
	public function getTotalSeconds($aserverId){
		$hashKey = 'aserverLoadData:' . $aserverId;
		$ifExists = $this->redis->hExists($hashKey, 'total_seconds');
		if($ifExists){
			return $this->redis->hGet($hashKey, 'total_seconds');
		} else{
			return 0;
		}
	}
}