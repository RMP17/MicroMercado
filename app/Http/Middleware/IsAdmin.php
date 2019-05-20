<?php

namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (is_null(request()->user()) || !(request()->user()->nivel_acceso == 0)) {
            return response()->json('Debe ser administrador para realizar está acción',511);
        }
        return $next($request);
    }
}
