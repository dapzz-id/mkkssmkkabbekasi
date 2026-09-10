<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is either an admin or superadmin
        if (Auth::check() && !(Auth::user()->role == "admin" || Auth::user()->role == "superadmin")) {
            return redirect('/');
        }

        return $next($request); // Continue to the next middleware or request handler
    }
}
