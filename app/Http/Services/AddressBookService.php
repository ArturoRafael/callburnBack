<?php 

namespace App\Http\Services;


class AddressBookService{

	/**
	 * Put three asterisk at the phonenumber
	 *
	 * @param string $phonenumber
	 * @return string
	 */
	public static function addThreeAsterisks($phonenumber)
	{
		return substr($phonenumber, 0, -3) . '***';
	}

}