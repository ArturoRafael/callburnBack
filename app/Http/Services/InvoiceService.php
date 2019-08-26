<?php 

namespace App\Http\Services;

use App\Http\Models\Coupon;
use App\Http\Models\Invoice;
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

	
	public function setInvoiceWorkflow($id_invoice, $id_workflow){

		$invoice = Invoice::find($id_invoice);
        if(!$invoice){
            return false;
        }
        $invoice->id_workflow = $id_workflow;
        $invoice->save();
        return true;
	}

	/**
	 * Create an order . Should be flexible and work for bank, paypal and stripe
	 *
	 * @param User $user
	 * 
	 */
	public function createOrder($user, $stripeCaptured)
	{
        $firstName = $user->first_name? $user->first_name: $user->email;
        $countryCode = strtoupper( $user->country_code );

        if($stripeCaptured['captured']){
        	$type = 'TRANSACTION';
        	$method = 'stripe';
        }else{        	
        	$type = 'CAPTURED';
        	$method = 'stripe_automatic_billing';
        }
        $invoiceData = [
            'user_email' => $user->email,
            'order_number' => $stripeCaptured['id'],
            'total_amount' => ($stripeCaptured['amount'] / 100),
            'customer_country_code' => $countryCode,
            'customer_name' => $firstName,
            'customer_address' => $user->address,
            'customer_postal_code' => $user->postal_code,
            'customer_city' => $user->city,
            'type' => $type,
            'invoice_date' => date('Y-m-d H:i:s'),
            'method' => $method,
            'receipt_url' => $stripeCaptured['receipt_url'],
            'description' => $stripeCaptured['description'] 
        ];

        $invoice = Invoice::create($invoiceData);
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