<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return auth()->check() && (auth()->user()->Kode_Users === 'RIDHO RAHMAT' || auth()->user()->Kode_Users === 'H45')
        ? $next($request)
        : abort(403);

    }
}
