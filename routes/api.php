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


Route::post('registration', 'AuthController@registration');
Route::get('registration/verify/{code}', 'AuthController@verify');
Route::post('login', 'AuthController@login');

Route::group(['prefix' => 'auth', 'middleware' => 'jwt.auth'], function () {

    Route::get('user', 'AuthController@user');
    Route::post('logout', 'AuthController@logout');

    Route::get('users','UsersController@listar_usuarios');
    Route::post('user_profile/{user_profile}','UsersController@update_profile');

    Route::apiResource('contact','ContactController');
	Route::post('contact_save','ContactController@save_file_contacts');

	Route::apiResource('reservation','ReservationController');

	Route::apiResource('group','GroupController');
	Route::apiResource('rol','RolController');
	Route::apiResource('typebusiness','TypeBusinessController');
	Route::apiResource('groupcontact','GroupContactController');

	Route::apiResource('workflow','WorkflowController');
	Route::post('update_workflow/{update_workflow}','WorkflowController@update_workflow');
	Route::get('workflow_user/{workflow_user}','WorkflowController@workflow_user');

	Route::apiResource('workflowcontact','WorkflowContactController');

	Route::apiResource('time','TimeController');
	Route::apiResource('day','DayController');
	Route::apiResource('keyeventype','KeyEventTypeController');

});

Route::middleware('jwt.refresh')->get('/token/refresh', 'AuthController@refresh');

