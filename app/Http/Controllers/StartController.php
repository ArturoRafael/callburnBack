<?php

namespace App\Http\Controllers\Website;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;

class StartController extends Controller
{
	/**
     * 
     */
    public function getStartAngular()
    {
        if(!Auth::check()){
            return redirect()->to('/');
        }
        return view('layout');
    }

    
    /**
     * 
     */
    public function getBeforeAngular()
    {
        if( Auth::check() ){
            return redirect()->to('/api');
        }
        return view('frontLayout');
    }  

    // public function getXxxxx()
    // {
    //     $invoice = [
    //         'customer_name' => 'sdsa',
            
    //     ];
    //     $data = [
    //         'invoice' => $invoice,
    //     ];
    //     return view('pdf.invoice',$data);
    // }
}