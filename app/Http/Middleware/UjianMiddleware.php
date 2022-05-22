<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UjianMiddleware
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
        $login = auth()->user()->logins()
            ->whereNotNull('start')
            ->whereNull('end')
            ->orderBy('id', 'asc')
            ->first();

        if (!$login) {
            return redirect()->route('ujian.index');
        }
        return $next($request);
    }
}
