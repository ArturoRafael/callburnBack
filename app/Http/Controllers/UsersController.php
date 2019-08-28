<?php

namespace App\Http\Controllers;

use App\Http\Models\Users;
use App\Http\Models\Rol;
use App\Http\Models\TypeBusiness;
use App\Http\Models\Language;
use App\Http\Models\Invoice;
use Illuminate\Http\Request;
use Validator;
use Hash;
use JWTAuth;

class UsersController extends BaseController
{
  
	/**
     *
     * @OA\Get(
     *   path="/api/auth/listar_usuarios",
     *   summary="List of all users",
     *   operationId="listar_usuarios",   
     *   tags={"Users"},     
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/Success"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   ),
     *  )
     */
    public function listar_usuarios()
    {
        $usuarios = Users::with('type_business')->with('rol')->get();

        return $this->sendResponse($usuarios->toArray(), 'Usuarios devueltos con éxito');
    }



    /**
     *
     * @OA\Post(
     *   path="/api/auth/block_user",
     *   summary="Block user",
     *   operationId="block_user",   
     *   tags={"Users"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/active"
     *    ),     
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/Success"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   ),
     *  )
     */
    public function block_user(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'active' => 'nullable|boolean',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }
        

        $user_now = JWTAuth::parseToken()->authenticate();
        $usuario = Users::find($user_now->email);
        if(!$usuario){
            return $this->sendError('Usuario no encontrado.');
        }
        
        if(is_null($input['active'])){
            $usuario->is_blocked = true;
        }else{
            $usuario->is_blocked = false;
        }

        $usuario->save();
        return $this->sendResponse($usuario->toArray(), 'El usuario ha sido desactivado.');
    }



    /**
     *
     * @OA\Post(
     *   path="/api/auth/update_timezones",
     *   summary="update timezone the user",
     *   operationId="update_timezones",   
     *   tags={"Users"},
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/timezone"
     *    ), 
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/language_id"
     *    ),          
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/Success"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   ),
     *  )
     */
    public function update_timezones(Request $request)
    {
        $input = $request->all();
        $user_now = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($input, [            
            'timezone' => 'nullable|string',
            'language_id' => 'nullable|integer'                        
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $usuario = Users::with('language')->find($user_now->email);
        if(!$usuario){
            return $this->sendError('Usuario no encontrado.');
        }

        if(!is_null($input['language_id'])){
            $lang = Language::find($input['language_id']);        
            if (is_null($lang)) {
                return $this->sendError('Lenguaje no encontrado');
            }
            $usuario->language_id = $input['language_id'];
        }

        if(!is_null($input['timezone'])){            
            $usuario->timezone = $input['timezone'];
        }

        $usuario->save();
        return $this->sendResponse($usuario->toArray(), 'Usuario actualizado con éxito');
    }

    /**
     *
     * @OA\Post(
     *   path="/api/auth/user_profile",
     *   summary="update profile the user",
     *   operationId="update_profile",   
     *   tags={"Users"},
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/email"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/password"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/c_password"
     *    ), 
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/firstname"
     *    ), 
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/lastname"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/phone"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/businessname"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/type_business"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/email_business_user"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/idrol"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/birthday"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/timezone"
     *    ), 
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/language_id"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/address"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/postal_code"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/country_code"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/city"
     *    ),     
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/Success"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   ),
     *  )
     */
    public function update_profile(Request $request)
    {
         
        $input = $request->all();
        $user_now = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($input, [
            'idrol' => 'nullable|integer',
            'type_business' => 'nullable|integer',
            'password' => 'nullable|string|min:3',
            'c_password' => 'nullable|string|min:3|same:password',
            'password_actual' => 'nullable|string|min:3',
            'firstname' => 'nullable|max:200',
            'lastname' => 'nullable|max:200',
            'phone' => 'nullable|max:200',
            'businessname' => 'nullable|required',
            'birthday' => 'nullable|date|date_format:Y-m-d',            
            'address' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string',
            'country_code' => 'nullable|string|max:2',
            'city' => 'nullable|string'            
        ]);


        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $usuario = Users::find($user_now->email);
        if(!$usuario){
            return $this->sendError('Usuario no encontrado.');
        }

        if(!is_null($input['password_actual'])){       
           if (!Hash::check($input['password_actual'], $user_now->password)) {
              return $this->sendError('La contraseña actual no es correcta', null, 401);
           }
        }
        
        if(!is_null($input['idrol'])){
            $rol = Rol::find($input['idrol']);        
            if (is_null($rol)) {
                return $this->sendError('Rol no encontrado');
            }
            $usuario->idrol = $input['idrol'];
        }

        if(!is_null($input['type_business'])){
            $type_business = TypeBusiness::find($input['type_business']);        
            if (is_null($type_business)) {
                return $this->sendError('Tipo de negocio no encontrado');
            }
            $usuario->id_type_business = $input['type_business'];
        }

        if(!is_null($input['password'])){       
            $usuario->password = bcrypt($input['password']);
        }

        $usuario->firstname = $input['firstname'];
        $usuario->lastname = $input['lastname'];
        $usuario->phone = $input['phone'];
        $usuario->businessname = $input['businessname'];
        $usuario->birthday = $input['birthday'];               
        $usuario->address = $input['address'];
        $usuario->postal_code = $input['postal_code'];
        $usuario->country_code = $input['country_code'];
        $usuario->city = $input['city'];
        
        $usuario->save();

        return $this->sendResponse($usuario->toArray(), 'Usuario actualizado con éxito');
    }




    /**
     *
     * @OA\Get(
     *   path="/api/auth/users",
     *   summary="List of user invoices",
     *   operationId="user_invoice",   
     *   tags={"Users"},     
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/Success"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   ),
     *  )
     */
    public function user_invoice()
    {

       $user_now = JWTAuth::parseToken()->authenticate();
       $invoices = Invoice::where('user_email', $user_now->email)
                        ->orderBy('invoice_date', 'desc')
                        ->get();

       return $this->sendResponse($invoices->toArray(), 'Facturas devueltos con éxito');

    }



    /**
     *
     * @OA\Post(
     *   path="/api/auth/languages_site",
     *   summary="List of the languages",
     *   operationId="languages_site",   
     *   tags={"Users"},     
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/Success"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   ),
     *  )
     */
    public function languages_site(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'json_lenguage' => 'nullable|string',
            'code' => 'nullable|string',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        if(!is_null($request->input('code'))){
            $lang = Language::where('code', $request->input('code'))->first();
            if(!$lang){
                return $this->sendError('No se encuentra el lenguage solicitado'); 
            }
            $lang->json = $request->input('json_lenguage');
            $lang->save();

        }else{
            $lang = Language::get();
        }        

        return $this->sendResponse($lang->toArray(), 'Exito');

    }



     /**
     *
     * @OA\Get(
     *   path="/api/auth/language_json",
     *   summary="Get JSON from a language",
     *   operationId="language_json",   
     *   tags={"Users"},     
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/Success"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   ),
     *  )
     */
    public function language_json(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'code' => 'required|string',
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        if(!is_null($request->input('code'))){
            $lang = Language::where('code', $request->input('code'))->first();
            if(!$lang){
                return $this->sendError('No se encuentra el lenguage solicitado'); 
            }
            return $this->sendResponse($lang->json, 'Exito');
        }
    }

}
