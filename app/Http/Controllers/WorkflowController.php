<?php

namespace App\Http\Controllers;

use App\Http\Models\Workflow;
use App\Http\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Validator;

class WorkflowController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflow",
     *   summary="List of workflow",
     *   operationId="index",   
     *   tags={"Workflows"},     
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
        $workflows = Workflow::with('usuario')->paginate(15);
        return $this->sendResponse($workflows->toArray(), 'Workflows devueltos con éxito');
    }

    

    /**
     *
     * @OA\Post(
     *   path="/api/auth/workflow",
     *   summary="create a workflow",
     *   operationId="store",   
     *   tags={"Workflows"},      
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/name"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/business_name"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/welcome_message"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/audio"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/sms"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/event"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/cost"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/type"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/user_email"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/date_register"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/date_begin"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/date_end"
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
            'name' => 'required|max:200',
            'business_name' =>'required|max:200',
            'welcome_message' =>'required',
            'audio' =>'required',
            'sms' =>'required',
            'event' =>'nullable|integer',
            'cost' =>'required|numeric|between:0,9999999.999',
            'type' => 'nullable|integer',
            'user_email'=>'required|email',
            'date_register' => 'nullable|date|date_format:Y-m-d',
            'date_begin' => 'nullable|date|date_format:Y-m-d',
            'date_end' => 'nullable|date|date_format:Y-m-d'           
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $user = Users::find($request->input('user_email'));
        if (is_null($user)) {
            return $this->sendError('El usuario indicado no existe');
        }

        if(is_null($request->input('event'))){
            Input::merge(['event' => 0]);
        }

        if(is_null($request->input('type'))){
            Input::merge(['type' => 1]);
        }


        if($request->hasfile('audio')){

            $file = $request->file('audio'); 
            $nombre = $file->getClientOriginalName(); 

            $fileUrl = Storage::disk('public')->put("audios", $file);
            $urlFile = "http://api.nelumbo.com.co/storage/".$fileUrl;
        }
       
        $workflow = new Workflow();
        $workflow->name = $request->input('name');
        $workflow->business_name = $request->input('business_name');
        $workflow->welcome_message = $request->input('welcome_message');
        $workflow->audio = $urlFile;
        $workflow->sms = $request->input('sms');
        $workflow->event = $request->input('event');
        $workflow->cost = $request->input('cost');
        $workflow->type = $request->input('type');
        $workflow->user_email = $request->input('user_email');
        $workflow->date_register = $request->input('date_register');
        $workflow->date_begin = $request->input('date_begin');
        $workflow->date_end = $request->input('date_end');
        
        $workflow->save();
        return $this->sendResponse($workflow->toArray(), 'Workflow creado con éxito');
    }

    
    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflow/{workflow}",
     *   summary="List a specific workflow",
     *   operationId="show",   
     *   tags={"Workflows"}, 
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
        $workflow = Workflow::with('usuario')->find($id);
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }
        return $this->sendResponse($workflow->toArray(), 'Workflow devuelto con éxito');
    }


    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflow_user/{workflow_user}",
     *   summary="List a specific workflow of user",
     *   operationId="show",   
     *   tags={"Workflows"}, 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/user_email"
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
    public function workflow_user($email)
    {
        $workflow = Workflow::with('usuario')->where('user_email', $email)->get();
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }
        return $this->sendResponse($workflow->toArray(), 'Workflows devueltos con éxito');
    }

   

    /**
     *
     * @OA\Post(
     *   path="/api/auth/update_workflow/{update_workflow}",
     *   summary="update a specific workflow",
     *   operationId="update_workflow",   
     *   tags={"Workflows"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/name"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/business_name"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/welcome_message"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/audio"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/sms"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/event"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/cost"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/type"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/user_email"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/date_register"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/date_begin"
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
    public function update_workflow(Request $request, $id)
    {
        

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:200',
            'business_name' =>'required|max:200',
            'welcome_message' =>'required',
            'audio' =>'required',
            'sms' =>'required',
            'event' =>'nullable|integer',
            'cost' =>'required|numeric|between:0,9999999.999',
            'type' => 'nullable|integer',
            'user_email'=>'required|email',
            'date_register' => 'nullable|date|date_format:Y-m-d',
            'date_begin' => 'nullable|date|date_format:Y-m-d',
            'date_end' => 'nullable|date|date_format:Y-m-d'             
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $workflow_search = Workflow::find($id);
        if (is_null($workflow_search)) {
            return $this->sendError('Workflow no encontrado');
        }

        $user = Users::find($request->input('user_email'));
        if (is_null($user)) {
            return $this->sendError('El usuario indicado no existe');
        }

        if(is_null($request->input('event'))){
            $workflow_search->event  = 0;
        }else{
            $workflow_search->event  = $request->input('event');
        }

        if(is_null($request->input('type'))){
            $workflow_search->type  = 1;
        }else{
            $workflow_search->type  = $request->input('type');
        }

        if($request->hasfile('audio')){

            $file = $request->file('audio');  
            $nombre = $file->getClientOriginalName(); 

            $fileUrl = Storage::disk('public')->put("audios", $file);
            $urlFile = "http://api.nelumbo.com.co/storage/".$fileUrl;
        }
        
        $input = $request->all();
        $workflow_search->name = $input['name'];
        $workflow_search->business_name = $input['business_name'];
        $workflow_search->welcome_message = $input['welcome_message'];
        $workflow_search->audio = $urlFile;
        $workflow_search->sms = $input['sms'];
        $workflow_search->cost = $input['cost'];
        $workflow_search->user_email = $input['user_email'];
        $workflow_search->date_register = $input['date_register'];
        $workflow_search->date_begin = $input['date_begin'];
        $workflow_search->date_end = $input['date_end'];
        
        $workflow_search->save();
        return $this->sendResponse($workflow_search->toArray(), 'Workflow actualizado con éxito');
    }

    

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/workflow/{workflow}",
     *   summary="Delete the workflow",
     *   operationId="destroy",   
     *   tags={"Workflows"}, 
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
            $workflow = Workflow::find($id);
            if (is_null($workflow)) {
                return $this->sendError('Workflow no encontrado');
            }
            $workflow->delete();

            return $this->sendResponse($workflow->toArray(), 'Workflow eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El Workflow no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
