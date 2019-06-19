<?php 

namespace App\Http\Services;

class SendEmailService{

	/**
	 * Create a new instance of ActivityLogService class.
	 *
	 * @return void
	 */
	public function __construct()
	{
	}

	private function checkNewsletterEmail (&$user)
	{
		if (is_null($user->email)) {
			$user->email = $user->email;
			$user->save();
		}
	}

	/**
	 * Send email for notifying about caller id verification
	 *
	 * @param User $user
	 * @param string $phonenumber
	 * @return void
	 */
	public function sendCallerIdNotificationEmail($user, $phonenumber)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.new_caller_id', ['callerId' => $phonenumber]));
		\Mail::send('emails.new.new_caller_id', ['callerId' =>  $phonenumber], function ($m) use ($user, $plainText) {
            $m->from('support@callburn.com', 'Callburn');
            $m->to($user->newsletter_email, $user->name)->subject(trans('main.emails.subject_caller_id_verified'));
            $m->addPart($plainText, 'text/plain');
        });
	}

	/**
	 * Send email for confirming emaill address for registration
	 *
	 * @param User $user
	 * @return void
	 */
	public function sendConfirmRegistrationEmail($user, $confirmation_code, $type_business, $rol)
	{	
		
		$plainText = $this->stripHtmlTags((string)view('emails.new.confirm_registration', ['users' => $user, 'confirmation' => $confirmation_code,'business' => $type_business, 'rol' => $rol]));
		
		\Mail::send('emails.new.confirm_registration', ['users' => $user, 'confirmation' => $confirmation_code, 'business' => $type_business, 'rol' => $rol], function ($m) use($user, $plainText) {
            $m->from('support@callburn.com', 'Callburn');
            $m->to($user->email, $user->first_name)->subject(trans('main.emails.subject_activate_your_account'));
        	$m->addPart($plainText, 'text/plain');
		});		
	}

	/**
	 * Send email for notifying about pending order
	 *
	 * @param User $user
	 * @param Invoice $invoice
	 * @return void
	 */
	public function sendPendingOrderEmail($user, $invoice)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.pending_order', ['invoice' => $invoice]));
		\Mail::send('emails.new.pending_order', ['invoice' => $invoice], function ($m) use ($user, $plainText) {
            $m->from('support@callburn.com', 'Callburn');
            $m->to($user->email, $user->name)->subject(trans('main.emails.subject_pending_order'));
            $m->addPart($plainText, 'text/plain');
        });
	}

	/**
	 * Send email for notifying about success payment
	 *
	 * @param User $user
	 * @param Invoice $invoice
	 * @return void
	 */
	public function sendSuccessPaymentEmail($user, $invoice)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$path  = public_path() . '/' . $invoice->invoice_number . '.pdf';
		$invoiceData = \PDF::setPaper('a4')->loadView('pdf.invoice', ['invoice' => $invoice,'user' => $user])
			->save($path);

		$plainText = $this->stripHtmlTags((string)view('emails.new.thanks_for_recharge', ['user' => $user]));
		\Mail::send('emails.new.thanks_for_recharge', ['user' => $user], function ($m) use ($user, $invoice, $path, $plainText) {
		    $m->from('support@callburn.com', 'Callburn');
		    $m->to($user->email, $user->name)->subject(trans('main.emails.subject_balance_recharged'));
		    $m->addPart($plainText, 'text/plain');
		    $m->attach($path);
		});
		unlink($path);
	}

	/**
	 * Send email for confirming new email address
	 *
	 * @param User $user
	 * @param string $email
	 * @param string $token
	 * @return void
	 */
	public function sendConfirmNewEmailAddressEmail($user, $email, $token)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.new-email-addres', ['token' =>  $token]));
		\Mail::send('emails.new.new-email-addres', ['token' =>  $token], function ($m) use ($user, $email, $plainText) {
		    $m->from('support@callburn.com', 'Callburn');
		    $m->to($email, $user->name)->subject(trans('main.emails.subject_activate_new_email'));
		    $m->addPart($plainText, 'text/plain');
		});
	}

	/**
	 * Send email for notifying that email was changed
	 *
	 * @param User $user
	 * @return void
	 */
	public function sendChangeEmailNotificationEmail($user)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.change-email-address'));
		
		\Mail::send('emails.new.change-email-address', [], function ($m) use ($user, $plainText) {
		    $m->from('support@callburn.com', 'Callburn');
		    $m->to($user->email, $user->name)->subject(trans('main.emails.Email_activated'));
		    $m->addPart($plainText, 'text/plain');
		});
	}

	/**
	 * Send email for resetting password
	 *
	 * @param User $user
	 * @return void
	 */
	public function sendPasswordResetNotificationEmail($user, $token)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.reset_password', ['token' => $token, 'name' => $user->first_name]));
		\Mail::send('emails.new.reset_password', ['token' => $token, 'name' => $user->first_name], function ($m) use ($user, $plainText) {
		    $m->from('support@callburn.com', 'Callburn');
		    $m->to($user->email, $user->name)->subject(trans('main.emails.subject_restore_your_password'));
		    $m->addPart($plainText, 'text/plain');
		});
	}

	/**
	 * Send email for notifying about the gift
	 *
	 * @param User $user
	 * @param Integer $giftAmount
	 * @return void
	 */
	public function giftAdded($user, $giftAmount)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.gift_added', ['user' =>  $user, 'bonus' => $giftAmount]));
		\Mail::send('emails.new.gift_added', ['user' =>  $user, 'bonus' => $giftAmount], function ($m) use ($user, $plainText) {
            $m->from('support@callburn.com', 'Callburn');
            $m->to($user->email, $user->name)->subject(trans('main.emails.subject_you_have_a_gift_from_callburn'));
            $m->addPart($plainText, 'text/plain');
        });
	}

	/**
	 * Send email for notifying that someone else registered  1 of your caller ids
	 *
	 * @param User $user
	 * @param string $callerId
	 * @return void
	 */
	public function callerIdAddedToOtherAccount($user, $callerId)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.caller_id_added_to_other_account', ['user' =>  $user, 'callerId' => $callerId]));
		\Mail::send('emails.new.caller_id_added_to_other_account', ['user' =>  $user, 'callerId' => $callerId], function ($m) use ($user, $plainText) {
            $m->from('support@callburn.com', 'Callburn');
            $m->to($user->email, $user->name)->subject(trans('main.emails.subject_caller_id_added_to_other_account'));
            $m->addPart($plainText, 'text/plain');
        });
	}

	public function sendLowBalanceToCallEmail($user)
	{
		$this->checkNewsletterEmail($user);
		$this->setUsersLanguage($user);
		$plainText = \App\Helper::stripHtmlTags((string)view('emails.new.call-hanged-up-of-low-credit',  ['user' =>  $user]));

		\Mail::send('emails.new.call-hanged-up-of-low-credit', ['user' =>  $user], function ($m) use ($user, $plainText) {
            $m->from('support@callburn.com', 'Callburn');
            $m->to($user->email, $user->name)->subject(trans('main.emails.subject_warrning_message'));
            $m->addPart($plainText, 'text/plain');
        });
		return true;
	}

	/**
	 * Send email of ClickToCall Snippet's Integration Codes
	 *
	 * @param User $user
	 *	@param string $email
	 *	@return void
	 */
	public function sendCTCIntegrationCodesEmail ($user, $email, $integrationCodes, $snippet)
	{
		$this->setUsersLanguage($user);
		$plainText = $this->stripHtmlTags((string)view('emails.new.sendIntegration', ['user' => $user, 'integrationCodes' => $integrationCodes, 'snippet' => $snippet]));

		\Mail::send('emails.new.sendIntegration', ['user' => $user, 'integrationCodes' => $integrationCodes, 'snippet' => $snippet], function ($m) use ($email, $plainText) {
            $m->from('support@callburn.com', 'Callburn');
            $m->to($email, $email)->subject(trans('main.emails.subject_integration_codes'));
            $m->addPart($plainText, 'text/plain');
        });
	}

	/**
	 * Set app locale for the user so system can translate
	 * We will use caller id language and if not exist
	 * The country of the user .
	 *
	 * @param User $user
	 * @return void
	 */
	private function setUsersLanguage($user)
	{
		$language = $user->language;
		if(!$language){return;}
		\App::setLocale($language->code);
	}

	private function stripHtmlTags($str){
	    $str = preg_replace('/(<|>)\1{2}/is', '', $str);
	    $str = preg_replace(
	        array(// Remove invisible content
	            '@<head[^>]*?>.*?</head>@siu',
	            '@<style[^>]*?>.*?</style>@siu',
	            '@<script[^>]*?.*?</script>@siu',
	            '@<noscript[^>]*?.*?</noscript>@siu',
	            ),
	        "", //replace above with nothing
	        $str );
	    //$str = replaceWhitespace($str);
	    $str = strip_tags($str);
	    return $str;
	}
}
