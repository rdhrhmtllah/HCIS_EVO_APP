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
    public function handle(Request $request, Closure $next, string $jabatan): Response
    {

        if(!Auth::check()){
            return redirect('login');
        }
        if(!$request->user()->memilikiJabatan($jabatan)){
            abort(403, 'Akses Ditolak.');
        }

        return $next($request);
    }
}
