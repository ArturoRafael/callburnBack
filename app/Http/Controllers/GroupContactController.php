<?php

namespace App\Http\Controllers;

use App\Http\Models\GroupContact;
use App\Http\Models\Contact;
use App\Http\Models\Group;
use Illuminate\Http\Request;
use Validator;

class GroupContactController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $group_contacto = GroupContact::with('contacto')->with('grupo')->paginate(15);

        return $this->sendResponse($group_contacto->toArray(), 'Contactos por grupos devueltos con éxito');
    }

 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_contact' => 'integer|required',            
            'id_group' => 'integer|required'

        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }


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
     * Display the specified resource.
     *
     * @param  \App\Http\Models\GroupContact  $id
     * @return \Illuminate\Http\Response
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Http\Models\GroupContact  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [            
            'id_contact_old' => 'required',
            'id_contact_new' => 'required',     
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());      
        }
        $group_contact_search = $this->group_contact_search($id, $input['id_contact_old']);

        if(count($group_contact_search) != 0){

            $group = Group::find($id);
            if (is_null($group)) {
                return $this->sendError('El grupo indicado no existe');
            }

            $contact = Contact::find($input['id_contact_new']);
            if (is_null($contact)) {
                return $this->sendError('La contacto indicado no existe');
            }

            $group_contact_search2 = $this->group_contact_search($id, $input['id_contact_new']);
            
            if(count($group_contact_search2) != 0){
                return $this->sendError('El contacto ya se encuentra asignado al grupo'); 
            }
            
        }else{
           return $this->sendError('El contacto por grupo no se encuentra'); 
        }

        GroupContact::where('id_group','=',$id)
                            ->where('id_contact','=', $input['id_contact_old'])
                            ->update(['id_contact' => $input['id_contact_new']]);  
        
        $group_contacto = $this->group_contact_search($id, $input['id_contact_new']);
                            
        return $this->sendResponse($group_contacto->toArray(), 'Contacto por grupo actualizado con éxito');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Http\Models\GroupContact $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group_contacto = GroupContact::where('id_group','=',$id)->get();
        if (count($group_contacto) == 0) {
            return $this->sendError('Contactos por grupo no encontrados');
        }
        GroupContact::where('id_group','=',$id)->delete();
        return $this->sendResponse($group_contacto->toArray(), 'Contactos por grupo eliminados con éxito');
    }



    public function group_contact_search($id_contact, $id_group){

        $search = GroupContact::where('id_contact','=',$id_contact)
                                ->where('id_group','=', $id_group)->get();
        return $search;
    }
}
