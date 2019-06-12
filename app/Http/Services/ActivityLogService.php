<?php 
namespace App\Http\Services;


class ActivityLogService{
	/**
	 * Object of ActivityLog class
	 *
	 * @var App\Models\ActivityLog
	 */
	private $activityLog;

	/**
	 * Create a new instance of ActivityLogService class.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->activityLog = new \App\Http\Models\ActivityLog();
	}

	/**
	 * Add a new log.
	 *
	 * @param array $logData
	 * @return App\Models\ActivityLog
	 */
	public function createActivityLog($logData)
	{
		$ip = \Request::ip();
		$logData['ip_address'] = $ip ? $ip : 'NOT_DETECTED';
		return $this->activityLog->create($logData);
	}
}