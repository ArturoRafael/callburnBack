<?php 

namespace App\Http\Services;

use File;
use App\Http\Models\Local\Extension;
use App\Http\Services\FileSystemService;
use App\Http\Models\Local\Extension as ExtensionModel;
use App\Http\Models\Local\Queue;
use App\Http\Models\Local\QueueMember;
use App\Http\Models\Local\Moh;
use App\Http\Models\Local\AudioFile;
use Storage;

class AsteriskService{

	/**
	 * Create a new instance of AsteriskService class
	 *
	 * @return void
	 */
	public function __construct(){}

	/**
	 * Create dial plan for the campaign
	 * Process includes creating standard configurations 
	 * for asterisk machine in asterisk table
	 *
	 * @param Campaign $campaign
	 * @return bool
	 */
	public function createDialPlan($campaign)
	{
		$this->createIVRQueue( $campaign );
		
        $filesToMove = [];
        if($campaign->voiceFile){
            $filesToMove[] = $campaign->voiceFile->stripped_name . '.gsm';
        }
        if($campaign->callbackFile){
            $filesToMove[] = $campaign->callbackFile->stripped_name . '.gsm';
        }
        if($campaign->doNotCallFile){
            $filesToMove[] = $campaign->doNotCallFile->stripped_name . '.gsm';
        }
        $fileSystemRepo = new FileSystemService();
        foreach ($filesToMove as $fileToMove) {
            $status = $fileSystemRepo->moveFromS3ToLocal($fileToMove, '');
        }
        return true;
	}

    /**
     * Generate call file for click-to-call
     *
     * @param string $recipient
     * @param string $callerId
     * 
     */
    public function createClickToCallFile(
        $phonenumberId, 
        $phonenumber,
        $dialOutContext,
        $context,
        $isp,
        $callerId, 
        $callId, 
        $waitLimit,
        $mohValue)
    {
        //Check if callfiles directory exists and create if not
        $callFilesDir = config('app.call_files_path');
        if(!File::isDirectory($callFilesDir)){
            File::makeDirectory($callFilesDir);
        }
        //Generate a name for the file
        //File name has the following format
        // <phonenumberId>_<phonenumber>_<hash>.call
        $hash = md5(microtime());
        $filePath = $callFilesDir . $phonenumberId . '_' . $phonenumber . '_' . $hash . '.call';
        //If file exitst just don't do anything
        //this is not usual case
        if(File::exists($filePath)){return false;}
        //Generate the call file content
        $data = "Channel:Local/$isp$phonenumber" . "@" . $dialOutContext . "/n" . PHP_EOL;
        $data .= "Context:" . $context . PHP_EOL;
        $data .= "Extension:s" . PHP_EOL;
        $data .= "Priority:1" . PHP_EOL;
        $data .= 'CallerID:' . $callerId . PHP_EOL;
        $data .= "WaitTime:60000" . PHP_EOL;
        $data .= "Set:IVRTOCONNECT=" . $context . PHP_EOL;
        $data .= "Set:__DNUM=$phonenumber" . PHP_EOL;
        $data .= "Set:__RECID=$callId" . PHP_EOL;
        $data .= "Set:WAITLIMIT=$waitLimit" . PHP_EOL;
        //$data .= "Set:__QUEUEISP=$isp" . PHP_EOL;
        $data .= "Set:MOH=$mohValue" . PHP_EOL;
        $data .= "Set:__QUEUENAME=ctc_queue_$phonenumberId" . PHP_EOL;

        $size = file_put_contents($filePath, $data);
        if ($size == 0) {
            $msg = 'Unable to generate call file for phonenumber ID: ' . $phonenumberId . ' Phonenumber: ' . $phonenumber;
            \Log::info($msg);
            return false;
        }
        chmod( $filePath, 0775 );
        return true;
    }

	/**
	 * Generate a call file .
	 * The file will be taken by asterisk and will make a call
	 *
	 * @param string $phonenumber
	 * @param string $callerId
	 * @param string $ispConfig
	 * @param integer $callId
	 * @param integer $campaignId
	 * @param string $dialPlanContext
	 * @param string $dialOutContext
	 * @param string $context
	 * @return bool
	 */
	public function createStandardCallFile(
		$phonenumber, 
		$callerId, 
		$ispConfig, 
		$callId,
		$campaignId,
		$dialPlanContext,
		$dialOutContext,
		$context)
	{
        //Check if callfiles directory exists and create if not
        $callFilesDir = config('app.call_files_path');
        if(!File::isDirectory($callFilesDir)){
        	File::makeDirectory($callFilesDir);
        }
        //Generate a name for the file
        //File name has the following format
        // <campaignId>_<phonenumber>_<hash>.call
        $hash = md5(microtime());
        $filePath = $callFilesDir . $campaignId . '_' . $phonenumber . '_' . $hash . '.call';
        //If file exitst just don't do anything
        //this is not usual case
        if(File::exists($filePath)){return false;}
        //Generate the call file content
        $data = "Channel:Local/$ispConfig$phonenumber" . "@" . $dialOutContext . "/n" . PHP_EOL;
        $data .= "Context:" . $context . PHP_EOL;
        $data .= "Extension:s" . PHP_EOL;
        $data .= "Priority:1" . PHP_EOL;
        $data .= "Set:__DNUM=$phonenumber" . PHP_EOL;
        $data .= "Set:__RECID=$callId" . PHP_EOL;
        $data .= "Set:__CAMPAIGNID=$campaignId" . PHP_EOL;
        $data .= "Set:IVRTOCONNECT=" . $dialPlanContext . PHP_EOL;
        $data .= 'CallerID:+' . $callerId . PHP_EOL;
        $data .= "WaitTime:60000" . PHP_EOL;

        $size = file_put_contents($filePath, $data);
        if ($size == 0) {
            $msg = 'Unable to generate call file for Campaign ID: ' . $campaignId . ' Phonenumber: ' . $phonenumber;
        	\Log::info($msg);
            return false;
        }
        chmod( $filePath, 0775 );
        return true;
	}

	/**
	 * Generate a verification call file
	 *
	 * @param string $phonenumber
	 * @param string $callId
	 * @param string $callerId
	 * @param string $ispConfig
	 * @param string $lang
	 * @param string $code
	 * @param string $context
	 * @return bool
	 */
	public function createVerificationCallFile(
		$phonenumber,
		$callId,
		$callerId,
		$ispConfig,
		$lang,
		$code,
		$context)
	{
        //Check if callfiles directory exists and create if not
        $callFilesDir = config('app.call_files_path');
        if(!File::isDirectory($callFilesDir)){
        	File::makeDirectory($callFilesDir);
        }
        //Generate a name for the file
        //File name has the following format
        //verification_call_<hash>_<phonenumber>_.call
        $hash = md5(microtime());
        $filePath = $callFilesDir . 'verification_call_' . $hash . '_' . $phonenumber . '_.call';
        //If file exitst just don't do anything
        //this is not usual case
        if(File::exists($filePath)){return false;}
        //Split the code to letters
        $splittedCode = str_split($code);
        $data = "Channel:Local/$ispConfig$phonenumber" . "@pincode-dial-out" . "/n" . PHP_EOL;
        $data .= "Context:pincode-" . $lang   . PHP_EOL;
        $data .= "Extension:s" . PHP_EOL;
        $data .= "Priority:1" . PHP_EOL;
        $data .= "Set:__DNUM=$phonenumber" . PHP_EOL;
        $data .= "Set:__RECID=$callId" . PHP_EOL;
        $data .= "Set:__CAMPAIGNID=0" . PHP_EOL;
        $data .= "Set:IVRTOCONNECT=pincode-" . $lang . PHP_EOL;
        $data .= 'CallerID:+' . $callerId . PHP_EOL;
        $data .= 'Set:PINCODE1=' . $splittedCode[0] . PHP_EOL;
        $data .= 'Set:PINCODE2=' . $splittedCode[1] . PHP_EOL;
        $data .= 'Set:PINCODE3=' . $splittedCode[2] . PHP_EOL;
        $data .= 'Set:PINCODE4=' . $splittedCode[3] . PHP_EOL;
        $data .= "WaitTime:60000" . PHP_EOL;
        $size = file_put_contents($filePath, $data);
        if ($size == 0) {
            $msg = 'Unable to make verification call for phonenumber with id ' . $callId;
        	\Log::info($msg);
            return false;
        }
        chmod( $filePath, 0775 );
        return true;
	}

	/**
	 * Create IVR queue for the campaign
	 *
	 * @param Campaign $campaign
	 * @return bool 
	 */
	private function createIVRQueue($campaign) {
        $replayDigit = $campaign['replay_digit'];
        $transferDigit = $campaign['transfer_digit'];
        $callbackDigit = $campaign['callback_digit'];
        $callbackDigitFileName = $campaign->callbackFile? $campaign->callbackFile->stripped_name : '';
        $doNotCallDigit = $campaign['do_not_call_digit'];
        $doNotCallDigitFileName = $campaign->doNotCallFile? $campaign->doNotCallFile->stripped_name : '';
        $campaignVoiceFileName = $campaign->voiceFile->stripped_name;
        
        $loopCount = isset( $campaign['playback_count'] ) ? $campaign['playback_count'] : 1;
        $transferOptions = array_filter( explode(',', $campaign['transfer_option']) );
        $onlyLiveAnswer = $campaign['live_answer_only'];
        $campaignId = trim($campaign['_id']);

        if( !$replayDigit && !$transferDigit && !$callbackDigit && !$doNotCallDigit){ 
            $ifThereIsInteraction = 0;
        } else{
            $ifThereIsInteraction = 1;
        }

        # Delete all the previous entries, if any.
        $this->deletePhonenumberConfiguration($campaignId, 'cb_queue_');

        $priorityCounter = 1;
        $extensionsArray = Array();

        $extensionsArray[] = Array('s', $priorityCounter++, 'Set', 'MSG=custom/callburn/' . $campaignVoiceFileName);
        if($loopCount != 1){
            $extensionsArray[] = Array('s', $priorityCounter++, 'Set', 'LOOPCOUNT=0');
        }
        if($ifThereIsInteraction){
            $extensionsArray[] = Array('s', $priorityCounter++, 'Set', 'iLOOPCOUNT=0');
            if($replayDigit){
                $extensionsArray[] = Array( 's', $priorityCounter++, 'Set', 'rLOOPCOUNT=0');
            }
        }
        $extensionsArray[] = Array( 's', $priorityCounter++, 'Set', '_IVR_CONTEXT=${IVRTOCONNECT}' );
        $waitAction = $onlyLiveAnswer ? $priorityCounter + 4 : $priorityCounter + 1;
        $extensionsArray[] = Array( 's', $priorityCounter++, 'GotoIf', '$[${CDR(disposition)} = ANSWERED]?s,' . $waitAction); //TOBEFIXED
        $extensionsArray[] = Array( 's', $priorityCounter++, 'Answer', '');

        if(!$ifThereIsInteraction) {
            $extensionsArray[] = Array('s', $priorityCounter++, 'Set', 'MUTEAUDIO(in)=on'); //MUTE AUDIO ON VOICE MESSAGES WITHOUT INTERACTION
        }

        if($onlyLiveAnswer){
            $extensionsArray[] = Array( 's', $priorityCounter++, 'AMD', '');
            $extensionsArray[] = Array( 's', $priorityCounter++, 'GotoIf', '$[${AMDSTATUS} = HUMAN]?s,8:a,1');
        } else{
            $extensionsArray[] = Array( 's', $priorityCounter++, 'Wait', '2'); //CHANGING TO 2 BECAUSE OF SOME CUSTOMERS CLAIMING THAT MESSAGE STARTS TOO FAST
        }
        if($ifThereIsInteraction){
            $extensionsArray[] = Array( 's', $priorityCounter++, 'Set', 'TIMEOUT(digit)=3');
            $extensionsArray[] = Array( 's', $priorityCounter++, 'Set', 'TIMEOUT(response)=5');
        }

        if(!$ifThereIsInteraction) {
            $extensionsArray[] = Array( 's', $priorityCounter++, 'Exec', 'Playback(${MSG})');
        } else{
            $extensionsArray[] = Array( 's', $priorityCounter++, 'Exec', 'Background(${MSG})');
        }

        if($loopCount != 1){
            $extensionsArray[] = Array( 'l', 1, 'Set', 'LOOPCOUNT=$[${LOOPCOUNT} + 1]');
            $extensionsArray[] = Array( 'l', 2, 'GotoIf', '$[${LOOPCOUNT} = ' . $loopCount . ']?h,1');
            $extensionsArray[] = Array( 'l', 3, 'Goto', 's,' . $waitAction);

        }
        if($ifThereIsInteraction){
        $extensionsArray[] = Array( 's', $priorityCounter++, 'WaitExten', ',');
        }

        if(!$ifThereIsInteraction) {
            $extensionsArray[] = Array( 's', $priorityCounter++, 'Wait', '1'); //WAIT TO NOT CLOSE IMMEDIATELY THE CALL IF NO INTERACTION
        }
 
        if($loopCount != 1){
            $extensionsArray[] = Array( 's', $priorityCounter++, 'Goto', 'l,1'); //LOOPCOUNT RETURN
        }

        $extensionsArray[] = Array( 't', 1, 'Hangup', '');
        $extensionsArray[] = Array( 'h', 1, 'Hangup', '');

        if($onlyLiveAnswer){
            $extensionsArray[] = Array( 'a', 1, 'Set', 'INSERT_ACTION()=AMD');
            $extensionsArray[] = Array( 'a', 2, 'Hangup', '');
        }
        if($ifThereIsInteraction){
            $extensionsArray[] = Array( 'i', 1, 'Set', 'iLOOPCOUNT=$[${iLOOPCOUNT} + 1]'); //INVALID KEY COUNTER
            $extensionsArray[] = Array( 'i', 2, 'GotoIf', '$[${iLOOPCOUNT} > 3]?h,1'); //INVALID KEY COUNTER
            $extensionsArray[] = Array( 'i', 3, 'Goto', 's,' . $waitAction); //INVALID KEY COUNTER
        }

        if($replayDigit !== NULL){
            $extensionsArray[] = Array( $replayDigit, 1, 'Set', 'rLOOPCOUNT=$[${rLOOPCOUNT} + 1]');
            $extensionsArray[] = Array( $replayDigit, 2, 'GotoIf', '$[${rLOOPCOUNT} > 3]?h,1');
            $extensionsArray[] = Array( $replayDigit, 3, 'Set', 'INSERT_ACTION()=REPLAY_REQUESTED');
            $extensionsArray[] = Array( $replayDigit, 4, 'Goto', 's,' . $waitAction);
        }

        if($callbackDigit !== NULL){
            $extensionsArray[] = Array( $callbackDigit, 1, 'Set', 'INSERT_ACTION()=CALLBACK_REQUESTED');
            $extensionsArray[] = Array( $callbackDigit, 2, 'Playback', 'custom/callburn/' . $callbackDigitFileName);
        }

        if($doNotCallDigit !== NULL){
            $extensionsArray[] = Array( $doNotCallDigit, 1, 'Set', 'INSERT_ACTION()=DONOTCALL_REQUESTED');
            $extensionsArray[] = Array( $doNotCallDigit, 2, 'Playback', 'custom/callburn/' . $doNotCallDigitFileName);
        }

        if($transferDigit !== NULL){
            $extensionsArray[] = Array( $transferDigit, 1, 'Set', 'CDR(userfield)=${RECID} 5');
            $extensionsArray[] = Array( $transferDigit, 2, 'Set', 'CALLERID(num)=+${DNUM}');
          //$extensionsArray[] = Array( $transferDigit, 3, 'Set', 'TRANSFER=1');
            $extensionsArray[] = Array( $transferDigit, 3, 'Set', 'MONITOR_FILENAME=/var/spool/asterisk/monitor/callburn/transfer_digit-${RECID}');
            $extensionsArray[] = Array( $transferDigit, 4, 'Set', 'INSERT_ACTION()=TRANSFER_REQUESTED');
            $extensionsArray[] = Array( $transferDigit, 5, 'Set', 'INSERT_LIVE()=LIVE_TRANSFER');
            $extensionsArray[] = Array( $transferDigit, 6, 'Queue', 'cb_queue_' . $campaignId . ',r,,');
        }
         
        $finalExtensionsArray = [];
        foreach ($extensionsArray as $arr) {
        	$finalExtensionsArray[] = [
        		'context' => 'cb_' . $campaignId,
        		'exten' => $arr[0],
        		'priority' => $arr[1],
        		'app' => $arr[2],
        		'appdata' => $arr[3]
        	];
        }
        ExtensionModel::insert($finalExtensionsArray);
       
       	$queueData = $this->prepareQueueDataForPhonenumber($campaignId, 'cb_queue_');

        $status = \DB::connection('local_mysql')->table('queue')->insert([$queueData]);

        if( count($transferOptions) > 0 ){
        	$queueMembers = [];
            foreach ($transferOptions as $rec) {
                $callerId = CallerId::where('phone_number', $rec)->with('tariff.bestIsp')->first();
                if(!$callerId){
                    \Log::info('missing caller id');
                    //return;
                }
                $bestIsp = $callerId->tariff->bestIsp;
                if(!$bestIsp){
                    \Log::info('missing best isp');
                    //return;
                }
            	$queueMembers[] = [
            		'membername' => NULL,
            		'queue_name' => 'cb_queue_' . $campaignId,
            		'interface' => 'Local/' . $bestIsp->config . $rec . '@queue-dial-out/n',
            		'penalty' => NULL,
            		'paused' => 0
            	];
            }
            QueueMember::insert($queueMembers);
        }
        return true;

    }

    /**
     * Move audio file to the folder,
     * Update the database with file info
     * Create queue and queue members
     *
     * @param Phonenumber $phonenumber
     * @param Snippet $snippet
     * @return void
     */
    public function createCTCConfigsPlan($phonenumber, $snippet)
    {
        $phonenumberId = $phonenumber->_id;
        //If snippet has file attached, means that customer using
        //custom ring tone for it . So we need to configure asterisk db
        //and move the file accordingly .
        if($snippet->file) {
            $ifFileCreated = $this->createClickToCallRingtoneConfigs($snippet->file, $snippet->_id);
        }
        $ifExist = Queue::where('name', 'ctc_queue_' . $phonenumberId)
            ->first();
        if(!$ifExist){
            $queueData = $this->prepareQueueDataForPhonenumber($phonenumberId, 'ctc_queue_');
            $status = Queue::insert([$queueData]);
        }
        $oldQueueMembers = QueueMember::where('queue_name', 'ctc_queue_' . $phonenumberId)
            ->select('uniqueid')->get()->pluck('uniqueid')->all();
        $transferOptions = $snippet->callerId;
        if( count($transferOptions) > 0 ){
            $queueMembers = [];
            foreach ($transferOptions as $callerId) {
                $bestIsp = $callerId->tariff->bestIsp;
                if(!$bestIsp){
                    \Log::info('missing best isp');
                    return;
                }
                $queueMembers[] = [
                    'membername' => NULL,
                    'queue_name' => 'ctc_queue_' . $phonenumberId,
                    'interface' => 'Local/' . $bestIsp->config . $callerId->phone_number . '@clicktocall-queue/n',
                    'penalty' => NULL,
                    'paused' => 0
                ];
            }
            QueueMember::insert($queueMembers);
        }
        QueueMember::whereIn('uniqueid', $oldQueueMembers)->delete();
        return true;
    }

    /**
     * Delete all configurations of the campaign with given id
     *
     * @param integer $phonenumberId
     * @return bool
     */
    private function deletePhonenumberConfiguration($phonenumberId, $prefix){
        ExtensionModel::where('context', $prefix . $phonenumberId)->delete();
        Queue::where('context', $prefix . $phonenumberId)->delete();
        QueueMember::where('queue_name', $prefix . $phonenumberId)->delete();
        AudioFile::where('context', $prefix . $phonenumberId)->delete();
        return true;
    }

    /**
     * Get Queue data depending on phonenumber id
     *
     * @param integer $phonenumberId
     * @return array
     */
    private function prepareQueueDataForPhonenumber($phonenumberId, $prefix)
    {
        return [
            'name' => $prefix . $phonenumberId,
            //'musiconhold' => '',
            //'announce' => '',
            'context' => $prefix . $phonenumberId,
            'timeout' => 60,
            //'monitor_join' => '',
            'monitor_format' => 'gsm',
            'queue_youarenext' => 'silence/1',
            'queue_thereare' => 'silence/1',
            'queue_callswaiting' => 'silence/1',
            //'queue_holdtime' => '',
            //'queue_minutes' => '',
            //'queue_seconds' => '',
            //'queue_lessthan' => '',
            //'queue_thankyou' => '',
            //'queue_reporthold' => '',
            'announce_frequency' => 0,
            //'announce_round_seconds' => '',
            'announce_holdtime' => 'no',
            'retry' => 3,
            'wrapuptime' => 0,
            'maxlen' => 0,
            //'servicelevel' => '',
            'strategy' => 'ringall',
            'joinempty' => 'no',
            'leavewhenempty' => 'no',
            'eventmemberstatus' => 0,
            //'eventwhencalled' => '',
            //'reportholdtime' => '',
            //'memberdelay' => '',
            'weight' => 0,
            //'timeoutrestart' => '',
            //'periodic_announce' => '',
            'periodic_announce_frequency' => 0,
            //'ringinuse' => '',
            //'setinterfacevar' => ''
        ];
    }

    /**
     * Create configuration for the ring tone file
     *
     * @param File $file
     * @return bool
     */
    public function createClickToCallRingtoneConfigs($file, $snippetId)
    {
        $fileSystemRepo = new FileSystemService();
        $prefix = $snippetId . '-snippet/';
        $moveFileResponse = $fileSystemRepo->moveFromS3ToLocal($file->map_filename, $prefix, 'ctc_ringtone');
        
        $mohObject = Moh::where('directory', '/var/lib/asterisk/moh/callburn/' . $prefix)->first();
        if(!$mohObject){
            $mohObject =  Moh::insert([[
            'name' => $file->map_filename,
            'directory' => '/var/lib/asterisk/moh/callburn/' . $prefix,
            'mode' => 'files',
            'format' => 'mp3'
            ]]);
        } elseif($mohObject->name != $file->map_filename) {
            Storage::disk('ctc_ringtone')->delete($prefix . $mohObject->name);
            $mohObject->name = $file->map_filename;
            $mohObject->save();
        }
        return true;
    }













}