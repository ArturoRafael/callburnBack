<?php namespace App\Services;

use App\Contracts\NumberVerificationInterface;
use App\Models\NumberVerification;

class NumberVerificationService{

	/**
	 * Object of Phonenumber class.
	 *
	 * @var Phonenumber
	 */
	private $phonenumber;

	/**
	 * Create a new instance of NumberVerificationService
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->phonenumber = new NumberVerification();
	}

	/**
	 * Store number for verfying.
	 *
	 * @param array $numberData
	 * @return Phonenumber
	 */
	public function createNumberVerification($numberData)
	{
		$phonenumber = $numberData['phone_number'];
		$code = $numberData['code'];
		$field = $this->phonenumber->where('phone_number', $phonenumber)->first();
		if($field){
			return $field->update($numberData);
		}
		$numField =  $this->phonenumber->create($numberData);
		return $numField;
	}

	/**
	 * Get number by code.
	 *
	 * @param string $code
	 * @param string $phonenumber
	 * @return Phonenumber
	 */
	public function getNumberVerification($code, $phonenumber)
	{
		$verificationRow = $this->phonenumber->where('phone_number', $phonenumber)->with('tariff')->first();
		if(!$verificationRow){
			return null;
		}
		if($verificationRow->retries == 4){
			$verificationRow->delete();
			return null;
		}
		$verificationRow->retries = $verificationRow->retries + 1;
		$verificationRow->save();
		if($verificationRow->code == $code){
			return $verificationRow;
		}
		return null;
	}

	/**
	 * Remove number verification field.
	 *
	 * @param integer $id
	 * @return bool
	 */
	public function removeNumberVerification($id)
	{
		return $this->phonenumber->find($id)->delete();
	}

	/**
	 * Check if users all caller ids are in the same country
	 *
	 * @param User $user
	 * @param Tariff $newNumberTariff
	 * @return bool
	 */
	public function checkIfAllCallerIdsHaveSameCountry($user, $newNumberTariff)
	{
		$callerIds = $user->numbers;
		//If user does not have any caller id, 
		//can add any valid number
		if(!$callerIds){return true;}
		foreach ($callerIds as $callerId) {
			if($callerId->tariff->country->code != $newNumberTariff->country->code){
				return false;
			}
		}
		return true;
	}
}