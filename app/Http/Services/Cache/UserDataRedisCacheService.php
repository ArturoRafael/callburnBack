<?php namespace App\Services\Cache;

/**
* This calss is responsible for all caching.
*/
class UserDataRedisCacheService
{
	
	function __construct()
	{
		$this->redis = \LaravelRedis::connection();
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
		} else{
			$messagesCount = \App\Models\Campaign::where('is_prototype', 0)
				->where('user_id', $userId)
				->count(\DB::raw('DISTINCT repeat_batch_grouping'));
			$this->redis->hSet($userHashKey, 'messages', $messagesCount);
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
		} else{
			$contactsCount = \App\Models\AddressBookContact::where('user_id', $userId)
				->count();
			$this->redis->hSet($userHashKey, 'contacts', $contactsCount);
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
		} else{
			$groupsCount = \App\Models\AddressBookGroup::where('user_id', $userId)
				->count();
			$this->redis->hSet($userHashKey, 'groups', $groupsCount);
		}
		return true;
	}

	/**
	 * Get all data for user
	 *
	 * @param integer $id
	 * @return array
	 */
	public function getUserInfo($userId)
	{
		$userHashKey = 'metronicUserInfo:' . $userId;
		$userData = $this->redis->hGetAll($userHashKey);
		if(!isset($userData['messages'])){
			$messagesCount = \App\Models\Campaign::where('is_prototype', 0)
				->where('user_id', $userId)
				->count(\DB::raw('DISTINCT repeat_batch_grouping'));
			$this->redis->hSet($userHashKey, 'messages', $messagesCount);
			$userData['messages'] = $messagesCount;
		}
		if(!isset($userData['audioTemplates'])){
			$templatesCount = \App\Models\File::where('is_template', 1)
				->where('user_id', $userId)
				->count();
			$this->redis->hSet($userHashKey, 'audioTemplates', $templatesCount);
			$userData['audioTemplates'] = $templatesCount;
		}
		if(!isset($userData['contacts'])){
			$contactsCount = \App\Models\AddressBookContact::where('user_id', $userId)
				->count();
			$this->redis->hSet($userHashKey, 'contacts', $contactsCount);
			$userData['contacts'] = $contactsCount;
		}
		if(!isset($userData['groups'])){
			$groupsCount = \App\Models\AddressBookGroup::where('user_id', $userId)
				->count();
			$this->redis->hSet($userHashKey, 'groups', $groupsCount);
			$userData['groups'] = $groupsCount;
		}
		return $userData;
	}
}