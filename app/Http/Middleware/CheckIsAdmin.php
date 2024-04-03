<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckIsAdmin
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
        // Get the user from the request via the Passport token
        $user = Auth::user();

        // Check if the user is an admin
        if ($user->isAdmin === 'admin') {
            return $next($request);
        }

        // If the user is not an admin, return a 404 error
        return response()->json(['error' => 'Not Found'], 404);
    }
}
