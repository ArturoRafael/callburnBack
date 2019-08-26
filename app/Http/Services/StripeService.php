<?php 
namespace App\Http\Services;

use Stripe;

class StripeService{
	
	/**
	 * Create a new instance of StripeService class
	 *
	 * @return void
	 */
	public function __construct()
	{
		$key = config('services.stripe.secret');
		$this->stripe = Stripe::make($key);
	}

	/**
	 * Create a new customer in stripe
	 *
	 * @param User $user
	 * @return string
	 */
	public function createCustomer($user)
	{
		$customer = $this->stripe->customers()->create([
		    'email' => $user->email
		]);

		return $customer['id'];
	}

	/**
	 * Get customer by token
	 * @param string $userStripeId
	 * @return array
	 */
	public function getCustomerByPK($userStripeId)
	{
		$customer = $this->stripe->customers()->find($userStripeId);
		return $customer;
	}

	/**
	 * Get all locasynced cards
	 *
	 * @param string $userStripeId
	 * @return array
	 */
	public function getAllLocalCards($user)
	{
		$cards = $user->localCards()->orderBy('is_default', 'DESC')->get();
		return $cards;
	}

	
	/**
	 * Get one cards of the user
	 *
	 * @param string $userStripeId
	 * @return array
	 */
	public function getCard($userStripeId , $stripe_id)
	{
		
		$cards = $this->stripe->cards()->find($userStripeId, $stripe_id);
		return $cards;
	}


	/**
	 * Get all cards of the user
	 *
	 * @param string $userStripeId
	 * @return array
	 */
	public function getAllCards($userStripeId)
	{
		$cards = $this->stripe->cards()->all($userStripeId);
		return $cards;
	}

	/**
	 * Create a new card object for the stripe user
	 *
	 * @param string $userStripeId
	 * @param array $cardData
	 * @return array
	 */
	public function createCard($userStripeId, $cardDataToken)
	{
		
		$card = $this->stripe->cards()->create($userStripeId, $cardDataToken);
		
		return $card;
	}


	/**
	 * Create a new token object for the stripe user
	 *
	 * @param array $cardData
	 * @return array
	 */
	public function createCardToken($cardData)
	{
		$card = $this->stripe->tokens()->create([ 'card' => $cardData]);
		return $card;
	}

	/**
	 * Mark card as default for the customer
	 *
	 * @param string $userStripeId
	 * @param string $cardId
	 * @return array
	 */
	public function markCardAsDefault($userStripeId, $cardId)
	{
		$customer = $this->stripe->customers()->update( $userStripeId, [
	        'default_source' => $cardId
	    ]);
		return $customer;
	}


	/**
	 * Mark local card as default for the user
	 *
	 * @param string $user
	 * @param string $cardId
	 * @return array
	 */
	public function markLocalCardAsDefault($user, $cardId)
	{
 		$user->localCards()->update(['is_default' => false]);
 		$user->localCards()->where('stripe_id', $cardId)->update(['is_default' => true]);
 		return true;
	}

	/**
	 * Delete card from customer
	 *
	 * @param string $userStripeId
	 * @param string $cardId
	 * @return array
	 */
	public function deleteCard($userStripeId, $cardId)
	{
		$card = $this->stripe->cards()->delete($userStripeId, $cardId);
		return $card;
	}

	/**
	 * Recharge user
	 */
	public function makeRechargeCapture($user, $amount, $cardId)
	{		
		$array = [
		    'customer' => $user->stripe_customer_id,
		    'currency' => 'EUR',
		    'amount'   => $amount,
		    'source' => $cardId,
		    'description' =>  $user->email,
		    'capture' => false,
		];
		
		$charge = $this->stripe->charges()->create($array);
		return $charge;
	}

	/**
	 * Synchronize stripe cards data with local card data
	 *
	 * @return bool
	 */
	public function syncStripeWithLocal($user)
	{
		
		$cards = $this->getAllCards($user->stripe_customer_id);
		$cardsData = $cards['data'];		
		$customer = $this->getCustomerByPK($user->stripe_customer_id);
		$defaultCardId = $customer['default_source'];
		$cardsIdsThatExist = [];		

		foreach ($cardsData as $cardData) {

			$localCardObject = [
				'user_email' => $user->email,
				'last_4_digits' => $cardData['last4'],
				'expiration_month' => $cardData['exp_month'],
				'expiration_year' => $cardData['exp_year'],
				'card_holder_name' => $cardData['name'],
				'stripe_id' => $cardData['id'],
				'is_default' => $cardData['id'] == $defaultCardId,
			];
			$cardsIdsThatExist[] = $cardData['id'];
			$localCard = $user->localCards()->where('stripe_id', $cardData['id'])->first();
			if($localCard) {
				$localCard->update($localCardObject);
			} else {
				$user->localCards()->create($localCardObject);
			}
		}
		
		$user->localCards()->whereNotIn('stripe_id', $cardsIdsThatExist)->delete();
		return true;
	}
}