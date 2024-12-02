<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $roles)
    {
        // Split roles into an array
        $rolesArray = explode('|', $roles);

        // Check if user has the required role
        if (!Auth::check() || !in_array($request->user()->role, $rolesArray)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
