<?php 

namespace App\Http\Services;

use App\Http\Models\Coupon;
use App\Http\Models\Invoice;
use App\Http\Contracts\InvoiceInterface;
use App\Http\Services\SendEmailService;
use App\Http\Services\SlackNotificationService;
use App\Http\Services\ActivityLogService;
use App\Http\Users;
use Carbon\Carbon;
use DB;

class InvoiceService{
	/**
	 * Object of Invoice class.
	 *
	 * @var Invoice
	 */
	private $invoice;

	/**
	 * Create a new instance of InvoiceService class.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->invoice = new Invoice();
	}

	/**
	 * Create new invoice .
	 *
	 * @param array $invoiceData
	 * @return Invoice
	 */
	public function createInvoice($invoiceData)
	{
		return $this->invoice->create($invoiceData);
	}

	/**
	 * Get invoice By primary key.
	 *
	 * @param string $id
	 * @return Invoice
	 */
	public function getInvoiceByPK($id)
	{
		return $this->invoice->find($id);
	}

	/**
	 * Get invoice by hash.
	 *
	 * @param string $hash
	 * @return mix
	 */
	public function getInvoiceByHash($hash)
	{
		return $this->invoice->where('hash', $hash)->first();
	}

	/**
	 * Get all invoices.
	 *
	 * @param array $formData
	 * @return Collection
	 */
	public function getAllInvoices($formData)
	{
		$invoices = $this->invoice;
		$count = $invoices->count();
        $invoices = $invoices->skip($formData['skip'])->take($formData['take'])
        			->orderBy($formData["order_by"], $formData['order'])->get();
       	return ['count' => $count, 'invoices' => $invoices];
	}

	/**
	 * Get the invoice number code
	 *
	 * @param string $type
	 * @return string
	 */
	public function getNextInvoiceNumber($type)
	{
		if($type == 'TRANSACTION' || $type == 'GIFT'){
			$types = ['TRANSACTION', 'GIFT'];
			$orderField = 'transaction_id';
		} else{
			$types = ['REFUND'];
			$orderField = 'refund_id';
		}
		$yearMonth = date('Ym');
		$lastInvoice = \App\Models\Invoice::whereIn('type', $types)->where('is_paid', 1)
			->orderBy('yearmonth_id', 'DESC')
			->orderBy($orderField, 'DESC')
			->first();
		if(!$lastInvoice){ return 1; }
		if($type == 'TRANSACTION' || $type == 'GIFT'){
			if($yearMonth != $lastInvoice->yearmonth_id || !$lastInvoice->transaction_id){
				return 1;
			}
			return $lastInvoice->transaction_id + 1;
		}
		if($yearMonth != $lastInvoice->yearmonth_id || !$lastInvoice->refund_id){
			return 1;
		}
		return $lastInvoice->refund_id + 1;
	}

	/**
	 * Create a new GIFT invoice for welcome credit
	 * and for importation
	 *
	 * @param User $user
	 * @param float $amount
	 * @param string $countryCode
	 * @return bool
	 */
	public function createGiftInvoice($user, $amount, $countryCode, $minimumMargin = 0)
	{
		$countryCode = $countryCode ? $countryCode: 'N/A';
		$invoiceData = [
			'user_id' => $user->_id,
			'order_number' => date('Y') . '-#' . rand(1000000, 9999999),
			'purchased_amount' => 0,
			'vat_amount' => 0,
			'vat_percentage' => 0,
			'total_amount' => 0,
			'discount_percentage' => 0,
			'discount_amount' => $amount,
			'remaining_gift_amount' => $amount,
			'type' => 'GIFT',
			'customer_country_code' => $countryCode,
			'customer_name' => $user->email,
			'customer_address' => NULL,
			'customer_postal_code' => NULL,
			'customer_city' => NULL,
			'minimum_margin_criteria_for_gift' => $minimumMargin,
			'current_balance_after_billing' => $user->balance
		];
		$transactionId = $this->getNextInvoiceNumber('GIFT');
		$invoice = $this->invoice->create($invoiceData);
		$invoice->invoice_number = strtoupper( $invoice->customer_country_code ) . '-I-' . date('Ym') . '-' . str_pad($transactionId, 5, '0', STR_PAD_LEFT);
		$invoice->is_paid = 1;
		$invoice->transaction_id = $transactionId;
		$invoice->yearmonth_id = date('Ym');
		$invoice->invoice_date = date('Y-m-d H:i:s');
		$invoice->save();
		return $invoice;
	}

	/**
	 * Create an order . Should be flexible and work for bank, paypal and stripe
	 *
	 * @param User $user
	 * 
	 */
	public function createOrder($user, $amount, $vatId, $couponCode)
	{
        $vatRepo = new \App\Services\VatService();
        $vatJson = json_decode( file_get_contents( public_path() . '/rates.json' ), 1);
		//If user does not have name, use email instead
        $firstName = $user->first_name? $user->first_name: $user->email;
        $countryCode = strtoupper( $user->country_code );

        //By default we will consider that should charge VAT . if the vatId is valid, we will make it false
        $vatAmount = 0;
        $vatPercentage = 0;
        if( isset($vatJson['rates'][$countryCode]) && (!$vatId || !$vatRepo->checkIfVatIdValid($vatId)) ){
    		$vat = $vatRepo->calculateVat($amount, $countryCode);
        	$vatAmount = $vat->getTax();
        	$vatPercentage = $vatJson['rates'][$countryCode]['standard_rate'];

        }
        $discountPercentage = NULL;
        if($couponCode){
            $now = Carbon::now();
            $coupon = $user->coupons()->where('is_used', 0)
                ->where('promotion_end','>',$now)
                    ->whereRaw('lower(`code`) = ?',[$couponCode])->first();
            if(!$coupon) {
                $coupon = Coupon::whereRaw('lower(`code`) = ?',[$couponCode])->where('type','UNLIMITED')->where('promotion_end','>',$now)->first();
            }
            if($coupon){
                if($coupon->type == 'UNLIMITED' && $amount >= $coupon->minimum_amount) {
                    $discountPercentage = $coupon->discount_percentage;
                }

                if($coupon->type != 'UNLIMITED') {
                    $discountPercentage = $coupon->discount_percentage;
                    $coupon->is_used = 1;
                    $coupon->save();
                }
            }
        }
        $total = (float) $amount + $vatAmount;
        $formattedToatal = number_format($total, 2);
        $invoiceData = [
            'user_id' => $user->_id,
            'order_number' => date('Y') . '-#' . rand(1000000, 9999999),
            'purchased_amount' => $amount,
            'vat_amount' => $vatAmount,
            'vat_percentage' => $vatPercentage,
            'vat_id' => $vatId,
            'total_amount' => $total,
            'discount_percentage' => $discountPercentage,
            'discount_coupon_code' => $couponCode,
            'customer_country_code' => $countryCode,
            'customer_name' => $firstName,
            'customer_address' => $user->address,
            'customer_postal_code' => $user->postal_code,
            'customer_city' => $user->city,
            //'current_balance_after_billing' => $user->balance
        ];

        $invoice = \App\Models\Invoice::create($invoiceData);
        return $invoice;
	}

	/**
	 * Make invoice from payment and add users balance
	 *
	 * @param User $user
	 * @param Invoice $invoice
	 * @param string $vendorJson
	 */
	public function finalizeOrder($user, $invoice, $vendorJson, $method)
	{
		$shouldTryAgain = 0;
		$wasSucceed = false;
		while($shouldTryAgain < 3){
			DB::beginTransaction();
			try {
				$bonusAmount = 0;
		        if($invoice->discount_percentage){
		            $bonusAmount = $invoice->purchased_amount * $invoice->discount_percentage / 100;
		        }
		        $discountAmount = $bonusAmount;
		        $balanaceToAdd = $invoice->purchased_amount + $bonusAmount;

		        $transactionId = $this->getNextInvoiceNumber('TRANSACTION');
		        $invoice->is_paid = true;
		        $invoice->method = $method;
		        $invoice->invoice_date = date('Y-m-d H:i:s');
		        $invoice->invoice_number = $invoice->customer_country_code . '-I-' . date('Ym') . '-' . str_pad($transactionId, 5, '0', STR_PAD_LEFT);
		        $invoice->yearmonth_id = date('Ym');
		        $invoice->transaction_id = $transactionId;
		        $invoice->discount_amount = $discountAmount;
		        $invoice->remaining_purchased_amount = $invoice->purchased_amount;
		        $invoice->remaining_gift_amount = $discountAmount;
		        $invoice->vendor_response_json = json_encode($vendorJson);
		        $invoice->current_balance_after_billing = $user->balance + $balanaceToAdd;
		        $invoice->save();


		        User::where('_id', $user->_id)->update([
	    		   'balance' => DB::raw('balance + ' . $balanaceToAdd),
	    		   'is_low_balance_notification_send' => false,
	    		   'purchased_amount' => DB::raw('purchased_amount + ' . $invoice->purchased_amount),
	    		   'gift_amount' => DB::raw('gift_amount + ' . $bonusAmount),
	    		]);
	    		$wasSucceed = true;
	    		$shouldTryAgain = 5;
		        DB::commit();
			} catch (\Exception $e) {
				\Log::info($e);
				DB::rollback();
	    		$shouldTryAgain++;  
			}
		}
		if(!$wasSucceed) {
			\App\Models\FailedPayment::create([
                'user_id' => $user->_id,
                'type' => 'STANDARD',
                'data' => json_encode($vendorJson)
            ]);
            return false;
		}

		$logData = [
            'user_id' => $user->_id,
            'device' => 'WEBSITE',
            'action' => 'BILLINGS',
            'description' => 'User paid with paypal - ' . $invoice->purchased_amount
        ];
        $user = User::find($user->_id);
        $activityLogRepo = new ActivityLogService();
        $sendEmailRepo = new SendEmailService();
        $activityLogRepo->createActivityLog($logData);
        $sendEmailRepo->sendSuccessPaymentEmail($user, $invoice);
		return $invoice;

	}
}