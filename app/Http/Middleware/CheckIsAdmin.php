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
        // Check if the user is authenticated
        if (Auth::check()) {
            // Get the authenticated user
            $user = Auth::user();
            
            // Get the requested route
            $route = $request->path();
            
            // Check if the requested route starts with '/admin'
            if (strpos($route, 'api/admin/') === 0) {
                // If the user is an admin, allow access to admin routes
                if ($user->userType === 'admin') {
                    return $next($request);
                } else {
                    // If the user is not an admin, return a 404 error
                    return response()->json(['error' => 'Not Found'], 404);
                }
            }

            // If the requested route is not an admin route, allow access
            return $next($request);
        }

        // If the user is not authenticated, return a 404 error
        return response()->json(['error' => 'not authenticated'], 404);
    }
}
