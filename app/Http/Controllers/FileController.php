<?php

namespace App\Http\Controllers;

use App\Http\Models\File;
use Illuminate\Http\Request;
use App\Http\Services\FileService;
use JWTAuth;
use Validator;

class FileController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }


    public function audioFromText(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'languageCode' =>'required|string',
            'ttsEngine' =>'required|string',                                
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $text = $request->input('text');
        $code = $request->input('languageCode');
        $gender = $request->input('ttsEngine');

        $fileservie = new FileService(); 
        $user_now = JWTAuth::parseToken()->authenticate(); 

        $file = $fileservie->createFromText($text, $code,  $gender, $user_now->email);
        $file = (array) $file;
       

        return $this->sendResponse($file, 'Audio');

    }


     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list_voices()
    {
        $ttsEngine = config('tts.text_speech_codes');
        $toOrderArray = $ttsEngine;
        $field = 'languageName';

        $position = array();
        $newRow = array();
        foreach ($toOrderArray as $key => $row) {
                $position[$key]  = $row[$field];
                $newRow[$key] = $row;
        }
        
        asort($position);
        
        $returnArray = array();
        foreach ($position as $key => $pos) {     
            $returnArray[] = $newRow[$key];
        }
        return $this->sendResponse($returnArray, 'Listado de voces por paises');
    }
    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Http\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        //
    }

   

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Http\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
