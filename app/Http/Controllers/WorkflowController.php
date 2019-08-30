<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Models\Workflow;
use App\Http\Models\Users;
use App\Http\Models\Country;
use App\Http\Models\GroupWorkflow;
use App\Http\Models\GroupContact;
use App\Http\Models\Contact;
use App\Http\Models\Calls;
use App\Http\Models\Key;
use App\Http\Models\KeyEventType;
use App\Http\Models\File;
use Illuminate\Http\Request;
use App\Exports\WorkflowExport;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use App\Http\Services\FileService;
use App\Http\Services\InvoiceService;
use App\Http\Services\VerificationService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

use JWTAuth;

class WorkflowController extends BaseController
{
    

    /**
     * Create a new instance of WorkflowController class
     *
     * @return void
     */
    public function __construct()
    {
        $this->invoiceRepo = new InvoiceService();
        $this->verificationRepo = new VerificationService();
    }



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
        $workflows = Workflow::where('filter_type',0)->orWhere('filter_type',1)->orWhere('filter_type',2)->with('usuario')->get();
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
     *      ref="../Swagger/definitions.yaml#/components/parameters/filter_type"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/status"
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
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/activate_hours"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/activate_before_hours"
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
            'filter_type' => 'nullable|integer',
            'status' => 'nullable|integer',
            'user_email'=>'required|email',
            'date_register' => 'nullable|date|date_format:Y-m-d',
            'date_begin' => 'nullable|date|date_format:Y-m-d',
            'date_end' => 'nullable|date|date_format:Y-m-d',
            'activate_hours' => 'nullable|integer',
            'activate_before_hours' => 'nullable|boolean'           
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
        $workflow->filter_type = $request->input('filter_type');
        $workflow->status = $request->input('status');
        $workflow->user_email = $request->input('user_email');
        $workflow->date_register = $request->input('date_register');
        $workflow->date_begin = $request->input('date_begin');
        $workflow->date_end = $request->input('date_end');
        $workflow->activate_hours = $request->input('activate_hours');
        $workflow->activate_before_hours = $request->input('activate_before_hours');
        
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
        $user_now = JWTAuth::parseToken()->authenticate();
        $email = $user_now->email;
        $workflow = Workflow::where('user_email', $email)->with("sms_recurrent")->with("call_recurrent")->with("call_id")->with("keys")->find($id);
        
        if (!$workflow) {
            return $this->sendError('Workflow no encontrado');
        }

        return $this->sendResponse($workflow->toArray(), 'Workflow devuelto con éxito');
    }





/*
    EXPORT EXCEL
*/
    public function export_excel($id)
    {
       
        $workflow = Workflow::find($id);
        
        if (!$workflow) {
            return $this->sendError('Workflow no encontrado');
        }

       return (new WorkflowExport)->forId($id)->download('Report the '.$workflow->name.'.xlsx');
        
    }


    /**
     *
     * @OA\Get(
     *   path="/api/auth/workflow_filter/{workflow_filter}",
     *   summary="List workflow by filter",
     *   operationId="workflow_filter",   
     *   tags={"Workflows"}, 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/filter_type"
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
    public function workflow_filter($filter_type)
    {
        $workflow = Workflow::where("filter_type", $filter_type)->get();
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrados');
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
        
        $user_now = JWTAuth::parseToken()->authenticate();
        $email = $user_now->email;
        $workflow = Workflow::where('user_email', $email)
                    ->with("sms_recurrent")
                    ->with("call_recurrent")
                    ->with("keys")
                    ->where('filter_type',3)
                    ->orWhere('filter_type',4)
                    ->orWhere('filter_type',5)
                    ->get();        

        //$workflow = Workflow::with('usuario')->where('user_email', $email)->get();
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }
        
        $arr = array();
        foreach ($workflow as $work) { 
            
            $startTime   = strtotime($work["date_register"]);
            $work["date_register"] = date("d/m/Y",$startTime);

            if(!is_null($work["date_begin"])){
                $startTime   = strtotime($work["date_begin"]);
                $work["date_begin"] = date("d/m/Y",$startTime);
            }
            
            if(!is_null($work["date_end"])){
                $startTime   = strtotime($work["date_end"]);
                $work["date_end"] = date("d/m/Y",$startTime);
            }

            $total_sms = 0;
            $total_call = 0;
            $success_call = 0;
            $success_sms = 0;

            if($work["filter_type"] == 3){
                foreach ($work["sms_recurrent"] as $sms) {
                    $total_sms = $total_sms + 1;
                    if($sms["status_code"] == 1){
                        $success_sms = $success_sms + 1;
                    }
                }

                $work['total_sms'] = $total_sms;
                $work['success_sms'] = $success_sms;

            }else if($work["filter_type"] == 4){

                foreach ($work["call_recurrent"] as $call) {
                    $total_call = $total_call + 1;
                    if($call["call_status"] == 'ANSWERED'){
                        $success_call = $success_call + 1;
                    }
                }

                $work['total_call'] = $total_call;
                $work['success_call'] = $success_call;

            }else{

                if($work["filter_type"] == 5){

                    foreach ($work["call_recurrent"] as $call) {
                        $total_call = $total_call + 1;
                        if($call["call_status"] == 'ANSWERED'){
                            $success_call = $success_call + 1;
                        }
                    }                   

                    foreach ($work["sms_recurrent"] as $sms) { 
                        $total_sms = $total_sms + 1;
                        $total_call = $total_call + 1;                  
                        if($sms["status_code"] == 1){
                            $success_sms = $success_sms + 1;
                        }
                    }

                    $work['total_call'] = $total_call;
                    $work['success_call'] = $success_call;
                    
                    $work['total_sms'] = $total_sms;
                    $work['success_sms'] = $success_sms;

                }

            }


            
            array_push($arr, $work);
            
        }
       

        return $this->sendResponse($arr, 'Workflows devueltos con éxito');
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
     *      ref="../Swagger/definitions.yaml#/components/parameters/filter_type"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/status"
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
     *      ref="../Swagger/definitions.yaml#/components/parameters/activate_hours"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/activate_before_hours"
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
            'filter_type' => 'nullable|integer',
            'status' => 'nullable|integer',
            'user_email'=>'required|email',
            'date_register' => 'nullable|date|date_format:Y-m-d',
            'date_begin' => 'nullable|date|date_format:Y-m-d',
            'date_end' => 'nullable|date|date_format:Y-m-d',
            'activate_hours' => 'nullable|integer',
            'activate_before_hours' => 'nullable|boolean'             
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

        if(is_null($request->input('filter_type'))){
            $workflow_search->filter_type  = 2;
        }else{
            $workflow_search->filter_type  = $request->input('filter_type');
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
        $workflow_search->status = $input['status'];
        $workflow_search->cost = $input['cost'];
        $workflow_search->user_email = $input['user_email'];
        $workflow_search->date_register = $input['date_register'];
        $workflow_search->date_begin = $input['date_begin'];
        $workflow_search->date_end = $input['date_end'];
        $workflow_search->activate_hours = $input['activate_hours'];
        $workflow_search->activate_before_hours = $input['activate_before_hours'];
        
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

            if($workflow->filter_type == 3){
                $work_recu = GroupWorkflow::where('id_workflow', $workflow->id)->delete();
            }else if($workflow->filter_type == 4){
                $work_recu = Calls::where('id_workflow', $workflow->id)->delete();
            }else{
                $work_recu = GroupWorkflow::where('id_workflow', $workflow->id)->delete();
                $work_recu = Calls::where('id_workflow', $workflow->id)->delete();
            }

            $iteraccion = key::where('id_workflow', $workflow->id)->get();
            if(count($iteraccion) > 0){
                key::where('id_workflow', $workflow->id)->delete();
            }

            $workflow->delete();

            return $this->sendResponse($workflow->toArray(), 'Workflow eliminado con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'El Workflow no se puedo eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }


    /**
                               PLANTILLAS de Workflows
    -------------------------------------------------------------------------------------------------------
    **/ 
     public function templates_workflows()
    {
        
        $user_now = JWTAuth::parseToken()->authenticate();
        $email = $user_now->email;
        $workflows = Workflow::where('user_email', $email)->whereIn('filter_type', array(4,5))->with("call_id")->with("keys")->get();
        
        if (!$workflows){
            return $this->sendError('Templates no encontrados');
        }

        return $this->sendResponse($workflows->toArray(), 'Templates devueltos con éxito');
    }


    /**
                                   Lista de Workflows
    -------------------------------------------------------------------------------------------------------
    **/ 
     public function groupworkflow_user($email)
    {
        
        $workflows = Workflow::where('user_email', $email)->whereIn('filter_type', array(3,4,5))->with("sms_recurrent")->with("call_recurrent")->with("call_id")->with("keys")->get();
        
        if (!$workflows) {
            return $this->sendError('Workflows no encontrados');
        }

        return $this->sendResponse($workflows->toArray(), 'Workflows devuelto con éxito');
    }


/**
                                Finish Workflow
-------------------------------------------------------------------------------------------------------
**/ 
    public function workflow_change_status(Request $request){

        $validator = Validator::make($request->all(), [
            'id_workflow' => 'required|integer',
            'pause' => 'nullable|boolean',
            'finish' => 'nullable|boolean',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        if(is_null($request->input('finish')) &&  is_null($request->input('pause'))){
            return $this->sendError('Debe agregar al menos una acción');
        }
        
        $user_now = JWTAuth::parseToken()->authenticate();
        $email = $user_now->email;
        $id = $request->input('id_workflow');

        try{

            DB::beginTransaction();

            $workflow = Workflow::with('usuario')->where('user_email', $email)->lockForUpdate()->find($id);
            
            if (is_null($workflow)) {
                return $this->sendError('Workflow no encontrado');
            }
            
            $workflow->is_blocked = 1;
            if(!is_null($request->input('finish')) &&  $request->input('finish')){
                $workflow->status = 2;
            }else if(!is_null($request->input('pause')) && $request->input('pause')){
                $workflow->status = 5;
            }
            
            
            $workflow->save();            
            
            DB::commit();
            return $this->sendResponse($workflow->toArray(), 'Workflow finalizado con éxito');

        }catch(\Exception $e){
            DB::rollBack();            
            return $this->sendError('Ha ocurrido un error al cambiar el estado de la campaña '.$e->getMessage());
        }

        

    }


/**
                                CLONE WORKFLOW
-------------------------------------------------------------------------------------------------------
**/ 
    public function cloneRecurrent(Request $request){

        $validator = Validator::make($request->all(), [
            'id_workflow' => 'required|integer',
            'phonenumbers' => 'nullable|boolean',                     
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $user_now = JWTAuth::parseToken()->authenticate();
        $email = $user_now->email;
        $id = $request->input('id_workflow');
        $workflow = Workflow::with('usuario')->with('keys')->where('user_email', $email)->find($id);
        
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        if($request->input('phonenumbers')){

            $phones_sms = GroupWorkflow::where('id_workflow', $id)->select('destination_number')->get();    
            $phones_call = Calls::where('id_workflow', $id)->select('phonenumber')->get();
           
            $phones_sms = $phones_sms->toArray(); 
            $phones_call = $phones_call->toArray(); 

            $phones = array();
            if(sizeof($phones_sms) > 0){
                foreach ($phones_sms as $key => $value) {
                    $group = $this->searchContactGroup($value['destination_number']);
                    $num = array('phone' => $value['destination_number'], 'group' => $group);
                    array_push($phones, $num );
                }
            }
            if(sizeof($phones_call) > 0){
                foreach ($phones_call as $key => $value) {
                    $group = $this->searchContactGroup($value['phonenumber']);
                    $num = array('phone' => $value['phonenumber'], 'group' => $group);
                    array_push($phones, $num );
                }
            }
            $workflow = compact('workflow', 'phones');
            return $this->sendResponse($workflow, 'Envío de información del Workflow');
        
        }else{
            $workflow = compact('workflow');
            return $this->sendResponse($workflow, 'Envío de información del Workflow');
        }

    }


/**
                                EDIT WORKFLOW
-------------------------------------------------------------------------------------------------------
**/ 
    public function editRecurrent(Request $request)
    {
        $regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        $validator = Validator::make($request->all(), [
            'id_workflow' => 'required|integer',
            'name' => 'nullable|max:200',
            'sender_name_sms' => 'nullable|max:11',
            'sender_name_call' => 'nullable|integer',
            'url_audio' => 'nullable|regex:' . $regex,
            'text' => 'nullable|string',
            'audio' => 'nullable|mimes:mpga,wav,oga,ogg,spx',
            'save' => 'required|boolean',
            'simulacion' => 'required|boolean',
            'id_invoice' => 'nullable|string',
            'groups' =>'nullable|array',
            'groups.*' =>'nullable|integer|min:1',
            'phonenumbers' =>'nullable|array',
            'phonenumbers.*' =>'nullable|integer|min:1',            
            'interaccion' => 'nullable|array',
            'interaccion.*.pre_texto' => 'nullable|string',
            'interaccion.*.post_texto' => 'nullable|string',
            'interaccion.*.tecla' => 'nullable|integer',
            'interaccion.*.tipo' => 'nullable|integer',
            'interaccion.*.phone' => 'nullable|string',
            'interaccion.*.limitador' =>'nullable|string',
            'interaccion.*.sender_name' =>'nullable|string',
            'planificacion' => 'nullable|boolean'                    
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }



        if((is_null($request->input("phonenumbers")) || 
            count($request->input("phonenumbers")) < 1) && 
            (is_null($request->input("groups")) &&  
            count($request->input("groups")) < 1) ){
            return $this->sendError('Debe agregar al menos un número telefónico o un grupo de contacto');
        }

        $user_now = JWTAuth::parseToken()->authenticate();
        $email = $user_now->email;
        $id = $request->input('id_workflow');


        $phonenumbers = array();
        if(!is_null($request->input("phonenumbers"))){
            $phonenumbers = $request->input("phonenumbers");
            for ($i=0; $i < sizeof($phonenumbers); $i++) { 
                
                $number = $this->verificationRepo->sanitizePhonenumbers($phonenumbers[$i]);
                
                if($number != false){
                    $phonenumbers[$i] = $number[0];
                
                }else{
                    return $this->sendError('Solo se aceptan valores numéricos y con una longitud mínima de 7 y máxima de 15 numeros.', $phonenumbers[$i]);
                    break;
                }
            }
        }

        if(!is_null($request->input("groups"))){
            $groups = $request->input("groups");
            for ($i=0; $i < sizeof($groups); $i++){ 

                $ids_contact = GroupContact::where('id_group','=', $groups[$i])
                            ->select('id_contact')
                            ->get();

                foreach ($ids_contact as $key) {
                   $contact = Contact::where('id', $key->id_contact)->first();
                   if (!in_array($contact->phone, $phonenumbers)) {
                        array_push($phonenumbers, $contact->phone);
                   }
                }
            }

        }
        
        try{

            DB::beginTransaction();

            $invoice = $this->invoiceRepo->setInvoiceWorkflow($request->input("id_invoice"), $id);
            if(!$invoice){
                return $this->sendError('No se pudo encontrar el pago');
            }

            $workflow = Workflow::with('usuario')->with('keys')->where('user_email', $email)->lockForUpdate()->find($id);
        
            if (is_null($workflow)) {
                return $this->sendError('Workflow no encontrado');
            }

            if($workflow->status == 3){
                return $this->sendResponse_message('El workflow ya ha finalizado su proceso.');
            }

            $text = "";
            if(!is_null($request->input("text"))){
                $text = $request->input("text");
            }

            $number_cost = array();       
            $valuetotal = 0;

            $part = 1;        
            if(strlen($text) > 153){
                $cut = 153;
                $i = 1 ;
                $part = 1;            
                do{
                    $i = $i + 1;
                    if(strlen($text) > $cut){
                        $part = $part + 1;
                    }else{
                        break;
                    }
                    $cut = 153 * $i;
                }while(1);
            }
           
            for ($i=0; $i < sizeof($phonenumbers); $i++) { 
                $phonconvert = $phonenumbers[$i];
                if($workflow->filter_type == 3){
                    $value = $this->tarff_phone($phonconvert, 1);
                    $valuetotal = $valuetotal + $value;
                }
                else{
                    $value = $this->tarff_phone($phonconvert, 0);
                    $valuetotal = $valuetotal + ($value * $part);
                }
            }


            if($workflow->filter_type == 3){ // --------------- EDIT WORKFLOW SMS 
                
                $senderName = $request->input("sender_name_sms");
                $phones = GroupWorkflow::where('id_workflow', $id)->where('status_code', '!=' , 1)->get();
                foreach ($number_cost as $key)
                {
                    foreach ($phones as $key2) 
                    {                    
                        if($key2['destination_number'] == $key['phone']){
                            GroupWorkflow::find($key2['id'])->delete();
                        }
                    }
                }
                $phones = GroupWorkflow::where('id_workflow', $id)->where('status_code', '!=' , 1)->get();
                foreach ($phones as $key) {
                    $valuetotal = $valuetotal + $key['cost'];
                }

                $name = $request->input("name");

                $workflow->name = $name;        
                $workflow->sms = $text;
                $workflow->sender_name = $senderName;
                $workflow->cost = $valuetotal;
                $workflow->updated_at = Carbon::now();
                $workflow->save();

                $camping_sms = $workflow->sms;

                $simulacion = true;
                if(!is_null($request->input("simulacion"))){
                    $simulacion = $request->input("simulacion");
                }

                if(!$simulacion){
                    $smsResponse = $this->sendSmsToMultipleRecipients($number_cost, $camping_sms, $senderName, $part, $id);
                }else{
                    $smsResponse = $this->simulacionSmsToMultipleRecipients($number_cost, $camping_sms, $senderName, $part, $id);
                }
                
                if($smsResponse->status == false){
                    Workflow::find($id)->delete();
                    return $this->sendError('Error SMS', $smsResponse);
                }

                if($simulacion){

                    $count_phones = GroupWorkflow::where('id_workflow', $id)->whereIn('status_code', array(3,4))->count();
                    if($count_phones == 0){
                        Workflow::find($id)->update(['updated_at' => Carbon::now(), 'date_end' => Carbon::now(), 'date_begin' => Carbon::now(), 'status' => 3, 'is_blocked' => 1]);
                    }

                }else{
                    Workflow::find($id)->update(['updated_at' => Carbon::now(), 'date_end' => Carbon::now(), 'date_begin' => Carbon::now()]);
                }
                
                

            
            }else{ 
                
                if($workflow->filter_type == 4 || $workflow->filter_type == 5){ 
                // --------------- EDIT WORKFLOW CALL OR CALL-SMS

                    if(!is_null($request->input("url_audio"))){
                
                        $searulrFile = File::where('orig_filename',$request->input("url_audio"))->first();
                        if(!$searulrFile){
                            return $this->sendError('La url del vídeo no es correcta');
                        }           
                        
                        $urlFile = $request->input("url_audio");
                    
                    }else{
                        if($request->hasfile('audio')){
                            $file = $request->file('audio');               
                            $fileUrl = Storage::disk('public')->put('audios', $file);            
                            $urlFile = env('APP_URL').'storage/'.$fileUrl;

                        }else{
                            return $this->sendError('Debes agregar un audio o un texto para ser usado en la llamada.');
                        }
                    }

                    $name = $request->input("name");
                    $call_id = $request->input("sender_name_call");
                    $sender_name = $request->input("sender_name_sms");
                                  
                    $workflow->name = $name;
                    $workflow->call_id = $call_id;
                    $workflow->sender_name = $sender_name;
                    $workflow->audio = $urlFile;
                    $workflow->sms = $text;                      
                    $workflow->updated_at = Carbon::now();

                    if(count($request->input("interaccion")) > 0 || !is_null($request->input("interaccion")) ){
                        $this->addInteraccion($request->input("interaccion"), $id, true);            
                    }

                    $calls = Calls::where('id_workflow', $id)->get();
                    foreach ($number_cost as $key) {
                        foreach ($calls as $key2) 
                        {                    
                            if($key2['phonenumber'] == $key['phone']){
                                Calls::find($key2['id'])->delete();
                            }
                        }
                    }
                    $calls = Calls::where('id_workflow', $id)->select('cost')->get();
                    foreach ($calls  as $key) {
                        $valuetotal = $valuetotal + $key['cost'];
                    }

                    if($workflow->filter_type == 5){
                        GroupWorkflow::where('id_workflow' , $id)->where('status_code','!=',1)->delete();
                    }

                    $workflow->cost = $valuetotal;
                    $workflow->save();
                
                    for ($k=0; $k < sizeof($number_cost); $k++) { 

                        $new_call = new Calls();
                        $new_call->phonenumber = $number_cost[$k]['phone'];                    
                        $new_call->call_status = 'SENT_TO_ASTERISK';
                        $new_call->id_workflow = $id;
                        $new_call->user_email = $user_now->email;
                        $new_call->cost = $number_cost[$k]['cost'];  
                        $new_call->save();                  
                        
                    }
                    Workflow::find($id)->update(['updated_at' => Carbon::now()]);
                
                }
            }

            DB::commit();
            return $this->sendResponse_message('El workflow se ha editado y ejecutado de forma exitosa.');

        }catch(\Exception $e){
            DB::rollBack();            
            return $this->sendError('Ha ocurrido un error al editar. '.$e->getMessage());
        }

    }


/**
                               SEARCH GROUP BY CONTACT 
-------------------------------------------------------------------------------------------------------
**/
    public function searchContactGroup($phonenumber){
        $contact = Contact::where('phone', $phonenumber)->first();
        if(!$contact){
            return null;
        }else{
            $id_cont = $contact->id;
            $groups = GroupContact::where('id_contact' ,$id_cont)->get();
            return $groups->toArray();
        }
    }




/**
                               ADD INTERACCION WORKFLOW
-------------------------------------------------------------------------------------------------------
**/
    public function addInteraccion($interaccion, $camping_id, $delete_iter = false){
        
        if($delete_iter){
            Key::where('id_workflow', $camping_id)->delete();
        }

        //Event_type : 6 Replay, 7 Live Transfer, 8 BlackList, 9 CallmeBack, 10 Sms Transfer
        foreach ($interaccion as $key) {
            
            $keys = new Key();
            $keys->id_workflow = $camping_id;
            $keys->keypad_value = $key["tecla"];                
            $keys->id_key_event_type = $key["tipo"];

            if((int)$key["tipo"] == 6){

                $keys->label = 'Replay';
                $keys->first_action_text = $key["pre_texto"];

            }else if((int)$key["tipo"] == 7){

                $keys->label = 'Live Transfer';
                $keys->first_action_text = $key["pre_texto"];
                $keys->simultaneus_transfer_limit = $key["limitador"];
                $keys->phone_number = $key["phone"];

            }else if((int)$key["tipo"] == 8){

                $keys->label = 'BlackList';
                $keys->first_action_text = $key["pre_texto"];
                $keys->post_action_text = $key["post_texto"];

            }else if((int)$key["tipo"] == 9){

                $keys->label = 'CallmeBack';
                $keys->first_action_text = $key["pre_texto"];
                $keys->post_action_text = $key["post_texto"];

            }else{

                $keys->label = 'Sms Transfer';
                $keys->phone_number = $key["phone"];
                $keys->sender_name = $key["sender_name"];
                $keys->first_action_text = $key["pre_texto"];

            }

            $keys->has_sub_key_menu = 0;                
            $keys->save();

        }
    } 



/**
                            CALCULA COSTO ESTIMADO
-------------------------------------------------------------------------------------------------------
**/ 
    public function calculate_cost(Request $request){

        $validator = Validator::make($request->all(), [
            'type_service' => 'integer',
            'text_sms' => 'nullable|string',
            'groups' =>'nullable|array',
            'groups.*' =>'nullable|integer|min:1',
            'phonenumbers' =>'nullable|array',
            'phonenumbers.*' =>'nullable|integer|min:1',
            'interaccion' => 'boolean'
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        } 

        if((is_null($request->input("phonenumbers")) || 
            count($request->input("phonenumbers")) < 1) && 
            (is_null($request->input("groups")) &&  
            count($request->input("groups")) < 1) ){
            return $this->sendError('Debe agregar al menos un número telefónico o un grupo de contacto');
        }


        $phonenumbers = array();
        if(!is_null($request->input("phonenumbers"))){
            $phonenumbers = $request->input("phonenumbers");
            for ($i=0; $i < sizeof($phonenumbers); $i++) { 
                
                $number = $this->verificationRepo->sanitizePhonenumbers($phonenumbers[$i]);
                
                if($number != false){
                    $phonenumbers[$i] = $number[0];
                
                }else{
                    return $this->sendError('Solo se aceptan valores numéricos y con una longitud mínima de 7 y máxima de 15 numeros.', $phonenumbers[$i]);
                    break;
                }
            }
        }

        if(!is_null($request->input("groups"))){
            $groups = $request->input("groups");
            for ($i=0; $i < sizeof($groups); $i++){ 

                $ids_contact = GroupContact::where('id_group','=', $groups[$i])
                            ->select('id_contact')
                            ->get();

                foreach ($ids_contact as $key) {
                   $contact = Contact::where('id', $key->id_contact)->first();
                   if (!in_array($contact->phone, $phonenumbers)) {
                        array_push($phonenumbers, $contact->phone);
                   }
                }
            }

        }

        $type_service = $request->input("type_service");
        $part = 1;
        $value = 0;
        $valuetotal = 0;

        if($type_service == 0){
            $smsText = $request->input("text_sms");
            if(strlen($smsText) > 153){
                $cut = 153;
                $i = 1 ;
                $part = 1;            
                do{
                    $i = $i + 1;
                    if(strlen($smsText) > $cut){
                        $part = $part + 1;
                    }else{
                        break;
                    }
                    $cut = 153 * $i;
                }while(1);
            }
        }

        for ($i=0; $i < sizeof($phonenumbers); $i++) { 
            
            $phonconvert = $phonenumbers[$i];
            $value = $this->tarff_phone($phonconvert, $type_service);            
            
            if($type_service == 0)
                $valuetotal = ((float)$valuetotal + ($value * $part));
            else
                $valuetotal = ((float)$valuetotal + $value);
        }

        $valuetotal = round($valuetotal, 2, PHP_ROUND_HALF_UP);
        if($request->input("interaccion"))
            $valuetotal = ($valuetotal * 2);
        
        return $this->sendResponse(['costo' => $valuetotal], 'Costo estimado');       

    }




/**
                                SMS
-------------------------------------------------------------------------------------------------------
**/ 
    public function sendSmsRecurrent(Request $request){
         

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:200',
            'sender_name' =>'required|max:11',
            'text_sms' =>'required',
            'id_invoice' => 'nullable|string',
            'save' =>'required|boolean',
            'groups' =>'nullable|array',
            'groups.*' =>'nullable|integer|min:1',
            'phonenumbers' =>'nullable|array',
            'phonenumbers.*' =>'nullable|integer|min:1',
            'simulacion' => 'nullable|boolean',                     
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        
        if((is_null($request->input("phonenumbers")) || 
            count($request->input("phonenumbers")) < 1) && 
            (is_null($request->input("groups")) &&  
            count($request->input("groups")) < 1) ){
            return $this->sendError('Debe agregar al menos un número telefónico o un grupo de contacto');
        }


        $phonenumbers = array();
        if(!is_null($request->input("phonenumbers"))){
            $phonenumbers = $request->input("phonenumbers");
            for ($i=0; $i < sizeof($phonenumbers); $i++) { 
                
                $number = $this->verificationRepo->sanitizePhonenumbers($phonenumbers[$i]);
                
                if($number != false){
                    $phonenumbers[$i] = $number[0];
                
                }else{
                    return $this->sendError('Solo se aceptan valores numéricos y con una longitud mínima de 7 y máxima de 15 numeros.', $phonenumbers[$i]);
                    break;
                }
            }
        }

        if(!is_null($request->input("groups"))){
            $groups = $request->input("groups");
            for ($i=0; $i < sizeof($groups); $i++){ 

                $ids_contact = GroupContact::where('id_group','=', $groups[$i])
                            ->select('id_contact')
                            ->get();

                foreach ($ids_contact as $key) {
                   $contact = Contact::where('id', $key->id_contact)->first();
                   if (!in_array($contact->phone, $phonenumbers)) {
                        array_push($phonenumbers, $contact->phone);
                   }
                }
            }

        }
        

        $number_cost = array();
        $phoneNumbersForSendSms = array();
        $valuetotal = 0;

        $part = 1;
        $smsText = $request->input("text_sms");
        if(strlen($smsText) > 153){
            $cut = 153;
            $i = 1 ;
            $part = 1;            
            do{
                $i = $i + 1;
                if(strlen($smsText) > $cut){
                    $part = $part + 1;
                }else{
                    break;
                }
                $cut = 153 * $i;
            }while(1);
        }
        
        $value = 0;
        for ($i=0; $i < sizeof($phonenumbers); $i++) { 
            
            $phonconvert = $phonenumbers[$i];
            $value = $this->tarff_phone($phonconvert,0);            
            array_push($number_cost, array("phone" =>  $phonconvert, "cost" => ($value * $part)));
            array_push($phoneNumbersForSendSms,  $phonconvert);
            
            $valuetotal = ((float)$valuetotal + ($value * $part));
        }


        $senderName = $request->input("sender_name");
        $name = $request->input("name");
        $user_now = JWTAuth::parseToken()->authenticate(); 

        $new_camping = new Workflow();
        $new_camping->name = $name;
        $new_camping->sender_name = $senderName;
        $new_camping->is_blocked = 0;
        $new_camping->sms = $request->input("text_sms");
        $new_camping->cost = $valuetotal;
        $new_camping->status = 1;
        $new_camping->filter_type = 3;
        $new_camping->user_email = $user_now->email;
        $new_camping->date_register = Carbon::now();
        $new_camping->save();

        $camping_id = $new_camping->id;
        $camping_sms = $new_camping->sms;

        // $invoice = $this->invoiceRepo->setInvoiceWorkflow($request->input("id_invoice"), $camping_id);
        // if(!$invoice){
        //     Workflow::find($camping_id)->delete();
        //     return $this->sendError('No se pudo encontrar el pago');
        // }

        $simulacion = true;
        if(!is_null($request->input("simulacion"))){
            $simulacion = $request->input("simulacion");
        }
        $bandera = $request->input("save");
        if(!$bandera){
            
            if(!$simulacion){
                $smsResponse = $this->sendSmsToMultipleRecipients($number_cost, $camping_sms, $senderName, $part, $camping_id);
            }else{
                $smsResponse = $this->simulacionSmsToMultipleRecipients($number_cost, $camping_sms, $senderName, $part, $camping_id);
            }
            
            if($smsResponse->status == false){
                Workflow::find($camping_id)->delete();
                return $this->sendError('Ocurrió errores al enviar los SMS', $smsResponse);
            }
            
            Workflow::find($camping_id)->update(['updated_at' => Carbon::now(), 'date_end' => Carbon::now(), 'date_begin' => Carbon::now()]);

            return $this->sendResponse($smsResponse, 'El workflow SMS se ha ejecutado de forma exitosa.');
        
        }else{
            for ($i=0; $i < sizeof($number_cost); $i++) { 
                $this->setSmsResponse(null, $part, $number_cost[$i], $senderName, $camping_id, 99);
            }
            Workflow::find($camping_id)->update(['updated_at' => Carbon::now(), 'date_begin' => Carbon::now()]);           
            return $this->sendResponse_message('El workflow SMS se ha guardado de forma exitosa.');
        }        
    }




/**
                                CALL
-------------------------------------------------------------------------------------------------------
**/ 
    public function sendCallRecurrent(Request $request){
        
        //$regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:200',
            'sender_name' =>'required|integer',
            'url_audio' => 'nullable|string',
            'text_call' => 'nullable|string',
            'audio' => 'nullable|mimes:mpga,wav,oga,ogg,spx',
            'save' =>'required|boolean',
            'simulacion' =>'required|boolean',
            'id_invoice' => 'nullable|string',
            'groups' =>'nullable|array',
            'groups.*' =>'nullable|integer|min:1',
            'phonenumbers' =>'nullable|array',
            'phonenumbers.*' =>'nullable|integer|min:1',            
            'interaccion' => 'nullable|array',
            'interaccion.*.pre_texto' => 'nullable|string',
            'interaccion.*.post_texto' => 'nullable|string',
            'interaccion.*.tecla' => 'nullable|integer',
            'interaccion.*.tipo' => 'nullable|integer',
            'interaccion.*.phone' => 'nullable|string',
            'interaccion.*.limitador' =>'nullable|string',
            'interaccion.*.sender_name' =>'nullable|string',
            'planificacion' => 'nullable|boolean'
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        if((is_null($request->input("phonenumbers")) || 
            count($request->input("phonenumbers")) < 1) && 
            (is_null($request->input("groups")) &&  
            count($request->input("groups")) < 1) ){
            return $this->sendError('Debe agregar al menos un número telefónico o un grupo de contacto');
        }

        if(!is_null($request->input("url_audio"))){

            if (!filter_var($request->input("url_audio"), FILTER_VALIDATE_URL)) {
                return $this->sendError('La url del audio no es correcta');
            }
            
            $searulrFile = File::where('orig_filename',$request->input("url_audio"))->first();
            if(!$searulrFile){
                return $this->sendError('La url del audio no es correcta');
            }           
            
            $urlFile = $request->input("url_audio");
        
        }else{
            if($request->hasfile('audio')){
                $file = $request->file('audio');               
                $fileUrl = Storage::disk('public')->put('audios', $file);            
                $urlFile = env('APP_URL').'storage/'.$fileUrl;

            }else{
                return $this->sendError('Debes agregar un audio o un texto para ser usado en la llamada.');
            }
        }

        
        if(!is_null($request->input("text_call"))){
            $text = $request->input("text_call");
        }

        
        $number_cost = array();
        $phoneNumbersForSendCall = array();
        $valuetotal = 0;
        if(!is_null($request->input("phonenumbers"))){
            $phonenumbers = $request->input("phonenumbers");

            for ($i=0; $i < sizeof($phonenumbers); $i++) { 
                $phonconvert = $phonenumbers[$i];
                $value = $this->tarff_phone($phonconvert, 1);
                
                array_push($number_cost, array("phone" =>  $phonconvert, "cost" => $value));
                array_push($phoneNumbersForSendCall,  $phonconvert);
                
                $valuetotal = ((float)$valuetotal + $value);
            }

        }

        if(!is_null($request->input("groups"))){
            $tariff_contact_number = array();
            $groups = $request->input("groups");
            
            for ($i=0; $i < sizeof($groups); $i++) {
                
                $arrays = $this->tarff_group($groups[$i], 1);  

                for ($j=0; $j < sizeof($arrays); $j++) { 
                    
                    if(!in_array(array('cost' => $arrays[$j]["cost"], 'phone' => $arrays[$j]["phone"]), $tariff_contact_number)){
                       array_push($tariff_contact_number, array('cost' => $arrays[$j]["cost"], 'phone' => $arrays[$j]["phone"])); 
                    }                    
                }                
            }
            
            
            for ($j=0; $j < sizeof($tariff_contact_number); $j++) {               
                $valuetotal = ($valuetotal + $tariff_contact_number[$j]["cost"]);
                array_push($phoneNumbersForSendCall,  $tariff_contact_number[$j]["phone"]);
            }
            for ($i=0; $i < sizeof($tariff_contact_number); $i++) { 
                array_push($number_cost, $tariff_contact_number[$i]);
            }            
        } 
        

        $name = $request->input("name");
        $call_id = $request->input("sender_name");
        
        $user_now = JWTAuth::parseToken()->authenticate(); 

        $new_camping = new Workflow();
        $new_camping->name = $name;
        $new_camping->call_id = $call_id;
        $new_camping->audio = $urlFile;
        $new_camping->sms = $text;
        $new_camping->cost = $valuetotal;
        $new_camping->is_blocked = 0;
        if($request->input("save")){
            $new_camping->status = 4;
        }else{
            $new_camping->status = 1;
        }
        $new_camping->filter_type = 4;
        $new_camping->user_email = $user_now->email;
        $new_camping->date_register = Carbon::now();

        $new_camping->save();
        $camping_id = $new_camping->id;

        // $invoice = $this->invoiceRepo->setInvoiceWorkflow($request->input("id_invoice"), $camping_id);
        // if(!$invoice){
        //     Workflow::find($camping_id)->delete();
        //     return $this->sendError('No se pudo encontrar el pago');
        // }


        if( !is_null($request->input("interaccion")) ){
            $this->addInteraccion($request->input("interaccion"), $camping_id);            
        }

        $estados = array('DIALLED','CHANNEL_UNAVAILABLE','CONGESTION','BUSY','NO_ANSWER','ANSWERED','FATAL_ERROR');

        for ($i=0; $i < sizeof($phoneNumbersForSendCall); $i++) { 
            
            $new_call = new Calls();
            $new_call->phonenumber = $phoneNumbersForSendCall[$i];
            $new_call->user_email = $user_now->email;
            
            if($request->input("simulacion") && !$request->input("save")){
                $new_call->call_status = $estados[rand(0,6)];
            }
            if($request->input("save")){
                $new_call->call_status = 'SENT_TO_ASTERISK';
            }

            $new_call->id_workflow = $camping_id;
            for ($k=0; $k < sizeof($number_cost); $k++) { 
                
                if($number_cost[$k]['phone'] == $phoneNumbersForSendCall[$i]){
                    $new_call->cost = $number_cost[$k]['cost'];                    
                }
            }
            $new_call->save();

            Workflow::find($camping_id)->update(['updated_at' => Carbon::now()]);
        }
        
        if($request->input("save")){
            Workflow::find($camping_id)->update(['date_end' => Carbon::now()]);
        }else{
            Workflow::find($camping_id)->update(['date_begin' => Carbon::now()]); 
        }
        

        return $this->sendResponse_message('El workflow recurrente CALL se ha guardado de forma exitosa.');
    }



/**
                                CALL - SMS
-------------------------------------------------------------------------------------------------------
**/ 
    public function sendCallSmsRecurrent(Request $request){
        //$regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:200',
            'sender_name_sms' => 'required|max:11',
            'sender_name_call' => 'required|integer',
            'text_sms' => 'required',
            'url_audio' => 'nullable|string',
            'audio' => 'nullable|mimes:mpga,wav,oga,ogg,spx',
            'save' => 'required|boolean',
            'id_invoice' => 'nullable|string',
            'simulacion' => 'required|boolean',
            'groups' => 'nullable|array',
            'groups.*' => 'nullable|integer',
            'phonenumbers' => 'nullable|array',
            'phonenumbers.*' => 'nullable|integer',
            'interaccion' => 'nullable|array',
            'interaccion.*.pre_texto' => 'nullable|string',
            'interaccion.*.post_texto' => 'nullable|string',
            'interaccion.*.tecla' => 'nullable|integer',
            'interaccion.*.tipo' => 'nullable|integer',
            'interaccion.*.phone' => 'nullable|string',
            'interaccion.*.limitador' =>'nullable|string',
            'interaccion.*.sender_name' =>'nullable|string',
            'planificacion' => 'nullable|boolean'                    
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        if((is_null($request->input("phonenumbers")) || 
            count($request->input("phonenumbers")) < 1) && 
            (is_null($request->input("groups")) &&  
            count($request->input("groups")) < 1) ){
            return $this->sendError('Debe agregar al menos un número telefónico o un grupo de contacto');
        }

        
        if(!is_null($request->input("url_audio"))){

            if (!filter_var($request->input("url_audio"), FILTER_VALIDATE_URL)) {
                return $this->sendError('La url del vídeo no es correcta');
            }
            
            $searulrFile = File::where('orig_filename',$request->input("url_audio"))->first();
            if(!$searulrFile){
                return $this->sendError('La url del vídeo no es correcta');
            }
                        
            $urlFile = $request->input("url_audio");
        
        }else{
            if($request->hasfile('audio')){
                $file = $request->file('audio');                 
                $fileUrl = Storage::disk('public')->put('audios', $file);            
                $urlFile = env('APP_URL').'storage/'.$fileUrl;

            }else{
                return $this->sendError('Debes agregar un audio o un texto para ser usado en la llamada.');
            }
        }
        
        if(is_null($request->input("text_sms"))){
            return $this->sendError('Debe agregar un texto para el SMS si no recibe la llamada el contacto.');
        }

       
        $number_cost = array();
        $phoneNumbersForSendCall = array();
        $phoneNumbersForSendSms = array();
        $valuetotal = 0;
        
        if( !is_null($request->input("phonenumbers")[0]) && count($request->input("phonenumbers")) > 0){
            $phonenumbers = $request->input("phonenumbers");

            for ($i=0; $i < sizeof($phonenumbers); $i++) { 
                $phonconvert = $phonenumbers[$i];
                $value = $this->tarff_phone($phonconvert, 1);                
                
                array_push($number_cost, array("phone" =>  $phonconvert, "cost" => $value));
                
                if($request->input("simulacion")){
                    if(!$request->input("save")){
                        if($i%2 == 0){
                            array_push($phoneNumbersForSendCall,  $phonconvert);
                        }else{
                            array_push($phoneNumbersForSendSms,  $number_cost);
                        }
                    }else{
                        array_push($phoneNumbersForSendCall,  $phonconvert);
                    }
                    
                }else{
                    array_push($phoneNumbersForSendCall,  $phonconvert);
                }                
                
                $valuetotal = ((float)$valuetotal + $value);
            }

        }

        if( !is_null($request->input("groups")[0]) && count($request->input("groups")) > 0){
            
           
                $tariff_contact_number = array();
                $groups = $request->input("groups");

                for ($i=0; $i < sizeof($groups); $i++) {
                    
                    $arrays = $this->tarff_group($groups[$i], 1);  
                    
                    for ($j=0; $j < sizeof($arrays); $j++) { 
                        
                        if(!in_array(array('cost' => $arrays[$j]["cost"], 'phone' => $arrays[$j]["phone"]), $tariff_contact_number)){
                           
                           array_push($tariff_contact_number, array('cost' => $arrays[$j]["cost"], 'phone' => $arrays[$j]["phone"])); 
                           
                           array_push($number_cost, array("phone" =>  $arrays[$j]["phone"], "cost" => $arrays[$j]["cost"]));
                        }                        
                    }                    
                }
                
                
                for ($j=0; $j < sizeof($tariff_contact_number); $j++) {               
                    $valuetotal = ($valuetotal + $tariff_contact_number[$j]["cost"]);
                    
                    if($request->input("simulacion")){

                        if(!$request->input("save")){
                            $simu = rand(1,25);
                            if($simu%2 == 0){
                                array_push($phoneNumbersForSendCall,  $tariff_contact_number[$j]["phone"]);
                            }else{
                                $smsPhones = array('phone' => $tariff_contact_number[$j]["phone"], 'cost' => $tariff_contact_number[$j]["cost"]);
                                array_push($phoneNumbersForSendSms,  $smsPhones);
                            }
                        }else{
                            array_push($phoneNumbersForSendCall,  $tariff_contact_number[$j]["phone"]);
                        }

                    }else{
                        array_push($phoneNumbersForSendCall,  $tariff_contact_number[$j]["phone"]);
                    }
                    
                    
                }
               
        }

       
    
        $name = $request->input("name");
        $smsText = $request->input("text_sms");
        $user_now = JWTAuth::parseToken()->authenticate(); 

        $new_camping = new Workflow();
        $new_camping->name = $name;
        
        $new_camping->welcome_message = "";
        $new_camping->audio = $urlFile;
        $new_camping->sms = $smsText;
        $new_camping->cost = $valuetotal;
        $new_camping->sender_name = $request->input("sender_name_sms");;
        $new_camping->call_id = $request->input("sender_name_call");;
        
        $new_camping->is_blocked = 0;
        if($request->input("save")){
            $new_camping->status = 4;
        }else{
            $new_camping->status = 1;
        }
        
        $new_camping->filter_type = 5;
        $new_camping->user_email = $user_now->email;
        $new_camping->date_register = Carbon::now();


        $new_camping->save();
        $camping_id = $new_camping->id;

        
        // $invoice = $this->invoiceRepo->setInvoiceWorkflow($request->input("id_invoice"), $camping_id);
        // if(!$invoice){
        //     Workflow::find($camping_id)->delete();
        //     return $this->sendError('No se pudo encontrar el pago');
        // }


        if( !is_null($request->input("interaccion")) ){
            $this->addInteraccion($request->input("interaccion"), $camping_id);            
        }

        
        for ($i=0; $i < sizeof($phoneNumbersForSendCall); $i++) { 
            
            $new_call = new Calls();
            $new_call->phonenumber = $phoneNumbersForSendCall[$i];
            $new_call->user_email = $user_now->email;
            
            if($request->input("simulacion") || !$request->input("save")){
                $new_call->call_status = 'ANSWERED';
            }

            if($request->input("save")){
                $new_call->call_status = 'SENT_TO_ASTERISK';
            }

            $new_call->id_workflow = $camping_id;
            for ($k=0; $k < sizeof($number_cost); $k++) { 
                
                if($number_cost[$k]['phone'] == $phoneNumbersForSendCall[$i]){
                    $new_call->cost = $number_cost[$k]['cost'];                    
                }
            }
            $new_call->save();
        }

        if(!$request->input("save")){

            $senderName = $request->input("sender_name_sms");
            $simulacion = $request->input("simulacion");
            if(!$simulacion){
                $callSmsResponse = $this->sendSmsToMultipleRecipients($phoneNumbersForSendSms, $smsText, $senderName, $camping_id);
            }else{
                $callSmsResponse = $this->simulacionSmsToMultipleRecipients($phoneNumbersForSendSms, $smsText, $senderName, $camping_id);
            }

            Workflow::find($camping_id)->update(['updated_at' => Carbon::now(), 'date_end' => Carbon::now(), 'date_begin' => Carbon::now()]); 

            if($callSmsResponse->status == false){
                return $this->sendError('Error SMS', $callSmsResponse);
            }
            return $this->sendResponse_message('El workflow recurrente CALL-SMS se ha ejecutado de forma exitosa.');

        }else{

            Workflow::find($camping_id)->update(['updated_at' => Carbon::now(), 'date_begin' => Carbon::now()]); 

            return $this->sendResponse_message('El workflow recurrente CALL-SMS se ha guardado de forma exitosa.');
        }       
    }




/*********
        Endpoint para calcular el costo total de todos los números contenidos en un grupo
*********/
    public function tarff_group($id_group, $type_service){

        $user_now = JWTAuth::parseToken()->authenticate();
        $search = GroupContact::with(array('contacto' => function($query) use ($user_now)
                        {  
                            $query->where('user_email','=', $user_now->email);
                            
                        }))
                    ->where('id_group','=', $id_group)
                    ->get();

        $number_cost = array();
        
        foreach ($search as $key) {
                $phonconvert = $key->contacto->phone;
                $value = $this->tarff_phone($phonconvert, $type_service);
                array_push($number_cost, array("phone" =>  $phonconvert, "cost" => $value));
        }

        return $number_cost;
    }


/*********
        Function para calcular el costo total de un número
        $type_service : 0 --> SMS
                        1 --> CALL, CALL-SMS
*********/

    public function tarff_phone($phone, $type_service){
        
        $value = 0;
        $prefix = substr($phone, 0, 2);            
        $countries = Country::where('phonenumber_prefix', $prefix)->first();
        if($countries){
            
            if($type_service == 0)
                $cost = (float)$countries->sms_customer_price;
            else
                $cost = (float) $countries->customer_price ;
            
            $value = round($cost, 2, PHP_ROUND_HALF_UP);
        }else{
            $prefix = substr($phone, 0, 3);            
            $countries_2 = Country::where('phonenumber_prefix', $prefix)->first();
            if($countries_2){
                
                if($type_service == 0)
                    $cost = (float) $countries->sms_customer_price;
                else
                    $cost = (float) $countries->customer_price ;
            
                $value = round($cost, 2, PHP_ROUND_HALF_UP);
            }                    
        }
        
        if($value < 0.50){
            $value = 0.50;
        }
        return $value;

    }


/*********
       REAL : Endpoint para enviar los SMS al proveedor
*********/
    public function sendSmsToMultipleRecipients($phoneNumbers,$smsText,$senderName, $message_parts, $idCampaign) {

        $userName = env("SMS_SERVICE_USERNAME");
        $password = env("SMS_SERVICE_PASSWORD");

        $result = (object) [
            'error' => '',
            'status' => false,
        ];

        if(count($phoneNumbers) == 0) {
            $result->error = 'Recipients Numbers not provided.';
            return $result;
        }

        if(!$smsText) {
            $result->error = 'SMS text not provided.';
            return $result;
        }

        if(!$senderName) {
            $result->error = 'Sender Name not provided.';
            return $result;
        }

        if(!$userName || !$password) {
            $result->error = 'SMS Service Credentials not Provided.';
            return $result;
        }

       
        $submit_date = Carbon::now();
        $phoneNumbersFormat = array();
        for ($i=0; $i < sizeof($phoneNumbers); $i++) { 
            array_push($phoneNumbersFormat, $phoneNumbers[$i]['phone']);
        }

        $post['to'] = $phoneNumbersFormat;
        $post['message'] = $smsText;
        $post['from'] = $senderName;
        $post['campaignName'] = $idCampaign;
        $post['trans'] = 1;        
        $post['parts'] = $message_parts; 

        $post['notificationUrl'] = "http://api.nelumbo.com.co/api/smsResponse/?id=%i&receiver=%P&donedate=%t&status=%d&sender=".urlencode($senderName)."&submitdate=".urlencode($submit_date)."&parts=".$part;

        $headers = ['Content-Type' => 'application/json',
                        'Accept' => 'application/json', 
                        'Authorization' => 'Basic '.base64_encode($userName.":".$password) 
                       ];
        $body = json_encode($post);

        $client = new Client(['headers' => $headers]);

        try {
            $request = $client->request('POST', 'https://dashboard.360nrs.com/api/rest/sms', ['body' => $body]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $result->error = explode("\n",$e->getMessage())[1];                
            }
            return $result;           
        }
        
        $resArray = json_decode($request->getBody()->getContents());
        $code = $request->getStatusCode();

        if($code == 202){                      
            foreach ($resArray->result as $key) {
               if($key->accepted){
                    
                    $this->setSmsResponse($key->id, $message_parts, $key->to, $senderName, $idCampaign, 1);                    
                }
            }                                
            $result->status = true;
            return $result;

        }else if($code == 207){
           
            $arayRespError = array();
            
            foreach ($resArray->result as $key) {                                 
                if($key->accepted){
                    $this->setSmsResponse($key->id, $message_parts, $key->to, $senderName, $idCampaign, 1);                                    
                }
                else{ 
                    $this->setSmsResponse(null, $message_parts, $key->to, $senderName, $idCampaign, 2, $key->error);
                    
                }                                                               
            }
            $result->status = true;
            return $result;
        }
        else{
            return $resArray->result;            
        }

    }

/*********
       SIMULACIÖN : Endpoint para enviar los SMS
*********/
    public function simulacionSmsToMultipleRecipients($phoneNumbers,$smsText,$senderName, $message_parts, $idCampaign) {

        $result = (object) [
            'error' => '',
            'status' => false,
        ];

        if(count($phoneNumbers) == 0) {
            $result->error = 'Recipients Numbers not provided.';
            return $result;
        }

        if(!$smsText) {
            $result->error = 'SMS text not provided.';
            return $result;
        }

        if(!$senderName) {
            $result->error = 'Sender Name not provided.';
            return $result;
        }

        
        for ($i=0; $i < sizeof($phoneNumbers); $i++) { 

            $it = rand(1, 100);            
            $referenceId = $it + 1234598601; 
            $this->setSmsResponse($referenceId, $message_parts, $phoneNumbers[$i], $senderName, $idCampaign, 1);
            
        }
        $result->status = true;
        return $result;
    }


/*********
        Endpoint para guardar el registro del envío del SMS. Dependiendo del tipo (success, error)
*********/
    public function setSmsResponse($referenceId, $message_parts, $phoneNumbers, $sender, $idCampaign, $type, $arrayError = null, $simulacion = true){
        
        if($type == 1){

            $response = GroupWorkflow::where('destination_number',$phoneNumbers)
                          ->where('id_workflow',$idCampaign)->first();

            if(!$response){
                 
                 $status_code = 3;
                 $status_text = "En proceso de envío";

                if($simulacion){
                    $it = rand(1, 200);
                    if($it%2 == 0 || $it%3 == 0){
                        $status_code = 1;
                        $status_text = "Mensaje expedido";

                        $response = GroupWorkflow::create([
                            'id_workflow' => $idCampaign,
                            'reference_id' => $referenceId,
                            'destination_number' => $phoneNumbers['phone'],
                            'cost' => $phoneNumbers['cost'],
                            'status_code' => $status_code,
                            'status_text' => $status_text,
                            'sender_name' => $sender,
                            'message_parts' => $message_parts,
                            'send_on'=> Carbon::now(),
                            'delivered_on' => Carbon::now()
                        ]);

                    }else{

                        $status_code = 2;
                        $status_text = "No se puedo entregar al destinatario";

                        $response = GroupWorkflow::create([
                            'id_workflow' => $idCampaign,
                            'reference_id' => $referenceId,
                            'destination_number' => $phoneNumbers['phone'],
                            'cost' => $phoneNumbers['cost'],
                            'status_code' => $status_code,
                            'status_text' => $status_text,
                            'sender_name' => $sender,
                            'message_parts' => $message_parts,
                            'send_on'=> Carbon::now(),
                            'delivered_on' => Carbon::now()
                        ]);

                    }
                    
                }else{

                    $response = GroupWorkflow::create([
                        'id_workflow' => $idCampaign,
                        'reference_id' => $referenceId,
                        'destination_number' => $phoneNumbers['phone'],
                        'cost' => $phoneNumbers['cost'],
                        'status_code' => $status_code,
                        'status_text' => $status_text,
                        'message_parts' => $message_parts,
                        'sender_name' => $sender
                    ]);

                }
                
            }

        }else if($type == 2){

            $response = GroupWorkflow::create([
                    'id_workflow' => $idCampaign,
                    'result_code' => $arrayError["description"],
                    'error_code' => $arrayError["code"],
                    'destination_number' => $phoneNumbers['phone'],
                    'cost' => $phoneNumbers['cost'],
                    'status_code' => 2,
                    'message_parts' => $message_parts,
                    'status_text' => "Fallo el envío, error de número telefónico",
                    'sender_name' => $sender
                ]);

        }else{

            $response = GroupWorkflow::create([
                    'id_workflow' => $idCampaign,
                    'destination_number' => $phoneNumbers['phone'],
                    'cost' => $phoneNumbers['cost'],
                    'status_code' => 3,
                    'message_parts' => $message_parts,
                    'status_text' => "En proceso de envío",
                    'sender_name' => $sender
                ]);

        }               
        
    }


/*************
        Metodo que usa el Proveedor de SMS para actualizar los estados de los SMS
*************/
    public function smsResponse(Request $request){

        $response = GroupWorkflow::where('reference_id', $request->get('id'))
            ->where('destination_number', $request->get('receiver'))
            ->first();
        if (!$response) {
            $response = new GroupWorkflow();
            $response->reference_id = $request->get('id');
            $response->destination_number = $request->get('receiver');
        }
        $response->status_code = $request->get('status');
        

        if ($request->get('submitdate')) {
            $response->send_on = Carbon::createFromTimestamp($request->get('submitdate'));
        }

        if ($request->get('donedate')) {
             $response->delivered_on = Carbon::createFromTimestamp($request->get('donedate'));
        }


        if($request->get('status') == 1){
            $response->status_text = 'Mensaje expedido';           
            
        }else if($request->get('status') == 2){
            $response->status_text = 'No se puedo entregar al destinatario';            
        }else if($request->get('status') == 4){
            $response->status_text = 'Mensaje aceptado. En espera por notificación';            
        }else{
            $response->status_text = 'Mensaje fallido. No se puede entregar a operadora';
        }        
        $response->sender_name = $request->get('sender');
        $response->message_parts = $request->get('parts');
        
        $response->save();
    }



}



