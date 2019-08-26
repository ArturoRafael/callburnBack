<?php

namespace App\Http\Controllers;

use App\Http\Services\NumberVerificationService;
use App\Http\Services\TariffService;
use Illuminate\Http\Request;
use App\Http\Models\CallerId;
use App\Http\Models\Phonenumber;
use App\Http\Models\Country;
use App\Http\Models\Tariff;
use App\Http\Services\VerificationService;
use JWTAuth;

class VerificationsController extends BaseController
{


    /**
     * Create a new instance of VerificationsController class
     *
     * @return void
     */
    public function __construct()
    {
        $this->verificationRepo = new VerificationService();
    }

    /**
     * Send request for receiving verification code.
     * POST /verification/send-verification
     *
     * @param Request $request
     * @return JSON
     */
    public function postSendVerificationCode (Request $request) 

    {
        $ipAddress = $request->ip();
        $phonenumber = $request->get('phonenumber');
        $userId = null;
        $ifLogged = JWTAuth::parseToken()->authenticate();
        if ($ifLogged) {
            $userId = $ifLogged->email;
        }else{
             return $this->sendError('El usuario debe estar logeado.');
        }
        
        $phone = $this->verificationRepo->verifyPhonenumbers($phonenumber);
        if(!$phone){
            return $this->sendError('Número no válido '.$phonenumber);
        }


        if ($ifLogged) {
            $callerId = CallerId::where('phone_number', $phonenumber)
                                ->where('user_email', $userId)->first();
            if ($callerId) {
                if($caller_id->is_verified == 1){
                    return $this->sendError('Ya se encuentra registrado y validado el caller_id');    
                }else{
                    CallerId::where('phone_number', $phonenumber)
                            ->where('user_email', $userId)->delete();
                }                
            }
        }
            
        $finalPhonenumber = new Phonenumber();
        $finalPhonenumber->user_email = $userId;
        $finalPhonenumber->phone_no = $phonenumber;
        $finalPhonenumber->country_id = $countries->_id;
        $finalPhonenumber->total_cost = 0;
        $finalPhonenumber->type = "NOT_CHECKED";        
        $finalPhonenumber->action_type = "VERIFICATION_CALL";        
        $finalPhonenumber->save();
        
        return $this->sendResponse($finalPhonenumber->toArray(), 'El sistema está enviando el codigo de confirmación');;
        
    }

    /**
     * Check call status
     * GET /verifications/check-call-status
     *
     * @param Request $request
     * @return JSON
     */
    public function getCheckCallStatus(Request $request)
    {
        $phonenumberId = $request->get('phonenumber_id');
        $phonenumber = Phonenumber::find($phonenumberId);
        if (!$phonenumber) {
            return $this->sendError('Numero no encontrado');  
        }
        
        $status = $phonenumber->status;
        return $this->sendResponse(['status' => $status], 'Estado del número');
    }



     /**
     * List caller
     * GET /verifications/caller-list
     *
     * @param Request $request
     * @return JSON
     */
    public function getCaller()
    {
        
        $Logged = JWTAuth::parseToken()->authenticate();
        $user = $Logged->email;

        $phonenumber = CallerId::where('user_email', $user)->get();
        if (!$phonenumber) {
            return $this->sendError('No hay números');  
        }        
        
        return $this->sendResponse($phonenumber->toArray(), '´Callers devueltos con éxito');
    }



/***********
    Metodo para Verificar numeros
***********/
    public function verifyPhonenumbers(Request $request){

        if(!is_null($request->input("phonenumber"))){
            
            $phonenumber = $this->verificationRepo->verifyPhonenumbers($request->input("phonenumber"));
            if (!$phonenumber) {
                return $this->sendError('Número no válido '.$request->input("phonenumber"));  
            }
            return $this->sendResponse('Número válido '.$request->input("phonenumber"));

        }else{
            return $this->sendError('Número no válido '.$request->input("phonenumber"));
        }
        
    }


}
