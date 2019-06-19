<?php

namespace App\Http\Controllers;

use App\Http\Models\TypeBusiness;
use Illuminate\Http\Request;
use Validator;

class TypeBusinessController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/typebusiness",
     *   summary="List of type business",
     *   operationId="index",   
     *   tags={"Business"},     
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
        $type = TypeBusiness::paginate(15);

        return $this->sendResponse($type->toArray(), 'Tipos de negocios devueltos con éxito');
    }

 

    /**
     *
     * @OA\Post(
     *   path="/api/auth/typebusiness",
     *   summary="create a specific type business",
     *   operationId="store",   
     *   tags={"Business"},
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
        $type = TypeBusiness::create($request->all());        
        return $this->sendResponse($type->toArray(), 'Tipo de negocio creado con éxito');
    }


    /**
     *
     * @OA\Get(
     *   path="/api/auth/typebusiness/{typebusiness}",
     *   summary="List a specific type business",
     *   operationId="show",   
     *   tags={"Business"}, 
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
        
         $type = TypeBusiness::find($id);


        if (is_null($type)) {
            return $this->sendError('Tipo de negocio no encontrado');
        }


        return $this->sendResponse($type->toArray(), 'Tipo de negocio devuelto con éxito');
    }



    /**
     *
     * @OA\Put(
     *   path="/api/auth/typebusiness/{typebusiness}",
     *   summary="update a specific type business",
     *   operationId="update",   
     *   tags={"Business"},
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

        $type = TypeBusiness::find($id);        
        if (is_null($type)) {
            return $this->sendError('Tipo de negocio no encontrado');
        }

        $type->description = $input['description'];
         $type->save();

        return $this->sendResponse($type->toArray(), 'Tipo de negocio actualizado con éxito');
    }

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/typebusiness/{typebusiness}",
     *   summary="Delete the type business",
     *   operationId="destroy",   
     *   tags={"Business"}, 
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
            $type = TypeBusiness::find($id);
            if (is_null($type)) {
                return $this->sendError('Tipo de negocio no encontrado');
            }
            $type->delete();

            return $this->sendResponse($type->toArray(), 'Tipo de negocio eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El Tipo de negocio no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
