<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cookie;

class ExtractTokenFromCookie
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
        // Check if the cookie exists
        if ($request->hasCookie('auth_token')) {
            // Get the token from the cookie
            $token = Cookie::get('auth_token');

            // Set the token to the Authorization header
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}
