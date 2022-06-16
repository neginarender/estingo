<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\v3;

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
use App\Notifications\EmailVerify;

class AuthController extends Controller
{

    public function signup(Request $request){
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
        }else {
            $user->notify(new EmailVerificationNotification());
        }

        $user->save();
        $customer = new Customer;
        $customer->user_id = $user->id;
        $customer->save();

        $request->mobile = $request->phone;

        $sendOTP = $this->sendOtp($request);
        $status = true;
        $message = 'OTP sent successfully.';

        return response()->json([
            'success'=>true,
            'message'=>$message,
            'mobile' => $request->phone

        ]);
    }

    public function login(Request $request){
        if(isset($request->email) || isset($request->phone)){
            if(!empty($request->email)){
            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials))
            
                return response()->json(['success'=>false,'message' => 'Invalid Email or Password']);
            $user = $request->user();
            }
            // elseif(!empty($request->phone)){
            //     $user = User::where(['phone'=>$request->phone])->first();
            //     if(Hash::check($request->password, $user['password']) == false){
            //         return response()->json(['message' => 'Invalid Phone or Password']);
            //     }
            // }

            // $user = User::where('email',$request->email)->where('is_mobile_verify',1)->first();
            // if(empty($user)){
            //     return response()->json(['success'=>false,'message' => 'Mobile number not verified.']);
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

    protected function loginSuccess($tokenResult,$user,$loginType)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();
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
            // $check_user_cart = \App\Models\Cart::where('device_id',$_SERVER['HTTP_DEVICE'])->count();
            
            $check_user_cart = \App\Models\Cart::where('device_id',$_SERVER['HTTP_DEVICE'])->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

            if($check_user_cart){
                $user_cart = true;
            }
        }

        $is_mobile_verify = \App\User::where('id',$user->id)->first()->is_mobile_verify;
        $is_email_verify = \App\User::where('id',$user->id)->first()->email_verification;

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
                'phone' => isset($user->phone)?$user->phone:"",
                'user_cart'=>$user_cart,
                'is_mobile_verify' => $is_mobile_verify,
                'is_email_verify' => $is_email_verify

            ]
        ]);
    }

    //15-11-2021
    public function loginWithOtp(Request $request){
        if($request->mobile){
            $number = $request->mobile;
            $user  = User::where('phone','=',$number)->first();

            if(is_null($user)){
                $user = new User([
                    'phone' => $request->mobile,
                ]);
                $user->save();
                $customer = new Customer;
                $customer->user_id = $user->id;
                $customer->save();
            }

            $sendOTP = $this->sendOtp($request);
            $status = true;
            $message = 'OTP sent successfully.';
            
            // if($user){
            //    $sendOTP = $this->sendOtp($request);
            //    $status = true;
            //    $message = 'OTP sent successfully.';
            // }else{
            //     $status = false;
            //     $message = 'Mobile number not registered.';
            // }
        
        }else{
            $status = false;
            $message = 'Please Enter Mobile No';
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    public function sendOtp(Request $request){


        $otp = rand(100000,999999);
        // Log::info("otp = ".$otp);
        $number = $request->mobile;
        $user   = User::where('phone',$number)
                ->orWhere('phone',$request->mobile)
                ->update(['login_otp' => $otp,'login_otp_create' => Carbon::now()]);
        
            $to = $request->mobile;
    
            // $sendSms = send_otp($to,$otp);
            $sendSms = send_otp_login($to,$otp);
            // dd($sendSms);
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
                    ]);
                }

                $diff = Carbon::now()->diffInMinutes($user->login_otp_create);
                if($diff > 15){
                    return response()->json([
                        'success'=>false,
                        'message'=>'OTP Expired.',
                    ]);
                }else{
                    $tokenResult = $user->createToken('Personal Access Token');

                    //update is_mobile_verify column to 1
                    User::where('phone',$request->mobile)
                           ->update([
                               'is_mobile_verify' => 1
                            ]);

                    return $this->loginSuccess($tokenResult, $user,'login');
                }
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'Invalid OTP.',
                ]);
            }
            
        }else{
            return response()->json([
                    'success'=>false,
                    'message'=>'Invalid OTP.',
                ]);
        } 

    }

    public function verifymail(Request $request){
        $check_verification = User::where('email', $request->email)->first();
        if(!empty($check_verification)){
             if($check_verification->email_verification == 0){
                $detail = array(
                    'email'=>$request->email
                );

                $check_verification->notify(new EmailVerify($detail));
                // $check_verification->notify(new EmailVerificationNotification());
                return response()->json([
                    'success'=> true,
                    'message'=>'Your verification request send successfully.',
                    'status' => 200
                ]);
              }else{
                return response()->json([
                    'success'=> true,
                    'message'=>'Email already verified.',
                    'status' => 200
                ]);
              }  
        }else{

            return response()->json([
                'success'=> false,
                'message'=>'Email not exist.',
                'status' => 401
            ]);
        }

    }

}
