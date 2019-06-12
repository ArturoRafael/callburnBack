<?php

namespace App\Http\Services;

class CookieService {

    public static function checkEU($request) {

        $ip = $request->ip();

        if(config('app.SHOULD_USE_GEOIP')) {
            $countryCode = strtolower(trim(@geoip_country_code_by_name($ip)));

        }
        else {
            $countryCode = 'am';
        }

        $country = \App\Models\Country::where('code',$countryCode)->first();
        if (! $country) {
            return false;
        }
        return $country->is_eu_member ? true : false;
    }

}