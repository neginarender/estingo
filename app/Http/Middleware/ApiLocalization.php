<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Session;
use Config;

class ApiLocalization
{
     /**
  * Handle an incoming request.
  *
  * @param \Illuminate\Http\Request $request
  * @param \Closure $next
  * @return mixed
  */
    public function handle($request, Closure $next)
    {
        // Check header request and determine localizaton
        $local = ($request->hasHeader('X-localization')) ? $request->header('X-localization') : 'en';
        // set laravel localization
        App::setLocale($local);
        // continue request
        return $next($request);
    }
}
