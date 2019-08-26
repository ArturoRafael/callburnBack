<?php

namespace App\Http\Services;

use App\Http\Services\AsteriskServersService;
use App\Http\Services\AsteriskService;
use App\Http\Services\NumberVerification;
use App\Http\Services\BillingService;
use App\Http\Services\LogService;
use App\Http\Services\Cache\AdminsDashboardRedisCacheService as DashboardRedisCacheService;
use App\Http\Services\Cache\AsteriskLoadCacheService;
use Carbon\Carbon;
use DB;

class CallsService{

	/**
     * Object of the AsteriskServersService class
     *
     * @var asteriskServerRepo
     */
    private $asteriskServerRepo;

	/**
     * Object of the AsteriskService class
     *
     * @var asteriskRepo
     */
    private $asteriskRepo;

	/**
     * Object of the DashboardRedisCacheService class
     *
     * @var dashboardRedisCacheRepo
     */
    private $dashboardRedisCacheRepo;

	/**
     * Object of the AsteriskLoadCacheService class
     *
     * @var aserverLoadCache
     */
    private $aserverLoadCache;

	/**
	 * Create a new instance of CallsService class
	 *
	 * @return void
	 */
	public function __construct(
		AsteriskServersService $asteriskServerRepo, 
		AsteriskService $asteriskRepo,
		//DashboardRedisCacheService $dashboardRedisCacheRepo,
		LogService $logRepo,
		AsteriskLoadCacheService $aserverLoadCache)
	{
		$this->asteriskServerRepo = $asteriskServerRepo;
		$this->asteriskRepo = $asteriskRepo;
		//$this->dashboardRedisCacheRepo = $dashboardRedisCacheRepo;
		$this->aserverLoadCache = $aserverLoadCache;
        $this->logRepo = $logRepo;

        $environment = config('app.env');
		if(!in_array($environment, ['production', 'beta'])) {
			$environment = 'default';
		}
		$this->environment = $environment;

	}

	/**
	 * Make verification call .
	 *
	 * @param Phonenumber $phonenumber
	 * @param AsteriskServer $asteriskServer
	 * @return bool
	 */
	public function makeVerificationCall($phonenumber, $asteriskServer)
	{
		

		$tariff = $phonenumber->tariff;
		if(!$tariff || $tariff->is_blocked || $tariff->is_deleted || $tariff->is_disabled) {
			$phonenumber->status = 'NOT_SUPPORTED_NUMBER';
			$phonenumber->save();
			return false;
		}
		//If the caller id from not EU and the phonenumber is to EU
		//We need to check isp configuration and if isp is not designed for 
		//making that calls, we will just update status and return false
		$isp = $tariff->bestIsp;
		if(!$isp->can_call_from_not_eu_to_eu && $phonenumber->is_from_not_eu_to_eu){
			$phonenumber->call_status = 'CANT_CALL_DUE_TO_EU';
			$phonenumber->save();
			return false;
		}

		$canMakeCalls = $this->asteriskServerRepo->canMakeCallsNow($asteriskServer);
		if(!$canMakeCalls){
			return false;
		}
		//$callerId = "22552876";
		$data = [
	    	'user_email' => $phonenumber->user_email,
	    	'phone_number' => $phonenumber->phone_no,
	    	'tariff_id' => $phonenumber->tariff->_id
	    ];
	    $oldNumVerification = NumberVerification::where('phone_number', $phonenumber->phone_no)->first();
	    if($oldNumVerification){
	    	$code = $oldNumVerification->code;
	    	$oldNumVerification->update($data);
	    } else{
			$code = rand(1000, 9999);
	    	$data['code'] = $code;
	    	NumberVerification::create($data);
	    }

	    $phonenumberCountry = $phonenumber->tariff->country;
	    $lang = $phonenumberCountry->verification_call_language_code;
	    if(!$lang){$lang = 'en';}

	    $callerIdToUse = $phonenumberCountry->verification_call_callerid ? $phonenumberCountry->verification_call_callerid : $phonenumber->phone_no;

		$callData = $this->setCurrentCostRates($phonenumber, $isp->_id, $asteriskServer);
		$call = $phonenumber->calls()->create($callData);
		$environment = $this->environment;
		$context = config("asterisk.{$environment}.standard_context");


		$validationCallResponse = $this->asteriskRepo->createVerificationCallFile(
	    	$phonenumber->phone_no,
	    	$call->_id,
	    	$callerIdToUse,
	    	$call->isp->config,
	    	$lang,
	    	$code,
	    	$context);
	   	if(!$validationCallResponse){return false;}
		//$this->dashboardRedisCacheRepo->incrementLiveCallsCount(1, $phonenumber->tariff->country->code);
		return true;
	}

	/**
	 * Create standard call file .
	 *
	 * @param Phonenumber $phonenumber
	 * @param AsteriskServer $asteriskServer
	 * @param Snippet $snippet
	 * @return bool
	 */
	public function makeClickToCallCall($phonenumber, $asteriskServer, $snippet)
	{
		$ifDemoSnippet = config('app.demo_snippet_user_email') == $phonenumber->user->email;
		//$this->logRepo->setSlackType('ctc');
		$canMakeCalls = $this->asteriskServerRepo->canMakeCallsNow($asteriskServer);
		if(!$canMakeCalls){
            $this->logRepo->log('Asterisk server with id - ' . $asteriskServer->id . ' reached max calls count');
			return false;
		}
		//Get the country code of the user
		//We are using the code from users caler id
		$countryCode = $phonenumber->user ? $phonenumber->user->country_code : NULL;

		$ifHasDialPlan = $phonenumber->aservers->where('id', $asteriskServer->id)->first();
		if(!$ifDemoSnippet && !$ifHasDialPlan){
            $this->logRepo->log('Creating config plan for asterisk server - ' . $asteriskServer->id . ' and phonenumber - ' . $phonenumber->_id);
			$resp = $this->asteriskRepo->createCTCConfigsPlan($phonenumber, $snippet);
			if(!$resp){return false;}
			$asteriskServer->phonenumbersConfig()->attach($phonenumber->id);
			$phonenumber->aservers->push($asteriskServer);
		}

		//We will use best isp of the tariff
		//best isp is the isp with minimal cost from active isps
		$tariff = $phonenumber->tariff;
		if(!$tariff || $tariff->is_blocked || $tariff->is_deleted || $tariff->is_disabled) {
			$phonenumber->status = 'NOT_SUPPORTED_NUMBER';
			$phonenumber->save();
			return 'NOT_SUPPORTED_NUMBER';
		}
		$isp = $tariff->bestIsp;
		if(!$isp){
            $this->logRepo->log('Cant create as isp missing for phonenumber - ' . $phonenumber->phone_no . ' with id - ' . $phonenumber->_id);
			return false;
		}
		//If the caller id from not EU and the phonenumber is to EU
		//We need to check isp configuration and if isp is not designed for 
		//making that calls, we will just update status and return false
		if(!$isp->can_call_from_not_eu_to_eu && $phonenumber->is_from_not_eu_to_eu){
			$phonenumber->call_status = 'CANT_CALL_DUE_TO_EU';
			$phonenumber->save();
            $this->logRepo->log('Cant call phonenumber - ' . $phonenumber->phone_no . ' with id - ' . $phonenumber->_id . ' because of EU-NONEU');
			return 'CANT_CALL_DUE_TO_EU';
		}

		try{
			DB::beginTransaction();
			//$dialOutContext = 'test-ctc-dial-out';
			$environment = $this->environment;
			$dialOutContext = config("asterisk.{$environment}.ctc_dial_out_context");
			
			$phonenumber->retries = $phonenumber->retries + 1;
			$phonenumber->save();
			$callData = $this->setCurrentCostRates($phonenumber, $isp->_id, $asteriskServer);
			$call = $phonenumber->calls()->create($callData);


			if($ifDemoSnippet){
				$siteLanguage = $tariff->country->demo_call_language_code ? $tariff->country->demo_call_language_code: 'en';
				//$siteLanguage = $phonenumber->site_language ? $phonenumber->site_language : 'en';
				$context = 'ctc-demo-' . $siteLanguage;
				$dialOutContext = 'ctc-demo-dial-out';
			} else{
				$context = $snippet->file ?  'clicktocall-moh' : 'clicktocall-ringtone';
			}
			$mohValue = $snippet->file ? $snippet->file->map_filename :'callburn_default';
			
			$waitLimit = $snippet->wait_time * 10;

			$callerId = $snippet->callingNumber->phone_number;
			if(!$callerId) {
				$callerId = $tariff->country->click_to_call_call_callerid? $tariff->country->click_to_call_call_callerid : '+34 966 602 314';
			}


			//Create a call file, which will be moved by cron to make a call
			$callFileStatus = $this->asteriskRepo->createClickToCallFile(
		        $phonenumber->id, 
		        $phonenumber->phone_no,
		        $dialOutContext,
		        $context, 
		        $isp->config,
		        $callerId,
		        $call->_id, 
		        $waitLimit,
		        $mohValue);
			if(!$callFileStatus){
				DB::rollBack();
            	$this->logRepo->log('Call file creation failed for phonenumber - ' . $phonenumber->phone_no . ' with id - ' . $phonenumber->_id);
				return false;
			}
			DB::commit();
            $this->logRepo->log('Call file successfully created for phonenumber - ' . $phonenumber->phone_no . ' with id - ' . $phonenumber->_id);
		} catch(\Exception $e){
			DB::rollBack();
			\Log::error($e);
			return false;
		}
		return true;
	}

	/**
	 * Create standard call file .
	 *
	 * @param Phonenumber $phonenumber
	 * @param Campaign $campaign
	 * @param AsteriskServer $asteriskServer
	 * @return bool
	 */
	public function makeStandardCall($phonenumber, $campaign, $asteriskServer)
	{
		$this->logRepo->setSlackType('vm');
		$callEstimateLength = $campaign->voiceFile->length;
		//Get the country code of the user
		//We are using the code from users caler id
		$countryCode = $phonenumber->user ? $phonenumber->user->country_code : NULL;

		$ifHasDialPlan = $campaign->aservers->where('_id', $asteriskServer->_id)->first();
		if(!$ifHasDialPlan){
			$resp = $this->asteriskRepo->createDialPlan($campaign);
			$this->logRepo->log('Creating dial plan for campaign ' . $campaign->_id);
			if(!$resp){return false;}
			$asteriskServer->campaigns()->attach($campaign->_id);
			$campaign->aservers->push($asteriskServer);
		}

		$tariff = $phonenumber->tariff;
		if(!$tariff || $tariff->is_blocked || $tariff->is_deleted || $tariff->is_disabled) {
			$phonenumber->status = 'NOT_SUPPORTED_NUMBER';
			$phonenumber->save();
			$this->logRepo->log('Phonenumber marked as NOT supported ' . $phonenumber->phone_no);
			return 'NOT_SUPPORTED_NUMBER';
		}
		//We will use best isp of the tariff
		//best isp is the isp with minimal cost from active isps
		$isp = $tariff->bestIsp;
		if(!$isp){
			$phonenumber->status = 'NOT_SUPPORTED_NUMBER';
			$phonenumber->save();
			$this->logRepo->log('Phonenumber marked as NOT supported ' . $phonenumber->phone_no);
			return 'NOT_SUPPORTED_NUMBER';
		}
		//If the caller id from not EU and the phonenumber is to EU
		//We need to check isp configuration and if isp is not designed for 
		//making that calls, we will just update status and return false
		if(!$isp->can_call_from_not_eu_to_eu && $phonenumber->is_from_not_eu_to_eu){
			$phonenumber->status = 'CANT_CALL_DUE_TO_EU';
			$phonenumber->save();
			return 'CANT_CALL_DUE_TO_EU';
		}

		$hasDoneCache = false;
		try{
			DB::beginTransaction();
			$dialPlanContext = 'cb_' . $campaign->_id;
			
			//$dialOutContext = $asteriskServer->dial_out_context;
			//$context = $asteriskServer->context;
			$environment = $this->environment;
			$dialOutContext = config("asterisk.{$environment}.standard_dial_out_context");
			$context = config("asterisk.{$environment}.standard_context");
			if($campaign->apiKey && $campaign->apiKey->type == 'test'){
				$dialPlanContext = config( "asterisk.{$environment}.test_context" );
				$dialOutContext = config( "asterisk.{$environment}.test_dial_out_context" );
			}
			$phonenumber->retries = $phonenumber->retries + 1;
			$phonenumber->last_called_at = Carbon::now();
			$phonenumber->save();

			$callData = $this->setCurrentCostRates($phonenumber, $isp->_id, $asteriskServer);
			$call = $phonenumber->calls()->create($callData);

			
			//Increment dashboard data 
			//$this->dashboardRedisCacheRepo->incrementLiveCallsCount(1, $countryCode);
			//$this->dashboardRedisCacheRepo->incrementTotalCallsMade(1, $countryCode);
			
			#Commented on September 21 . no need to update last_called of campaign .
			#TOBEREMOVED in the future

			$hasDoneCache = true;
			// if($phonenumber->retries <= 1) {
			// 	$campaign->last_called = Carbon::now();
			// 	$campaign->save();
			// }
			//Put temp billing for the user, not to make more calls than user
			//can afford . 
			if( (!$campaign->apiKey || $campaign->apiKey->type == 'live' ) && $phonenumber->retries <= 1){
				$billingRepo = new BillingService();
				$billingRepo->putTempBilling($callEstimateLength, $phonenumber);
			}
			//Create a call file, which will be moved by cron to make a call
			$this->logRepo->log('Creating call File for phone number ' . $phonenumber->phone_no);
			$callFileStatus = $this->asteriskRepo->createStandardCallFile(
				$phonenumber->phone_no, 
				$campaign->caller_id, 
				$isp->config, 
				$call->_id,
				$campaign->_id,
				$dialPlanContext,
				$dialOutContext,
				$context);
			$this->logRepo->log('File created for phone number ' . $phonenumber->phone_no);
			if(!$callFileStatus){
				DB::rollBack();
				//$this->dashboardRedisCacheRepo->incrementLiveCallsCount(-1, $countryCode);
				//$this->dashboardRedisCacheRepo->incrementTotalCallsMade(-1, $countryCode);
				return 'NO';
			}
			DB::commit();
		} catch(\Exception $e){
			DB::rollBack();
			if($hasDoneCache){
				//$this->dashboardRedisCacheRepo->incrementLiveCallsCount(-1, $countryCode);
				//$this->dashboardRedisCacheRepo->incrementTotalCallsMade(-1, $countryCode);
			}
			\Log::error($e);
			return 'NO';
		}
		return 'YES';
	}

	/**
	 * Put service and customer price to the phonenumber object
	 *
	 * @param Phonenumber $phonenumber
	 * @return Phoneunmber
	 */
	private function setCurrentCostRates($phonenumber, $ispId, $asteriskServer)
	{
		$tariff = $phonenumber->tariff;
		$ispTariff = $tariff->isps->where('_id', $ispId)->first();
		//Get the service cost for 1 minute
		$serviceCostPerMinute = $ispTariff->pivot->cost;
		$arrayToPut['cost_per_minute'] = $phonenumber->tariff->country->customer_price;
		$arrayToPut['service_cost_per_minute'] = $serviceCostPerMinute;
		$arrayToPut['isp_id'] = $ispId;
		$arrayToPut['aserver_id'] = $asteriskServer->_id;
		$arrayToPut['user_id'] = $phonenumber->user_id;
		return $arrayToPut;
	}

}