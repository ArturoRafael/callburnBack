<?php

namespace App\Http\Middleware;

use Closure;

class JwtHeaders
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

        $jwtHeader = $request->header('JWTAuthorization');

        $request->headers->set('Authorization', $jwtHeader);

        return $next($request);
    }
}
