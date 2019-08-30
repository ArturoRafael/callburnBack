<?php

namespace App\Http\Controllers;

use App\Http\Services\NumberVerificationService;
use App\Http\Services\TariffService;
use Illuminate\Http\Request;
use App\Http\Models\CallerId;
use App\Http\Models\Phonenumber;
use App\Http\Models\NumberVerification;
use App\Http\Models\Country;
use App\Http\Models\Tariff;
use App\Http\Services\VerificationService;
use JWTAuth;
use Validator;

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
    public function postSendVerificationCode (Request $request, NumberVerificationService $numVerificationRepo)
    {
        
        $phonenumber = $request->get('phonenumber');
        $simulacion = $request->get('simulacion');
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

        if ($ifLogged){
            $callerId = CallerId::where('phone_number', $phonenumber)
                                ->where('user_email', $userId)->first();
            if ($callerId) {
                if($callerId->is_verified == 1){
                    return $this->sendError('Ya se encuentra registrado y validado el caller_id');    
                }else{
                    CallerId::where('phone_number', $phonenumber)
                            ->where('user_email', $userId)->delete();
                }                
            }
        }

        $phonconvert = $phonenumber;
        $prefix = substr($phonconvert, 0, 2);            
        $countries = Country::where('phonenumber_prefix', $prefix)->first();            
        if(!$countries){
            $prefix = substr($phonconvert, 0, 3);            
            $countries = Country::where('phonenumber_prefix', $prefix)->first();   
        }
            
        $finalPhonenumber = new Phonenumber();
        $finalPhonenumber->user_email = $userId;
        $finalPhonenumber->phone_no = $phonenumber;
        $finalPhonenumber->country_id = $countries->_id;
        $finalPhonenumber->total_cost = 0;
        $finalPhonenumber->type = (!$simulacion) ? "NOT_CHECKED" : "PERSONAL_NUMBER";        
        $finalPhonenumber->action_type = "VERIFICATION_CALL";        
        $finalPhonenumber->save();


        if($simulacion){

            $verifacion = NumberVerification::where('phone_number', $phonenumber)->first();
            if($verifacion){
                $verifacion->retries = $verifacion->retries + 1;
                $verifacion->save();
                
                if($verifacion->retries > 4){
                    $finalPhonenumber->is_locked = 1;
                    $finalPhonenumber->save();

                    return $this->sendError('El sistema está en modo simulación. Ha superado los intentos de validar número telefónico. Ha sido bloqueado.'); 
                }
            }else{
                
                $numberData = [
                    'phone_number' => $phonenumber,
                    'code' => 123456,
                    'user_email' => $userId
                ];

                $numberField = $numVerificationRepo->createNumberVerification($numberData);

                return $this->sendResponse($finalPhonenumber->toArray(), 'El sistema está en modo simulación. El código de confirmación es: 123456'); 
            }
               
        }
        return $this->sendResponse($finalPhonenumber->toArray(), 'El sistema está enviando el codigo de confirmación');  
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
     * Check call status
     * GET /verifications/check-call-status
     *
     * @param Request $request
     * @return JSON
     */
    public function postNameCallerid(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'phonenumber_id' => 'required|integer',
            'name' => 'required|string'
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $phonenumber_id = $request->input('phonenumber_id');
        $name = $request->input('name');

        $callerid = CallerId::find($phonenumber_id);
        if(!$callerid){
            return $this->sendError('Numero no encontrado');
        }        
        $callerid->name = $name;          
        $callerid->save();

        return $this->sendResponse($callerid->toArray(), 'Actualizado con éxito');

    }


    /**
     * Check code send 
     * GET /verifications/check-call-status
     *
     * @param Request $request
     * @return JSON
     */
    public function verifacation_callerid_code(Request $request, NumberVerificationService $numVerificationRepo)
    {
        $phonenumber = $request->get('phonenumber');
        $code = $request->get('voice_code');
        $simulacion = $request->get('simulacion');

        $numberField = $numVerificationRepo->getNumberVerification($code, $phonenumber);
        if (!$numberField) {
            return $this->sendError('Invalid code and phonenumber');
        }

        if($simulacion){

            $ifLogged = JWTAuth::parseToken()->authenticate();

            $callerid = new CallerId();
            $callerid->user_email = $ifLogged->email;  
            $callerid->tariff_id = 13500;
            $callerid->phone_number = $phonenumber;
            $callerid->is_verified = 1;
            $callerid->save();
        }

        return $this->sendResponse_message('Phonenumber is valid');       
        
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

        $phonenumber = CallerId::where('user_email', $user)->where('is_verified', 1)->get();
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
