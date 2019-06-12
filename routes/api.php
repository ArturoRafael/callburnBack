<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/
Route::post('auth/postRegistration', 'AuthController@postRegistration');
Route::post('auth/postLogin', 'AuthController@postLogin');


	    
		// Route::post('auth/postActivateAccount', 'AuthController@postActivateAccount');  		
		
		// Route::get('auth/getLogout', 'AuthController@getLogout');
		// Route::post('auth/postSendResetLink', 'AuthController@postSendResetLink'); 
		// Route::post('auth/postMakeResetPassword', 'AuthController@postMakeResetPassword'); 
		// Route::get('auth/getConfirmEmailAddress', 'AuthController@getConfirmEmailAddress'); 

		// Route::post('auth/postRecoverUsername', 'AuthController@postRecoverUsername'); 
		// Route::post('auth/checkPhonenumberCodeAndAddToAccount/{code}/{newuser}', 'AuthController@checkPhonenumberCodeAndAddToAccount');  


  //       Route::get('getShowUser', 'UsersController@getShowUser');

