<?php 

namespace App\Http\Services;

use DB;

use Carbon\Carbon;
use App\Http\Models\Phonenumber;
use App\Http\Services\AsteriskServersService;
use App\Http\Services\LogService;

class ClickToCallService{

	/**
     * Object of the AsteriskServersService class
     *
     * @var asteriskServersRepo
     */
    private $asteriskServersRepo;

	/**
	 * Create a new instance of CampaignCronService class
	 *
	 * @return void
	 */
	public function __construct(AsteriskServersService $asteriskServersRepo, Phonenumber $phonenumberModel ,LogService $logRepo)
	{
		$this->asteriskServersRepo = $asteriskServersRepo;
		$this->phonenumberModel = $phonenumberModel;
		$this->logRepo = $logRepo;
	}

	
	/**
	 * Get phonenumbers for calling
	 *
	 * @return Collection
	 */
	public function getPhonenumbersForCron($aserver)
	{
		$aserverId = $aserver->id;
		$response = (object) [
		    'phonenumbers' => NULL,
		    'phonenumberIds' => NULL,
		    'error' => NULL
		];
        $ifAsteriskIsRunning = exec('pidof asterisk');
		if(!$ifAsteriskIsRunning) {
        	throw new \Exception("Asterisk is crashed");
        }
		$callsThatSystemCanHandle = exec('for pid in $(pidof asterisk); do pidstat 1 1 -p $pid ; done | awk \'{ load = $6} END { print 45 - int(load)}\'');
        $this->logRepo->log('CPU can handle  - ' . $callsThatSystemCanHandle);
		if($callsThatSystemCanHandle <= 0){
			if($aserver->status == 'RUNNING') {
				$aserver->status = 'BLOCK_AS_MAX_LOAD_REACHED';
            	$aserver->save();
			}
        	$response->error = 'Asterisk is overloaded';
            return $response;
		} elseif($aserver->status == 'BLOCK_AS_MAX_LOAD_REACHED') {
			$aserver->status = 'RUNNING';
            $aserver->save();
		}
		
		try{
			DB::beginTransaction();
			$now = Carbon::now();
			$phonenumbers =  $this->phonenumberModel
				->where('is_locked', 0)
				->where('status', 'IN_PROGRESS')
				->whereNotNull('snippet_id')
				->where(function($query) use($now){
					$query->where('first_scheduled_date', '<=', $now)
						->orWhereNull('first_scheduled_date');
				})
				->where('retries', '<', 2)
				->whereHas('user_balance', function($userQuery){
					$userQuery->where('balance_available', '>', 0);
				}, '>', 0)
				->whereHas('calls', function($query){
					$query->whereIn('call_status', ['SENT_TO_ASTERISK', 'DIALLED']);
				}, '=', 0)
				->with(['tariff.isps', 'user'])
				->orderBy('updated_at', 'ASC')
				->orderBy('id', 'ASC')
				->limit($callsThatSystemCanHandle)
				->lockForUpdate()
				->get();
			//\Log::info($phonenumbers);

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
			$this->logRepo->log($e->getMessage());
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
		if(!$phonenumberIds){return;}
		return Phonenumber::whereIn('id', $phonenumberIds)->update([
				'is_locked' => 0, 
				'locked_at' => NULL
			]);
	}

}