<?php

namespace App\Http\Controllers;

use App\Http\Models\WorkflowContact;
use App\Http\Models\Workflow;
use App\Http\Models\Contact;
use Illuminate\Http\Request;
use Validator;
class WorkflowContactController extends Controller
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflowcontact",
     *   summary="List of workflow contact",
     *   operationId="index",   
     *   tags={"WorkflowsContact"},     
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
        $workflows = Workflow::with('workflow')->with('contact')->paginate(15);
        return $this->sendResponse($workflows->toArray(), 'Contactos por Workflows devueltos con éxito');
    }

   
    /**
     *
     * @OA\Post(
     *   path="/api/auth/workflowcontact",
     *   summary="create a specific workflow contact",
     *   operationId="store",   
     *   tags={"WorkflowsContact"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_contact"
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
            'id_workflow' => 'required|integer',
            'id_contact' => 'required|integer'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $workflow = Workflow::find($request->input('id_workflow'));
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $contact = Contact::find($request->input('id_contact'));
        if (is_null($contact)) {
            return $this->sendError('Contacto no encontrado');
        }
        
        $workflow_c = WorkflowContact::create($request->all());        
        return $this->sendResponse($workflow_c->toArray(), 'Contacto por Workflow agregado con éxito');
    }

    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflowcontact/{workflowcontact}",
     *   summary="List the contacts of a specific workflow",
     *   operationId="show",   
     *   tags={"WorkflowsContact"}, 
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
        
        $workflow = WorkflowContact::with('contact')->where('id_workflow',$id_workflow)->get();
        if (is_null($workflow)) {
            return $this->sendError('Contactos por Workflow no encontrados');
        }
        return $this->sendResponse($workflow->toArray(), 'Contactos por Workflow devueltos con éxito');
    }

 
     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkflowContact  $workflowContact
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return true;
    }

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/workflowcontact/{workflowcontact}",
     *   summary="Delete the workflow contact",
     *   operationId="destroy",   
     *   tags={"WorkflowsContact"}, 
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
            $workflow = WorkflowContact::where('id_workflow',$id_workflow)->get();
            if (is_null($workflow)) {
                return $this->sendError('Contactos por Workflow no encontrados');
            }
            $workflow->delete();

            return $this->sendResponse($workflow->toArray(), 'Contactos por Workflow eliminados con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'Los contactos por Workflow no se pueden eliminar, son usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
