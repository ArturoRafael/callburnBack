<?php

namespace App\Http\Controllers;

use App\Http\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends BaseController
{
    
    /**
     *
     * @OA\Get(
     *   path="/api/auth/reservation",
     *   summary="List of reservations",
     *   operationId="index",   
     *   tags={"Reservations"},     
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
        $reser = Reservation::with('contacto')->paginate(15);
        return $this->sendResponse($reser->toArray(), 'Reservaciones devueltas con éxito');
    }

   
    /**
     *
     * @OA\Post(
     *   path="/api/auth/reservation",
     *   summary="create a specific reservation",
     *   operationId="store",   
     *   tags={"Reservations"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/id"
     *    ),  
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/reservation_date"
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
            'id' => 'required|integer',
            'reservation_date' => 'required|date'            
        ]);
        if($validator->fails()){
            return $this->sendError('Error de validación.', $validator->errors());       
        }

        $contact = Contact::find($request->input('id'));
        if (is_null($contact)) {
            return $this->sendError('Contacto no encontrado');
        }
        
        $reser = new Reservation();
        $reser->id_contact = $request->input('id');
        $reser->reservation_date = $request->input('reservation_date');
        $reser->save();

        return $this->sendResponse($reser->toArray(), 'Reservación creada con éxito');
    }




    /**
     *
     * @OA\Get(
     *   path="/api/auth/reservation/{reservation}",
     *   summary="List the reservations of a specific contact",
     *   operationId="show",   
     *   tags={"Reservations"}, 
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
        $reser = Reservation::with('contacto')->where('id_contact', $id)->get();
        if (is_null($reser)) {
            return $this->sendError('Reservaciones no encontradas');
        }
        return $this->sendResponse($rol->toArray(), 'Reservaciones devueltas con éxito');
    }

 
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reservation  $reservation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Reservation $reservation)
    {
        return true;
    }

    /**
     *
     * @OA\Delete(
     *   path="/api/auth/reservation/{reservation}",
     *   summary="Delete the reservations for contact",
     *   operationId="destroy",   
     *   tags={"Reservations"}, 
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
            $reser = Reservation::where('id_contact', $id);
            if (is_null($reser)) {
                return $this->sendError('Reservaciones no encontradas');
            }
            $reser->delete();

            return $this->sendResponse($reser->toArray(), 'Reservaciones eliminadas con éxito');

        }catch (\Illuminate\Database\QueryException $e){
            return response()->json(['error' => 'Las reservaciones no se pueden eliminar, son usadas en otra tabla', 'exception' => $e->errorInfo], 400);
        }
    }
}
