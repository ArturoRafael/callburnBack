<?php 
namespace App\Http\Services;

use App\Http\Models\File;
use File as LaravelFile;
use App\Helper;
use App\Http\Models\Users;
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
    public function createFromText($text, $language, $gender, $userEmail, $savedFrom = null)
    {
        $response = (object)[
            'file' => NULL,
            'error' => NULL,
        ];


        $user = Users::find($userEmail);

        $pattern = '/\d{4,}/';
        preg_match_all($pattern, $text, $matches);
        $replacement = $patterns = [];
        foreach (current($matches) as $key => $row) {
            $patterns[$key] = "/{$row}/";
            $replacement[$key] = implode('-', str_split($row));
        }
        $text = preg_replace($patterns, $replacement, $text);

        $ttsPrice = 0;
        $isFile = $this->file->where('tts_language', $gender.' / '.$language)->where('tts_text', $text)->where('user_email', $user->email)->first();
        if($isFile){
            // $isFile->saved_from = $savedFrom;
            // $file = $this->copyFile($isFile, $user->email, 1);
            // if($file == false){
            //     $response->error = "Ocurrio un error al copiar el audio";
            //     return $response;
            // }
            // $file->save();
            
            $response->file = $isFile;
            return $response;
        }
        $ttsEngine = config('tts.engine');
        if($ttsEngine == 'SPEECH'){
            $nuanceTTSRepo = new \App\Http\Services\TTS\TextToSpeechTTSService();
            $resp = $nuanceTTSRepo->createFromText($text, $language, $gender, $user, $ttsPrice, $savedFrom);
        }else{
            $response->error = "no_tts_configured";
            return $response;
        }
        
        if(!$resp){
            $response->error = "endpoint_connecting_failed";
            return $response;
        }
        

        if(!$resp->was_copied) {
            $this->moveAudioFileToAmazon($resp->map_filename);
            $this->moveAudioFileToAmazon($resp->stripped_name . '.mp3');
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
       
       
        if(file_exists(public_path('/uploads/audios_calls/'.$file[0]['map_filename']))){            
        
            $newFile = $this->file->create([
                'orig_filename' => $file[0]['orig_filename'],
                'map_filename' => $file[0]['map_filename'],
                'extension' => $file[0]['extension'],
                'stripped_name' => $file[0]['stripped_name'],
                'user_email' => $userId,
                'length' => $file[0]['length'],
                'type' => $file[0]['type'],
                'tts_language' => $file[0]['tts_language'],
                'tts_text' => $file[0]['tts_text'],
                'saved_from' => $file[0]['saved_from'],
                'cost' => $file[0]['cost'],
                'is_template' => $isTemplate,            
                ]);

            //$this->moveAudioFileToAmazon($newFile->map_filename);
            //$this->moveAudioFileToAmazon($newFile->stripped_name . '.gsm');
            return $newFile;
        
        }else{
            return false;
        }
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
        $gsmAudioFileName = $file->stripped_name  . '.mp3';
//        if(file_exists(public_path() . '/uploads/audio/' . $gsmAudioFileName)){
//
//        }

        $path = public_path() . '/uploads/audios_calls/' . $gsmAudioFileName;

        if(!LaravelFile::exists($path)){
            return false;
        }

        $gsmFileSize = LaravelFile::size(public_path() . '/uploads/audios_calls/' . $gsmAudioFileName);
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
        

        $filePath = public_path() . '/uploads/audios_calls/' . $fileName;

        if(!file_exists($filePath)){
            return false;
        }
       

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