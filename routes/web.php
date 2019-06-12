<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sendemail', function () {
	$data = array(
		'name' => "Learning Laravel",
	);

	Mail::send('emails.welcome', $data, function($message){
		$message->from('angelica.informatik@gmail.com', 'Learning Laravel');

		$message->to('angelica.informatik@gmail.com')->subject('Learning Laravel test email for Sendinblue');
	});

    return "Your email has been sent successfully";
});
