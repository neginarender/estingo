<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\PeerPartner;
use App\User;
use Hash;
use DB;
use App\BusinessSetting;
use App\Notifications\EmailVerificationNotification;
use App\Notifications\MailPeerPartner;
use Illuminate\Support\Str;
use App\OrderReferalCommision;
use App\Category;
use App\Product;
use App\PeerSetting;
use App\ShortingHub;
use App\ProductStock;
use App\MappingProduct;
use App\OtpMapping;
use App\DOFO;
use Excel;
use App\PeerPartnerExport;
use App\AllPeerPartnerExport;

class PeerPartnerController extends Controller
{
    public function index(Request $request){

        //dd($request->all());

        $checkPeerStatus = DB::table('global_switch')->where('name','Peer')->first();
        $sort_search = null;
        $approved = null;
        $peer_partner = PeerPartner::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'partner')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%')->orWhere('phone', 'like', '%'.$sort_search.'%');
            })->pluck('id')->toArray();
            $peer_partner = $peer_partner->where(function($peer_partner) use ($sort_search,$user_ids){
                if(!empty($user_ids)){
                    $peer_partner->whereIn('user_id', $user_ids);
                }else{
                    $peer_partner->where('code', $sort_search);
                }
                
            });
        }
        if ($request->approved_status != null) {
            $approved = $request->approved_status;
            $peer_partner = $peer_partner->where('verification_status', $approved);
        }

        if($checkPeerStatus->access_switch == 0){
            $peer_partner->where('dofo_peer',0);
        }
        $peer_partner = $peer_partner->paginate(10);

        return view('frontend.peer_partner.index', compact('peer_partner','approved'));
    }

    public function create(Request $request){

    	if(Auth::check() && Auth::user()->user_type == 'admin'){
            flash(translate('Admin can not be a peer partner'))->error();
            return back();
        }elseif(Auth::check() && Auth::user()->peer_partner == 0){
            return view('frontend.peer_partner.create');
        }elseif(Auth::check() && Auth::user()->peer_partner == 1){

            $peer_partner = PeerPartner::where('user_id', Auth::user()->id)->first();
            if(!empty($peer_partner)){
                if($peer_partner->verification_status == 1){
                     flash(translate('You are already a registered Peer Partner'))->warning();
                     return back();
                }else{
                     flash(translate('Peer partner request is on process'))->warning();
                     return back();
                }
            } flash(translate('Something went wrong'))->error();
            return back();
        }
        else{
            flash(translate('You are not registered user'))->error();
            return back();
        }
    }

    public function store(Request $request){

         if($request->parent_id!=''){
             $p_id = $request->parent_id;
        }else{
           $defaultmaster = 'defaultpeer@rozana.in';
            $defaultid = PeerPartner::where('email', $defaultmaster)->first('id'); 
            $p_id = $defaultid->id;
        }
        $user = null;
        $peer_type = 'sub';
        DB::beginTransaction();

        try {

            $user = Auth::user();
            $user->peer_partner = 1;
            $user->save();

        if(PeerPartner::where('user_id', $user->id)->first() == null){

            $peer_partner = new PeerPartner;

            $peer_partner->user_id = $user->id;
            $peer_partner->name = $request->name;
            $peer_partner->peer_type = $peer_type;
            $peer_partner->parent = $p_id;
            $peer_partner->email = $request->email;
            $peer_partner->phone = $request->phone;
            $peer_partner->address = $request->address;
            $peer_partner->addressone = $request->addressone;
            $peer_partner->addresstwo = $request->addresstwo;
            $peer_partner->zone = $request->zone;
            $peer_partner->pan_num = $request->pannumber;
            $peer_partner->verification_status = 0;
            $peer_partner->added_by = auth()->user()->id;

            if($request->fb_account == 1){
                $peer_partner->facebook = 1;
                $peer_partner->facebook_page = $request->fb_page_name;
                $peer_partner->facebook_follower = $request->fb_follower;
            }

            if($request->instagram_account == 1){
                $peer_partner->instagram = 1;
                $peer_partner->instagram_page = $request->instagram_page_name;
                $peer_partner->instagram_follower = $request->instagram_follower;
            }

            if($request->linkedin_account == 1){
                $peer_partner->linkedin = 1;
                $peer_partner->linkedin_page = $request->linkedin_profile_name;
                $peer_partner->linkedin_follower = $request->linkedin_follower;
            }

            if($peer_partner->save()){
                auth()->login($user, false);
                if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
                    $user->email_verified_at = date('Y-m-d H:m:s');
                    $user->save();
                }
                else {
                    $user->notify(new EmailVerificationNotification());
                }

                DB::commit();

                if(Auth::check() && Auth::user()->user_type == 'admin'){
                    flash(translate('Peer Partner account been created successfully!'))->success();
                    return redirect()->route('peer_partner.index');
                }else{
                    flash(translate('Peer Partner account request been send successfully!'))->success();
                    return redirect()->route('dashboard');
                }
            }
            else{
                $peer_partner->delete();
                $peer_partner->user_type == 'customer';
                $user->peer_partner = 0;
                $peer_partner->save();
                DB::commit();
            }
        }

        } catch (Exception $e) {
             DB::rollback();
             flash(translate('Sorry! Something went wrong.'))->error();
             return back();
        }
    }

    public function updateApproved(Request $request)
    {

        DB::beginTransaction();

        try {
            $partner = PeerPartner::findOrFail($request->id);

            $user = User::find($partner->user_id);
            $getDOFO = DOFO::where('email',$user->email)->where('status','=',1)->first();
            $user_name = User::Where('id', $partner->user_id)->pluck('name')->first();

            if(!empty($user)){

                if($request->status != 0){
                
                    if($user->user_type!="staff"){
                        $user->user_type = 'partner';
                    }
                    
                    $user->save();

                    $partner->verification_status = $request->status;

                    $arr2 = str_split($user_name, 3);
                    $peer_name = $arr2[0];
                    $referral_code = strtoupper($peer_name.auth()->user()->id.Str::random(3));
                    // do {
                    //    $referral_code = strtoupper($peer_name.auth()->user()->id.Str::random(3));
                    // }  while ( DB::table(app()->make(\App\PeerPartner::class)->getTable())->where('code', $referral_code)->exists());

                    $partner->code = $referral_code;
                    $partner->discount = 10;
                    $partner->commission = 5;
                    $partner->save();
                
            }else{
                $referral_code = "";
                $partner->verification_status = $request->status;
                $user->user_type = 'customer';

                if($partner->save()){
                    $user->save();
                }
            }

            DB::commit();
            $detail = array(
                'status'=>$request->status,
                'peercode'=>$referral_code
            );

                if($request->status==1 && empty($getDOFO)){
                $to = $user->phone;
                $from = "RZANA";
                // $tid  = "1707162443968348198"; 
                // $msg = "Dear ".$user->name.", Congratulations! ".$referral_code." is your unique Peer Partner Code. Share this code with your friends, family, and business associates so they can avail some spectacular year-round discounts. Enjoy shopping with Rozana.";
                $tid  = "1707164406313997895";
                $msg = "Dear ".$user->name.", Congratulations! ".$referral_code." is your unique Peer Partner Code. Share to earn points and avail spectacular discounts. Rozana.";
                mobilnxtSendSMS($to,$from,$msg,$tid);
                }
            
            //Mail to peer Partner
            if(empty($getDOFO)){

                $user->notify(new MailPeerPartner($detail));
            }
            
            return 1;

            }

        } catch (Exception $e) {
            DB::rollback();
            return 0;
        }
    }

    //22 may 2021
    public function updatePeerDiscount(Request $request)
    {
        $partner = PeerPartner::findOrFail($request->id);
        if(!empty($partner)){
               PeerPartner::where('id', $request->id)
               ->update([
                   'peer_discount' => $request->status
                ]);
            return 1;
        }else{
            return 0;
        }    
               
    }

    public function get_peerdiscount(Request $request){
          // echo '<pre>'; print_r($request->all()); die;
          $id = $request->peer_id;
          $peer_commission = $request->peer_commission;
            PeerPartner::where('id', $id)
               ->update([
                   'commission' => $peer_commission
                ]);        
          
          return back();
    }

    /*public function login($id)
    {
        $partner = PeerPartner::findOrFail(decrypt($id));
        $partner  = $partner->user;
        auth()->login($partner, true);
        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');

        setcookie('logged',encrypt(true),time()+60*60*24*30,'/');
        setcookie('auth',encrypt($user->id),time()+60*60*24*30,'/');
        session()->put('access_token',$tokenResult->accessToken);
        session()->put('user_id',$user->id);
        session()->put('user',json_encode($user));
        //location info
        //dd($user->partner);
        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$user->postal_code.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        setcookie('peer',$user->partner->code,time()+60*60*24*30,'/');
        setcookie('sid',encrypt($shortId['sorting_hub_id']),time()+60*60*24*30,'/');
        setcookie('pincode',$user->postal_code,time()+60*60*24*30,'/');
        setcookie('city_name',$user->city,time()+60*60*24*30,'/');
        // empty cart
       
        flash('Login successfully')->success();
        return redirect()->route('home');
    }*/

    public function login($id)
    {
        $partner = PeerPartner::findOrFail(decrypt($id));
        $code = $partner->code;
        $partner  = $partner->user;

        // auth()->login($partner, true);
        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');

        setcookie('logged',encrypt(true),time()+60*60*24*30,'/');
        setcookie('auth',encrypt($user->id),time()+60*60*24*30,'/');
        session()->put('access_token',$tokenResult->accessToken);
        session()->put('user_id',$partner->id);
        session()->put('user',json_encode($partner));
        //location info
        //dd($user->partner);
        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$partner->postal_code.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        setcookie('peer',$code,time()+60*60*24*30,'/');
        setcookie('sid',encrypt($shortId['sorting_hub_id']),time()+60*60*24*30,'/');
        setcookie('pincode',$partner->postal_code,time()+60*60*24*30,'/');
        setcookie('city_name',$partner->city,time()+60*60*24*30,'/');
        // empty cart
       
        flash('Login successfully')->success();
        return redirect()->route('home');
    }


    public function ban($id) {

        $partner = PeerPartner::findOrFail($id);
        if($partner->user->banned == 1) {
            $partner->user->banned = 0;
        } else {
            $partner->user->banned = 1;
        }

        $partner->user->save();
        return back();
    }

    public function destroy($id)
    {
        $partner = PeerPartner::findOrFail($id);
        User::destroy($partner->user->id);
        if(PeerPartner::destroy($id)){
            flash(translate('Peer Partner has been deleted successfully'))->success();
            return redirect()->route('peer_partner.index');
        }
        else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function profile_modal(Request $request){

        $partner = PeerPartner::findOrFail($request->id);
        return view('frontend.peer_partner.profile_modal', compact('partner'));
    }

    public function referral_history(Request $request){

        $userIds = [];
        $peer= PeerPartner::where('user_id',auth()->user()->id)->first();
        $peer_type="sub-peer";
        if(!is_null($peer)){
            $userIds[] = $peer->user_id;
            if($peer->peer_type=='master'){
                $peer_type="master-peer";
                $userIds = PeerPartner::where('parent',$peer->id)->pluck('user_id');
            }
        }
        $ReferralOrders = DB::table('orders')
        ->join('order_referal_commision','orders.id','=','order_referal_commision.order_id')
        ->whereIn('order_referal_commision.partner_id', $userIds)
        ->where('orders.log',0)
        ->select('orders.id','orders.code','orders.grand_total','orders.wallet_amount','orders.total_shipping_cost','orders.shipping_address','orders.referal_discount','orders.order_status','order_referal_commision.referal_commision_discount','order_referal_commision.wallet_status','order_referal_commision.refral_code','order_referal_commision.master_discount')
        ->orderBy('orders.id','desc')
        ->paginate();
        
       //$ReferralOrders = OrderReferalCommision::with(['order', 'user'])->where('partner_id', auth()->user()->id)->orderBy('id', 'DESC')->paginate(10);
       return view('frontend.peer_partner.referral_history', compact('ReferralOrders','peer_type'));
    }

    //14may2020

    public function create_peer(Request $request)
    {    
        $categories = Category::all();
        $shorting_hub = ShortingHub::all();
        return view('frontend.peer_partner.create_peerdiscount_map', compact('categories','shorting_hub'));
    }
    public function get_map_otp(Request $request){

       $codeval = $request->codeval; 
       $otp  = random_int(1000, 9999);

       $map_product = new OtpMapping;
       $map_product->code = $codeval;
       $map_product->otp = $otp; 
       if($map_product->save()){
            // $to = "8929079351";
            $to = "8368788699";
            $from = "RZANA";
            $tid  = "1707162788862198349"; 

            $msg = "Dear Founder, This is an auto-generated OTP ".$otp." to initiate the change in discounts of mapping products. Team Rozana";
            mobilnxtSendSMS($to,$from,$msg,$tid);
            // dd(mobilnxtSendSMS($to,$from,$msg,$tid));
            return 1;
       }else{
            return 0;
       }
    }

    public function set_map_otp(Request $request){
       $codeval = $request->codeval; 
       $otp = $request->otp; 

       $map_product = OtpMapping::where('code', $codeval)->select('otp')->first();    
       if(!empty($map_product)){
         if($map_product->otp == $otp){
            return 1;
         }else{
            return 0;
         }   
       }else{
            return 0;
       }
    }

    public function store_peer_discount(Request $request){
        // echo "<pre>";
        // print_r($request->all());
        // die;
        $sortinghub_id = $request->hub_ids[0];
        $customer_map = $request->customer_discount;
        $peer_map = $request->peer_discount;
        $company_map = $request->company_margin;
        if(!empty($request->products)){
            try {

                if(!empty($request->flash_deal)){
                    $flashdeal = MappingProduct::whereIn('product_id',$request->flash_deal)
                    ->where('sorting_hub_id',$sortinghub_id)
                    ->update(['flash_deal'=>1]);
                }
                
                foreach($request->products as $key => $product) {  

                  $peer_discount_check = PeerSetting::where('product_id', '"'.$product.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $sortinghub_id. '"]\')')->latest('id')->first(); 


                 $product_s = Product::where('id', $product)->first();
                 $variant_prices = ProductStock::where('product_id', $product)->latest('price')->first();
                 if(!empty($sortinghub_id)){
                 
                    $products =  MappingProduct::where(['sorting_hub_id'=>$sortinghub_id,'product_id'=>$product])->latest()->first();
                    $product_s = Product::where('id', $product)->first();

                    $unit_price = $products['purchased_price'];
                    $variant_price = $products['selling_price'];
                    if($unit_price == 0 || $variant_price == 0){
                        $unit_price = $product_s->unit_price;
                        $variant_price = $variant_prices['price'];
                    } 
                 }else{
                        $unit_price = $product_s->unit_price;
                        $variant_price = $variant_prices['price'];
                 }

                 $unitprice_tax = ($unit_price*100)/(100+$product_s->tax);
                 $unitprice_tax = ($unitprice_tax*$product_s->tax)/100;
                 $variant_price_tax = ($variant_price*100)/(100+$product_s->tax);
                 $variant_price_tax = ($variant_price_tax*$product_s->tax)/100;

                 $last_margin = $peer_map + $company_map;
                 $margin = 100 - $last_margin;

                if($product_s->tax!=0){
                         if($customer_map!=0){
                            $customer_price = ($variant_price*$customer_map)/100;
                            if($customer_price!=0){
                                $price_after_discount = $variant_price - $customer_price;
                                $customer_price_tax =  ($price_after_discount*100)/(100+$product_s->tax);
                                $customer_price_tax = ($customer_price_tax*$product_s->tax)/100;
                                if($customer_price_tax > $unitprice_tax){
                                    $taxes = $customer_price_tax - $unitprice_tax;
                                    $difference = $price_after_discount - $taxes;
                                    if($customer_price_tax > $unitprice_tax){
                                        $main_price = $difference - $unit_price;
                                        $peer_price = round(($main_price*$peer_map)/100,2);  
                                        $master_price = round(($main_price*$company_map)/100,2);  
                                        $rozana_margin = round(($main_price*$margin)/100,2);
                                    }else{
                                        $main_price = 0;
                                        $peer_price = round(($main_price*$peer_map)/100,2);  
                                        $master_price = round(($main_price*$company_map)/100,2);  
                                        $rozana_margin = round(($main_price*$margin)/100,2);
                                    }
                                    
                                }else{
                                     $taxes = 0;
                                     $difference = $price_after_discount - $taxes;
                                     if($customer_price_tax > $unitprice_tax){
                                        $main_price = $difference - $unit_price;
                                    }else{
                                        $main_price = 0;
                                        $peer_price = 0;
                                        $master_price = 0;
                                        $rozana_margin = 0;
                                    }
                                }
                                //$peer_price = 0;
                                //$master_price = 0;
                                //$rozana_margin = 0;
                            }
                         }else{
                            $customer_price = 0;
                            $price_difference = $variant_price - $unit_price;
                            $tax_difference = $variant_price_tax - $unitprice_tax;

                            $main_price = $price_difference - $tax_difference;
                            $peer_price = round(($main_price*$peer_map)/100,2);  
                            $master_price = round(($main_price*$company_map)/100,2);  
                            $rozana_margin = round(($main_price*$margin)/100,2);
                         }
                    }else{
                        if($customer_map!=0){
                            $customer_price = ($variant_price*$customer_map)/100;
                            $price_after_discount = $variant_price - $customer_price;
                            if($price_after_discount > $unit_price){
                                $price_difference = $price_after_discount - $unit_price;
                                $main_price = $price_difference;
                                $peer_price = round(($main_price*$peer_map)/100,2);  
                                $master_price = round(($main_price*$company_map)/100,2);  
                                $rozana_margin = round(($main_price*$margin)/100,2);
                            }else{
                                $customer_price = ($variant_price*$customer_map)/100;
                                $peer_price = 0;
                                $master_price = 0;
                                $rozana_margin = 0;
                            }    
                      }else{
                            $customer_price = 0;
                            $price_difference = $variant_price - $unit_price;
                            $main_price = $price_difference;
                            $peer_price = round(($main_price*$peer_map)/100,2);  
                            $master_price = round(($main_price*$company_map)/100,2);  
                            $rozana_margin = round(($main_price*$margin)/100,2);
                      } 
                    }   

                    if($rozana_margin==0){
                        if($unit_price>$price_after_discount){
                             $margin = $unit_price - $price_after_discount;
                              $m="-";
                                $margin = $m."".$margin;
                        }else{
                             $margin = 0;
                        }
                    }else{
                        $margin = $rozana_margin;
                    }  

                        //16-10-2021

                        $data = PeerSetting::where('product_id', '"'.$product.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $sortinghub_id. '"]\')')->latest('id')->first();

                        if($data != NULL){
                            DB::table('peer_settings')
                        ->where('id', $data->id)
                        ->update(['status' => 0]);
                        }
                        

                                         
                        $mapping_product = new PeerSetting;
                        $mapping_product->sorting_hub_id = json_encode($request->hub_ids);
                        $mapping_product->category_id = json_encode($request->category_ids);
                        $mapping_product->sub_category_id = json_encode($request->subcategory_ids);
                        $mapping_product->product_id = json_encode($product);
                        $mapping_product->discount = json_encode($request->discount);
                        $mapping_product->peer_discount = json_encode($request->peer_discount);
                        $mapping_product->customer_discount = json_encode($request->customer_discount);
                        $mapping_product->company_margin = json_encode($request->company_margin);

                        $mapping_product->customer_off = $customer_price;
                        $mapping_product->peer_commission = $peer_price;
                        $mapping_product->master_commission = $master_price;
                        $mapping_product->rozana_margin = $rozana_margin;
                        $mapping_product->margin = $margin;

                        //16-10-2021
                        $mapping_product->status = '1';

                        
                        if($mapping_product->save()){
                            continue;
                        }else{
                            break;
                        }
                      }  
             
                flash(translate('Discount has been inserted successfully'))->success();
               return redirect()->back();
            } catch (Exception $e) {
                flash(translate('Something went wrong'))->error();
                return redirect()->back();
            }
        }else{
            flash(translate('Please select products'))->error();
                return redirect()->back();
        }    
    }
     //27 may 2021
    public function showall_peer_commission(Request $request)
    { 
        $start_date = '';
        $end_date = '';
        $from_date = date('Y-m-d');
        $to_date   = date('Y-m-d');

        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount, refral_code, created_at, id')->groupBy('refral_code')->get();         
        if(!empty($all_orders)){
              $all_orders = $all_orders;
        }else{
            $all_orders = array();
        }  
        return view('frontend.peer_partner.showall_peer_commission', compact('all_orders', 'distributorids', 'start_date', 'end_date'));
    } 

    public function showall_peer_commission_by_date(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;
        
        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount, refral_code, created_at, id')->groupBy('refral_code')->get();  
        if(!empty($all_orders)){
              $all_orders = $all_orders;
        }else{
            $all_orders = array();
        }       
        return view('frontend.peer_partner.showall_peer_commission', compact('all_orders', 'distributorids', 'start_date', 'end_date'));
    }  

    //

     public function showall_subpeer($id)
    { 
        $id = decrypt($id);
        $all_peers = PeerPartner::where('parent', $id)->get();
        $allsub_peers = PeerPartner::where('parent', 0)->where('peer_type', '!=',  'master')->get(); 

        return view('frontend.peer_partner.showall_subpeer', compact('all_peers', 'allsub_peers' ,'id'));
    } 

    public function add_subpeer(Request $request, $id)
    {      
     
        $id = decrypt($id);
        $peer_id = $request->input('sub_id');

        
        $allsub_peers = PeerPartner::where('peer_type', '!=' , 'master')->where('parent', 0)->get();

       $update_sub = PeerPartner::where('id', $peer_id)->update([
                                            'peer_type' => 'sub',
                                            'parent' => $id
                                        ]);
       $all_peers = PeerPartner::where('parent', $id)->get();
        if(!empty($update_sub)){
             flash(translate('Sub peer added successfully'))->success();
        }else{
            flash(translate('Something went wrong'))->error();
        }       
         return view('frontend.peer_partner.showall_subpeer', compact('all_peers', 'allsub_peers' ,'id'));
    }  

   public function subpeer_destroy(Request $request, $id)
    {      
      $update_sub = PeerPartner::where('id', $id)->update([
                                            'parent' => ''
                                        ]);
        if($update_sub != null){
            flash(translate('Sub peer has been deleted successfully'))->success();
            return back();
        }else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
     }
     public function updatesubApproved(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $type = $request->type;

        if($type=='sub'){
            $defaultmaster = 'defaultpeer@rozana.in';
            $defaultid = PeerPartner::where('email', $defaultmaster)->first('id');
            $d_id = $defaultid->id;

            $subpeer_change = PeerPartner::where('parent', $id)->select('id')->get();
            foreach($subpeer_change as $key => $row){
                $update_parent = PeerPartner::where('id', $row->id)->update([
                                            'parent' => $d_id
                                        ]);
            }
        }else{
            $d_id = '0';
        }
        

        $update_sub = PeerPartner::where('id', $id)->update([
                                            'peertype_approval' => $status,
                                            'peer_type' => $type,
                                            'parent' => $d_id
                                        ]);
        if($update_sub != null){
            return 1;
        }else{
            return 0;
        }
    }
    public function check_referral(Request $request)
    {
        $referral_code = $request->referral_code;
        $master = 'master';
        $defaultid = PeerPartner::where('code', $referral_code)->where('peer_type', $master)->where('peertype_approval', 1)->first('id');       

        if($defaultid != null){
            $master_id = $defaultid->id;
            return $master_id;
        }else{
            return 0;
        }
    }

    //11-11-2021 - custom code
    public function custom_code(Request $request){
        
        if($request->custom_code != null){
            $len = strlen($request->custom_code);
            if($len < 5 || $len > 9){
                flash(translate('Required 5 to 9 characters.'))->error();
                return back();
            }
            $code = $request->custom_code;
        }else{
            flash(translate('Invalid Code.'))->error();
            return back();
        }


        $id = $request->partner_id;
        $peerPartner = PeerPartner::findOrFail($id);
        $user = User::find($peerPartner->user_id);

        if($peerPartner){
            $code = count(PeerPartner::where('code',$code)->get());
            if($code == 0){
                PeerPartner::where('id', $id)
                        ->update([
                         'code' => $request->custom_code
                        ]);

                //SEND SMS
                $detail = array(
                    'status'=>1,
                    'peercode'=>$request->custom_code
                );

                    $referral_code = $request->custom_code;
                    $to = $user->phone;
                    $from = "RZANA";
                    // $tid  = "1707162443968348198"; 
                    // $msg = "Dear ".$user->name.", Congratulations! ".$request->custom_code." is your unique Peer Partner Code. Share this code with your friends, family, and business associates so they can avail some spectacular year-round discounts. Enjoy shopping with Rozana.";
                    $tid  = "1707164406313997895";
                    $msg = "Dear ".$user->name.", Congratulations! ".$referral_code." is your unique Peer Partner Code. Share to earn points and avail spectacular discounts. Rozana.";
                    mobilnxtSendSMS($to,$from,$msg,$tid);
                   
                
                //Mail to peer Partner
                $user->notify(new MailPeerPartner($detail));
                
                flash(translate('Code has been updated successfully'))->success();
                return back();
            }else{
                flash(translate('Code already exists.'))->error();
                return back();
            }
            
        }else{
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    //13-11-2021
    public function export(){
       $status = $_GET['status'];
       if($status == ""){
            $status = NULL;
       }
       ini_set('max_execution_time', -1);
        return Excel::download(new PeerPartnerExport($status), 'peerpartner.xlsx');
    }

    public function allexport(){       
       ini_set('max_execution_time', -1);
        return Excel::download(new AllPeerPartnerExport(), 'allpeerpartner.xlsx');
    }

    public function createPartnerByAdmin(){
        return view('frontend.peer_partner.create_partner_admin');
     }
 
     public function storePartnerCreateByAdmin(Request $request){
         //dd($request->all());
         $request->validate([
             'name'=>'required',
             'phone'=>'required|digits_between:10,10|numeric',
             'address'=>'required',
             'city'=>'required',
             'pincode'=>'required',
             'state'=>'required',
             'block_id'=>'required'
         ]);
 
         $check = User::where('phone',$request->phone)->first();
         if(!is_null($check)){
             flash('Email or Phone already exist');
             return back();
         }
         try{
             DB::beginTransaction();
            
             $user = new \App\User;
             $user->name = $request->name;
             $user->phone = $request->phone;
             $user->user_type = 'partner';
             $user->peer_partner = 1;
             $user->password = Hash::make('Rozana@123');
             $user->city = $request->city;
             $user->postal_code = $request->pincode;
             $user->address = $request->address;
             $user->state = $request->state;
             $user->country = "India";
             $user->status = 1;
             $user->email_verification = 1;
 
             if($user->save()){
                 // Creating dummy email if user has no email address
                 $email = (empty($request->email)) ? $user->id."@xyz.com": $request->email;
                 \App\User::where('id',$user->id)->update(['email'=>$email]);
                 // create peer partner
                 $parent = (empty($request->parent_id)) ? 50 : $request->parent_id;
                 $partner = new \App\PeerPartner;
                 $partner->name = $request->name;
                 $partner->email = $email;
                 $partner->phone = $request->phone;
                 $partner->peer_type = 'sub';
                 $partner->user_id = $user->id;
                 $partner->address = $request->address;
                 $partner->parent = $parent;
                 $partner->zone = $request->zone;
                 $partner->save();
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
                 $address->village = $request->village;
                 $address->save();
 
             }
             DB::commit();
             flash('Peer Partner added successfully')->success();
             return back();
 
         }
         catch(\Exception $e){
             DB::rollabck();
             flash('Something went wrong');
             return back();
         }
         
 
 
     }


    public function editPartnerByAdmin($id){
        $partner = \App\PeerPartner::find(decrypt($id));
        $user = $partner->user;
        $address = \App\Address::where('user_id',$user->id)->first();
        $parent_code = "";
        if($partner->parent!=0){
            $parent_code = \App\PeerPartner::find($partner->parent)->code;
        }   
        return view('frontend.peer_partner.edit_partner_admin',compact('partner','user','address','parent_code'));
     }

     public function updatePartnerCreateByAdmin(Request $request){


      

        $request->validate([
            'name'=>'required',
            'phone'=>'required|digits_between:10,10|numeric',
            'address'=>'required',
            'city'=>'required',
            'pincode'=>'required',
            'state'=>'required',
            'block_id'=>'required'
        ]);

        $check = User::where('phone',$request->phone)->first();
        if(!is_null($check)){
            if($request->user_id!=$check->id){
                flash('Email or Phone already exist');
                return back();
            }
            
        }
        try{
            DB::beginTransaction();
           
            $user = \App\User::find($request->user_id);
            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->user_type = 'partner';
            $user->peer_partner = 1;
            $user->password = Hash::make('Rozana@123');
            $user->city = $request->city;
            $user->postal_code = $request->pincode;
            $user->address = $request->address;
            $user->state = $request->state;
            $user->country = "India";
            $user->status = 1;
            $user->email_verification = 1;

            if($user->save()){
                // Creating dummy email if user has no email address
                $email = (empty($request->email)) ? $user->id."@xyz.com": $request->email;
                \App\User::where('id',$user->id)->update(['email'=>$email]);
                // create peer partner
                $parent = (empty($request->parent_id)) ? 50 : $request->parent_id;
                $partner = \App\PeerPartner::where('user_id',$user->id)->first();
                $partner->name = $request->name;
                $partner->email = $email;
                $partner->phone = $request->phone;
                $partner->peer_type = 'sub';
                $partner->user_id = $user->id;
                $partner->address = $request->address;
                $partner->parent = $parent;
                $partner->zone = $request->zone;
                $partner->save();
                // create an address in address table
                $address = \App\Address::where('user_id',$user->id)->first();
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
                $address->village = $request->village;
                $address->save();

            }
            DB::commit();
            flash('Peer Partner updated successfully')->success();
            return back();

        }
        catch(\Exception $e){
            DB::rollabck();
            flash('Something went wrong');
            return back();
        }
        


     }
}
