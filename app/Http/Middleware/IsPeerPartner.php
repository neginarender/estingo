<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class IsPeerPartner
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
        if(Auth::check() && Auth::user()->user_type == 'partner' && Auth::user()->peer_partners == '1'  && !Auth::user()->banned) {
            return $next($request);
        }
        else{
            session(['link' => url()->current()]);
            return redirect()->route('user.login');
        }
    }
}
