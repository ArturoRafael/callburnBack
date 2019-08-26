<?php 

namespace App\Http\Services\Cache;

/**
* This calss is responsible for all caching.
*/
class UserDataRedisCacheService
{
	
	function __construct()
	{
		$this->redis = \Redis::connection();
	}

	/**
	 * Increment messages count of the user by X
	 *
	 * @param integer $userId
	 * @param integer $incrementBy
	 * @return bool
	 */
	public function incrementMessages($userId, $incrementBy)
	{
		$userHashKey = 'metronicUserInfo:' . $userId;
		$ifExists = $this->redis->hExists($userHashKey, 'messages');
		if($ifExists){
			$this->redis->hIncrBy($userHashKey, 'messages', $incrementBy);
		} 
		return true;
	}

	/**
	 * Increment audio templates count of the user by X
	 *
	 * @param integer $userId
	 * @param integer $incrementBy
	 * @return bool
	 */
	public function incrementAudioTemplates($userId, $incrementBy)
	{
		$userHashKey = 'metronicUserInfo:' . $userId;
		$ifExists = $this->redis->hExists($userHashKey, 'audioTemplates');
		if($ifExists){
			$this->redis->hIncrBy($userHashKey, 'audioTemplates', $incrementBy);
		} else{
			$templatesCount = \App\Models\File::where('is_template', 1)
				->where('user_id', $userId)
				->count();
			$this->redis->hSet($userHashKey, 'messages', $templatesCount);
		}
		return true;
	}

	/**
	 * Increment contacts count of the user by X
	 *
	 * @param integer $userId
	 * @param integer $incrementBy
	 * @return bool
	 */
	public function incrementContacts($userId, $incrementBy)
	{
		$userHashKey = 'metronicUserInfo:' . $userId;
		$ifExists = $this->redis->hExists($userHashKey, 'contacts');
		if($ifExists){
			$this->redis->hIncrBy($userHashKey, 'contacts', $incrementBy);
		} 
		return true;
	}

	/**
	 * Increment groups count of the user by X
	 *
	 * @param integer $userId
	 * @param integer $incrementBy
	 * @return bool
	 */
	public function incrementGroups($userId, $incrementBy)
	{
		$userHashKey = 'metronicUserInfo:' . $userId;
		$ifExists = $this->redis->hExists($userHashKey, 'groups');
		if($ifExists){
			$this->redis->hIncrBy($userHashKey, 'groups', $incrementBy);
		} 
		return true;
	}


}