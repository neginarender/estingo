<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

use Socialite;
use App\User;
use App\Customer;
use App\Address;
use Illuminate\Http\Request;
use CoreComponentRepository;
use Illuminate\Support\Str;
use Validator;
use Redirect;
use Session;
use Cookie;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    /*protected $redirectTo = '/';*/


    /**
      * Redirect the user to the Google authentication page.
      *
      * @return \Illuminate\Http\Response
      */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback(Request $request, $provider)
    {
        
        try {
            if($provider == 'twitter'){
                $user = Socialite::driver('twitter')->user();
            }
            else{
                
                $user = Socialite::driver($provider)->stateless()->user();
               
            }
        } catch (\Exception $e) {
            
            flash("Something Went wrong. Please try again.")->error();
            return redirect()->route('user.login');
        }

        // check if they're an existing user
        $existingUser = User::where('provider_id', $user->id)->orWhere('email', $user->email)->first();
          if ($user->email != null){
              $useremails = $user->email; 
              $peer_codes = \App\PeerPartner::where('email', $useremails)->where('verification_status', 1)->where('peertype_approval', 0)->select('code', 'user_id', 'discount')->first(); 
              if(!empty($peer_codes)){
                   Session::put('partner_id', $peer_codes->user_id);
                   Session::put('referal_discount', $peer_codes->discount);
                   Session::put('referal_code', $peer_codes->code);
              }
              //13-10-2021 - start
              else{
                    if(@$existingUser->used_referral_code != NULL){
                        $prev_peer_codes = \App\PeerPartner::where('code', $existingUser->used_referral_code)->where('verification_status', 1)->where('peertype_approval', 0)->select('code','user_id', 'discount')->first(); 
                        if(!empty($prev_peer_codes)){
                            Session::put('partner_id', $prev_peer_codes->user_id);
                            Session::put('referal_discount', $prev_peer_codes->discount);
                            Session::put('referal_code', $prev_peer_codes->code);
                        }
                    }
              }
            //13-10-2021 - end
          }
        if($existingUser){
            // log them in

            auth()->login($existingUser, true);
        } else {
            // create a new user
            $newUser                  = new User;
            $newUser->name            = $user->name;
            $newUser->email           = $user->email;
            $newUser->email_verified_at = date('Y-m-d H:m:s');
            $newUser->provider_id     = $user->id;

            // $extension = pathinfo($user->avatar_original, PATHINFO_EXTENSION);
            // $filename = 'uploads/users/'.Str::random(5).'-'.$user->id.'.'.$extension;
            // $fullpath = 'public/'.$filename;
            // $file = file_get_contents($user->avatar_original);
            // file_put_contents($fullpath, $file);
            //
            // $newUser->avatar_original = $filename;
            $newUser->save();

            $customer = new Customer;
            $customer->user_id = $newUser->id;
            $customer->save();
            
            setcookie('auth_id',$newUser->id,time()+60*60*24*30,'/');
            auth()->login($newUser, true);
        }
        if(session('link') != null){
            return redirect(session('link'));
        }
        else{
            Session::flash('success','Welcome '.auth()->user()->name); 
            return redirect()->route('home');
        }
    }

    /**
        * Get the needed authorization credentials from the request.
        *
        * @param  \Illuminate\Http\Request  $request
        * @return array
        */
       protected function credentials(Request $request)
       {
           if(filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)){
               return $request->only($this->username(), 'password');
           }
           return ['phone'=>$request->get('email'),'password'=>$request->get('password')];
       }

    /**
     * Check user's role and redirect user based on their role
     * @return
     */
    public function authenticated()
    {

      if (Auth::user()->id != null){
            $ids = Auth::user()->id; 
            session()->put('user_id',$ids);
            $address = \App\Address::where('user_id', $ids)->first();
            if(!is_null($address)){
                $city = \App\City::where('name', $address->city)->first();
                setcookie('pincode',$address->postal_code,time()+60*60*24*30,'/');
                setcookie('city_name',$address->city,time()+60*60*24*30,'/'); 
                setcookie('city_id',$city->id,time()+60*60*24*30,'/');
                setcookie('state',$address->city,time()+60*60*24*30,'/');
            }
 
            $peer_codes = \App\PeerPartner::where('user_id', $ids)->where('verification_status', 1)->where('peertype_approval', 0)->select('code', 'user_id', 'discount')->first(); 
            if(!empty($peer_codes)){
                 Session::put('partner_id', $peer_codes->user_id);
                 Session::put('referal_discount', $peer_codes->discount);
                 Session::put('referal_code', $peer_codes->code);
            }
            //13-10-2021 - start
              else{
                $user = \App\User::where('id', $ids)->select('used_referral_code')->first();
                    if($user->used_referral_code != NULL){
                        $prev_peer_codes = \App\PeerPartner::where('code', $user->used_referral_code)->where('verification_status', 1)->where('peertype_approval', 0)->select('code','user_id', 'discount')->first(); 
                        if(!empty($prev_peer_codes)){
                            Session::put('partner_id', $prev_peer_codes->user_id);
                            Session::put('referal_discount', $prev_peer_codes->discount);
                            Session::put('referal_code', $prev_peer_codes->code);
                        }
                    }
              }
            //13-10-2021 - end
        }
        setcookie('auth_id',$ids,time()+60*60*24*30,'/');
        Session::flash('success','Welcome '.auth()->user()->name); 

        if(auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')
        {
            //CoreComponentRepository::instantiateShopRepository();
            return redirect()->route('admin.dashboard');
        }elseif(auth()->user()->user_type == 'callcenter'){


            
            if(auth()->user()->status == '1'){

                 return redirect()->route('callceter.callcetertbl');

            }else{
                  Auth::logout();
              return Redirect::route('login')->with('message', 'User Not Active From Admin');
            }


        }elseif( auth()->user()->user_type == 'operations'){

            if(auth()->user()->status == '1'){

                 return redirect()->route('callceter.orderspration');

            }else{
                  Auth::logout();
              return Redirect::route('login')->with('message', 'User Not Active From Admin');
            }

           



        }elseif( auth()->user()->user_type == 'fieldofficer'){

                if(auth()->user()->status == '1'){

                 return redirect()->route('callceter.fieldofficer');

            }else{
                  Auth::logout();
              return Redirect::route('login')->with('message', 'User Not Active From Admin');
            }


        } else {
        
            if(session('link') != null){
                return redirect(session('link'));
            }
            else{
                return redirect()->route('home');
            }
        }
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        flash(translate('Invalid email or password'))->error();
        return back();
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        if(auth()->user() != null && (auth()->user()->user_type == 'admin' || auth()->user()->user_type == 'staff')){
            $redirect_route = 'login';
        }
        else{
            $redirect_route = 'home';
        }

        $cart=collect([]);
        if($request->session()->has('cart'))
        {
            $cart = $request->session()->get('cart');
        }
        
        $this->guard()->logout();

        $request->session()->invalidate();
        if (Cookie::has('auth_id')) {
            Cookie::queue(Cookie::forget('auth_id'));
        }
        if(Cookie::has('cart'))
        {
            Cookie::queue(Cookie::forget('cart'));
        }
        Session::put('cart',$cart);
        return $this->loggedOut($request) ?: redirect()->route($redirect_route);
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function loginphone(Request $request)
{

    if(isset($request->otp)){
       $validator = Validator::make($request->all(),
            ['otp'=>'required',
              'phone'=>'required'
            ]
    );
       if ($validator->fails()) {
            $errors = $validator->messages();
            return Redirect::back()->withErrors($errors);
        }
        return $this->authenticate($request);
    }else{
         $validator = Validator::make($request->all(),
            [
              'phone'=>'required'
            ]);
         if ($validator->fails()) {
            $errors = $validator->messages();
            return Redirect::back()->withErrors($errors);
        }else{
            
            return $this->validatePhone($request);
        }
    }
        
}

public function authenticate($request)
{
  
            if(Session::get('otp') == $request->otp)
            {
                $user = User::where('phone',$request->phone)->first();   
                if(Auth::loginUsingId($user->id))
                {
                    $userphones = $request->phone;
                    if ($userphones != ''){
                        $peer_codes = \App\PeerPartner::where('phone', $userphones)->where('verification_status', 1)->where('peertype_approval', 0)->select('code', 'user_id', 'discount')->first(); 
                        if(!empty($peer_codes)){
                             Session::put('partner_id', $peer_codes->user_id);
                             Session::put('referal_discount', $peer_codes->discount);
                             Session::put('referal_code', $peer_codes->code);
                        }
                        //13-10-2021 - start
                          else{
                                if($user->used_referral_code != NULL){
                                    $prev_peer_codes = \App\PeerPartner::where('code', $user->used_referral_code)->where('verification_status', 1)->where('peertype_approval', 0)->select('code','user_id', 'discount')->first(); 
                                    if(!empty($prev_peer_codes)){
                                        Session::put('partner_id', $prev_peer_codes->user_id);
                                        Session::put('referal_discount', $prev_peer_codes->discount);
                                        Session::put('referal_code', $prev_peer_codes->code);
                                    }
                                }
                          }
                        //13-10-2021 - end
                    }
                    setcookie('auth_id',$user->id,time()+60*60*24*30,'/');
                    Session::flash('success','Login successful. Hello '.$user->name);
                    Session::forget('otp');
                    return redirect()->route('home');
                }
                $errors['otp'] = "Invalid OTP";
                return redirect()->route('user.login_otp')->withErrors($errors);
            }
            $errors['otp'] = "Invalid OTP";
            return redirect()->route('user.verify_otp')->withErrors($errors);
}
    public function validatePhone($request)
    {
        
        $user = User::where('phone',$request->phone)->first();
        //echo decrypt($user->password);exit;
        if($user!=null)
        {
            Session::put('phone', $request->phone);
            $otp  = random_int(1000, 9999);
            $send_otp = send_otp($request->phone,$otp);
            //dd($send_otp);
            if(!is_null($send_otp)){
                if($send_otp->status == 'success')
                {
                    $phone = $request->phone;
                    Session::flash('success','OTP sent');
                    Session::put('otp',$otp);
                    return view('frontend.verify_otp',compact('phone'));
                }
        }
             $errors['phone'] ="OTP sending fail"; 
             return Redirect::back()->withErrors($errors);
        }
        $errors['phone'] ="Phone No. does not exists"; 
        return Redirect::back()->withErrors($errors);

    }

     public function resend_otp(Request $request)
    {
        $user = User::where('phone',$request->phone)->first();
        //echo decrypt($user->password);exit;
        if($user!=null)
        {
            Session::put('phone', $request->phone);
            $otp  = random_int(1000, 9999);
            $send_otp = send_otp($request->phone,$otp);
            if($send_otp->status == 'success')
            {
                $phone = $request->phone;
                Session::put('otp',$otp);
                return "otp sent successfuly";
            }
             return "otp sending fail";
        }
    }

}
