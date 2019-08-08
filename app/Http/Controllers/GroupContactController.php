<?php

namespace App\Http\Controllers;

use App\Http\Models\GroupContact;
use App\Http\Models\Contact;
use App\Http\Models\Group;
use App\Http\Requests\GroupContact\GroupCreateContacts;
use App\Http\Requests\GroupContact\CreateGroupContact;
use Validator;

class GroupContactController extends BaseController
{
    /**
     *
     * @OA\Get(
     *   path="/api/auth/groupcontact",
     *   summary="List of group contact",
     *   operationId="index",   
     *   tags={"GroupContact"},     
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
        
        $group_contacto = GroupContact::with('contacto')->with('grupo')->paginate(15);

        return $this->sendResponse($group_contacto->toArray(), 'Contactos por grupos devueltos con éxito');
    }

 

    /**
     *
     * @OA\Post(
     *   path="/api/auth/groupcontact",
     *   summary="create a specific group contact",
     *   operationId="store",   
     *   tags={"GroupContact"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_contact"
     *    ), 
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_group"
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
    public function store(CreateGroupContact $request)
    {
        
        $group_contact_search = $this->group_contact_search($request->input('id_contact'), $request->input('id_group'));

        if(count($group_contact_search) != 0){
           return $this->sendError('El contacto ya esta asignado a un grupo'); 
        }

        $contact = Contact::find($request->input('id_contact'));
        if (is_null($contact)) {
            return $this->sendError('El contacto indicado no existe');
        }

        $group = Group::find($request->input('id_group'));
        if (is_null($group)) {
            return $this->sendError('El grupo indicado no existe');
        }

        $group_contacto = GroupContact::create($request->all());        
        return $this->sendResponse($group_contacto->toArray(), 'Contacto asignado a grupo con éxito');
    }



     /**
     *
     * @OA\Post(
     *   path="/api/auth/createGroupContact",
     *   summary="create a specific group for multi contact (array)",
     *   operationId="createGroupContact",   
     *   tags={"GroupContact"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/array_contact"
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
    public function createGroupContact(GroupCreateContacts $request)
    {
        
        $array_new_ids = array();
        $array_contact = $request->input('array_contact');

        for ($i=0; $i < sizeof($array_contact); $i++) { 

            $contact = Contact::find($array_contact[$i]);
            if (is_null($contact)) {
                return $this->sendError('El contacto con el ID: '.$array_contact[$i].' no existe');
            }
        }

        $group = new Group();
        $group->description = $request->input('description');
        $group->save();

        $_id = $group->id;

        for ($i=0; $i < sizeof($array_contact); $i++) { 

            $group_contact_search = $this->group_contact_search($array_contact[$i], $_id);
            if(count($group_contact_search) == 0){

                $group_contacto = new GroupContact();
                $group_contacto->id_contact = $array_contact[$i];
                $group_contacto->id_group = $_id;
                $group_contacto->save();

                array_push($array_new_ids, $array_contact[$i]);
            }
            
        }

        $search_new = GroupContact::with('grupo')->with('contacto')->whereIn('id_contact',$array_new_ids)
                                ->where('id_group','=', $_id)->get();
       
          
        return $this->sendResponse($search_new->toArray(), 'Contactos asignados a grupo con éxito');
    }





    

   /**
     *
     * @OA\Get(
     *   path="/api/auth/groupcontact/{groupcontact}",
     *   summary="List the contacts for group a specific",
     *   operationId="show",   
     *   tags={"GroupContact"}, 
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
        $group_contacto = GroupContact::with('contacto')->with('grupo')->where('id_group','=',$id)->get();
        if (count($group_contacto) == 0) {
            return $this->sendError('Contactos por grupo no encontrados');
        }
        return $this->sendResponse($group_contacto->toArray(), 'Contactos por grupo devueltos con éxito');
    }



 

     /**
     *
     * @OA\Post(
     *   path="/api/auth/deleteContactGroup",
     *   summary="update a specific group deleting a contact",
     *   operationId="deleteContactGroup",   
     *   tags={"GroupContact"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id_group"
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
    public function deleteContactGroup(CreateGroupContact $request)
    {
        
        $input = $request->all();
        
        $group_contact_search = $this->group_contact_search($input['id_contact'], $input['id_group']);
       
        if(count($group_contact_search) != 0){

            $group = Group::find($input['id_group']);
            if (is_null($group)) {
                return $this->sendError('El grupo indicado no existe');
            }

            $contact = Contact::find($input['id_contact']);
            if (is_null($contact)) {
                return $this->sendError('La contacto indicado no existe');
            }

            GroupContact::where('id_group','=', $input['id_group'])
                            ->where('id_contact','=', $input['id_contact'])
                            ->delete();

             return $this->sendResponse_message('Contacto por grupo eliminado con éxito');
            
        }else{
           return $this->sendError('El contacto por grupo no se encuentra'); 
        }

                            
       
    }

    

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/groupcontact/{groupcontact}",
     *   summary="Delete the group and contact associate",
     *   operationId="destroy",   
     *   tags={"GroupContact"}, 
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
        $group_contacto = GroupContact::where('id_group','=',$id)->get();
        if (count($group_contacto) == 0) {
            return $this->sendError('Contactos por grupo no encontrados');
        }
        GroupContact::where('id_group','=',$id)->delete();
        Group::find($id)->delete();
        return $this->sendResponse($group_contacto->toArray(), 'Contactos desasociados y grupo eliminado con éxito');
    }



    public function group_contact_search($id_contact, $id_group){

        $search = GroupContact::where('id_contact','=',$id_contact)
                                ->where('id_group','=', $id_group)->get();
        return $search;
    }
}
