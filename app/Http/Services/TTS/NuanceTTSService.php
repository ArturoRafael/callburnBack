<?php 

namespace App\Http\Services\TTS;

use App\Http\Helper;
use App\Http\Services\FileService;
use Cache;
use Carbon\Carbon;

class NuanceTTSService
{

    /**
     * Create a new instance of NuanceTTSService class
     *
     * @return void
     */
    public function __construct()
    {
        $this->fileRepo = new FileService();
    }

    /**
     * Create an audio file from text with NUANCE
     *
     * @param string $text
     * @param string $language
     * @param string $userId
     * @return File
     */
    public function createFromText($text, $language, $user, $ttsPrice, $savedFrom = null)
    {
        $url = $this->buildTtsRequestString($language, $text);
        $guzzleClient = new \GuzzleHttp\Client();

        set_time_limit(200);
        $attempts = 0;
        $apiResponse = null;
        while ($attempts < 20) {
            if (Cache::has('nuance_calls_count')) {
                Cache::increment('nuance_calls_count');
            } else {
                $expiresAt = Carbon::now()->addSeconds(10);
                Cache::put('nuance_calls_count', 1, $expiresAt);
            }
            $count = Cache::get('nuance_calls_count');
            if ($count <= 2) {
                try {
                    
                    $apiResponse = $guzzleClient->get($url);
                    if (Cache::has('nuance_calls_count')) {
                        Cache::decrement('nuance_calls_count');
                    }
                    break;
                } catch (\Exception $e) {
                    \Log::info($e);
                }
            }
            sleep(1);
            $attempts++;
        }
        if (!$apiResponse) {
            return false;
        }


        $responseData = $apiResponse->getBody();
        $uploadFolder = public_path() . '/uploads/audio/';
        $newName = str_random();
        $fileExtension = 'wav';
        $fh = fopen($uploadFolder . $newName . '.wav', 'w');
        fwrite($fh, $responseData);
        fclose($fh);



        $file = \App\Http\Models\File::create([
            'orig_filename' => $newName . '.' . $fileExtension,
            'map_filename' => $newName . '.' . $fileExtension,
            'extension' => $fileExtension,
            'stripped_name' => $newName,
            'tts_language' => $language,
            'tts_text' => $text,
            'user_email' => $user->email,
            'type' => 'TTS',
            'saved_from' => $savedFrom,
            'is_template' => 1,
            'cost' => $ttsPrice,
        ]);
        $gsmAudioFile = Helper::_stripFileExtension($newName) . '.gsm';
        $cmd = 'sox ' . $uploadFolder . $newName . '.' . $fileExtension . ' -r 8000 -c 1 ' . $uploadFolder . $gsmAudioFile . ' silence 1 0.1 1%';
        $response = shell_exec($cmd);
        
        $length = $this->fileRepo->getFileSizeByPK($file->id);
        $file->length = ceil($length / 1000);
        $file->save();
        return $file;
    }

    /**
     * Create url for tts.
     *
     * @param string $language
     * @param string $text
     * @return string
     */
    private function buildTtsRequestString($language, $text)
    {
        $url = 'https://tts.nuancemobility.net:443/NMDPTTSCmdServlet/tts';
        $appId = config('tts.app_id');
        $appKey = config('tts.app_key');
        $deviceId = config('tts.device_id');
        $codec = config('tts.codec');

        $fields = array(
            'appId' => urlencode($appId),
            'appKey' => urlencode($appKey),
            '_id' => urlencode($deviceId),
            'voice' => urlencode($language),
            'codec' => urlencode($codec),
            'text' => urlencode($text),
        );

        $fieldsString = "";
        foreach ($fields as $key => $value) {
            $fieldsString .= $key . '=' . $value . '&';
        }
        $fieldsString = rtrim($fieldsString, '&');

        $url .= '?' . $fieldsString;
        return $url;
    }

}
