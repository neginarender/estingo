<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

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
        if (Auth::check() && in_array(Auth::user()->user_type, ['admin', 'staff', 'cluster_hub', 'sorting_hub'])) {
            return $next($request);
        }
        else{
            abort(404);
        }
    }
}
