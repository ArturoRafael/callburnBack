<?php

namespace App\Http\Controllers;

use App\Http\Models\Day;
use App\Http\Models\Workflow;
use Illuminate\Http\Request;

class DayController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/day",
     *   summary="List of days by workflow",
     *   operationId="index",   
     *   tags={"Days"},     
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
        $day = Day::with('workflow')->paginate(15);
        return $this->sendResponse($day->toArray(), 'Días por Workflows devueltos con éxito');
    }

   
    /**
     *
     * @OA\Post(
     *   path="/api/auth/day",
     *   summary="create a specific day",
     *   operationId="store",   
     *   tags={"Days"},      
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_day"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
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
            'id_day' => 'required|integer',
            'id_workflow' => 'required|integer'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $workflow = Workflow::find($request->input('id_workflow'));
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $days = Day::create($request->all());        
        return $this->sendResponse($days->toArray(), 'Dia por Workflow agregado con éxito');
    }

    
    /**
     *
     * @OA\Get(
     *   path="/api/auth/day/{day}",
     *   summary="List days of a specific workflow",
     *   operationId="show",   
     *   tags={"Days"}, 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
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
    public function show($id_workflow)
    {
        $day = Day::with('workflow')->where('id_workflow',$id_workflow)->get();
        if (is_null($day)) {
            return $this->sendError('Días por Workflow no encontrados');
        }
        return $this->sendResponse($workflow->toArray(), 'Días por Workflow devueltos con éxito');
    }

  
    /**
     *
     * @OA\Put(
     *   path="/api/auth/day",
     *   summary="update a specific day",
     *   operationId="update",   
     *   tags={"Days"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_day"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
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
            'id_day' => 'required|integer',
            'id_workflow' => 'required|integer'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $workflow = Workflow::find($input['id_workflow']);
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $days = Day::find($id);        
        if (is_null($Days)) {
            return $this->sendError('Dia no encontrado');
        }
        
        $days->id_day = $input['id_day'];
        $days->id_workflow = $input['id_workflow'];
        $days->save();

        return $this->sendResponse($days->toArray(), 'Día actualizado con éxito');
    }

    

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/day/{day}",
     *   summary="Delete the day",
     *   operationId="destroy",   
     *   tags={"Days"}, 
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
            $day = Day::find($id);
            if (is_null($day)) {
                return $this->sendError('Día no encontrado');
            }
            $day->delete();

            return $this->sendResponse($day->toArray(), 'Día eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El día no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
