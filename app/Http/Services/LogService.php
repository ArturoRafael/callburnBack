<?php 

namespace App\Http\Services;

use Log;

class LogService{

	public function __construct()
	{
		
	}

	/**
	 * Do logging depending on env
	 *
	 * @param string $logText
	 * @return void
	 */
	public function log($logText, $type = NULL)
	{
		
		try{
			
			$currentTime = date('Y-m-d H:i:s');
			$logText = $logText.' Date: '.$currentTime;
			
			\Log::info($logText);
			
		} catch(\Exception $e) {
			\Log::info($e);
		}
		
	}

	

}