<?php

namespace App\Http\Controllers;

use App\Http\Models\Time;
use App\Http\Models\Workflow;
use Illuminate\Http\Request;

class TimeController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/time",
     *   summary="List of times by workflow",
     *   operationId="index",   
     *   tags={"Times"},     
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
        $times = Time::with('workflow')->paginate(15);
        return $this->sendResponse($times->toArray(), 'Horarios por Workflows devueltos con éxito');
    }

   
    /**
     *
     * @OA\Post(
     *   path="/api/auth/time",
     *   summary="create a specific time",
     *   operationId="store",   
     *   tags={"Times"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/start_time"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/end_time"
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
            'start_time' => 'required|timezone',
            'end_time' => 'required|timezone',
            'id_workflow' => 'required|integer'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $workflow = Workflow::find($request->input('id_workflow'));
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $times = Time::create($request->all());        
        return $this->sendResponse($times->toArray(), 'Horario por Workflow agregado con éxito');
    }

    
    /**
     *
     * @OA\Get(
     *   path="/api/auth/time/{time}",
     *   summary="List times of a specific workflow",
     *   operationId="show",   
     *   tags={"Times"}, 
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
        $time = Time::with('workflow')->where('id_workflow',$id_workflow)->get();
        if (is_null($time)) {
            return $this->sendError('Horarios por Workflow no encontrados');
        }
        return $this->sendResponse($workflow->toArray(), 'Horarios por Workflow devueltos con éxito');
    }

  
    /**
     *
     * @OA\Put(
     *   path="/api/auth/time",
     *   summary="update a specific time",
     *   operationId="update",   
     *   tags={"Times"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/start_time"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/end_time"
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
            'start_time' => 'required|timezone',
            'end_time' => 'required|timezone',
            'id_workflow' => 'required|integer'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $workflow = Workflow::find($input['id_workflow']);
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $times = Time::find($id);        
        if (is_null($times)) {
            return $this->sendError('Horario no encontrado');
        }

        $times->start_time = $input['start_time'];
        $times->end_time = $input['end_time'];
        $times->id_workflow = $input['id_workflow'];
        $times->save();

        return $this->sendResponse($times->toArray(), 'Horario actualizado con éxito');
    }

    

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/time/{time}",
     *   summary="Delete the time",
     *   operationId="destroy",   
     *   tags={"Times"}, 
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
            $time = Time::find($id);
            if (is_null($time)) {
                return $this->sendError('Horario no encontrado');
            }
            $time->delete();

            return $this->sendResponse($time->toArray(), 'Horario eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El horario no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
