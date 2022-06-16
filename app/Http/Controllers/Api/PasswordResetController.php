<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\User;
use App\Models\PasswordReset;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetRequestOTP;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Validator;
use Hash;
class PasswordResetController extends Controller
{
    //use ResetsPasswords;

    public function create(Request $request)
    {
        $user = User::where('email', $request->email)->first();
       
        if (!$user)
            return response()->json([
                'success' => false,
                'user_id'=>'',
                'message' => 'We can not find a user with that e-mail address'], 200);

        // $token = Str::random(60);
        // $passwordReset = PasswordReset::updateOrCreate(
        //     ['email' => $user->email],
        //     [
        //         'email' => $user->email,
        //         'token' => bcrypt($token)
        //     ]
        // );

        // if ($user && $passwordReset)
        //     $user->notify(
        //         new PasswordResetRequest($token)
        //     );
        $otp = random_int(100000, 999999);
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'otp' => $otp
            ]
        );

        // if(!empty($user->phone)){
        //     changePasswordOtp($user->phone,$otp);
        // }
        
        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequestOTP($otp)
            );


        return response()->json([
            'success' => true,
            'email'=>$user->email,
            'message' => 'Please check your email. We have e-mailed your password reset otp'
        ], 200);
    }

    public function verifyOTP(Request $request){
        $passwordReset = PasswordReset::where('email',$request->email)->first();
        $user = User::where('email',$request->email)->first();
        $user_id = "";
        if(!is_null($user)){
            $user_id = $user->id;
        }

        if(!is_null($passwordReset)){
            $minutes = \Carbon\Carbon::parse($passwordReset->updated_at);
             if($request->otp==$passwordReset->otp && $minutes->diffInMinutes()<=10){
                return response()->json([
                    'status'=>true,
                    'user_id'=>$user_id,
                    'message'=>'OTP verified successfully'
                ]);
            }

        }
        
           
        return response()->json([
            'status'=>false,
            'user_id'=>$user_id,
            'message'=>'Invalid OTP'
        ]);
    }

    public function updatePassword(Request $request)
    {

      $validator = Validator::make($request->all(),[
            'id' => 'required',
            'password'=>'required|confirmed|min:6'
        ]);

      if($validator->fails())
      {
        return response()->json([
            'success'=>false,
            'message'=>$validator->messages()->get('*')
        ]);
      }
       $user = User::find($request->id);
       $user->password = Hash::make($request->password);
       if($user->save())
       {
        return response()->json([
            'success'=>true,
            'message'=>'Password reset successfully'
        ]);
       }

       return response()->json([
            'success'=>false,
            'message'=>'Password not reset'

       ]);

    }


    public function changePassword(REQUEST $request){

        if($request->email || $request->phone){

             if(!empty($request->email)){

                $user = User::where('email',$request->email)->update(['password'=>Hash::make($request->password)]); 

                if($user){

                    $status = true;

                    $msg = "your password has been reset.";

                }

             }elseif(!empty($request->phone)){

                $user = User::where('phone',"+91".$request->phone)->update(['password'=>Hash::make($request->password)]);

                if($user){

                    $status = true;

                    $msg = "your password has been reset.";

                }

             }    

        }

        return response()->json([
            'success' => $status,
            'message' => $msg
        ]);

    }

    public function verifyUser(Request $request){
        if(!empty($request->email) || !empty($request->phone)){
            if(!empty($request->email)){
                $user = User::where('email',$request->email)->first();
                    if($user){
                        $status = true;
                        $message = "User Verified";
                        $id = $user->id;
                    }
                    else{
                        $status = false;
                        $message = "User not found";
                        $id = "";

                    }
                }
                elseif(!empty($request->phone)){
                    $user = User::where('phone',$request->phone)->first();
                    if($user){
                        $status = true;
                        $message = "User verified";
                        $id = $user->id;
                    }
                    else{
                        $status = false;
                        $message = "User not found";
                        $id = "";

                    }
                }
                
                return response()->json([
                    'success'=> $status,
                    'message'=> $message,
                    'id' => $id
                ]);
           
        }

        return response()->json([
            'success'=>false,
            'message'=>'Email/Phone can not be empty',
            'id'=>""
        ]);
        
    }


    public function changePassApi(Request $request){
        $user = User::where('id',$request->id)->first();

        if(!is_null($user))
        {
            //dd(Hash::check($request->old_password,$user->password));
            if(Hash::check($request->old_password,$user->password)){
                if(Hash::check($request->password,$user->password)){
                    return response()->json([
                        'success'=>false,
                        "message"=>"Your old and new passwords are same. Please choose something diffrenet"
                    ]);
                }
                return $this->updatePassword($request);
            }
            return response()->json([
                'success'=>false,
                'message'=>"Password not match"
            ]);
        }
    }
}
