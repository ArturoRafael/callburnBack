<?php 

namespace App\Http\Services;

use App\Http\Models\Tariff;
use App\Http\Services\IspService;

class TariffService{
	/**
	 * Object of Tariff class for working woth database
	 *
	 * @var Tariff
	 */
	private $tariff;

	/**
	 * Create a new instance of TariffService
	 *
	 * @param Tariff $tariff
	 * @return void
	 */
	public function __construct()
	{
		$this->tariff = new Tariff();
	}

	/**
	 * Calculate cost for the given phoennubmer and seconds.
	 *
	 * @param string $phonenumber
	 * @param integer $billsec
	 * @param integer $type   standard - 0, custom - 1
	 * @return float
	 */
	public function calculateCost($phonenumber, $billsec, $type)
	{
		$tariff = $phonenumber->tariff;
		if(!$tariff){
			return false;
		}
		if($type == 0){
			return $tariff->standard_price;
		}
		if($type == 1){
			return $tariff->country->customer_price * $billsec / 60;
		}
		return false;
	}

	/**
	 * Detect isp .
	 *
	 * @param string $phonenumber
	 * @return string
	 */
	public function detectIsp($phonenumber)
	{
		$tariff = $phonenumber->tariff;
		if(!$tariff){
			return false;
		}
		return $tariff->bestIsp;
	}
}
