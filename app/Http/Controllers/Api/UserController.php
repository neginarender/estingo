<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserCollection;
use App\User;
use Illuminate\Http\Request;
use App\PeerPartner;
use Carbon\Carbon;


class UserController extends Controller
{
    public function info($id)
    {
        return new UserCollection(User::where('id', $id)->get());
    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone
        ]);
        return response()->json([
            'message' => 'Profile information has been updated successfully'
        ]);
    }
    public function createPeerPartner(REQUEST $request){

        $getExistUser = PeerPartner::where('user_id',$request->user_id)->first();

        $update_user = User::where('id',$request->user_id)->update(['name'=>$request->name,'email'=>$request->email]);

        if(empty($getExistUser)){

            $createpeerpartner = PeerPartner::create([

                'name' => $request->name,

                'email' => $request->email,

                'phone' => $request->phone,

                'address' => $request->address,

                'user_id' => $request->user_id,
                'parent'=> $request->parent_id,
                'pan_num'=>$request->pan_no,
                 'peer_type'=>'sub',
                'instagram' => $request->instagram,

                'instagram_page' => $request->instagram?$request->instagram_page:NULL,

                'instagram_follower' =>  $request->instagram?$request->instagram_follower:NULL,

                'facebook' => $request->facebook,

                'facebook_page' => $request->facebook?$request->facebook_page:NULL,

                'facebook_follower' =>  $request->facebook?$request->facebook_follower:NULL,

                'linkedin' => $request->linkedin,

                'linkedin_page' => $request->linkedin?$request->linkedin_page:NULL,

                'linkedin_follower' =>  $request->linkedin?$request->linkedin_follower:NULL,

            ]);



            if($createpeerpartner){

                $updateuser = User::where('id',$request->user_id)->update(['peer_partner'=>1,'user_type'=>'partner']);

                $status = true;

                $msg = "Application has been submitted." ;

                $user = User::findOrFail($request->user_id);
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(100);
                $token->save();

                return response()->json([
                    'success'=>true,
                    'message'=>$msg,
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
                        'phone' => !empty($user->phone)?$user->phone:""
                    ]
                ]);

            }



        }else{

         $status = false;

         $msg = "already applied." ;

         return response()->json([

            'success' => $status,

            'message' => $msg,
            'user' => ""

        ]);

        }



        

        

    }

    public function check_referral(Request $request)
    {
        $referral_code = $request->referral_code;
        $master = 'master';
        $defaultid = PeerPartner::where('code', $referral_code)->where('peer_type', $master)->where('peertype_approval', 1)->first('id');       

        if($defaultid != null){
            $master_id = $defaultid->id;

            return response()->json([
                'success'=>true,
                'message'=>'Peer code applied',
                'parent_id'=>$master_id
            ]);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'Invalid peer code',
                'parent_id'=>50
            ]);
        }
    }

    public function getUserLastUsedPeerCode($user_id){
        $user = User::find($user_id);
        if(!is_null($user)){
            $code = ($user->user_type=='partner' && is_null($user->used_referral_code)) ? $user->partner->code: $user->used_referral_code;
            return response()->json([
                'success'=>true,
                'message'=>'Referral code applied',
                'user_id'=>$user->id,
                'peer_code'=>$code
            ]);
        }

        return response()->json([
            'success'=>false,
            'message'=>'User Not Exist',
            'user_id'=>'',
            'peer_code'=>''
        ]);

    }

}
