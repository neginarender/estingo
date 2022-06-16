<?php

namespace App\Http\Middleware;
use Closure;
use App\Traits\LocationTrait;

class Localization
{
    use LocationTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $device_id = null;
        $user_id = null;
        $locale = 'en';
        // dd($_SERVER['PHP_SELF']);
        // dd($request->hasHeader('DEVICE'));
        //dd($_SERVER);
        if($request->hasHeader('DEVICE')){
            $device_id = $request->header('DEVICE');
            
            $locale = $this->language($device_id,$user_id);

        }else{
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                $url = explode('/',$_SERVER['REQUEST_URI']);
                $user_id = (int)end($url);
                $locale = $this->language($device_id,$user_id);
            }elseif($_SERVER['REQUEST_METHOD'] == 'POST'){
                 if($request->has('user_id')){
                    $user_id = $request->user_id;
                 }elseif($request->has('device_id')){
                    $device_id = $request->device_id;

                 }
                 $locale = $this->language($device_id,$user_id);
            }
        }
        app()->setLocale($locale);
        return $next($request);
    }
}
