<?php

namespace App\Http\Controllers;

use App\Http\Models\Users;
use App\Http\Models\Rol;
use App\Http\Models\TypeBusiness;
use Illuminate\Http\Request;
use Validator;

class UsersController extends BaseController
{
  
	/**
     *
     * @OA\Get(
     *   path="/api/auth/users",
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
     *   path="/api/auth/user_profile/{user_profile}",
     *   summary="update profile the user",
     *   operationId="update_profile",   
     *   tags={"Users"},
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
    public function update_profile(Request $request, $email)
    {
         
        $input = $request->all();

        $usuario = Users::find($email);        
        if (is_null($usuario)) {
            return $this->sendError('Usuario no encontrada');
        }

        $validator = Validator::make($input, [
            'idrol' => 'integer|required',
            'type_business' => 'integer|required',
            'password' => 'string|min:3',
            'c_password' => 'string|min:3|same:password',
            'firstname' => 'requiredmax:200',
            'lastname' => 'required|max:200',
            'phone' => 'nullablemax:200',
            'businessname' => 'required',
            'email_business_user' => 'email|nullable'
        ]);


        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $rol = Rol::find($input['idrol']);        
        if (is_null($rol)) {
            return $this->sendError('Rol no encontrado');
        }

        $type_business = TypeBusiness::find($input['type_business']);        
        if (is_null($type_business)) {
            return $this->sendError('Tipo de negocio no encontrado');
        }

        $usuario->idrol = $input['idrol'];
        $usuario->id_type_business = $input['type_business'];
        $usuario->password = bcrypt($input['password']);
        $usuario->firstname = $input['firstname'];
        $usuario->lastname = $input['lastname'];
        $usuario->phone = $input['phone'];
        $usuario->businessname = $input['businessname'];
        $usuario->email_business_user = $input['email_business_user'];
        
        $usuario->save();

        return $this->sendResponse($usuario->toArray(), 'Usuario actualizado con éxito');
    }

}
