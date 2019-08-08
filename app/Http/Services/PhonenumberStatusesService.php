<?php 

namespace App\Http\Services;


class PhonenumberStatusesService{

	/**
	 * Create a new instance of PhonenumberStatusesService class
	 *
	 * @return void
	 */
	public function __construct(){}

	/**
	 * Parse the data from the local storage
	 * to fit to the centralized db and make less calls
	 *
	 * @param Collection $localStatuses
	 * @return Array
	 */
	public function parseLocalStatuses($localStatuses)
	{
		$phonenumbersFinalArray = [];
		foreach ($localStatuses as $record) {
			$userfield = explode( " ", $record->userfield );
			$callId = isset($userfield[0]) ? $userfield[0] : NULL;
			$status = isset($userfield[1]) ? $userfield[1] : NULL;
			$isVerification = isset($userfield[2]) ? $userfield[2] : NULL;
			$status = $this->getOurStatus($status);
			if(!$status){
				continue;
			}

			$duration = in_array( config('app.env'), ['production', 'beta'] ) ? $record->billsec : $record->duration;
			if(!$duration){$duration = 0;}

			$phonenumbersFinalArray[$callId] = [
				'call_status' => $status,
				'duration' =>  $duration,
				'dialled_datetime' => $record->calldate,
				'uniqueid' => $record->id,
				'is_verification' => $isVerification
			];
		}
		return $phonenumbersFinalArray;
	}

	/**
	 * Get mapping to our db and asterisk server statuses
	 *
	 * @param string $status
	 * @return string
	 */
	private function getOurStatus($status){
		$mappingArray = [
			'7' 	=> 'CHANNEL_UNAVAILABLE',
			'11' 	=> 'CONGESTION',
			'4' 	=> 'BUSY',
			'3' 	=> 'NO_ANSWER',
			'10' 	=> 'ANSWERED',
			'5' 	=> 'TRANSFER',
			'13' 	=> 'FATAL_ERROR',
		];
		if(!isset($mappingArray[$status])){
			return false;
		}
		return $mappingArray[$status];
	}
}