<?php

namespace App\Services;


class InfoService {


    public static function getTimezoneName($request) {

        if(config('app.SHOULD_USE_GEOIP')) {

            try {
                $ip = $request->ip();
                $country = geoip_record_by_name ($ip);
                $code = $country['country_code'];
                $region = $country['region'] ? $country['region'] : null;
                $timezone = geoip_time_zone_by_country_and_region($code, $region);
                return $timezone;

            } catch (\Exception $e) {

                return null;
            }


        } else {

            return null;
        }
    }



    public static function getCountryCode($request) {

        if(config('app.SHOULD_USE_GEOIP')) {

            try {
                $ip = $request->ip();
                $country = geoip_record_by_name ($ip);
                $code = $country['country_code'];
                return $code;

            } catch (\Exception $e) {

                return null;
            }


        } else {

            return null;
        }
    }

}