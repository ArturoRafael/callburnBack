<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Session;
use Validator;
use JWTAuth;
use App\Http\Models\Users;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends BaseController
{

    
    /**
     *
     * @OA\Post(
     *   path="/api/auth/postRegistration",
     *   summary="Send registration eail to the user and create basic account",
     *   operationId="postRegistration",
     *   tags={"Auth"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/email"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/password"
     *    ), 
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/firstname"
     *    ), 
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/lastname"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/phone"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/businessname"
     *    ),        
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/AuthToken"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=422,
     *      ref="../Swagger/definitions.yaml#/components/responses/UnprocessableEntity"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   )
     *  )
     */
    public function postRegistration(Request $request)
    {
        $emailAddress = $request->get('email');
        $password = $request->get('password');
        $c_password = $request->get('repeat_password');
        $firstname = $request->get('firstname');
        $lastname = $request->get('lastname');
        $phone = $request->get('phone');
        $businessname = $request->get('businessname');
        $type_business = 1;
        $idrol = 1;

        $validator = Validator::make($request->all(), [
		        'email' => 'email|required',
                'password' => 'string|min:3',
                'c_password' => 'string|min:3|same:password',
                'firstname' => 'required',
                'lastname' => 'required',
                'phone' => 'nullable',
                'businessname' => 'required'
		    ]
		);

        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 404);            
        }

        else {            
		
            $userData = [
				'email' => $emailAddress,
                'password' => base64_encode($password),
                'firstname' => $firstname,
				'lastname' => $lastname,
                'phone' => $phone,
                'businessname' => $businessname,
                'id_type_business' => $type_business,
                'idrol' => $idrol
			];

            $newUser = Users::where('email', $emailAddress)->first();


            if (!$newUser) {
				
                $newUser = Users::create( $userData); 
			
			} else {
                return response()->json(['success' => false, 'message' => "Usuario ya existente"], 404);			
			}

			/*Envío de confirmacion de email*/
            //$this->sendEmailRepo->sendConfirmRegistrationEmail($newUser);	
			 
		}
		
		return response()->json([ 'success' => true, 'data' => $newUser], 200);
    }



    // Commented By Arman
    // public function getCrispToken(Request $request) 
    // {
    // 	$response = [
    // 		'crispToken' => ''
    // 	];
    // 	return response()->json(['resource' => $response]);
    // }

    /**
     * Send request to API for activating account
     * POST /auth/activate-account
     *
     * @param Request $request
     * @return JSON
     */

  //   public function postActivateAccount(Request $request)
  //   {
  //       $ip = $request->ip();
  //       $emailConfirmationToken = $request->get('email_confirmation_token');
  //       $password = $request->get('password');
  //       $passwordConfirmation = $request->get('password_confirmation');
  //       $phonenumber = $request->get('phonenumber');
  //       $userName = $request->get('myName');
  //       $companyName = $request->get('companyName');

  //       $emailAddress = $request->get('email');
  //       $code = $request->get('voice_code');
  //       $sendNewsletter = $request->get('send_newsletter');
  //       $user = \App\User::where('email', $emailAddress)->first();
		// if(!$user){
		// 	$response = $this->createBasicResponse(-1, 'invalid__email');
		// 	return response()->json(['resource' => $response]);
		// }
  //       $validator = Validator::make(
		//     [
		//         'password' => $password,
		//         'password_confirmation' => $passwordConfirmation
		//     ],
		//     [
		//         'password' => 'confirmed|required|min:4|max:20'
		//     ]
		// );
		// if ($validator->fails())
		// {
		// 	$failedRules = $validator->failed();
		// 	$errorNumber = -100;
		// 	$errorMessage = trans('main.crud.something_went_wrong');
		// 	if(isset($failedRules['password']['Confirmed'])){
		// 		$errorNumber = -2;
		// 		$errorMessage = trans('main.crud.passwords_do_not_match');
		// 	} elseif(isset($failedRules['password']['Min'])){
		// 		$errorNumber = -3;
		// 		$errorMessage = trans('main.crud.password_is_too_short');
		// 	} elseif(isset($failedRules['password']['Max'])){
		// 		$errorNumber = -4;
		// 		$errorMessage = trans('main.crud.password_is_too_long');
		// 	} elseif(isset($failedRules['password']['AlphaDash'])){
		// 		$errorNumber = -5;
		// 		$errorMessage = trans('main.crud.password_criteria_not_fullfilled');
		// 	} elseif(isset($failedRules['password']['Required'])){
		// 		$errorNumber = -6;
		// 		$errorMessage = trans('main.crud.password_is_required');
		// 	}
		// 	$response = $this->createBasicResponse($errorNumber, $errorMessage);

		// 	return response()->json(['resource' => $response]);
		// }

		// \DB::beginTransaction();
		// try{
		// 	$tariff = NULL;
		// 	if($code){
		// 		$ifPhonenumberValid = $this->checkPhonenumberCodeAndAddToAccount($phonenumber, $code, $user);
		// 		if(!$ifPhonenumberValid){
		// 			$response = $this->createBasicResponse(7, 'code_is_not_valid_2');
		// 			return response()->json(['resource' => $response]);
		// 		}
		// 		$tariff = \App\Models\Tariff::with('country')->find($ifPhonenumberValid->tariff_id);
		// 	}
		// 	$apiToken = str_random(20);

		// 	$countryCodeFromCallerId = \Cache::get('countryCodeOf_' . $phonenumber);
  //           if (config('app.SHOULD_USE_GEOIP')) {
  //               $countryCode = strtolower(trim(@geoip_country_code_by_name($ip)));
  //           } elseif ($countryCodeFromCallerId) {
  //           	$countryCode = $countryCodeFromCallerId;
  //           } else {
  //               $countryCode = null;
  //           }

  //           \Cache::forget('countryCodeOf_' . $phonenumber);
		// 	$user->password = bcrypt($password);
		// 	$user->country_code = $countryCode;
		// 	$user->personal_name = $userName;
		// 	$user->company_name = $companyName;
		// 	$user->is_active = true;
		// 	$user->send_newsletter = $sendNewsletter;

		// 	$amount = 0;
		// 	if($tariff && $tariff->country){
		// 		$amount = $tariff->country->web_welcome_credit;
		// 	}
		// 	$bonusWithOtherUser = User::where('caller_id_used_for_wlc_credit', $phonenumber)->first();
		// 	if ($amount && !$bonusWithOtherUser) {
		// 		$user->balance = $amount;
		// 		$user->gift_amount = $amount;
		// 		$user->first_time_bonus = $amount;
		// 		$user->caller_id_used_for_wlc_credit = $phonenumber;
		// 		$minimumMargin = 0;

		// 		$countryCode = $tariff ? $tariff->country->code : 'N/A';
		// 		$invoiceRepo = new \App\Services\InvoiceService();
		// 		$invoice = $invoiceRepo->createGiftInvoice($user, $amount, $countryCode, $minimumMargin);

		// 		$this->sendEmailRepo->giftAdded($user, $amount);

		// 		$logData = [
		// 			'user_id' => $user->_id,
		// 			'device' => 'CALLBURN',
		// 			'action' => 'BILLINGS',
		// 			'description' => 'Welcome gift added to user as first caller id added'
		// 		];
		// 		$this->activityLogRepo->createActivityLog($logData);
		// 	}

		// 	$timezone = InfoService::getTimezoneName($request);

		// 	if ($timezone) {
  //               $user->timezone = $timezone;
  //           }

  //           $countryCode = InfoService::getCountryCode($request);
  //           if($countryCode) {
  //               $user->country_code = $countryCode;
  //           }

		// 	$user->save();

	 //        $credentials = $request->only('email', 'password');
	 //        $jwtToken = JWTAuth::attempt($credentials);

		// 	\Auth::login($user);

		// 	$token = new \App\Models\ApiToken();
	 //        $token->user_id = $user->_id;
	 //        $token->api_token = str_random(10);
	 //        $token->ip_address = $ip;
	 //        $token->agent = '';
	 //        $token->device = 'WEBSITE';
	 //        $token->api_token_validity = Carbon::now()->addMinutes(60)->toDateTimeString();
	 //        $token->session_id = \Session::getId();
	 //        $token->save();
	 //        \DB::commit();

  //           event(new \App\Events\UserDataUpdated( [
  //               'user_id' => $user->_id] ));

	 //    } catch(\Exception $e) {
	 //    	\Log::info($e);
	 //    	\DB::rollback();
	 //    	$response = [
		// 		'error' => [
		// 			'no' => -100,
		// 			'text' => 'something_went_wrong'
		// 		],
		// 		'message' => $e->getMessage()
		// 	];
		// 	return response()->json(['resource' => $response]);
	 //    }
		// SlackNotificationService::notify('User activated account with email - ' . $user->email);

		// $response = [
		// 	'error' => [
		// 		'no' => 0,
		// 		'text' => 'account_activated'
		// 	],
		// 	'user_data' => $user,
  //           'jwtToken' => $jwtToken,
		// ];
		// return response()->json(['resource' => $response]);
  //   }

    
	
     /**
     *
     * @OA\Post(
     *   path="/api/auth/postLogin",
     *   summary="Try to login the user",
     *   operationId="postLogin",
     *   tags={"Auth"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/email"
     *    ),
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/password"
     *    ),     
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/AuthToken"
     *    ),
     *   @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     *   @OA\Response(
     *      response=422,
     *      ref="../Swagger/definitions.yaml#/components/responses/UnprocessableEntity"
     *   ),
     *   @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   )
     *  )
     */
    public function postLogin(Request $request)
    {
    	
        $email = $request->get('email');
        $password = $request->get('password');

        $validator = Validator::make($request->all(), [
                'email' => 'email|required',
                'password' => 'string|min:3|required',                
            ]
        );

        if ($validator->fails()) { 
            return response()->json(['error'=>$validator->errors()], 404);            
        }

        $tempUser = Users::where('email', $email)
                        ->where('password', base64_encode($password))
                        ->first();
        
        if ($tempUser) {
        	 $success['usuario'] =  $tempUser;
             $success['status'] =  "Login Exitoso";
            return response()->json(['success' => $success], 200); 
        } else{
            return response()->json(['error'=>'Unauthorised'], 404);
        }   
        
    }



    
    /**
     *
     * @OA\Get(
     *   path="/api/auth/getLogout",
     *   summary="Logout the user",
     *   operationId="getLogout",   
     *   tags={"Auth"},
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
    // public function getLogout()
    // {
    //     $token = JWTAuth::getToken();
    //     JWTAuth::invalidate($token);

    // 	$sessionId = \Session::getId();
	   //  \App\Http\Models\ApiToken::where('session_id', $sessionId)->delete();
    // 	Auth::logout();
    //     $response = $this->createBasicResponse(0, 'logged_out');
    // 	return response()->json(['resource' => $response]);
    // }

    /**
     * Send request to API for sending password reset link to the user with given email.
     * POST /auth/send-reset-link
     *
     * @param Request $request
     * @return JSON
     */
  //   public function postSendResetLink(Request $request, UserService $userRepo)
  //   {
  //   	$emailAddress = $request->get('email');

  //       $user = \App\User::whereEmail($emailAddress)->first();

  //       // if($user and $user->password) {

  //       //     $response = $this->createBasicResponse(-5, 'access_denied');
  //       //     return response()->json(['resource' => $response]);

  //       // }

  //   	$validator = Validator::make(
		//     [
		//         'email' => $emailAddress,
		//     ],
		//     [
		//         'email' => 'required|exists:users',
		//     ]
		// );
		// if ($validator->fails())
		// {
		// 	$failedRules = $validator->failed();
		// 	$errorNumber = -100;
		// 	$errorMessage = 'Ssomething__went__wrong';
		// 	if(isset($failedRules['email']['Required'])){
		// 		$errorNumber = -1;
		// 		$errorMessage = 'email_can_not_be_blank';
		// 	} elseif(isset($failedRules['email']['Exists'])){
		// 		$errorNumber = -2;
		// 		$errorMessage = 'user_with_this_email_does__not_exists';
		// 	}
		// } else{
		// 	$token = str_random(20);
		// 	$user = $userRepo->getUserByEmail($emailAddress);
		// 	$updateData = ['password_reset' => $token];
		// 	$userRepo->updateUser($user->_id, $updateData);

  //           /*Envio email*/
		// 	//$this->sendEmailRepo->sendPasswordResetNotificationEmail($user, $token);
			
		// 	$errorNumber = 0;
		// 	$errorMessage = 'password_reset_code_sent';

		// 	$logData = [
		// 		'user_id' => $user->_id,
		// 		'device' => 'WEBSITE',
		// 		'action' => 'REGISTRATION-LOGIN',
		// 		'description' => 'User ordered password reset link with token - ' . $token
		// 	];
		// 	$this->activityLogRepo->createActivityLog($logData);
		// }
		// $response = $this->createBasicResponse($errorNumber, $errorMessage);
		// return response()->json(['resource' => $response]);
  //   }

    /**
     * Send request to server for resetting password.
     * POST /auth/make-reset-password
     *
     * @param Request $request
     * @return JSON
     */
 //    public function postMakeResetPassword(Request $request, UserService $userRepo)
 //    {
 //    	$errorNumber = -100;
 //        $language = \App\Models\Language::where('code',$request->get('language','en'))->first();
	// 	$errorMessage = 'something__went__wrong';
 //    	$token = $request->get('token');
 //    	$password = $request->get('password');
 //    	$passwordConfirmation = $request->get('password_confirmation');
 //    	$validator = Validator::make(
	// 	    [
	// 	        'password' => $password,
	// 	        'password_confirmation' => $passwordConfirmation,
	// 	        'token' => $token,
	// 	    ],
	// 	    [
	// 	        'password' => 'required|confirmed|min:4|max:20',
	// 	        'token' => 'required'
	// 	    ]
	// 	);
	// 	if ($validator->fails())
	// 	{
	// 		$failedRules = $validator->failed();
	// 		if(isset($failedRules['password']['Required'])){
	// 			$errorNumber = -1;
	// 			$errorMessage = trans('main.crud.password_can_not_be_blank');
	// 		} elseif(isset($failedRules['password']['Confirmed'])){
	// 			$errorNumber = -2;
	// 			$errorMessage = trans('main.crud.passwords_do_not_match');
	// 		} elseif(isset($failedRules['password']['Min'])){
	// 			$errorNumber = -3;
	// 			$errorMessage = trans('main.crud.password_is_too_short');
	// 		} elseif(isset($failedRules['password']['Max'])){
	// 			$errorNumber = -4;
	// 			$errorMessage = trans('main.crud.password_is_too_long');
	// 		} elseif(isset($failedRules['password']['AlphaDash'])){
	// 			$errorNumber = -5;
	// 			$errorMessage = trans('main.crud.password_criteria_not_fullfilled');
	// 		} elseif(isset($failedRules['token']['Required'])){
	// 			$errorNumber = -6;
	// 			$errorMessage = 'token_is_missing';
 //                abort('404');
	// 		}
	// 	} else{
	// 		$user = $userRepo->getUserByPasswordToken($token);
	// 		if($user){


 //                $user->language_id = $language->_id;
 //                $user->save();
	// 			$updateData = ['password' => bcrypt($password), 'password_reset' => null];
	// 			$userRepo->updateUser($user->_id, $updateData);
	// 			$errorNumber = 0;
	// 			$errorMessage = 'password__changed_1';

	// 			$user->apiTokens()->delete();
	// 			$token = str_random(20);
	// 			\App\Models\ApiToken::create([
	// 				'user_id' => $user->_id,
	// 				'api_token' => $token
	// 				]);

	// 			$logData = [
	// 				'user_id' => $user->_id,
	// 				'device' => 'WEBSITE',
	// 				'action' => 'REGISTRATION-LOGIN',
	// 				'description' => 'User updated his password'
	// 			];
	// 			$this->activityLogRepo->createActivityLog($logData);


 //                $credentials = [
 //                    'email'    => $user->email,
 //                    'password' => $password
 //                ];
 //                $jwtToken = JWTAuth::attempt($credentials);


 //                //\Auth::login($user);

	// 			$response = $this->createBasicResponse($errorNumber, $errorMessage);
	// 			$response['api_token'] = $token;
	// 			$response['jwtToken'] = $jwtToken;

	// 			return response()->json(['resource' => $response]);
	// 		} else{
	// 			$errorNumber = -7;
	// 			$errorMessage = 'invalid_token';
	// 		}
	// 	}
	// 	$response = $this->createBasicResponse($errorNumber, $errorMessage);
	// 	return response()->json(['resource' => $response]);
 //    }

    

	// public function RefreshToken() {

 //        $token = \JWTAuth::getToken();
 //        $token = \JWTAuth::refresh($token);

 //        if($token) {
 //            $response = [
 //                'error' => [
 //                    'no' => 0,
 //                    'text' => ''
 //                ],
 //                'token' => $token

 //            ];
 //        } else {
 //            $response = [
 //                'error' => [
 //                    'no' => -1,
 //                    'text' => ''
 //                ],


 //            ];
 //        }

 //        return response()->json(['resource' => $response]);

 //    }

}