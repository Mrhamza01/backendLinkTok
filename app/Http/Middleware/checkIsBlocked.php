<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class checkIsBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         // Check if the user is authenticated
         if (Auth::check()) {
            // Get the authenticated user
            $user = Auth::user();
            
            // Check if the user is blocked
            if ($user->isblocked) {
                // If the user is blocked, return an error message
                return response()->json(['message' => 'You are blocked from accessing this platform'], 403);
            }

            // If the user is not blocked, proceed with the request
            return $next($request);
        }

        // If the user is not authenticated, return an authentication error message
        return response()->json(['message' => 'Not authenticated'], 401);
    }
}
