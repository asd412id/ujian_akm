<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->getId() != auth()->user()->session_id) {
            auth()->logout();
            return redirect()->route('index')->with('error', 'Anda telah login di perangkat lain!');
        }
        return $next($request);
    }
}
