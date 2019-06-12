<?php

namespace App\Http\Middleware;

use Closure;

class ActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $user = \Auth::user();

        if($user->is_deleted) {

            return response()->json([

                'error' => [
                    'no' => -70,
                    'text' => trans('crud.we_are_sorry_your_account_was_blocked_contact_us_for_further_assistance'),
                ]
            ],422);


        } else {
            return $next($request);
        }

    }
}
