<?php

namespace App\Http\Controllers;

use App\Http\Models\GroupWorkflow;
use App\Http\Models\Workflow;
use Illuminate\Http\Request;
use Validator;

class GroupWorkflowController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/groupworkflow",
     *   summary="List of workflows recurrents",
     *   operationId="index",   
     *   tags={"GroupWorkflows"},     
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
        $workflows = GroupWorkflow::with('workflows_recurrent')->get();
        return $this->sendResponse($workflows->toArray(), 'Workflows recurrentes devueltos con éxito');
    }

 

    /**
     *
     * @OA\Get(
     *   path="/api/auth/groupworkflow/{groupworkflow}",
     *   summary="Status workflows recurrents",
     *   operationId="show",   
     *   tags={"GroupWorkflows"}, 
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
        $workflows = GroupWorkflow::find($id);

        if (is_null($workflows)) {
            return $this->sendError('Workflows no encontrado');
        }
        $success["status"] = $workflows->status_code;
        $success["status_text"] = $workflows->status_text;
        $success["send_on"] = $workflows->send_on;
        $success["delivered_on"] = $workflows->delivered_on;
        return $this->sendResponse($success, 'Workflows devuelto con éxito');
      
    }


    
    public function update(Request $request, $id)
    {
        
    }

   


    /**
     *
     * @OA\Delete(
     *   path="/api/auth/groupworkflow/{groupworkflow}",
     *   summary="Delete the workflow recurrent",
     *   operationId="destroy",   
     *   tags={"GroupWorkflows"}, 
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
    public function destroy($id_workflow)
    {
        try {
            $workflows = GroupWorkflow::where('id_workflow', $id_workflow)->get();
            if (is_null($workflows)) {
                return $this->sendError('Workflow no encontrado');
            }
            $workflows->delete();

            return $this->sendResponse($workflows->toArray(), 'Workflow recurrentes eliminados con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El Workflow recurrente no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
