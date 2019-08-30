<?php 

namespace App\Http\Services;

use DB;
use App\Http\Models\Phonenumber;
use App\Http\Models\Workflow;
use App\Http\Models\Country;
use Carbon\Carbon;

class VerificationService{

	

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
				->with(['user','workflow'])
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



	/***********
        Sanitize PhoneNumbers
    ***********/
    public function sanitizePhonenumbers($phonenumbers){
        $phonenumbers_leng = preg_match("/^[0-9]{9,15}$/", $phonenumbers);
        if(!$phonenumbers_leng){
           return false;
        }
        $phonenumbers = str_replace(chr(13), ',', $phonenumbers);
        $phonenumbers = str_replace(chr(10), ',', $phonenumbers);
        $phonenumbers = str_replace(';', ',', $phonenumbers);
        $phonenumbers = str_replace('|', ',', $phonenumbers);
        $phonenumbers = str_replace(' ', '', $phonenumbers);
        $phonenumbers = explode(',', $phonenumbers);

        return $phonenumbers;
    }


 /***********
    Metodo para Verificar numeros
***********/
    public function verifyPhonenumbers($phonenumber){
       
            $phonenumber = $this->sanitizePhonenumbers($phonenumber);
            if(!$phonenumber){
                return false;
            }
            $phonconvert = $phonenumber[0];
            $prefix = substr($phonconvert, 0, 2);            
            $countries = Country::where('phonenumber_prefix', $prefix)->first();            
            if(!$countries){
                $prefix = substr($phonconvert, 0, 3);            
                $countries = Country::where('phonenumber_prefix', $prefix)->first();   
            }
            if(is_null($countries)){
                return false;
            }

            return true;
        
    }

}