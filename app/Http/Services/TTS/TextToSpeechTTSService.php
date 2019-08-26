<?php 

namespace App\Http\Services\TTS;

use App\Helper;
use App\Http\Services\FileService;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;


class TextToSpeechTTSService{

	/**
	 * Create a new instance of GoogleTTSService class
	 *
	 * @return void
	 */
	public function __construct()
	{
	    $this->fileRepo = new FileService();
	}

	/**
	 * Create an audio file from text with GOOGLE
	 *
	 * @param string $text
	 * @param string $language
	 * @param string $userId
	 * @return File
	 */
	public function createFromText($text, $language, $gender, $user,  $ttsPrice, $savedFrom = null)
	{
		
		$uploadFolder = public_path() . '/uploads/audios_calls/';
		$newName = str_random();
		$path = $uploadFolder . $newName . '.mp3';
		$wavFileName = $uploadFolder . $newName;
		// create client object
		$client = new TextToSpeechClient([
    		'credentials' => json_decode(file_get_contents(public_path().'/credencials_google_speech.json'), true)
		]);
		
		// sets text to be synthesised
		$synthesisInputText = (new SynthesisInput())
		    ->setText($text);
		// build the voice request, select the language code ("en-US") and the ssml
		// voice gender	

		if($gender == "FEMALE"){
			$voice = (new VoiceSelectionParams())
		    ->setLanguageCode($language)
		    ->setSsmlGender(SsmlVoiceGender::FEMALE);
		}else if($gender == "MALE"){
			$voice = (new VoiceSelectionParams())
		    ->setLanguageCode($language)
		    ->setSsmlGender(SsmlVoiceGender::MALE);
		}else{
			$voice = (new VoiceSelectionParams())
		    ->setLanguageCode($language)
		    ->setSsmlGender(SsmlVoiceGender::NEUTRAL);
		}

		
		// Effects profile
		$effectsProfileId = "telephony-class-application";
		// select the type of audio file you want returned
		$audioConfig = (new AudioConfig())
		    ->setAudioEncoding(AudioEncoding::MP3)
		    ->setEffectsProfileId(array($effectsProfileId))
		    ->setSpeakingRate(1);


		// perform text-to-speech request on the text input with selected voice
		// parameters and audio file type
		$response = $client->synthesizeSpeech($synthesisInputText, $voice, $audioConfig);
		$audioContent = $response->getAudioContent();
		// the response's audioContent is binary
		
		file_put_contents($path, $audioContent);

		$file = \App\Http\Models\File::create([
			'orig_filename' => env('APP_URL').'uploads/audios_calls/'.$newName . '.mp3',
			'map_filename' => $newName . '.mp3',
			'extension' => "mp3",
			'stripped_name' => $newName,
			'tts_language' => $gender.' / '.$language,
			'tts_text' => $text,
			'user_email' => $user->email,
			'type' => 'TTS',
			'saved_from' => $savedFrom,
			'is_template' => 1,
			'cost' =>  $ttsPrice,
			'length' => 1,
			'saved_from' => 'NOT_SPECIFIED'
			]);

		$file->save();
        $file_s = \App\Http\Models\File::find($file->id);
        $gsmAudioFile = Helper::_stripFileExtension($newName).'.mp3';
        $cmd = 'sox ' . $wavFileName . '.mp3 -r 8000 -c 1 ' . $uploadFolder . $gsmAudioFile . ' silence 1 0.1 1%';
        $response = shell_exec( $cmd );
        $length = $this->fileRepo->getFileSizeByPK($file_s->id);
        $file_s->length = ceil($length/1000);
        $file_s->save();
        return $file_s;
		
		
	}

	private function splitString($str)
	{
	    $ret = array();
	    $arr = explode(" ",$str);
	    $constr = '';
	    for($i = 0; $i < count($arr); $i++)
	    {
	        if(strlen($constr.$arr[$i]." ") < 98)
	        {
	            $constr =$constr.$arr[$i]." ";
	        }
	        else
	        {
	            $ret[] = $constr;
	            $constr = '';
	            $i--;
	        }
	 
	    }
	    $ret[]=$constr;
	    return $ret;
	}

	

	

}