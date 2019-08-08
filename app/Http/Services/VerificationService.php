<?php 

namespace App\Http\Services;

use DB;
use App\Http\Models\Phonenumber;
use App\Http\Models\Workflow;
use App\Http\Models\Schedulation;
use Carbon\Carbon;
use App\Http\Services\AsteriskServersService;

class VerificationService{

	/**
	 * Create a new instance of CampaignCronService class
	 *
	 * @return void
	 */
	public function __construct(AsteriskServersService $asteriskServersRepo, Phonenumber $phonenumberModel)
	{
		$this->asteriskServersRepo = $asteriskServersRepo;
		$this->phonenumberModel = $phonenumberModel;
	}

	/**
	 * Get phonenumbers for calling
	 *
	 * @return Collection
	 */
	public function getPhonenumbersForCron($aserver)
	{
		$response = (object) [
		    'phonenumbers' => NULL,
		    'phonenumberIds' => NULL,
		    'error' => NULL
		];
		DB::beginTransaction();
		try{
			$phonenumbers =  $this->phonenumberModel
				->where('is_locked', 0)
				->where('status', 'IN_PROGRESS')
				->where('action_type','VERIFICATION_CALL')
				->where('retries', 0)
				->whereHas('calls', function($query){
					$query->whereIn('call_status', ['SENT_TO_ASTERISK', 'DIALLED']);
				 }, '=', 0)
                ->doesntHave('calls')
				->with(['tariff.isps', 'user','workflow'])
				->orderBy('updated_at', 'ASC')
				->orderBy('id', 'ASC')
				#TODO should implement server availability instead of 200
				->limit(200)
				->lockForUpdate()
				->get();
			//\log::info($phonenumbers);

			$phonenumberIds = $phonenumbers->pluck('id')->all();
			if(count($phonenumberIds) > 0){
				Phonenumber::whereIn('id', $phonenumberIds)
					->update([
						'is_locked' => 1, 
						'locked_at' => date('Y-m-d H:i:s')
					]);
			}
			DB::commit();
			$response->phonenumbers = $phonenumbers;
			$response->phonenumberIds = $phonenumberIds;
			return $response;
		} catch(\Exception $e){
			\Log::error($e);
			DB::rollBack();
			$response->error = 'DEAD';
			return $response;
		}
	}



	/**
	 * Unlock phonenumbers which were blocked by cron .
	 *
	 * @param array $phonenumberIds
	 * @return bool
	 */
	public function unlockPhonenumbers($phonenumberIds)
	{
		return Phonenumber::whereIn('_id', $phonenumberIds)->update([
				'is_locked' => 0, 
				'locked_at' => NULL
			]);
	}

}