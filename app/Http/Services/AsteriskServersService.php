<?php 

namespace App\Http\Services;

use App\Http\Models\AsteriskServer;
use App\Http\Services\Cache\AsteriskLoadCacheService;

class AsteriskServersService{

	/**
     * Object of the AsteriskServer class
     *
     * @var asteriskServerModel
     */
    private $asteriskServerModel;

	/**
	 * Create a new instance of AsteriskServersService class
	 *
	 * @return void
	 */
	public function __construct(AsteriskServer $asteriskServerModel)
	{
		$this->asteriskServerModel = $asteriskServerModel;
	}

	/**
	 * Get Asterisk server using ip address
	 *
	 * @param string $ipAddress
	 * @return AsteriskServer
	 */
	public function getAsteriskServerByIpAddress($ipAddress)
	{
		return $this->asteriskServerModel->where('ip', $ipAddress)->first();
	}

	/**
	 * Get self asterisk server , from which the command is being run
	 *
	 * @return AsteriskServer
	 */
	public function getCurrentAsteriskServer()
	{
		$envsThatShouldUseFirst = ['test', 'local'];
		$currentEnv = config('app.env');
		if( in_array($currentEnv, $envsThatShouldUseFirst) ) {
			return $this->asteriskServerModel->first();
		}
		$selfIpAddress = exec("ifconfig eth1 2>/dev/null|awk '/inet addr:/ {print $2}'|sed 's/addr://'");
		$asteriskServer = $this->asteriskServerModel->where('ip', $selfIpAddress)
			->whereIn('status', ['RUNNING', 'BOOTING', 'BLOCK_AS_MAX_LOAD_REACHED'])
			->first();
		if($asteriskServer && $asteriskServer->status == 'BOOTING'){
			$asteriskServer->status = 'RUNNING';
			$asteriskServer->save();
		}
		return $asteriskServer;
	}

	/**
	 * Check if asterisk server can make calls at this moment
	 *
	 * @param AsteriskServer $asteriskServer
	 * @return bool
	 */
	public function canMakeCallsNow($asteriskServer)
	{
		$asteriskLoadCacheRepo = new AsteriskLoadCacheService();
		$currentLiveCallsCount = $asteriskLoadCacheRepo->getLiveCalls($asteriskServer->id);
		return $currentLiveCallsCount < $asteriskServer->server_max_concurrent_calls;
	}

	/**
	 * Can queue call now . 
	 * We are checking if the asterisk server can handle the call
	 * inside some period of time configured in .env
	 *
	 * @param AsteriskServer $asteriskServer
	 * @param integer $audioLenght
	 * @return bool
	 */
	public function canQueueCalls($asteriskServer, $audioLenght)
	{
		$asteriskLoadCacheRepo = new AsteriskLoadCacheService();
		//Get total seconds that are running and queues on this asterisk server
		$currentTotalSeconds = $asteriskLoadCacheRepo->getTotalSeconds($asteriskServer->id);
		//Get total count of phonenumbers that are running and queued on this asterisk server
		$currentTotalCalls = $asteriskLoadCacheRepo->getTotalCalls($asteriskServer->id);
		//We will add 10 seconds to audioLength
		//This is estimate time that asterisk server will take
		//for making a call and updating status
		$audioLenght += 10;
		//Capacity is the maximum concurrent calls that server can handle
		$capacity = $aserver->server_max_concurrent_calls;
		//We need to udnerstand how much it will take for the system to 
		//finsih all the current queued calls .
		$currentlyWillTakeSeconds = $currentTotalSeconds / $capacity;
		//Return true if all the current calls can be done inside 300 seconds .
		return $currentlyWillTakeSeconds < 300;
	}

}