<?php 

namespace App\Http\Services\TTS;

use \GetId3\GetId3Core as GetId3;
use App\Helper;
use App\Http\Services\FileService;

class GoogleTTSService{

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
	public function createFromText($text, $language, $user,  $ttsPrice, $savedFrom = null)
	{
		$googleTts = $this;
		$uploadFolder = public_path() . '/uploads/audios_calls/';
		
		$newName = str_random();
		$fileExtension = 'mp3';
		$path = $uploadFolder . $newName . '.' . $fileExtension;
		$wavFileName = $uploadFolder . $newName;
		$error = $googleTts->converTextToMP3($text, $path, $language, $wavFileName);
		if($error == 'ErrorDownload'){
			return false;
		}
		$file = \App\Http\Models\File::create([
			'orig_filename' => env('APP_URL', 'http://api.nelumbo.com.co/').'uploads/audios_calls/'.$newName . '.' . $fileExtension,
			'map_filename' => $newName . '.' . $fileExtension,
			'extension' => $fileExtension,
			'stripped_name' => $newName,
			'tts_language' => $language,
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

	public function converTextToMP3($str,$outfile, $lang, $wavFileName)
	{          
	    
		$base_url='http://translate.google.com/translate_tts?ie=UTF-8&client=tw-ob&tl=' . $lang . '&ie=UTF-8&q=';
	    $words = $this->splitString($str);
	    $files=array();
	    
	    foreach($words as $word)
	    {
	        $url= $base_url.urlencode($word);
	        $filename = md5($word).".mp3";
	        //echo ".";	        
	        if(!$this->downloadMP3($url,$filename, $wavFileName))
	        {
	            return 'ErrorDownload';	                        
	        }
	        else
	        {
	            $files[] = $filename;
	        }
	    }
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

	private function downloadMP3($url,$file, $wavFileName)
	{
	    $ch = curl_init();  
	    curl_setopt($ch,CURLOPT_URL,$url);
	    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	    $output=curl_exec($ch);
	    curl_close($ch);
	    if($output === false)   
	    	return false;
	 	
	 	$uploadFolder = $wavFileName;
		//$fp = fopen($file,"wb");
	 	
		$fh = fopen($uploadFolder.'.mp3', 'wb');

        fwrite($fh, $output);
	    
	    //fwrite($fp,$output);

	    fclose($fh);

	    return true;
	}


	

}