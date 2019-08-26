<?php

namespace App\Http\Controllers;

use App\Http\Models\Workflow;
use App\Http\Models\Users;
use App\Http\Models\Country;
use App\Http\Models\GroupWorkflow;
use App\Http\Models\GroupContact;
use App\Http\Models\Contact;
use App\Http\Models\Calls;
use App\Http\Models\Key;
use App\Http\Models\KeyEventType;
use App\Http\Services\InvoiceService;
use App\Http\Services\StripeService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Validator;
use JWTAuth;

class StripeController extends BaseController
{

	/**
	 * Create a new instance of StripeController class
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->stripeRepo = new StripeService();
	}


	/**
	 * Get all cards of the user .
	 * We will use our internal database for this, without making request to Stripe
	 *
	 * @return JSON
	 */
	public function getCards()
	{
		$user = JWTAuth::parseToken()->authenticate();		
		if($user->stripe_customer_id){
			$cards = $this->stripeRepo->getAllLocalCards($user);
		} else{
			$cards = [];
		}
		
        return response()->json(['cards' => $cards], 200);
	}
    
	/**
	                COBRO BLOQUEO SALDO            
	-------------------------------------------------------------------------------------------------------
	**/ 
    public function postMakePayment(Request $request){
    	
        
		$validator = Validator::make($request->all(),[
		        'name' => 'nullable',
		        'number' => 'nullable',
		        'exp_month' => 'nullable',
		        'exp_year' => 'nullable',
		        'cvc' => 'nullable',
		        'amount' => 'nullable',
		        'card_id' => 'nullable'
		    ]
		);

		if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }
        
 		$cardData = $request->only(['name', 'number', 'exp_year', 'exp_month', 'cvc']);
 		$user = JWTAuth::parseToken()->authenticate();
 		
 		if (!$user->stripe_customer_id) {
			$user->stripe_customer_id = $this->stripeRepo->createCustomer($user);
			$user->save();
		}

		if(!is_null($request->input('card_id'))){
			$localCard = $user->localCards()->where('stripe_id', $request->input('card_id'))->first();			
			if(!$localCard){
				 return $this->sendError('Tarjeta no registrada');
			}
			$stripe_id = $localCard['stripe_id'];
			$card = $this->stripeRepo->getCard($user->stripe_customer_id, $stripe_id);
			$card_id = $card['id'];

		}else{
			
			try {

				$createdCardToken = $this->stripeRepo->createCardToken($cardData);
				$token = $createdCardToken['id'];
				$card_id = $createdCardToken['card']['id'];

				$createdCard = $this->stripeRepo->createCard($user->stripe_customer_id, $token);
				$this->stripeRepo->syncStripeWithLocal($user);

			} catch (\Cartalyst\Stripe\Exception\CardErrorException $e) {			
				return response()->json(['data' => $e->getMessage()], 400);
			} catch (\Exception $e) {
	        	return response()->json(['data' => 'Algo salio mal '.$e->getMessage()], 404);
			}
		}

		
 		try {
            
            $chargeAmount = $request->input('amount');
            $stripeResponse = $this->stripeRepo->makeRechargeCapture($user, $chargeAmount, $card_id);    

            if($stripeResponse['status'] == 'succeeded'){
            	$invoiceRepo = new InvoiceService();
            	
	            $invoice = $invoiceRepo->createOrder($user, $stripeResponse);

	            return $this->sendResponse($invoice->toArray(), 'La recarga ha sido autorizada. Puede continuar.');
            }else{
            	return response()->json(['error' => 'Ocurrio un error, no puede ejecutar la campaña.'], 400);
            }

        } catch (\Exception $e) {
        	return $this->sendError('Error '.$e->getMessage());
        	
        }  

    }
}
