<?php

namespace App\Http\Middleware;

use Closure;
use \App\User;
use Carbon\Carbon;

class LastSeenLog
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
        $now = Carbon::now();
        $user->last_seen = $now;
        $user->save();
        return $next($request);
    }
}
