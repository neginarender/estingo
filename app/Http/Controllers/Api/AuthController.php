<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api;

use App\Models\BusinessSetting;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\User;
use App\DeliveryBoy;
use App\Notifications\EmailVerificationNotification;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // public function signup(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string',
    //         'email' => 'required|string|email|unique:users',
    //         'password' => 'required|string|min:6'
    //     ]);
    //     $user = new User([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => bcrypt($request->password)
    //     ]);

    //     if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
    //         $user->email_verified_at = date('Y-m-d H:m:s');
    //     }
    //     else {
    //         $user->notify(new EmailVerificationNotification());
    //     }
    //     $user->save();

    //     $customer = new Customer;
    //     $customer->user_id = $user->id;
    //     $customer->save();
    //     return response()->json([
    //         'message' => 'Registration Successful. Please verify and log in to your account.'
    //     ], 201);
    // }

    public function signup(Request $request)

    {

        // $request->validate([

        //     'name' => 'required|string',

        //     'email' => 'required|string|email|unique:users',

        //     'password' => 'required|string|min:6'

        // ]);

        if(!empty($request->email)){

            $checkExistEmail = User::where('email',$request->email)->first();



            if(!empty($checkExistEmail)){

                return response()->json([

                    'message' => 'Email Already Exist.'

                ], 200);

            }

        }



        if(!empty($request->phone)){

            $checkExistPhone = User::where('phone',$request->phone)->first();



            if(!empty($checkExistPhone)){

                return response()->json([

                    'message' => 'Phone Already Exist.'

                ], 200);

            }

        }

        $user = new User([

            'name' => $request->name,

            'email' => $request->email,

            'phone' => $request->phone,

            'password' => bcrypt($request->password)

        ]);



        if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){

            $user->email_verified_at = date('Y-m-d H:m:s');

        }

        else {

            $user->notify(new EmailVerificationNotification());

        }

        $user->save();



        $customer = new Customer;

        $customer->user_id = $user->id;

        $customer->save();

        $tokenResult = $user->createToken('Personal Access Token');
        return $this->loginSuccess($tokenResult, $user,'signup');
        // return response()->json([

        //     'status'=>true,

        //     'message' => 'Registration Successful. Please verify and log in to your account.'

        // ], 201);

    }

    public function login(Request $request)
    {
        // $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required|string',
        //     'remember_me' => 'boolean'
        // ]);
        if(isset($request->email) || isset($request->phone)){
            if(!empty($request->email)){
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials))
        
            return response()->json(['success'=>false,'message' => 'Invalid Email or Password']);
        $user = $request->user();
        }elseif(!empty($request->phone)){

              

            $user = User::where(['phone'=>$request->phone])->first();

            

            if(Hash::check($request->password, $user['password']) == false){

                return response()->json(['message' => 'Invalid Phone or Password']);

            }



        }   
        // if($user->email_verified_at == null){
        //     return response()->json(['message' => 'Please verify your account']);
        // }
        if($user->banned == 1){
                return response()->json(['success'=>false,'message' => 'This user is banned.']);
            }
        $tokenResult = $user->createToken('Personal Access Token');
        User::where('id',$user->id)->update(['device_id'=>$request->device_id]);
        return $this->loginSuccess($tokenResult, $user,'login');
    }
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function socialLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email'
        ]);
        if (User::where('email', $request->email)->first() != null) {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = new User([
                'name' => $request->name,
                'email' => $request->email,
                'provider_id' => $request->provider,
                'email_verified_at' => Carbon::now()
            ]);
            $user->save();
            $customer = new Customer;
            $customer->user_id = $user->id;
            $customer->save();
        }
        $tokenResult = $user->createToken('Personal Access Token');
        return $this->loginSuccess($tokenResult, $user,'social');
    }

    protected function loginSuccess($tokenResult, $user,$loginType)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();
        $phone = $user->phone;
        if($user->user_type == "staff"){
            $phone = DeliveryBoy::where('user_id',$user->id)->first()->phone;
        }
        $message = "Login successfully";
        if($loginType=="signup"){
            $message = "Registration successful";
        }
        $peer_code = "";
        $peer_type = "";
        $peer_partner = \App\PeerPartner::where('user_id',$user->id)->first();

        $user_cart = false;
        if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
            $check_user_cart = \App\Models\Cart::where('device_id',$_SERVER['HTTP_DEVICE'])->count();
            if($check_user_cart){
                $user_cart = true;
            }
        }

        if(!is_null($peer_partner)){
            if($peer_partner->peer_type!='master'){
                $peer_code = $peer_partner->code;
                $peer_type = $peer_partner->peer_type;
            }
            else{
                if(!is_null($user->used_referral_code)){
                    $peer_code = $user->used_referral_code;
                } 
            }
            
        } else{
            if(!is_null($user->used_referral_code)){
                $peer_code = $user->used_referral_code;
            }
        }
        
        return response()->json([
            'success'=>true,
            'message'=>$message,
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => $user->avatar_original,
                'address' => $user->address,
                'country'  => $user->country,
                'city' => $user->city,
                'postal_code' => $user->postal_code,
                'peer_partner' => $user->peer_partner,
                "peer_code" => $peer_code,
                'peer_type'=>$peer_type,
                'phone' => !is_null($phone) ? $phone:"",
                'user_cart'=>$user_cart
            ]
        ]);
    }

    //15-11-2021
    public function loginWithOtp(Request $request)

    {
        if($request->mobile)

        {

            $number = $request->mobile;
            $user  = User::where('phone','=',$number)->first();
            if($user){

               $senOTP = $this->sendOtp($request);
               $status = true;//$senOTP->status;
               $message = 'OTP sent successfully.';
               // $data = [];

            }else{

                $status = false;
                $message = 'Mobile number not registered.';
                // $data = [];

            }

            return response()->json([
                'status' => $status,
                'message' => $message,
                // 'data' =>$data

            ]);
        }
    }

    public function sendOtp(Request $request){

        $otp = rand(100000,999999);
        //Log::info("otp = ".$otp);
        $number = $request->mobile;
        $user   = User::where('phone',$number)
                ->orWhere('phone',$request->mobile)
                ->update(['login_otp' => $otp,'login_otp_create' => Carbon::now()]);
        
            $to = $request->mobile;
    
            $sendSms = send_otp($to,$otp);
            
        // send otp to mobile no using sms api
        return $sendSms;
    }

    public function verifyOtpUser(Request $request){
        if($request->otp && $request->mobile){
            $user = User::where('phone',$request->mobile)
                    ->where('login_otp',$request->otp)
                    ->where('banned',0)
                    ->first();
            if($user){
                if($user->login_otp != $request->otp){
                    return response()->json([
                        'success'=>false,
                        'message'=>'OTP not matched.',
                        // 'access_token' => "",
                        // 'token_type' => "",
                        // 'expires_at' => "",
                        // 'user' =>  (object)[]
                    ]);
                }

                $diff = Carbon::now()->diffInMinutes($user->login_otp_create);
                if($diff > 15){
                    return response()->json([
                        'success'=>false,
                        'message'=>'OTP Expired.',
                        // 'access_token' => "",
                        // 'token_type' => "",
                        // 'expires_at' => "",
                        // 'user' =>  (object)[]
                    ]);
                }else{
                    $tokenResult = $user->createToken('Personal Access Token');
                    return $this->loginSuccess($tokenResult, $user,'login');
                }
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'Invalid OTP.',
                    // 'access_token' => "",
                    // 'token_type' => "",
                    // 'expires_at' => "",
                    // 'user' =>  (object)[]
                ]);
            }
            
        }else{
            return response()->json([
                    'success'=>false,
                    'message'=>'Both fields are required.',
                    // 'access_token' => "",
                    // 'token_type' => "",
                    // 'expires_at' => "",
                    // 'user' =>  (object)[]
                ]);
        } 

    }

}
