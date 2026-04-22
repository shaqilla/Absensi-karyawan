<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Kalau gak login atau role-nya gak ada di daftar yang dibolehin, tendang!
        if (!auth()->check() || !in_array(auth()->user()->role, $roles)) {
            abort(403, 'eits, gabisa akses ini ya!');
        }

        return $next($request);
    }
}
