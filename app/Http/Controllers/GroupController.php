<?php

namespace App\Http\Controllers;

use App\Http\Models\Group;
use Illuminate\Http\Request;
use Validator;

class GroupController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/group",
     *   summary="List of groups",
     *   operationId="index",   
     *   tags={"Groups"},     
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
        $group = Group::paginate(15);

        return $this->sendResponse($group->toArray(), 'Grupos devueltos con éxito');
    }

 

    /**
     *
     * @OA\Post(
     *   path="/api/auth/group",
     *   summary="create a specific group",
     *   operationId="store",   
     *   tags={"Groups"},
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
          $group=Group::create($request->all());        
         return $this->sendResponse($group->toArray(), 'Grupo creado con éxito');
    }

    /**
     *
     * @OA\Get(
     *   path="/api/auth/group/{group}",
     *   summary="List a specific group",
     *   operationId="show",   
     *   tags={"Groups"}, 
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
        
        $group = Group::find($id);
        if (is_null($group)) {
            return $this->sendError('Grupo no encontrado');
        }
        return $this->sendResponse($group->toArray(), 'Grupo devuelto con éxito');
    }




    /**
     *
     * @OA\Put(
     *   path="/api/auth/group/{group}",
     *   summary="update a specific group",
     *   operationId="update",   
     *   tags={"Groups"},
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

        $group = Group::find($id);        
        if (is_null($group)) {
            return $this->sendError('Grupo no encontrada');
        }

        $group->description = $input['description'];
         $group->save();

        return $this->sendResponse($group->toArray(), 'Grupo actualizado con éxito');
    }

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/group/{group}",
     *   summary="Delete the group",
     *   operationId="destroy",   
     *   tags={"Groups"}, 
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
            $group = Group::find($id);
            if (is_null($group)) {
                return $this->sendError('Grupo no encontrado');
            }
            $group->delete();

            return $this->sendResponse($group->toArray(), 'Grupo eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El Grupo no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
