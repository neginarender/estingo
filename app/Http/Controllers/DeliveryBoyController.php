<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Distributor;
use App\Cluster;
use App\User;
use App\ShortingHub;
use App\DeliveryBoy;
use App\Staff;
use Auth;
use App\Order;
use App\Product;
use Session;
use DB;
use Mail;
//use CoreComponentRepository;
use App\Traits\OrderTrait;
use App\OrderReferalCommision;
use App\PeerPartner;
use App\ReferalUsage;
use PDF;
use Hash;
use App\Wallet;
use Excel;
use Illuminate\Support\Str;
use App\DeliveryBoyOrderExport;
use App\OtpOrderCancel;
use App\CancelLog;
use App\OrderDetail;

class DeliveryBoyController extends Controller
{
    use OrderTrait;
    //
    public function index()
    {
        $checkSwitch = DB::table('global_switch')->where('name','Delivery Boy')->first();
        if(auth()->user()->staff->role->name == "Sorting Hub" || auth()->user()->staff->role->name == "Sorting Hub Manager"){
            $sorting_hub_id = (auth()->user()->staff->role->name == "Sorting Hub Manager") ? auth()->user()->sortinghubmanager->sorting_hub_id: Auth::user()->id;
            if($checkSwitch->access_switch == 0){
                $deliveryboy = DeliveryBoy::where(['sorting_hub_id' => $sorting_hub_id,'dofo_status'=>0])->get();

            }elseif($checkSwitch->access_switch == 1){
                $deliveryboy = DeliveryBoy::where('sorting_hub_id',$sorting_hub_id)->get();
            }
            
            // $distributors = Distributor::where('sorting_hub_id', auth()->user()->id)->get();

        }elseif(auth()->user()->user_type == "admin"){
            $deliveryboy = DeliveryBoy::get();

        }
        
        return view('delivery_boy.index', compact(['deliveryboy']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $sorting_hub_name = ShortingHub::where('user_id',auth()->user()->id)->first();
        // dd($sorting_hub_name);
        return view('delivery_boy.create',compact(['sorting_hub_name']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        if(User::where('email', $request->email)->first() != null){
            flash(translate('Email already exists!'))->error();
            return back();
        }

        DB::beginTransaction();
        try{
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->user_type = 'staff';
            $user->email_verified_at = date("Y-m-d h:i:sa");
            $user->password = Hash::make('123456');

            if($user->save()){
                $deliveryboy = new DeliveryBoy;
                $deliveryboy->user_id = $user->id;
                $deliveryboy->cluster_hub_id = $request->cluster_hub_id;
                $deliveryboy->sorting_hub_id = $request->sorting_hub_id;
                $deliveryboy->phone = $request->phone;
                // $deliveryboy->area_id = $request->area_id;

                if($deliveryboy->save()){
                        $staff = new Staff;
                        $staff->user_id = $user->id;
                        $staff->role_id = 6;

                        if($staff->save()){
                        DB::commit();
                            flash(translate('Delivery Boy has been inserted successfully'))->success();
                            return redirect()->route('delivery_boy.index');
                        }else{
                            flash(translate('Somthing went wrong'))->error();
                            return redirect()->back();
                        }

                    }
            }
        }catch(Exception $e){
            DB::rollBack();
            flash(translate('Something went wrong'))->error();
            return redirect()->back();

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $sorting_hub_name = ShortingHub::where('user_id',auth()->user()->id)->first();
        $deliveryboy = DeliveryBoy::find($id);
        return view('delivery_boy.edit', compact(['deliveryboy','sorting_hub_name']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

         $deliveryboy = DeliveryBoy::find(decrypt($id));


         $deliveryboy->user->name = $request->name;
         $deliveryboy->phone = $request->phone;
         $deliveryboy->area_id = $request->area_id;
         $deliveryboy->user->save();

         if($deliveryboy->save()){
                flash(translate('Delivery has been update successfully'))->success();
                return redirect()->route('delivery_boy.index');
            }else{
                flash(translate('Somthing went wrong'))->error();
                return redirect()->back();
            }

     
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $deliveryboy = DeliveryBoy::find($id);
        Staff::where('user_id', $deliveryboy->user_id)->delete();
        User::destroy($deliveryboy->user_id);
        if(!empty($deliveryboy)){
            if($deliveryboy->delete()){
                 flash(translate('Delivery Boy has been delete successfully'))->success();
                 return redirect()->route('delivery_boy.index');
            }else{
                  flash(translate('Somthing went wrong'))->error();
                  return redirect()->back();
            }
        }
    }


    public function deliveryBoyOrders(Request $request)
    {   
        
        $id = Auth()->user()->id;
        $orderID = $this->orders($id);

        //CoreComponentRepository::instantiateShopRepository();

        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;

        
        $orders = DB::table('orders')
                    ->whereIn('orders.id', $orderID)
                    ->orderBy('code', 'desc')
                    //->join('order_details', 'orders.id', '=', 'order_details.order_id')
                    ->distinct('code');
                    

        if ($request->payment_type != null){
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        // if ($request->has('search')){
        //     $sort_search = $request->search;
        //     $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        // }
        $orders = $orders->paginate(15);


      
        return view('delivery_boy.your_order', compact('orders','payment_status','delivery_status', 'sort_search', 'admin_user_id'));
    }


    public function login($id)
    {
        $deliveryboy = DeliveryBoy::findOrFail(decrypt($id));

        $user  = $deliveryboy->user;

        auth()->login($user, true);

        return redirect()->route('admin.dashboard');
    }

    public function sendOtpVerification(REQUEST $request){
        $order = Order::where('id',$request->order_id)->first();
        $address = json_decode($order['shipping_address']);
        $phone = $address->phone;
        $otp  = random_int(1000, 9999);
        $order->otp = $otp;
        $order->save();
        $to = $phone;
        $from = "RZANA";
        // $tid  = "1707162443937828624"; 
        // $msg = "Your order ".$order->code." from Rozana is out for delivery, please share delivery code ".$otp." with the executive. For further queries call 9667018020 Thank you, Team Rozana";

        $tid  = "1707164406052847021"; 
        $msg = "Your order ".$order->code." from Rozana is out for delivery, please share delivery code ".$otp.". For help call 9667018020. Rozana";
        
        $response = array();
        if($order->payment_status=="paid"){
            $otp_request = mobilnxtSendSMS($to,$from,$msg,$tid);
        //dd($otp_request);
        
            if($otp_request->status == 'success'){
                
                $response['message'] = "OTP send successfully.";
                $response['status'] = 1;
            
            }else{
                $response['message'] = "something went wrong.";
                $response['status'] = 0;
            }
        }else{
            $response['message'] = "Order payment should be paid";
            $response['status'] = 0;

        }
        
        return response()->json($response);

    }


    public function verifyOTP(REQUEST $request){
        $OTP = $request->otp;
        $phone = $request->phone;
        $order_id = $request->order_id;
        $order = Order::findOrFail($order_id);
        //$verifyOTP = OTPverifyTwillio($OTP,$phone,'+91');
        $status = 'delivered';
        if($order->otp == $OTP){
            
            $order->delivery_viewed = '0';
            $order->save();
            foreach($order->orderDetails as $key => $orderDetail){
                $orderDetail->delivery_status = 'delivered';

                // if($status == 'delivered' && $order->payment_status == 'paid'){
                if(($status == 'delivered' && $order->payment_status == 'paid') || ($status == 'delivered' && $order->payment_type == "cash_on_delivery")){
                    // echo 'pp'; die;
                        $OrderReferalCommision = OrderReferalCommision::where('order_id', $order->id)->first();

                        if(!empty($OrderReferalCommision) && $OrderReferalCommision->wallet_status == 0){
                            $partner = PeerPartner::where('user_id', $OrderReferalCommision->partner_id)->first();

                             if(!empty($partner) && $partner->verification_status == 1 && $partner->parent != 0){
                                $select_partner = PeerPartner::where('id', $partner->parent)->first();
                                $master_partner = User::find($select_partner->user_id);
                                $mastertotal_balance = $master_partner->balance+$OrderReferalCommision->master_discount;
                                $master_partner->balance = $mastertotal_balance;
                                $master_partner->save();

                                $to = $select_partner->phone; 
                                $from = "RZANA";
                                $tid  = "1707163117111494696"; 

                                $msg = "Hello Rozana Master Peer, an order has been delivered to your customer using ".$partner->code." Peer Code. You have received ".$OrderReferalCommision->master_discount." points in your Rozana wallet. To review your points please log into your Rozana dashboard. Thank you for helping make Rozana a part of everyone’s daily lives. Feel free to reach out to us for any concerns or queries on +91 9667018020. Team Rozana";
                                    mobilnxtSendSMS($to,$from,$msg,$tid);
                             }   

                            if(!empty($partner) && $partner->verification_status == 1){
                                $peer_partner = User::find($partner->user_id);
                                $total_balance = $peer_partner->balance+$OrderReferalCommision->referal_commision_discount;
                                $peer_partner->balance = $total_balance;
                                
                                if($peer_partner->save()){

                                    $wallet = new Wallet;
                                    $wallet->user_id = $partner->user_id;
                                    $wallet->amount = $OrderReferalCommision->referal_commision_discount;
                                    $wallet->payment_method = 'referral';
                                    $wallet->payment_details = null;
                                    $wallet->order_id = $order_id;
                                    $wallet->save();

                                    $OrderReferalCommision->wallet_status = 1;
                                    $OrderReferalCommision->save();
                                }
                            }
                    }
                }

                $orderDetail->save();
            }
            $order->order_status = 'delivered';
            $order->save();
            flash(translate('OTP verified successfully.'))->success();
            return back();

        }else{
            flash(translate('Something went wrong'))->error();
            return back();

        }




    }

    public function delivery_export(Request $request){
        $id = Auth()->user()->id;
        $orderID = $this->orders($id);
        $deliveryStatus = empty($request->deliveryStatus)?NULL:$request->deliveryStatus;
        $paymentStatus = empty($request->paymentStatus)?NULL:$request->paymentStatus;

        ini_set('max_execution_time', -1);
        return Excel::download(new DeliveryBoyOrderExport($deliveryStatus,$paymentStatus,$orderID), 'inhouseorder.xlsx');
    }


    public function set_cancel_otp(Request $request){
       $mobile = $request->mobile;
       $activenum = OtpOrderCancel::where('mobile', $mobile)->first();    
       if(!empty($activenum)){
            $otp = rand(10,10000);
            OtpOrderCancel::where('mobile', $mobile)
                ->update([
                 'otp'    => $otp,
                 'status' => 1
            ]);

            $to = $mobile;
            $from = "RZANA";
            $tid  = "1707164586764849522"; 

            $msg = "Dear user, your OTP for Rozana Admin Related Changes is ".$otp.". Team Rozana";
            mobilnxtSendSMS($to,$from,$msg,$tid);

            return 1;
       }else{
            return 0;
       }
    }

    public function get_cancel_otp(Request $request){
       $mobile = $request->mobile;
       $get_otp = $request->otp;
       $order_id = $request->order_id;

       $activeotp = OtpOrderCancel::where('mobile', $mobile)->where('otp', $get_otp)->where('status', 1)->first();   
       if(!empty($activeotp)){ 
            $cancellog = new CancelLog;
            $cancellog->mobile = $mobile;
            $cancellog->save();

            $order = Order::findOrFail($order_id);
            $order->order_status = 'cancel';
            $order->save();

             $orderdetails = OrderDetail::
                            where('order_id', $order_id)
                            ->update([
                             'delivery_status'      => 'cancel'
                          ]);

            $otp = 0;
            OtpOrderCancel::where('mobile', $mobile)
                ->update([
                 'otp'    => $otp,
                 'status' => $otp
            ]);                    
            return 1;
       }else{
            return 0;
       }
    }
}
