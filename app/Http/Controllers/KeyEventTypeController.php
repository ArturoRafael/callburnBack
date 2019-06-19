<?php

namespace App\Http\Controllers;

use App\Http\Models\KeyEventType;
use Illuminate\Http\Request;
use Validator;
class KeyEventTypeController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/keyeventype",
     *   summary="List of key event type",
     *   operationId="index",   
     *   tags={"KeyEventTypes"},     
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
        $key_type = KeyEventType::paginate(15);
        return $this->sendResponse($key_type->toArray(), 'Tipos claves de eventos devueltos con éxito');
    }


    /**
     *
     * @OA\Post(
     *   path="/api/auth/keyeventype",
     *   summary="create a specific key event type",
     *   operationId="store",   
     *   tags={"KeyEventTypes"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/name"
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'description' => 'required|string'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }
        
        $key_type = KeyEventType::create($request->all());        
        return $this->sendResponse($key_type->toArray(), 'Tipo clave de evento creado con éxito');
    }

    /**
     *
     * @OA\Get(
     *   path="/api/auth/keyeventype/{keyeventype}",
     *   summary="List a specific key event type",
     *   operationId="show",   
     *   tags={"KeyEventTypes"}, 
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
        $key_type = KeyEventType::find($id);
        if (is_null($key_type)) {
            return $this->sendError('Tipo clave de evento no encontrado');
        }
        return $this->sendResponse($key_type->toArray(), 'Tipo clave de evento devuelto con éxito');
    }

   
    /**
     *
     * @OA\Put(
     *   path="/api/auth/keyeventype/{keyeventype}",
     *   summary="update a specific key event type",
     *   operationId="update",   
     *   tags={"KeyEventTypes"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/name"
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
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string|max:100',
            'description' => 'required|string'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $key_type = KeyEventType::find($id);        
        if (is_null($key_type)) {
            return $this->sendError('Tipo clave de evento no encontrado');
        }

        $key_type->name = $input['name'];
        $key_type->description = $input['description'];
        $key_type->save();

        return $this->sendResponse($key_type->toArray(), 'Tipo clave de evento actualizado con éxito');
    }

    
    /**
     *
     * @OA\Delete(
     *   path="/api/auth/keyeventype/{keyeventype}",
     *   summary="Delete the key event type",
     *   operationId="destroy",   
     *   tags={"KeyEventTypes"}, 
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
            $key_type = KeyEventType::find($id);
            if (is_null($key_type)) {
                return $this->sendError('Tipo clave de evento no encontrado');
            }
            $key_type->delete();

            return $this->sendResponse($key_type->toArray(), 'Tipo clave de evento eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El tipo clave de evento no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
