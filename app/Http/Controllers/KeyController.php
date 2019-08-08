<?php

namespace App\Http\Controllers;

use App\Http\Models\Key;
use App\Http\Models\KeyEventType;
use App\Http\Models\Workflow;
use App\Http\Requests\Key\KeyRequest;
use Validator;

class KeyController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/key",
     *   summary="List of key",
     *   operationId="index",   
     *   tags={"Keys"},     
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
        $key = Key::with('workflow')->with('key_event_type')->with('parent_key')->paginate(15);
        return $this->sendResponse($key->toArray(), 'Configuración de teclas devueltas con éxito');
    }


    /**
     *
     * @OA\Post(
     *   path="/api/auth/key",
     *   summary="create a specific key",
     *   operationId="store",   
     *   tags={"Keys"},      
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/keypad_value"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_key_event_type"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/label"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/first_action_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/first_action_audio"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/post_action_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/post_action_audio"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/phone_number"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/simultaneus_transfer_limit"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/name_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/transfer_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/has_sub_key_menu"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_parent_key"
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
    public function store(KeyRequest $request)
    {
        $workflow = Workflow::find($request->input('id_workflow'));        
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $key_type = KeyEventType::find($request->input('id_key_event_type'));        
        if (is_null($key_type)) {
            return $this->sendError('Tipo clave de evento no encontrado');
        }

        if(!is_null($request->input('id_parent_key'))){
            $key = Key::find($request->input('id_parent_key'));
            if (is_null($key)) {
                return $this->sendError('Parent key no encontrado');
            }
        }

        $keys = Key::create($request->all());        
        return $this->sendResponse($keys->toArray(), 'key agregada con éxito');

    }

     /**
     *
     * @OA\Get(
     *   path="/api/auth/key/{key}",
     *   summary="List key a specific",
     *   operationId="show",   
     *   tags={"Keys"}, 
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
        $key = Key::with('workflow')->with('key_event_type')->with('parent_key')->find($id);
        if (is_null($key)) {
            return $this->sendError('key no encontrado');
        }
        return $this->sendResponse($key->toArray(), 'key devueltas con éxito');
    }


    /**
     *
     * @OA\Get(
     *   path="/api/auth/keysWorkflow/{keysWorkflow}",
     *   summary="List key a specific by workflow",
     *   operationId="keysWorkflow",   
     *   tags={"Keys"}, 
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
    public function keysWorkflow($id_workflow)
    {
        $workflow = Workflow::find($id_workflow);
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }
        $key = Key::with('key_event_type')->with('parent_key')->where('id_workflow' , $id_workflow)->get();
        if (is_null($key)) {
            return $this->sendError('keys no encontradas');
        }
        return $this->sendResponse($key->toArray(), 'keys devueltas con éxito');
    }

 
    /**
     *
     * @OA\Put(
     *   path="/api/auth/key/{key}",
     *   summary="update a specific key",
     *   operationId="update",   
     *   tags={"Keys"}, 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_workflow"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/keypad_value"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_key_event_type"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/label"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/first_action_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/first_action_audio"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/post_action_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/post_action_audio"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/phone_number"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/simultaneus_transfer_limit"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/name_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/transfer_text"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/has_sub_key_menu"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_parent_key"
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
    public function update(KeyRequest $request, $id)
    {
        $input = $request->all();

        $workflow = Workflow::find($input['id_workflow']);        
        if (is_null($workflow)) {
            return $this->sendError('Workflow no encontrado');
        }

        $key_type = KeyEventType::find($input['id_key_event_type']);        
        if (is_null($key_type)) {
            return $this->sendError('Tipo clave de evento no encontrado');
        }

        if(!is_null($input['id_parent_key'])){
            $key = Key::find($input['id_parent_key']);
            if (is_null($key)) {
                return $this->sendError('Parent key no encontrado');
            }
        }

        $keys = Key::find($id);
        if (is_null($keys)) {
            return $this->sendError('key no encontrado');
        }

        $keys->id_workflow = $input['id_workflow']; 
        $keys->keypad_value = $input['keypad_value'];
        $keys->id_key_event_type = $input['id_key_event_type']; 
        $keys->label = $input['label'];
        $keys->first_action_text = $input['first_action_text'];
        $keys->first_action_audio = $input['first_action_audio'];
        $keys->post_action_text = $input['post_action_text'];
        $keys->phone_number = $input['phone_number'];
        $keys->simultaneus_transfer_limit = $input['simultaneus_transfer_limit'];
        $keys->name_text = $input['name_text'];
        $keys->transfer_text = $input['transfer_text'];
        $keys->transfer_audio = $input['transfer_audio'];
        $keys->has_sub_key_menu = $input['has_sub_key_menu'];
        $keys->id_parent_key = $input['id_parent_key'];

        $keys->save();

        return $this->sendResponse($keys->toArray(), 'key actualizada con éxito');
    }

    

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/key/{key}",
     *   summary="Delete the key",
     *   operationId="destroy",   
     *   tags={"Keys"}, 
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

            $key = Key::find($id);
            if (is_null($key)) {
                return $this->sendError('key no encontrado');
            }
            if($key->has_sub_key_menu == 0){
                $key->delete();
            }else{
                $key_anidadas = array();
                array_push($key_anidadas, array('id'=>$id, 'id_parent_key'=> null, 'has_sub_key_menu'=> 1));
                do{                    
                    $keys_type = Key::where('id_parent_key', $id)->first();
                    
                    array_push($key_anidadas, array('id'=>$keys_type->id,'id_parent_key'=>$keys_type->id_parent_key, 'has_sub_key_menu'=>$keys_type->has_sub_key_menu));
                    $id = $keys_type->id;
                }while($keys_type->has_sub_key_menu == 1);
                
                $key_anidadas = array_reverse($key_anidadas);                
                foreach ($key_anidadas as $key) { 
                    Key::where('id', $key['id'])->where('id_parent_key', $key['id_parent_key'])->delete();                 
                }
            }            
            return $this->sendResponse_message('Keys en cascada eliminadas con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'Keys no se pueden eliminar, es usado en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }



    /**
     *
     * @OA\Delete(
     *   path="/api/auth/destroyAll/{destroyAll}",
     *   summary="Delete all key by workflow",
     *   operationId="destroyAll",   
     *   tags={"Keys"}, 
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
    public function destroyAll($id_workflow)
    {
        
        try {
            
            $keys = Key::where('id_workflow', $id_workflow)->get();
            if (is_null($keys)) {
                return $this->sendError('keys no encontradas');
            }
            
            $keys = Key::where('id_workflow', $id_workflow)->delete();                     
            return $this->sendResponse_message('Keys eliminadas con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'Keys no se pueden eliminar', 'exception' => $e->errorInfo], 400);
        }
    }
}
