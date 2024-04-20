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
        
        // Check if the requested route starts with 'api/admin/'
        if (strpos($route, 'api/admin/') === 0) {
            // Check if the isAdmin field is true
            if ($user->isAdmin) {
                // If the user is an admin, allow access to admin routes
                return $next($request);
            } else {
                // If the user is not an admin, return an error message
                return response()->json(['error' => 'You are not authorized to access this route'], 403);
            }
        }

        // If the requested route is not an admin route, allow access
        return $next($request);
    }

    // If the user is not authenticated, return an authentication error message
    return response()->json(['error' => 'Not authenticated'], 401);
}
}
