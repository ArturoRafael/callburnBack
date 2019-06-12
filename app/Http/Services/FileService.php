<?php 
namespace App\Services;

use App\Models\File;
use App\Contracts\FileInterface;
use File as LaravelFile;
use App\Helper;
use App\User;
use Carbon\Carbon;
use Storage;
use Illuminate\Support\Facades\DB;

class FileService{

    /**
     * Object of File class for working with database
     *
     */
    private $file;

    /**
     * Create a new instance of FileService
     *
     * @param File $file
     * @return void
     */
    public function __construct()
    {
        $this->file = new File;
    }

    /**
     * Create a new file.
     *
     * @param array $inputs
     * @return File 
     */
    public function createFile($inputs)
    {
        $file = $this->file->create($inputs);
        return $file;
    }

    /**
     * Update file data by id.
     *
     * @param integer $id
     * @param array $inputs
     * @return bool
     */
    public function updateFile($id, $inputs)
    {
        return $this->getFileByPK($id)->update($inputs);
    }

    /**
     * Remove file by primary key.
     *
     * @param integer $id
     * @return bool
     */
    public function removeFile($id)
    {
        $file = $this->getFileByPK($id);
        if($file){
            return Storage::delete($file->map_filename);
            /*if(LaravelFile::exists(public_path() . '/uploads/audio/' . $file->map_filename)){
                LaravelFile::delete(public_path() . '/uploads/audio/' . $file->map_filename);
            }
            return $file->delete();*/
        }
        return true;
    }

    /**
     * Get file by primary key.
     *
     * @param integer $id
     * @return File
     */
    public function getFileByPK($id)
    {
        return $this->file->find($id);
    }

    /**
     * Get file by name.
     *
     * @param string $name
     * @return File
     */
    public function getFileByName($name)
    {
        return $this->file->where('map_filename', $name)->first();
    }

    /**
     * Create an audio file from text
     *
     * @param string $text
     * @param string $language
     * @param string $userId
     * @return File
     */
    public function createFromText($text, $language, $userId, $savedFrom = null)
    {
        $response = (object)[
            'file' => NULL,
            'error' => NULL,
        ];
        $user = \App\User::find($userId);

        $pattern = '/\d{4,}/';
        preg_match_all($pattern, $text, $matches);
        $replacement = $patterns = [];
        foreach (current($matches) as $key => $row) {
            $patterns[$key] = "/{$row}/";
            $replacement[$key] = implode('-', str_split($row));
        }
        $text = preg_replace($patterns, $replacement, $text);

        // $country = $user->country;
        // if(!$country){
        //     $response->error = "country_of_the_caller_id_is_not_defined_Please_add_caller_id";
        //     return $response;
        // }
        // $dailyTTSLimit = $country->free_tts_count_per_day;
        
        // $todayCreatedTTSCount = $user->today_created_tts_count;
        // if($user->last_tts_created_at){
        //     $lastCreatedTTS = Carbon::createFromFormat( 'Y-m-d H:i:s', $user->last_tts_created_at );
        //     $now = Carbon::now();
        //     $diffInHours = $lastCreatedTTS->diffInHours($now);
        //     $ifLastCreatedToday = ($diffInHours < 24);
        // } else{
        //     $ifLastCreatedToday = false;
        // }

        // if(!$ifLastCreatedToday){
        //     $user->today_created_tts_count = 0;
        //     $todayCreatedTTSCount = 0;
        //     $user->last_tts_created_at = Carbon::now();
        // }
        // if( $user->role == 'administrator' || 
        //     $dailyTTSLimit == 0 ||
        //     $todayCreatedTTSCount < $dailyTTSLimit
        // ){
        //     $ttsPrice = 0;
        // } else{
        //     if($user->country && $user->country->tts_price){
        //         $ttsPrice = $user->country->tts_price;
        //     } else{
        //         $ttsPrice = 0.01;
        //     }
        // }

        // if($user->balance < $ttsPrice){
        //     $response->error = "balance_is_not_enough";
        //     return $response;
        // } else{
        //     User::where('_id', $userId)->update([
        //                'balance' => \DB::raw('balance - ' . $ttsPrice),
        //                'billed_amount' => \DB::raw('billed_amount + ' . $ttsPrice)
        //             ]);
        // }

        $ttsPrice = 0;
        $isFile = $this->file->where('tts_language', $language)->where('tts_text', $text)->first();
        if($isFile){
            $isFile->saved_from = $savedFrom;
            $file = $this->copyFile($isFile, $user->_id, 1);
            $response->file = $file;
            return $response;
        }
        $ttsEngine = config('tts.engine');
        if($ttsEngine == 'GOOGLE'){
            $googleTTSRepo = new \App\Services\TTS\GoogleTTSService();
            $resp = $googleTTSRepo->createFromText($text, $language, $user, $ttsPrice, $savedFrom);
        } elseif($ttsEngine == 'NUANCE'){
            $nuanceTTSRepo = new \App\Services\TTS\NuanceTTSService();
            $resp = $nuanceTTSRepo->createFromText($text, $language, $user, $ttsPrice, $savedFrom);

        } elseif($ttsEngine == 'BING'){
            $bingTTSRepo = new \App\Services\TTS\BingTTSService();
            $resp = $bingTTSRepo->createFromText($text, $language, $user, $ttsPrice, $savedFrom);
        } else{
            $response->error = "no_tts_configured";
            return $response;
        }
        if(!$resp){
            $response->error = "endpoint_connecting_failed";
            return $response;
        }

        $user->today_created_tts_count++;

        $user->save();

        $cacheService = new \App\Services\Cache\UserDataRedisCacheService();
        $cacheService->incrementAudioTemplates($user->_id, 1);

        if(!$resp->was_copied) {
            $this->moveAudioFileToAmazon($resp->map_filename);
            $this->moveAudioFileToAmazon($resp->stripped_name . '.gsm');
        }
        unset($resp->was_copied);
        $response->file = $resp;
        return $response;
    }

    /**
     * Copy file for new user.
     *
     * @param string $userId
     * @param File $file
     * @return File
     */
    public function copyFile( $file, $userId, $isTemplate = false )
    {
        $newName = str_random();
        $uploadFolder = public_path() . '/uploads/audio/';

        $fileExtension = $file->extension;
        Storage::copy($file->map_filename, $newName . '.' . $fileExtension);
        Storage::copy($file->stripped_name . '.gsm', $newName . '.gsm');
        //\File::copy($uploadFolder . $file->map_filename, $uploadFolder . $newName . '.' . $fileExtension);
        //\File::copy($uploadFolder . $file->stripped_name . '.gsm', $uploadFolder . $newName . '.gsm');
        $newFile = $this->file->create([
            'orig_filename' => $newName . '.' . $fileExtension,
            'map_filename' => $newName . '.' . $fileExtension,
            'extension' => $fileExtension,
            'stripped_name' => $newName,
            'user_id' => $userId,
            'length' => $file->length,
            'type' => $file->type,
            'tts_language' => $file->tts_language,
            'tts_text' => $file->tts_text,
            'saved_from' => $file->saved_from,
            'cost' => $file->cost,
            'is_template' => $isTemplate
            ]);
        //$this->moveAudioFileToAmazon($newFile->map_filename);
        //$this->moveAudioFileToAmazon($newFile->stripped_name . '.gsm');
        return $newFile;
    }

    /**
     * Get file size.
     *
     * @param integer $id
     * @return integer
     */
    public function getFileSizeByPK($id)
    {
        $file = $this->getFileByPK($id);

        //return $file ? $file->length : false;
        //$gsmAudioFileName = $file->stripped_name  . '.wav';
        $gsmAudioFileName = $file->stripped_name  . '.gsm';
//        if(file_exists(public_path() . '/uploads/audio/' . $gsmAudioFileName)){
//
//        }
        $path = public_path() . '/uploads/audio/' . $gsmAudioFileName;
        $gsmFileSize = LaravelFile::size(public_path() . '/uploads/audio/' . $gsmAudioFileName);
        return round( $gsmFileSize / 1.716 );
    }



    /**
     * Move audio file to amazon s3 and remove from local
     *
     * @param string $fileName
     * @return bool
     */
    public function moveAudioFileToAmazon($fileName)
    {
        $filePath = public_path() . '/uploads/audio/' . $fileName;

//        if(!LaravelFile::exists($filePath)){
//            return false;
//        }
        //dd($fileName);

        $status = Storage::put($fileName, file_get_contents($filePath));
        //LaravelFile::delete($filePath);
        return true;
    }


    public static function moveImageToAmazon($fileName, $filePath)
    {

        if(!LaravelFile::exists($filePath)){
            return false;
        }

        $status = Storage::put($fileName, file_get_contents($filePath . $fileName));
        //LaravelFile::delete($filePath);
        return true;
    }





}