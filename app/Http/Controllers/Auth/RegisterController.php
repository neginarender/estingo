<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Customer;
use App\BusinessSetting;
use App\OtpConfiguration;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\OTPVerificationController;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Mail;
use App\Mail\OtpSendMail;
use Cookie;
use Nexmo;
use Twilio\Rest\Client;
use Redirect;
use Session;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'email' => 'required|email',
            'phone' => 'required',

        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        //     $user = User::create([
        //         'name' => $data['name'],
        //         'email' => $data['email'],
        //         'password' => Hash::make($data['password']),
        //     ]);

        //     $customer = new Customer;
        //     $customer->user_id = $user->id;
        //     $customer->save();
        // }
        // else {
            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)){
                 dump("dd1");
            if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated){

                dump("dd2");
                $user = User::create([
                    'name' => $data['name'],
                    'phone' => '+'.$data['country_code'].$data['phone'],
                    'password' => Hash::make($data['password']),
                    'email' => $data['email'],
                    'verification_code' => rand(100000, 999999)
                ]);
                $customer = new Customer;
                $customer->user_id = $user->id;
                $customer->save();

                $otpController = new OTPVerificationController;
                $otpController->send_code($user);
                //send otp at email
                $array = array();
                $array['view'] = 'emails.send_otp_at_email';
                $array['subject'] = 'Please verify your account with OTP';
                $array['from'] = env('MAIL_USERNAME');
                $array['verification_code'] = $user->verification_code;
                $array['content'] =  $user->verification_code.' is your verification code for eKhadiIndia.com';
                Mail::to($data['email'])->queue(new OtpSendmail($array));


            }
        }
        // }

        if(Cookie::has('referral_code')){
            $referral_code = Cookie::get('referral_code');
            $referred_by_user = User::where('referral_code', $referral_code)->first();
            if($referred_by_user != null){
                $user->referred_by = $referred_by_user->id;
                $user->save();
            }
        }

        exit;

        return $user;
    }

    // public function register(Request $request)
    // {
    //     // dd($request);
    //     if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
    //         if(User::where('email', $request->email)->first() != null){
    //             flash(translate('Email or Phone already exists.'));
    //             return back();
    //         }
    //     }
        
    //     if (User::where('phone', '+'.$request->country_code.$request->phone)->first() != null) {
    //         flash(translate('Phone already exists.'));
    //         return back();
    //     }

    //     $this->validator($request->all())->validate();

    //     $user = $this->create($request->all());

    //     $this->guard()->login($user);

    //     // if($user->email != null){
    //     //     if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
    //     //         $user->email_verified_at = date('Y-m-d H:m:s');
    //     //         $user->save();
    //     //         flash(translate('Registration successfull.'))->success();
    //     //     }
    //     //     else {
    //     //         event(new Registered($user));
    //     //         flash(translate('Registration successfull. Please verify your email.'))->success();
    //     //     }
    //     // }

    //     return $this->registered($request, $user)
    //         ?: redirect($this->redirectPath());
    // }

        public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'email'   => 'required|unique:users',
            'phone' => 'required|unique:users',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            $errors = $validator->messages();
            return Redirect::back()->withErrors($errors);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();
        //$this->sendOtp($input);
        Session::flash('success','You are registered successfully');
        return redirect('users/login');
    }

    protected function registered(Request $request, $user)
    {
        if ($user->email != null && $user->phone) {
            return redirect()->route('verification');
        }
        else {
            return redirect()->route('home');
        }
    }

    public function register_user_phone(Request $request)
{
            $validator = Validator::make($request->all(), [
            'name'     => 'required',
            'phone' => 'required|unique:users',
        ]);
        if ($validator->fails()) {
            $errors = $validator->messages();
            return Redirect::back()->withErrors($errors);
        }
            //send otp
            $otp  = random_int(1000, 9999);
            Session::put('phone', $request->phone);
            Session::put('name', $request->name);
            Session::put('otp',$otp);
            
            send_otp($request->phone,$otp);
            Session::flash('success','OTP sent');
            return view('frontend.user_otp_verify');
}

public function verifyRegistration(Request $request)
{
    $validator = Validator::make($request->all(), [
            'otp'     => 'required',
            
        ]);
        if ($validator->fails()) {
            $errors = $validator->messages();
            return Redirect::back()->withErrors($errors);
        }

        if(Session::get('otp') == $request->otp)
            {
                $input = $request->all();
               $user = User::create($input)->id;
               $customer = new Customer;
               $customer->user_id = $user;
               $customer->save();
                if(Auth::loginUsingId($user))
                {
                    $user = Auth::loginUsingId($user);
                    Session::flash('success','Login sucessful. Hello '.$user->name);
                    Session::forget('otp');
                    return redirect()->route('home');
                }
            }

            $errors['otp'] = "Invalid OTP";
            return redirect()->route('user.user_otp')->withErrors($errors);
}

    public function verifyReg($id){
        $id=decrypt($id);
        $check_verification = User::where('id', $id)->select('email_verification')->first();
        if($check_verification->email_verification == 0){
            User::where('id', $id)
                   ->update([
                       'email_verification' => 1
                    ]);
            Session::flash('success','Email verified sucessful.');  
        }else{
            Session::flash('success','Already verified.'); 
        }         
        return redirect()->route('user.login');
    }

}
