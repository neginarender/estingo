<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\UserCollection;
use App\User;
use Illuminate\Http\Request;
use App\PeerPartner;
use Carbon\Carbon;
use Validator;
use DB;
use Hash;


class UserController extends Controller
{
    public function info($id)
    {

        $user = DB::table('users')
        ->LeftJoin('peer_partners','users.id','=','peer_partners.user_id')
        ->where('users.id','=',$id) 
        ->select('users.*','peer_partners.address as peerAddresss','peer_partners.pan_num','peer_partners.instagram','peer_partners.instagram_page','peer_partners.instagram_follower','peer_partners.facebook','peer_partners.facebook_page','peer_partners.facebook_follower','peer_partners.linkedin','peer_partners.linkedin_page','peer_partners.linkedin_follower')
        ->get();
        
        return new UserCollection($user);
    }

    public function updateName(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'dob' => $request->dob,
            'gender' => $request->gender,
            'about_yourself' => $request->about_yourself,

        ]);

        $userData = DB::table('users')
        ->LeftJoin('peer_partners','users.id','=','peer_partners.user_id')
        ->where('users.id','=',$request->user_id) 
        ->select('users.*','peer_partners.address as peerAddresss','peer_partners.pan_num','peer_partners.instagram','peer_partners.instagram_page','peer_partners.instagram_follower','peer_partners.facebook','peer_partners.facebook_page','peer_partners.facebook_follower','peer_partners.linkedin','peer_partners.linkedin_page','peer_partners.linkedin_follower')
        ->get();

        $Detail = new UserCollection($userData);
        return response()->json([
            'success'=>true,
            'message' => 'Profile information has been updated successfully',
            'data' => $Detail
        ]);
    }


    public function lasfivedays()
    {
        $date = Carbon::now()->subDays(5);

  
        // $users = User::where('created_at', '>=', $date)->get();

        $users = DB::table('orders')
                ->join('orders', 'orders.id', '=', 'order_details.order_id')->where('created_at', '>=', $date)
                ->select('order_details.*', 'orders.code', 'order_details.product_id')->get();

        return $users;

  
        
    }

    

    public function enrolledCustomer(REQUEST $request)
    {

        if(isset($request->phone) && !empty($request->phone)){
            $checkphone = User::where('phone',$request->phone)->first();
            if($checkphone){
                return response()->json([
                    'success'=>false,
                    'message'=>'Phone number already exist.'
                ]);
            }
            
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'Phone number required.'
                ]);
        }

        // if(isset($request->email) && !empty($request->email)){
        //     $checkemail = User::where('email',$request->email)->first();
        //     if($checkemail){
        //         return response()->json([
        //             'success'=>false,
        //             'message'=>'Email already exist.'
        //         ]);
        //     }
            
        // }else{
        //     return response()->json([
        //         'success'=>false,
        //         'message'=>'Email required.'
        //         ]);
        // }

        if(isset($request->email) && !empty($request->email)){
            $checkemail = User::where('email',$request->email)->first();
            if($checkemail){
                return response()->json([
                    'success'=>false,
                    'message'=>'Email already exist.'
                ]);
            }
            
        }

            $str=$request->name;
            $avatar = substr("$str",0,1);

            $user = new \App\User;
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->avatar = $avatar;
            $user->peer_user_id = $request->peer_user_id;
            $user->postal_code = $request->pincode;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->address = $request->address;
            $user->block_id = $request->block_id;
            $user->country = "India";
            $user->password = Hash::make('12345678');
                      
            if($user->save()){
            $address = new \App\Address;
            $address->name = $request->name;
            $address->user_id = $user->id;
            $address->address = $request->address;
            $address->country = "India";
            $address->city = $request->city;
            $address->state = $request->state;
            $address->postal_code = $request->pincode;
            $address->phone = $request->phone;
            $address->block_id =$request->block_id;
            $address->village =$request->village;
            $address->save();
        
            return response()->json([
                'success'=>true,
                'message'=>'Enrollment Customer add successfully'
                ]);
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'Something went wrong'   
                ]);
            }
    }

    public function enrolledCustomerlist( $id)
    {
        // $peer_user_id = $request->peer_user_id;
        $customerall = new UserCollection(User::where('peer_user_id',$id)->get());
        $data =  User::where('peer_user_id',$id)->first();
       
        if($data==null){
            return response()->json([
                'success'=>true,
                'message' => "Enrollment Customer Not Found",
                'status' => 404,
            ]);
        }
        return $customerall;
    }

    public function enrolledcustomerview($id)
    {
        $viewenrollcustomer = new UserCollection(User::where('id',$id)->get());
        return response()->json([
            'success'=>true,
            'status' => 200,
            'state'=> $viewenrollcustomer   
        ]);

    }
    public function createPeerPartner(REQUEST $request){

        $check = User::where('phone',$request->phone)->first();
        if(!is_null($check)){
            return response()->json([
                'success'=>false,
                'message'=>'User already exist',
                "user"=>null
            ]);
        }
        try{
            DB::beginTransaction();
            $user = new \App\User;
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->user_type = 'partner';
            $user->peer_partner = 1;
            $user->password = Hash::make($request->password);
            $user->city = $request->city;
            $user->postal_code = $request->pincode;
            $user->address = $request->address;
            $user->state = $request->state;
            $user->country = "India";
            $user->status = 0;
            $user->email_verification = 1;
            
        if($user->save()){

            // Creating dummy email if user has no email address
            $email = (empty($request->email)) ? $user->id."@xyz.com": $request->email;
            \App\User::where('id',$user->id)->update(['email'=>$email]);
            // create peer partner
            $parent = (empty($request->parent_id)) ? 50 : $request->parent_id;

            $createpeerpartner = PeerPartner::create([

                'name' => $request->name,
                'email' => $email,
                'phone' => $request->phone,
                'address' => $request->address,
                'user_id' => $user->id,
                'parent'=> $parent,
                'pan_num'=>$request->pan_no,
                'peer_type'=>'sub',
                "zone"=>$request->zone,
                'instagram' => $request->instagram,
                'instagram_page' => $request->instagram?$request->instagram_page:NULL,
                'instagram_follower' =>  $request->instagram?$request->instagram_follower:NULL,
                'facebook' => $request->facebook,
                'facebook_page' => $request->facebook?$request->facebook_page:NULL,
                'facebook_follower' =>  $request->facebook?$request->facebook_follower:NULL,
                'linkedin' => $request->linkedin,
                'linkedin_page' => $request->linkedin?$request->linkedin_page:NULL,
                'linkedin_follower' =>  $request->linkedin?$request->linkedin_follower:NULL,
                'pincode'=>$request->pincode

            ]);



            if($createpeerpartner){

                //$updateuser = User::where('id',$request->user_id)->update(['peer_partner'=>1,'user_type'=>'partner']);
                // create an address in address table
                $address = new \App\Address;
                $address->name = $request->name;
                $address->user_id = $user->id;
                $address->address = $request->address;
                $address->country = "India";
                $address->city = $request->city;
                $address->state = $request->state;
                $address->postal_code = $request->pincode;
                $address->phone = $request->phone;
                $address->set_default = 1;
                $address->block_id = $request->block_id;
                $address->village = $request->panchayat;
                $address->save();

                $status = true;

                $msg = "Application has been submitted." ;

                $user = User::findOrFail($user->id);
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(100);
                $token->save();
                DB::commit();
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
                        'phone' => $user->phone
                    ]
                ]);

            }
        }
           
        }
        catch(\Exception $e){
            echo $e;
            DB::rollback();
            info($e);
            return response()->json([
                'success'=>false,
                'message'=>'Something went wrong',
                'user'=>null
            ]);
        }



    }

    public function updatePeerPartner(REQUEST $request){

        // $check = User::where('phone',$request->phone)->first();
        // if(!is_null($check)){
        //     return response()->json([
        //         'success'=>false,
        //         'message'=>'User already exist',
        //         "user"=>null
        //     ]);
        // }
        try{
            DB::beginTransaction();
            $user =\App\User::find($request->user_id);
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->user_type = 'partner';
            $user->peer_partner = 1;
            //$user->password = Hash::make($request->password);
            $user->city = $request->city;
            $user->postal_code = $request->pincode;
            $user->address = $request->address;
            $user->state = $request->state;
            $user->country = "India";
            $user->email_verification = 1;
            $user->is_old = 0;
            
        if($user->save()){

            // Creating dummy email if user has no email address
            $email = (empty($request->email)) ? $user->id."@xyz.com": $request->email;
            \App\User::where('id',$user->id)->update(['email'=>$email]);
            // create peer partner
            $parent = (empty($request->parent_id)) ? 10882 : $request->parent_id;

            $createpeerpartner = PeerPartner::updateOrCreate([
                'user_id'=>$request->user_id
            ],
            [

                'name' => $request->name,
                'email' => $email,
                'phone' => $request->phone,
                'address' => $request->address,
                'user_id' => $user->id,
                'parent'=> $parent,
                'pan_num'=>$request->pan_no,
                'peer_type'=>'sub',
                "zone"=>$request->zone,
                'instagram' => $request->instagram,
                'instagram_page' => $request->instagram?$request->instagram_page:NULL,
                'instagram_follower' =>  $request->instagram?$request->instagram_follower:NULL,
                'facebook' => $request->facebook,
                'facebook_page' => $request->facebook?$request->facebook_page:NULL,
                'facebook_follower' =>  $request->facebook?$request->facebook_follower:NULL,
                'linkedin' => $request->linkedin,
                'linkedin_page' => $request->linkedin?$request->linkedin_page:NULL,
                'linkedin_follower' =>  $request->linkedin?$request->linkedin_follower:NULL,
                'old'=>0,
                'pincode'=>$request->pincode

            ]);



            if($createpeerpartner){
                \App\Address::where('user_id',$request->user_id)->delete();
                //$updateuser = User::where('id',$request->user_id)->update(['peer_partner'=>1,'user_type'=>'partner']);
                // create an address in address table
                $address = new \App\Address;
                $address->name = $request->name;
                $address->user_id = $user->id;
                $address->address = $request->address;
                $address->country = "India";
                $address->city = $request->city;
                $address->state = $request->state;
                $address->postal_code = $request->pincode;
                $address->phone = $request->phone;
                $address->set_default = 1;
                $address->block_id = $request->block_id;
                $address->village = $request->panchayat;
                $address->save();

                $status = true;

                $msg = "Application has been submitted." ;

                $user = User::findOrFail($user->id);
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addWeeks(100);
                $token->save();
                DB::commit();
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
                        'phone' => $user->phone
                    ]
                ]);

            }
        }
           
        }
        catch(\Exception $e){
            DB::rollback();
            //info($e);
            return response()->json([
                'success'=>false,
                'message'=>'Something went wrong',
                'user'=>null
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
                'parent_id'=>10882
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

    public function getPeerAddress(Request $request){
        $peercode  = $request->peercode;
        $check = \App\PeerPartner::where('code',$peercode)->first();
        //$address[] = [];
        if(!is_null($check)){
            $user = \App\User::find($check->user_id);
            $adres = \App\Address::where('user_id',$user->id)->orderBy('id','desc')->first();
            $block = \App\Block::find($adres->block_id);
            $block_name = "";
            if(!is_null($block)){
                $block_name= $block->name;
            }
            $address[] = [
                'name'=>$user->name,
                'user_id'=>$user->id,
                'address'=>$user->address,
                'country'=>$user->country,
                'state'=>$user->state,
                'postal_code'=>$user->postal_code,
                'phone'=>$user->phone,
                'zone'=>$check->zone,
                'block'=>$block_name,
                'panchayat'=>$adres->village,
                'city'=>$adres->city
            ];
                return response()->json([
                    'success'=>true,
                    'data'=>$address
                ]
                );
        }
        return response()->json([
            'success'=>false,
            'data'=>[(object) null]
        ]);
    }   

    public function createUserPeerPartnerAtOnce(){
        
    }

    public function createUserBySelf(Request $request){
        
        if(!is_null($request->email)){
            $userEmail = User::where('email',$request->email)->get();
            if(count($userEmail) > 0){
                return response()->json([
                    'success'=>false,
                    'message'=>'Email already exist.'
                ]);
            }
        }
            
       
            

            
            $userPhone = User::where('phone',$request->phone)->get();
            if(count($userPhone) > 0){
                return response()->json([
                    'success'=>false,
                    'message'=>'Phone number already exist.'
                ]);
            }

            $checkpeer = \App\PeerPartner::where('code',$request->peer_code)->first();
            if(is_null($checkpeer)){
                return response()->json([
                    'success'=>false,
                    'message'=>'Peercode not exist.'
                ]);
            }

        

          $check = User::where('phone',$request->phone)->where('email',$request->email)->first();
          $checkk = User::where('email',$request->email)->first();

          $str=$request->name;
          $avatar = substr("$str",0,1);

          $user = new \App\User;
          $user->name = $request->name;
          $user->phone = $request->phone;
          $user->email = $request->email;
          $user->password = Hash::make($request->password);
          $user->avatar = $avatar;
          $user->peer_user_id = $checkpeer->user_id;
          $user->postal_code = $request->pincode;
          $user->city = $request->city;
          $user->state = $request->state;
          $user->address = $request->address;
          $user->block_id = $request->block_id;
          $user->country = "India";
          
                    
          if($user->save()){
          $address = new \App\Address;
          $address->name = $request->name;
          $address->user_id = $user->id;
          $address->address = $request->address;
          $address->country = "India";
          $address->city = $request->city;
          $address->state = $request->state;
          $address->postal_code = $request->pincode;
          $address->phone = $request->phone;
          $address->block_id=$request->block_id;
          $address->village = $request->village;
          $address->save();

          return response()->json([
              'success'=>true,
              'message'=>'Registered successfully'
              ]);
          }else{
              return response()->json([
                  'success'=>false,
                  'message'=>'Something went wrong'   
              ]);
          }
    }

    public function updateUserBySelf(Request $request){
    
        // $userEmail = User::where('email',$request->email)->get();
        //       if(count($userEmail) > 0){
        //           return response()->json([
        //               'success'=>false,
        //               'message'=>'Email already exist.'
        //           ]);
        //       }
  
            //   $userPhone = User::where('phone',$request->phone)->get();
            //   if(count($userPhone) > 0){
            //       return response()->json([
            //           'success'=>false,
            //           'message'=>'Phone number already exist.'
            //       ]);
            //   }
  
              $checkpeer = \App\PeerPartner::where('code',$request->peer_code)->first();
              if(is_null($checkpeer)){
                  return response()->json([
                      'success'=>false,
                      'message'=>'Peercode not exist.'
                  ]);
              }
  
          
  
           // $check = User::where('phone',$request->phone)->where('email',$request->email)->first();
           // $checkk = User::where('email',$request->email)->first();
  
            $str=$request->name;
            $avatar = substr("$str",0,1);
  
            $user =  \App\User::find($request->user_id);
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            //$user->password = Hash::make($request->password);
            $user->avatar = $avatar;
            $user->peer_user_id = $checkpeer->user_id;
            $user->postal_code = $request->pincode;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->address = $request->address;
            $user->block_id = $request->block_id;
            $user->block_name = $request->block_name;
            $user->country = "India";
            $user->is_old=0;
            
                      
            if($user->save()){
            \App\Address::where('user_id',$request->user_id)->delete();
            $address = new \App\Address;
            $address->name = $request->name;
            $address->user_id = $user->id;
            $address->address = $request->address;
            $address->country = "India";
            $address->city = $request->city;
            $address->state = $request->state;
            $address->postal_code = $request->pincode;
            $address->phone = $request->phone;
            $address->block_id=$request->block_id;
            $address->village = $request->village;
            $address->block_name = $request->block_name;
            $address->save();
  
            return response()->json([
                'success'=>true,
                'message'=>'Enrollment Customer add successfully'
                ]);
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'Something went wrong'   
                ]);
            }
      }

      public function updatePeerAddressByCallCenter(Request $request){

            try{
                DB::beginTransaction();
                $user =\App\User::find($request->user_id);
               //dd($request->all());
                $user->city = $request->city;
                $user->postal_code = $request->pincode;
                $user->address = $request->address;
                $user->state = $request->state;
                $user->country = "India";
                $user->status = 1;
                $user->email_verification = 1;
                $user->is_old = 0;
                
            if($user->save()){
    
                // Creating dummy email if user has no email address
                // $email = (empty($request->email)) ? $user->id."@xyz.com": $request->email;
                // \App\User::where('id',$user->id)->update(['email'=>$email]);
                // // create peer partner
                // $parent = (empty($request->parent_id)) ? 10882 : $request->parent_id;
    
                $createpeerpartner = PeerPartner::updateOrCreate([
                    'user_id'=>$request->user_id
                ],
                [
    
                    //'name' => $request->name,
                    //'email' => $email,
                    //'phone' => $request->phone,
                    'address' => $request->address,
                    'user_id' => $user->id,
                    //'parent'=> $parent,
                    //'pan_num'=>$request->pan_no,
                    //'peer_type'=>'sub',
                    "zone"=>$request->zone,
                    
                    'old'=>0,
                    'pincode'=>$request->pincode
    
                ]);
    
    
    
                if($createpeerpartner){
                    \App\Address::where('user_id',$request->user_id)->delete();
                    //$updateuser = User::where('id',$request->user_id)->update(['peer_partner'=>1,'user_type'=>'partner']);
                    // create an address in address table
                    $address = new \App\Address;
                    $address->name = $user->name;
                    $address->user_id = $user->id;
                    $address->address = $request->address;
                    $address->country = "India";
                    $address->city = $request->city;
                    $address->state = $request->state;
                    $address->postal_code = $request->pincode;
                    $address->phone = $user->phone;
                    $address->set_default = 1;
                    $address->block_id = $request->block_id;
                    $address->village = $request->village;
                    $address->save();
    
                    $status = true;
    
                    
                    DB::commit();
                    return response()->json([
                        'success'=>true,
                        'message'=>"Address updated successfully"
    
                    ]);
                }
            }
               
            }
            catch(\Exception $e){
                DB::rollback();
                dd($e);
                return response()->json([
                    'success'=>false,
                    'message'=>'Something went wrong',
                    'user'=>null
                ]);
            }
    
    
    
        
      }

}
