<?php

namespace App\Http\Controllers;
use App\Http\Services\SendEmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Models\Users;
use App\Http\Models\Rol;
use App\Http\Models\TypeBusiness;
use Session;
use Redirect;
use Validator;

class AuthController extends BaseController
{
    

    /**
     * Create a new instance of AuthController class
     *
     * @return void
     */
    public function __construct()
    {
        $this->sendEmailRepo = new SendEmailService();
    }



    /**
     *
     * @OA\Get(
     *   path="/api/registration/verify",
     *   summary="Validate acount the user",
     *   operationId="verify",   
     *   tags={"Account"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/token_query"
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
    public function verify($code)
    {
        $user = Users::where('confirmation_code', $code)->first();
        if (! $user){
            return $this->sendError('Error de código de activación');
        }
        $user->confirmed = true;
        $user->confirmation_code = null;
        $user->save();

        return Redirect::to(env('APP_URL_REDIRECT'));
    }

    /**
     *
     * @OA\Post(
     *   path="/api/registration",
     *   summary="Send registration eail to the user and create basic account",
     *   operationId="registration",
     *   tags={"Account"},
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/email"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/password"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/c_password"
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
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/type_business"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/email_business_user"
     *    ),
     * @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/idrol"
     *    ),        
     *   @OA\Response(
     *      response=200,
     *      ref="../Swagger/definitions.yaml#/components/responses/AuthToken"
     *    ),
     * @OA\Response(
     *      response=401,
     *      ref="../Swagger/definitions.yaml#/components/responses/Unauthorized"
     *    ),
     * @OA\Response(
     *      response=422,
     *      ref="../Swagger/definitions.yaml#/components/responses/UnprocessableEntity"
     *   ),
     * @OA\Response(
     *      response=500,
     *      ref="../Swagger/definitions.yaml#/components/responses/InternalServerError"
     *   )
     *  )
     */
    public function registration(Request $request)
    {

        $request = $request->all();

        $validator = Validator::make($request, [
            'email' => 'email|required',
            'password' => 'string|min:3|required',
            'c_password' => 'string|min:3|same:password|required',
            'firstname' => 'required|max:200',
            'lastname' => 'required|max:200',
            'phone' => 'required|max:200',
            'businessname' => 'required'                      
        ]);


        if($validator->fails()){
            return $this->sendError('Error de validación', $validator->errors());       
        }

        $emailAddress = $request['email'];
        $password = $request['password']; 
        $c_password = $request['c_password']; 
        $firstname = $request['firstname'];
        $lastname = $request['lastname'];
        $phone = $request['phone'];
        $businessname = $request['businessname'];                 
        $idrol = 1;
        $confirmation_code = str_random(60);

        $userData = [
			'email' => $emailAddress,
            'password' => bcrypt($password),
            'firstname' => $firstname,
			'lastname' => $lastname,
            'phone' => $phone,
            'businessname' => $businessname,            
            'idrol' => $idrol,
            'confirmation_code' => $confirmation_code
		];

        $newUser = Users::find($emailAddress);

        if (is_null($newUser)) {
			
            $newUser = Users::create($userData);                 
		
		} else {
            return $this->sendError('Usuario ya se encuentra registrado');			
		}

		/*Envío de confirmacion de email*/
        $this->sendEmailRepo->sendConfirmRegistrationEmail($newUser, $confirmation_code);			
		
        return $this->sendResponse($newUser->toArray(), 'Usuario registrado exitosamente');
    }





    /**
     *
     * @OA\Get(
     *   path="/api/auth/user",
     *   summary="Information user active currently",
     *   operationId="user",        
     *   tags={"Account"},
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
    public function user(Request $request)
    {
        $user = Users::with('call_id')->find(Auth::user()->email);
        return $this->sendResponse($user->toArray(), 'Información del usuario');
    }

   /**
     *
     * @OA\Post(
     *   path="/api/login",
     *   summary="Try to login the user",
     *   operationId="login",
     *   tags={"Account"},
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
    public function login(Request $request)
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

        $tempUser = Users::with("rol")
                    ->with('type_business')
                    ->where('confirmed', 1)
                    ->find($email);
         
        if(is_null($tempUser)) {
            return response()->json(['error'=>'User is not validated'], 404);
        }              
        
        $credentials = $request->only('email', 'password');
        
        if ($token = JWTAuth::attempt($credentials)) {
        	 
             $success['usuario'] =  $tempUser;
             $success['access_token'] =  $token;
             $success['token_type'] = 'Bearer';
             $success['message'] =  "Login Exitoso";

            return response()->json(['success' => $success], 200); 
        } else{
            return response()->json(['error'=>'Unauthorised'], 404);
        }   
        
    }



    public function refresh()       
    {           
        return response([
            'status' => 'success'           
        ]);  

    }



    
    /**
     *
     * @OA\Post(
     *   path="/api/auth/logout",
     *   summary="Logout the user",
     *   operationId="logout",       
     *   tags={"Account"},
     *   @OA\Parameter(
     *      ref="../Swagger/definitions.yaml#/components/parameters/token"
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
    public function logout(Request $request) 
    {
        
        $this->validate($request, ['token' => 'required']);
        
        try {
            JWTAuth::invalidate($request->input('token'));  
            return $this->sendResponse('You have successfully logged out.'); 
        } catch (JWTException $e) {
            return $this->sendError('Failed to logout, please try again.');
        }

    }


}