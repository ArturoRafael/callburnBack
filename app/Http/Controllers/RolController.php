<?php

namespace App\Http\Controllers;

use App\Http\Models\Rol;
use Illuminate\Http\Request;
use Validator;

class RolController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/rol",
     *   summary="List of rols",
     *   operationId="index",   
     *   tags={"Rols"},     
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
    public function index()
    {
        $rol = Rol::paginate(15);

        return $this->sendResponse($rol->toArray(), 'Roles devueltos con éxito');
    }

 
    /**
     *
     * @OA\Post(
     *   path="/api/auth/rol",
     *   summary="create a specific rol",
     *   operationId="store",   
     *   tags={"Rols"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/description"
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }
          $rol=Rol::create($request->all());        
         return $this->sendResponse($rol->toArray(), 'Rol creado con éxito');
    }

    /**
     *
     * @OA\Get(
     *   path="/api/auth/rol/{rol}",
     *   summary="List a specific rol",
     *   operationId="show",   
     *   tags={"Rols"}, 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
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
    public function show($id)
    {
        
         $rol = Rol::find($id);


        if (is_null($rol)) {
            return $this->sendError('Rol no encontrado');
        }


        return $this->sendResponse($rol->toArray(), 'Rol devuelto con éxito');
    }



    /**
     *
     * @OA\Put(
     *   path="/api/auth/rol/{rol}",
     *   summary="update a specific rol",
     *   operationId="update",   
     *   tags={"Rols"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/description"
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
    public function update(Request $request, $id)
    {
         //
        $input = $request->all();


        $validator = Validator::make($input, [
            'description' => 'required',            
        ]);


        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $rol = Rol::find($id);        
        if (is_null($rol)) {
            return $this->sendError('Rol no encontrado');
        }

        $rol->description = $input['description'];
         $rol->save();

        return $this->sendResponse($rol->toArray(), 'Rol actualizado con éxito');
    }

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/rol/{rol}",
     *   summary="Delete the rol",
     *   operationId="destroy",   
     *   tags={"Rols"}, 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
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
    public function destroy($id)
    {
        try {
            $rol = Rol::find($id);
            if (is_null($rol)) {
                return $this->sendError('Rol no encontrado');
            }
            $rol->delete();

            return $this->sendResponse($rol->toArray(), 'Rol eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El rol no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
