<?php namespace App\Services;

use App\Models\Download;

class DownloadsService{


    public static function user($token) {

        try {

            $download = Download::with(['user'])->where('token',$token)->first();
            $user = $download->user;
            $download->delete();
            return $user;


        } catch (\Exception $e) {

            return null;
        }
    }

}