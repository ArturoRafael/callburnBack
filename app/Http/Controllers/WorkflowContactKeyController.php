<?php

namespace App\Http\Controllers;

use App\Http\Models\WorkflowContactKey;
use App\Http\Models\Workflow;
use App\Http\Models\Contact;
use App\Http\Models\Key;
use App\Http\Requests\WorkflowContactKey\WorkflowContactKeyRequest;
use Illuminate\Http\Request;
use Validator;

class WorkflowContactKeyController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflowcontactkey",
     *   summary="List of workflow contact key",
     *   operationId="index",   
     *   tags={"WorkflowContactKeys"},     
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
        $workflows_cont_key = WorkflowContactKey::with('workflow')->with('contact_workflow')->with('keys')->paginate(15);
        return $this->sendResponse($workflows_cont_key->toArray(), 'Workflows contact key devueltos con éxito');
    }

    
    /**
     *
     * @OA\Post(
     *   path="/api/auth/workflowcontactkey", 
     *   summary="create a workflow contact key",
     *   operationId="store",   
     *   tags={"WorkflowContactKeys"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_contact_workflow"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_key"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/date_time"
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
    public function store(WorkflowContactKeyRequest $request)
    {
        $workflow = Workflow::find($request->input('id_workflow'));
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $contact = Contact::find($request->input('id_contact_workflow'));
        if (is_null($contact)) {
            return $this->sendError('El contacto indicado no existe');
        }

        $key = Key::find($request->input('id_key'));
        if (is_null($key)) {
            return $this->sendError('key no encontrada');
        }

        $workflows_cont_key = WorkflowContactKey::create($request->all());        
        return $this->sendResponse($workflows_cont_key->toArray(), 'Workflows contact key agregado con éxito');
    }

    
    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflowcontactkey/{workflowcontactkey}",
     *   summary="List times of a specific workflow contact key",
     *   operationId="show",   
     *   tags={"WorkflowContactKeys"}, 
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
        $workflows_cont_key = WorkflowContactKey::with('workflow')
                            ->with('contact_workflow')
                            ->with('keys')
                            ->find($id);
        if (is_null($workflows_cont_key)) {
            return $this->sendError('Workflow contact key no encontrados');
        }
        return $this->sendResponse($workflow->toArray(), 'Workflow contact key devueltos con éxito');
    }

 
    /**
     *
     * @OA\Put(
     *   path="/api/auth/workflowcontactkey",
     *   summary="update a specific workflow contact key",
     *   operationId="update",   
     *   tags={"WorkflowContactKeys"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_contact_workflow"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_key"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/date_time"
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
            'id_workflow' => 'required|integer',
            'id_contact_workflow' => 'required|integer',
            'id_key' => 'required|integer',
            'date_time' => 'nullable|date|date_format:Y-m-d H:m:s',           
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $workflow = Workflow::find($input['id_workflow']);
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $contact = Contact::find($input['id_contact_workflow']);
        if (is_null($contact)) {
            return $this->sendError('El contacto indicado no existe');
        }

        $key = Key::find($input['id_key']);
        if (is_null($key)) {
            return $this->sendError('key no encontrada');
        }

        $workflows_cont_key = WorkflowContactKey::find($id);        
        if (is_null($workflows_cont_key)) {
            return $this->sendError('Workflow contact key no encontrado');
        }

        $workflows_cont_key->id_workflow = $input['id_workflow'];
        $workflows_cont_key->id_contact_workflow = $input['id_contact_workflow'];
        $workflows_cont_key->id_key = $input['id_key'];
        $workflows_cont_key->date_time = $input['date_time'];
        $workflows_cont_key->save();

        return $this->sendResponse($workflows_cont_key->toArray(), 'Workflow contact key actualizado con éxito');
    }

    
    /**
     *
     * @OA\Delete(
     *   path="/api/auth/workflowcontactkey/{workflowcontactkey}",
     *   summary="Delete the workflow contact key",
     *   operationId="destroy",   
     *   tags={"WorkflowContactKeys"}, 
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
            $workflows_cont_key = WorkflowContactKey::find($id);
            if (is_null($workflows_cont_key)) {
                return $this->sendError('Workflow contact key no encontrado');
            }
            $workflows_cont_key->delete();

            return $this->sendResponse($workflows_cont_key->toArray(), 'Workflow contact key eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El Workflow contact key no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
