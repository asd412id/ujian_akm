<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $role = is_null(auth()->user()->role) ? 'null' : strval(auth()->user()->role);

        if (!in_array($role, $guards)) {
            return redirect()->back()->withErrors('Anda tidak memiliki akses');
        }
        return $next($request);
    }
}
