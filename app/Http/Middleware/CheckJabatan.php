<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckJabatan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next, string ...$jabatans): Response
{
    if (!Auth::check()) {
        return redirect('login');
    }

    foreach ($jabatans as $jabatan) {
        if ($request->user()->memilikiJabatan($jabatan)) {
            return $next($request);
        }
    }


    return redirect()->back();
    // abort(403, 'Akses Ditolak.');
}
}
