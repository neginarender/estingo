<?php

namespace App\Http\Controllers\Api\v5;

use Illuminate\Http\Request;
use App\Models\Order;

use App\Models\Cart;
use App\Product;
use App\Models\OrderDetail;

//use App\OrderDetail;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\BusinessSetting;
use App\Traits\OrderTrait;
use App\User;
use App\Wallet;
use App\OrderReferalCommision;
use App\PeerPartner;
use App\DeliveryBoy;
use App\AssignOrder;
use DB;
use Carbon\Carbon as Carbon;
use App\ProductStock;
use App\Models\ReferalUsage;
use Razorpay\Api\Api;
use App\LogActivity;
use App\CancelOrderLog;
use App\Notifications\CancelOrderMail;
use App\Distributor;
use App\MappingProduct;
use App\PeerSetting;
use PDF;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\ShortingHub;
use App\SubOrder;
use Validator;

use App\Events\OrderPlaced;
class OrderController extends Controller
{
        public $ref_dis = 0;
        public $peer_percentage = 0;
        public $total_peer_percent = 0;
        public $total_discount_percent = 0;
        public $total_master_percent = 0;
        public $master_percentage = 0;
        public $referal_code = "";

    use OrderTrait;
  

    public function processOrder(Request $request)
    {

        DB::beginTransaction();
        try{
            $userDetail = User::where('id',$request->user_id)->where('banned',1)->get();
            if(count($userDetail) > 0){
                return response()->json([
                    'success'=>false,
                    'order'=>[],
                    'banned' => 1,
                    'message'=>"Sorry, Due to some suspected activities, your account has been blocked by Admin. Please contact to Admin for further process."
                ]);
            }
                $shippingAddress = json_decode($request->shipping_address);
                $cartItems = Cart::where('device_id', $request->device_id)->get();
                $shipping_cost = Cart::where('device_id', $request->device_id)->sum('shipping_cost');
                $payment_type = $request->payment_type;
                $shipping = 0;
                $admin_products = array();
                $seller_products = array();
                $sorting_hub_id = 0;
                $referal_code = "";
                if(count($cartItems)==0){
                    return response()->json([
                        'success'=>false,
                        'order'=>[],
                        'banned' => 0,
                        'message'=>"Unable to process your order.Try again later"
                    ]);
                }

                if (\App\BusinessSetting::where('type', 'shipping_type')->first()->value == 'flat_rate') {
                    $shipping = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
                }
                elseif (\App\BusinessSetting::where('type', 'shipping_type')->first()->value == 'seller_wise_shipping') {
                    foreach ($cartItems as $cartItem) {
                        $product = \App\Product::find($cartItem->product_id);
                        if($product->added_by == 'admin'){
                            array_push($admin_products, $cartItem->product_id);
                        }
                        else{
                            $product_ids = array();
                            if(array_key_exists($product->user_id, $seller_products)){
                                $product_ids = $seller_products[$product->user_id];
                            }
                            array_push($product_ids, $cartItem->product_id);
                            $seller_products[$product->user_id] = $product_ids;
                        }
                    }
                        if(!empty($admin_products)){
                            $shipping = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
                        }
                        if(!empty($seller_products)){
                            foreach ($seller_products as $key => $seller_product) {
                                $shipping += \App\Shop::where('user_id', $key)->first()->shipping_cost;
                            }
                        }
                }

                $lastorderID = Order::orderBy('id', 'desc')->first();

                if(!empty($lastorderID)){
                    $orderId = $lastorderID->id;
                }else{
                    $orderId = 1;
                }

                $code = 'ORD'.mt_rand(10000000,99999999).$orderId;


            if(!empty($request->coupon_code)){
                $coupon_id = Coupon::where('code', $request->coupon_code)->first()->id;
            }else{
                $coupon_id = null;
            }
            if(!empty($request->referal_code)){
                $used_referral_code = '1';
                $referal_code = $request->referal_code;
            }else{
                $used_referral_code = '0';
            }

            if(isset($request->sorting_hub_id) && !empty($request->sorting_hub_id)){
                $sorting_hub_id = $request->sorting_hub_id;
                $min_cod_amount = \App\ShortingHub::where('user_id',$sorting_hub_id)->pluck('min_cod')->first();
                if($min_cod_amount > $request->amount_by_wallet && $payment_type =="wallet"){
                    $msg = "Minimum order amount ".$min_cod_amount;
                    return response()->json([
                        'success'=>false,
                        'order'=>[],
                        'message'=>$msg
                    ]);
                }

                if($min_cod_amount > $request->grand_total && $payment_type == 'cash_on_delivery'){
                    $msg = "Minimum order amount ".$min_cod_amount;
                    return response()->json([
                        'success'=>false,
                        'order'=>[],
                        'message'=>$msg
                    ]);
                }

                $max_cod_amount = \App\ShortingHub::where('user_id',$sorting_hub_id)->pluck('max_cod')->first();
                if($payment_type == 'cash_on_delivery' && $max_cod_amount < $request->grand_total){
                    $msg = "Currently, You are unable to use Cash On Delivery, Please continue with Online Payment.";
                    return response()->json([
                        'success'=>false,
                        'order'=>[],
                        'message'=>$msg
                    ]);
                }

            }

        $orderArray = [
            'coupon_id' => $coupon_id,
            //'sorting_hub_id'=>$sorting_hub_id,
            'shipping_address' => json_encode($shippingAddress),
            'billing_address' =>  json_encode($shippingAddress),
            'payment_type' => $payment_type,
            'code' => $code,
            'payment_status' => $request->payment_status,
            'grand_total' => $request->grand_total,    
            'test_grand_total'=> $request->grand_total,
            'coupon_discount' => $request->coupon_discount,
            'shipping_pin_code' => $shippingAddress->postal_code,
            'platform' =>$request->platform,
            'date' => strtotime('now'),
            'order_status'=>'pending',
            'total_shipping_cost'=>$shipping_cost,
            'used_referral_code' => $used_referral_code,
            'device_id' => $request->device_id,
            'order_for' => (isset($request->order_for) || is_null($request->order_for))?$request->order_for:NULL,
            'order_type' => $request->order_type,
            'sorting_hub_id' => $request->sorting_hub_id,
            'referal_discount'=>$request->referal_discount
        ];

        if(!empty($request->user_id)){
            User::where('id',$request->user_id)->update(['device_id'=>$request->device_id,'platform' =>$request->platform]);
        }


        if(!empty($request->user_id)){
            $orderArray['user_id'] = $request->user_id;
        }else{
            $orderArray['guest_id'] = mt_rand(100000, 999999);
        }

        if($payment_type =="wallet"){
            $orderArray['wallet_amount'] = $request->amount_by_wallet;  
        }

        // create an order
        $order = Order::create($orderArray);
        $total_peer_commission = 0;
        $total_master_commission = 0;
        foreach ($cartItems as $cartItem) {
            $product = Product::findOrFail($cartItem->product_id);
            if ($cartItem->variation) {
                $cartItemVariation = $cartItem->variation;
            }else{
                $product->update([
                    'current_stock' => DB::raw('current_stock - ' . $cartItem->quantity)
                ]);
            }

            
            // save order details
            $order_detail =  OrderDetail::create(['order_id' => $order->id,
                'product_id' => $product->id,
                'variation' => $cartItem->variation,
                'price' => $cartItem->mrp * $cartItem->quantity,
                'tax' => ((($cartItem->price-$cartItem->discount)*$cartItem->product->tax)/100) * $cartItem->quantity,
                'shipping_cost' => $cartItem->shipping_cost,
                'shipping_type' => $payment_type,
                'quantity' => $cartItem->quantity,
                'payment_status' => $request->payment_status,
                'delivery_status' =>'pending',
                'peer_discount' => $cartItem->quantity * $cartItem->discount,
                'sub_peer'=>$cartItem->quantity*$cartItem->peer_commission,
                'master_peer'=>$cartItem->quantity*$cartItem->master_commission
            ]);
            
            $total_peer_commission += $cartItem->quantity*$cartItem->peer_commission;
            $total_master_commission += $cartItem->quantity*$cartItem->master_commission;

            // if(!empty($request->referal_code)){
            //     $this->ApplyPeerDiscountOld($order->id,$product->id,$shippingAddress->postal_code,$order_detail->id);
            // }
                    
            $product->update([
                    'num_of_sale' => DB::raw('num_of_sale + ' . $cartItem->quantity)
                ]);
        }

        $this->peer_percentage = $total_peer_commission;
        $this->master_percentage = $total_master_commission;

 
            $this->referal_code = $request->referal_code;
            if(!empty($request->referal_code)){
                //dd($this->peer_percentage);
                $this->updateCommission($order->id);//Refearl usage
                $this->updateReferalCommission($order); // order referal commission
                // $this->smsToMasterPeerOnOrderPlaced($this->referal_code);
            }


            if(isset($request->user_id) && $request->payment_type=="wallet")
            {
                $user = User::findOrFail($request->user_id);
                $balance = $user->balance-$request->amount_by_wallet;
                $user->balance  = $balance;
                $user->save();
                $this->createHistoryInWallet($request->user_id,$request->amount_by_wallet,$order->id);

            }

            $currentDateTime = Carbon::now();
            //orderSchedule - 01-02-2022
            /*if($request->is_grocery == 1){
                $delivery_type = $request->delivery_detail_grocery['delivery_type'];
                    if($delivery_type =="scheduled"){
                        $delivery_date = $request->delivery_detail_grocery['delivery_date'];
                        $delivery_time = $request->delivery_detail_grocery['delivery_time'];
                    }
                    else{
                        $delivery_date = $currentDateTime->addHour(24);
                        $delivery_time = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                    }
                $schedule =  SubOrder::create(['order_id' => $order->id,
                    'delivery_name' => 'grocery',
                    'delivery_type' => $delivery_type,
                    'delivery_date' => $delivery_date,
                    'delivery_time' => $delivery_time,
                    'status' => 1,
                    'payment_status' => $request->payment_status,
                    'delivery_status' =>'pending']);
            }

            if($request->is_fresh == 1){
                $delivery_type = $request->delivery_detail_fresh['delivery_type'];
                if($delivery_type=="scheduled"){
                    $delivery_date = $request->delivery_detail_fresh['delivery_date'];
                    $delivery_time = $request->delivery_detail_fresh['delivery_time'];
                    
                }
                else{
                    $delivery_date = $currentDateTime->addHour(24);
                    $delivery_time = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                }
                $schedule =  SubOrder::create(['order_id' => $order->id,
                'delivery_name' => 'fresh',
                'delivery_type' => $delivery_type,
                'delivery_date' => $delivery_date,
                'delivery_time' => $delivery_time,
                'status' => 1,
                'payment_status' => $request->payment_status,
                'delivery_status' =>'pending']);
                
            }*/
            
            //Update sorting hub stock
            $data = $this->updateStock($order->id);
            // clear user's cart
            Cart::where('device_id',$request->device_id)->delete();
            DB::commit();
            // $this->sendOrderMail($order);
            // SMSonOrderPlaced($order->id);
            SMSonOrderPlacedWithSlot($order->id);
            OrderPlaced::dispatch($order->id);
            return response()->json([
                'success' => true,
                'order' => $this->OrderResponseDetail($order->id),
                'message' => 'Your order has been placed successfully'
            ]);
        } catch(\Exception $e){
            echo $e;
            DB::rollback();
            info($e);
            return response()->json([
                'success'=>false,
                'order'=>[],
                'banned' => 0,
                'message'=>"Unable to process your order.Try again later"
            ]);
        }
    }

    public function store(Request $request)
    {
        return $this->checkout_done($request);
    }

    public function deliveryBoyOrders(Request $request)
    {
        
        $dboy = DeliveryBoy::where('user_id',$request->id)->first()['id'];
        
        $new_order = AssignOrder::select('order_id')->where('delivery_boy_id',$dboy)->orderBy('created_at','desc')->get();

        $orderID = $new_order->map(function($item){
            return $item->order_id;
        });
        

        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;

        
        $orders = DB::table('orders')
                    ->select('orders.*','order_details.order_id','order_details.seller_id','order_details.product_id','order_details.variation','order_details.price','order_details.tax','order_details.shipping_cost','order_details.quantity','order_details.delivery_status','order_details.shipping_type')
                    ->whereIn('orders.id', $orderID)
                    ->orderBy('id', 'desc')
                    ->distinct('code');
                    

        if ($request->payment_type != null){
            $orders = $orders->where('orders.payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('orders.order_status', $request->delivery_status);
            //$orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        // else{
        //     $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id');
        // }
        if ($request->has('search')){
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }
        $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id');
        $orders = $orders->groupBy('order_details.order_id')->get();
        
        $orders = $orders->map(function($item){
            unset($item->platform);
            unset($item->guest_id);
            unset($item->coupon_id);
            unset($item->order_create);
            unset($item->deleted_at);
            unset($item->delivered_to);
            unset($item->otp);
            unset($item->seller_id);
            $item->total_product    = sprintf("%02d",count(DB::table('order_details')->where('order_id',$item->id)->get()->toArray()));
            $item->delivery_status = DB::table('order_details')->where('order_id',$item->id)->first()->delivery_status;

            // $no_of_item = OrderDetail::where('order_id',$item->id)->count();

            $no_of_item = OrderDetail::where('order_id',$item->id)->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

            // $no_of_delivered_item = OrderDetail::where('order_id',$item->order_id)->where('delivery_status','delivered')->count();

            $no_of_delivered_item = OrderDetail::where('order_id',$item->order_id)->where('delivery_status','delivered')->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

            $item->delivery_status = str_replace('_',' ',$item->order_status);
            
            $item->shipping_address= json_decode($item->shipping_address);
            //var_dump($item->shipping_address, json_last_error_msg());
            $item->payment_details = json_decode($item->payment_details);
            $item->payment_method= ($item->payment_type=='razorpay') ? 'Razor Pay':($item->payment_type=="wallet" ? 'Wallet':'Cash On Delivery');
            $item->pay_staus = ($item->payment_status=='paid') ? 'Paid' : "Unpaid";
            if($item->payment_status == "partial_paid"){
                $item->pay_staus = "Partially Paid";
            }
            return $item;
        });
        return response()->json([
            'success'=>true,
            'orders'=>$orders,
            
            // 'payment_status'=>$payment_status,
            // 'sort_search'=>$sort_search,
            // 'admin_user_id'=>$admin_user_id
        ]);

    }

    public function get_order_product_wise(Request $request)
    {
        $dboy = DeliveryBoy::where('user_id',$request->id)->first()['id'];
        
        $new_order = AssignOrder::select('order_id')->where('delivery_boy_id',$dboy)->orderBy('id','desc')->get();

        $orderID = $new_order->map(function($item){
            return $item->order_id;
        });
        

        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;

        
        $orders = DB::table('orders')
                    ->select('orders.*','order_details.order_id','order_details.seller_id','order_details.product_id','order_details.variation','order_details.price','order_details.tax','order_details.shipping_cost','order_details.quantity','order_details.payment_status','order_details.delivery_status','order_details.shipping_type')
                    ->whereIn('order_details.order_id', $orderID)
                    ->orderBy('id', 'desc');
                    
                    

        if ($request->payment_type != null){
            $orders = $orders->where('orders.payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.order_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        else{
            $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id');
        }
        if ($request->has('search')){
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }
        
        $orders = $orders->get();
        
        $orders = $orders->map(function($item){
            $item->total_product    = sprintf("%02d",count(DB::table('order_details')->where('order_id',$item->id)->get()->toArray()));
            $item->delivery_status = DB::table('order_details')->where('order_id',$item->id)->first()->delivery_status;

            // $no_of_item = OrderDetail::where('order_id',$item->id)->count();
            $no_of_item = OrderDetail::where('order_id',$item->id)->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

            // $no_of_delivered_item = OrderDetail::where('order_id',$item->order_id)->where('delivery_status','delivered')->count();
             $no_of_delivered_item = OrderDetail::where('order_id',$item->order_id)->where('delivery_status','delivered')->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

           if($no_of_item > $no_of_delivered_item)
           {
            $item->delivery_status = 'On Delivery';
           }
            if($item->delivery_status =='On delivery')
            {
                $item->delivery_status = 'On Delivery';
            }
            if($item->delivery_status =='on_review')
            {
                $item->delivery_status = 'On Review';
            }

            $item->shipping_address= json_decode($item->shipping_address);
            $item->payment_details = json_decode($item->payment_details);
            $item->payment_method= ($item->payment_type=='letzpay_payment') ? 'Letzpay Payment':($item->payment_type=="wallet" ? 'Wallet':'Cash On Delivery');
            $item->pay_staus = ($item->payment_status=='paid') ? 'Paid' : "Unpaid";
            if($no_of_item > $no_of_delivered_item){
                $item->pay_staus = "In Process";
            }
            return $item;
        });
        return response()->json([
            'success'=>true,
            'orders'=>$orders
        ]);

    }

    public function update_delivery_status(Request $request)
    {
        //print_r(json_decode($request->order_detail_id));exit;
        
        $order = Order::findOrFail($request->order_id);
        $order->delivery_viewed = '0';
        $order->save();
        $phone = $order->user['phone'];
        $message="";
        $address = array();

        if($request->status=="on_delivery"){
            $otp  = random_int(1000, 9999);
            $order->otp = $otp;
            $order->save();
            $to = json_decode($order->shipping_address,true)['phone'];
            $from = "RZANA";
            // $tid  = "1707162443937828624"; 
            $tid  = "1707164406052847021"; 
            // $msg = "Your order ".$order->code." from Rozana is out for delivery, please share delivery code ".$otp." with the executive. For further queries call 9667018020 Thank you, Team Rozana";
            $msg = "Your order ".$order->code." from Rozana is out for delivery, please share delivery code ".$otp.". For help call 9667018020. Rozana";
            mobilnxtSendSMS($to,$from,$msg,$tid);
        }
        if($request->status=='in_transit' && empty($request->name)){
            if($request->otp!=$order->otp){
                return response()->json([
                    'success'=>false,
                    'message'=>'Invalid OTP'
                ]);
            }
            
        }
        if(!empty($request->name)){
            $address['name'] = $request->name;
            $address['mobile'] = $request->mobile;
            $order->delivered_to = json_encode($address);
            
        }
               
        //$order->order_status = $order_status;
        $order->save();

        $order_details = orderDetail::whereIn('id',json_decode($request->order_detail_id))->whereNull('deleted_at')->get();  
        foreach($order_details as $key => $orderDetail){
            $orderDetail->delivery_status = $request->status;
            
            if($request->status == 'in_transit'){
                $orderDetail->payment_status = "paid";
            }

            $orderDetail->save();
        }

        // $no_of_item = OrderDetail::where('order_id',$request->order_id)->whereNull('deleted_at')->count();
        $no_of_item = OrderDetail::where('order_id',$request->order_id)->whereNull('deleted_at')->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

        // $no_of_delivered_item = (OrderDetail::where('order_id',$request->order_id)->where('delivery_status','delivered')->whereNull('deleted_at')->count());

        $no_of_delivered_item = OrderDetail::where('order_id',$request->order_id)->where('delivery_status','in_transit')->whereNull('deleted_at')->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

        // $no_of_status_item = (OrderDetail::where('order_id',$request->order_id)->where('delivery_status', $request->status)->whereNull('deleted_at')->count());

        $no_of_status_item = OrderDetail::where('order_id',$request->order_id)->where('delivery_status', $request->status)->whereNull('deleted_at')->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();;
       
        if($order->payment_status!="paid"){
            $order->payment_status  = "unpaid";
            if($request->status == 'in_transit' && $no_of_item==$no_of_delivered_item){
                $order->payment_status  = "paid";
            }
            else if($no_of_delivered_item!==0){
                $order->payment_status = "partial_paid";
            }
        }

        $order_status = "partially_delivered";
        if($no_of_status_item == $no_of_item){
            $order_status = $request->status;

        }
        elseif($request->status=="Pending" && $no_of_delivered_item==0){
            $order_status = "in_process";

        }
        elseif($request->status=="on_review" && $no_of_delivered_item==0){
            $order_status = "in_process";
        }
        elseif($request->status=="on_delivery" && $no_of_delivered_item==0){
            $order_status = "in_process";
        }
       
        $order->order_status = $order_status;
        $order->save();

        return response()->json([
            'success'=>true,
            'message'=>'Order status updated']);
    }

    public function orderDetail($id)
    {
        $orders = Order::where('id',$id)->select('id','shipping_address','billing_address','shipping_pin_code','payment_type','payment_status','order_status','grand_total','order_status','referal_discount','wallet_amount','total_shipping_cost','code','date','created_at')->get();
        $orders = $orders->map(function($item){
            unset($item->user_id);
            $item->shipping_address = json_decode($item->shipping_address);
            //$item->payment_details = json_decode($item->payment_details);
            $item->payment_method= ($item->payment_type=='razorpay') ? 'Razorpay':($item->payment_type=="wallet" ? 'Wallet':'Cash On Delivery');
            $item->pay_staus = ($item->payment_status=='paid') ? 'Paid' :'Unpaid';
            return $item; 
        });
        $order = Order::where('id',$id)->first();
        $other_order_status = "";
        $veg_order_status = "";
        $other_payment_status = "";
        $veg_payment_status = "";
        $vegetable = array();
        $other = array();
			foreach($order->orderDetails->whereNull('deleted_at') as $key=>$orderdetail){
				if($orderdetail->product['category_id'] == '18'|| $orderdetail->product['category_id']=='26' || $orderdetail->product['subcategory_id'] == '129' || $orderdetail->product['category_id']=='33'){
					array_push($vegetable,$order->orderDetails[$key]['id']);
					
				}else{
					array_push($other,$order->orderDetails[$key]['id']);
				}
			}
        $veg_order_detail=[];
		if(count($vegetable)>0){
            $veg_order_detail = orderDetail::whereIn('id',$vegetable)->whereNull('deleted_at')->get();
            $veg_order_detail = $veg_order_detail->map(function($item){
                unset($item->pickup_point_id);
                unset($item->product_referral_code);
                unset($item->deleted_at);
                unset($item->sub_peer);
                unset($item->master_peer);
                $item['product_name'] = Product::where('id',$item->product_id)->first()->name;
                $item['thumnail_img'] = Product::where('id',$item->product_id)->first()->thumbnail_img;
                $item->price = doubleval($item->price);
                $item->shipping_cost = doubleval($item->shipping_cost);
                $item->delivery_status = str_replace('_',' ',$item->delivery_status);
                return $item;
            });
            $veg_order_status = orderDetail::whereIn('id',$vegetable)->first()->delivery_status;
            $veg_payment_status = orderDetail::whereIn('id',$vegetable)->first()->payment_status;

        }
        $other_order_detail =[];
        if(count($other)>0){
            $other_order_detail = orderDetail::whereIn('id',$other)->whereNull('deleted_at')->get();
            $other_order_detail = $other_order_detail->map(function($item){
                unset($item->pickup_point_id);
                unset($item->product_referral_code);
                unset($item->deleted_at);
                unset($item->sub_peer);
                unset($item->master_peer);
                $item['product_name'] = Product::where('id',$item->product_id)->first()->name;
                $item['thumnail_img'] = Product::where('id',$item->product_id)->first()->thumbnail_img;
                $item->price = doubleval($item->price);
                $item->shipping_cost = doubleval($item->shipping_cost);
                $item->delivery_status = str_replace('_',' ',$item->delivery_status);
                return $item;
            });
            $other_order_status = orderDetail::whereIn('id',$other)->first()->delivery_status;
            $other_payment_status = orderDetail::whereIn('id',$other)->first()->payment_status;
        }
        

        if($order!=null){
            return response()->json(
                ['success'=>true,
                'order'=>$orders,
                'other_order_detail'=>$other_order_detail,
                'veg_order_detail'=>$veg_order_detail,
                'other_order_status'=>$other_order_status,
                'veg_order_status'=>$veg_order_status,
                'other_payment_status'=>$other_payment_status,
                'veg_payment_status'=>$veg_payment_status
        ]);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Order details not available'
        ]);
        
    }

    public function count_new_order($id)
    {
        $dboy = DeliveryBoy::where('user_id',$id)->first()->id;
        // $new_order = AssignOrder::where('delivery_boy_id',$dboy)->where('is_view',0)->count();
        $new_order = AssignOrder::where('delivery_boy_id',$dboy)->where('is_view',0)->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

        return response()->json([
                'success'=>true,
                'new_order'=>$new_order
            ]);
    }

    public function update_new_order_status($id)
    {
        $dboy = DeliveryBoy::where('user_id',$id)->first()->id;
        $update = AssignOrder::where('delivery_boy_id',$dboy)->update(array('is_view'=>1));
        return response()->json([
            'success'=>true
        ]);
    }

    public function new_orders($id)
    {
        
        $dboy = DeliveryBoy::where('user_id',$id)->first()->id;
        $new_order = AssignOrder::select('order_id')->where('delivery_boy_id',$dboy)->orderBy('id','desc')->get();

        $order_id = $new_order->map(function($item){
            return $item->order_id;
        });
        $orders = Order::select('id','code','shipping_address','created_at')->whereIn('id',$order_id)->orderBy('id','desc')->get()->map(function($item){
            $item->delivery_status = OrderDetail::select('delivery_status')->where('order_id',$item->id)->first()->delivery_status;
            $item->time_ago = $item->created_at->diffForHumans();
            $item->description = json_decode($item->shipping_address);
            unset($item->shipping_address);
            unset($item->created_at);
            return $item;
        });
        
        return response()->json([
                'success'=>true,
                'order_code'=>$orders
            ]);
    }


    public function orderinitiate(Request $request)

    {
        
        DB::beginTransaction();
        try{

        $requestPar = $request->all();
        $order_id = array();
        $payment_type = $requestPar['payment_type'];
        $user_id = $requestPar['user_id']; 

        $userDetail = User::where('id',$user_id)->where('banned',1)->get();
        if(count($userDetail) > 0){
            return response()->json([
                'success'=>false,
                'order'=>[],
                'banned' => 1,
                'message'=>"Sorry, Due to some suspected activities, your account has been blocked by Admin. Please contact to Admin for further process."
            ]);
        }
        $sorting_hub_id = 0;
        $referal_code = "";
        $shipping_address = $requestPar['shipping_address'];
        //generate order code
        $lastorderID = Order::orderBy('id', 'desc')->first();
        if(!empty($lastorderID)){
            $orderId = $lastorderID->id;
        }else{
            $orderId = 1;
        }

        $code = 'ORD'.mt_rand(10000000,99999999).$orderId;
        $cartItems = Cart::where('device_id', $request->device_id)->get();
       
        $shipping_cost = Cart::where('device_id', $request->device_id)->sum('shipping_cost');
        $totalproduct = count($cartItems);
        $admin_products = array();
        $seller_products = array();
        $sum = 0;

        if(count($cartItems)==0){
            return response()->json([
                'success'=>false,
                'order'=>[],
                'banned' => 0,
                'message'=>"Unable to process your order.Try again later"
            ]);
        }

            // if($requestPar['amount'] >1499){
            //     $ship_rate = \App\BusinessSetting::where('type', 'flat_rate_shipping_cost')->first()->value;
            //     $shipping = 0;
            // }else{
            //     $shipping = 29;
            // }

        if(!empty($request->coupon_code)){
            $coupon_id = Coupon::where('code', $request->coupon_code)->first()->id;
        }else{
            $coupon_id = null;
        }

        if(isset($request->sorting_hub_id) && !empty($request->sorting_hub_id)){
            $sorting_hub_id = $request->sorting_hub_id;
            $min_cod_amount = \App\ShortingHub::where('user_id',$sorting_hub_id)->pluck('min_cod')->first();
                if($min_cod_amount > ($requestPar['amount'] + $request->amount_by_wallet)){
                    $msg = "Minimum order amount ".$min_cod_amount;
                    return response()->json([
                        'success'=>false,
                        'order'=>[],
                        'banned' => 0,
                        'message'=>$msg
                    ]);
                }
        }

        if(!empty($request->referal_code)){
            $used_referral_code = '1';
            $referal_code = $request->referal_code;
        }else{
            $used_referral_code = '0';
        }

        $orderArray = [

        'coupon_id' => $coupon_id,
        'shipping_address' => json_encode($shipping_address),
        'billing_address' =>  json_encode($shipping_address),
        'payment_type' => $payment_type,
        'code' => $code,
        'payment_status' => 'unpaid',
        'grand_total' => round($requestPar['amount'],2),    //// 'grand_total' => $request->grand_total + $shipping,
        'test_grand_total'=>round($requestPar['amount'],2),
        'coupon_discount' => $requestPar['coupon_discount'],
        'shipping_pin_code' => $shipping_address['postal_code'],
        'platform' =>$requestPar['platform'],
        'referal_code'=>$referal_code,
        'date' => strtotime('now'),
        'order_status'=>'pending',
        'total_shipping_cost'=>$shipping_cost,
        'log'=> 1,
        'device_id' => $request->device_id,
        'order_for' => (isset($request->order_for) || is_null($request->order_for))?$request->order_for:NULL,
        'order_type' => $request->order_type,
        'sorting_hub_id' => $request->sorting_hub_id,
        'referal_discount'=>$request->referal_discount
       ];

        if(!empty($request->user_id)){
            User::where('id',$request->user_id)->update(['device_id'=>$request->device_id,'platform' =>$request->platform]);
        }

        if(!empty($request->user_id)){
            $orderArray['user_id'] = $user_id;
        }else{
            $orderArray['guest_id'] = mt_rand(100000, 999999);
        }
        
        if($request->amount_by_wallet > 0){
            $orderArray['wallet_amount'] = $request->amount_by_wallet;
            $orderArray['wallet_refund_status'] = 1;
        }

        // create an order
    
        $order = Order::create($orderArray);

        //delivery slot Detail -start
        /*$currentDateTime = Carbon::now();
            //orderSchedule - 01-02-2022
            if($request->is_grocery == 1){
                $delivery_type = $request->delivery_detail_grocery['delivery_type'];
                    if($delivery_type =="scheduled"){
                        $delivery_date = $request->delivery_detail_grocery['delivery_date'];
                        $delivery_time = $request->delivery_detail_grocery['delivery_time'];
                    }
                    else{
                        $delivery_date = $currentDateTime->addHour(24);
                        $delivery_time = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                    }
                $schedule =  SubOrder::create(['order_id' => $order->id,
                    'delivery_name' => 'grocery',
                    'delivery_type' => $delivery_type,
                    'delivery_date' => $delivery_date,
                    'delivery_time' => $delivery_time,
                    'status' => 1,
                    'payment_status' => $request->payment_status,
                    'delivery_status' =>'pending']);
            }

            if($request->is_fresh == 1){
                $delivery_type = $request->delivery_detail_fresh['delivery_type'];
                if($delivery_type=="scheduled"){
                    $delivery_date = $request->delivery_detail_fresh['delivery_date'];
                    $delivery_time = $request->delivery_detail_fresh['delivery_time'];
                    
                }
                else{
                    $delivery_date = $currentDateTime->addHour(24);
                    $delivery_time = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                }
                $schedule =  SubOrder::create(['order_id' => $order->id,
                'delivery_name' => 'fresh',
                'delivery_type' => $delivery_type,
                'delivery_date' => $delivery_date,
                'delivery_time' => $delivery_time,
                'status' => 1,
                'payment_status' => $request->payment_status,
                'delivery_status' =>'pending']);
                
            }
        //delivery slot detail - end
*/
        $total_peer_commission = 0;
        $total_master_commission = 0;
        
        foreach ($cartItems as $cartItem) {

            $product = Product::findOrFail($cartItem->product_id);

            if ($cartItem->variation) {
                $cartItemVariation = $cartItem->variation;
                $product_stocks = $product->stocks->where('variant', str_replace(' ', '', $cartItem->variation))->first();
                //$product_stocks->qty -= $cartItem->quantity;

                //$product_stocks->save();

            } else {
                $product->update([
                    'current_stock' => DB::raw('current_stock - ' . $cartItem->quantity)
                ]);

            }

            // save order details

            $OrderDetail = OrderDetail::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variation' => $cartItem->variation,
                'price' => $cartItem->mrp * $cartItem->quantity,
                'tax' => ((($cartItem->price-$cartItem->discount)*$cartItem->product->tax)/100) * $cartItem->quantity,
                'shipping_cost' => $cartItem->shipping_cost,
                'shipping_type' => $payment_type,
                'quantity' => $cartItem->quantity,
                'payment_status' => 'unpaid',
                'delivery_status' =>'pending',
                'peer_discount' => $cartItem->quantity * $cartItem->discount,
                'sub_peer'=>$cartItem->quantity*$cartItem->peer_commission,
                'master_peer'=>$cartItem->quantity*$cartItem->master_commission
            ]);

            $total_peer_commission += $cartItem->quantity*$cartItem->peer_commission;
            $total_master_commission += $cartItem->quantity*$cartItem->master_commission;

            // $product->update([
            //     'num_of_sale' => DB::raw('num_of_sale + ' . $cartItem->quantity)
            // ]);

            //20-11-2021
            // if(!empty($request->referal_code))
            // {
            //     $this->ApplyPeerDiscountOld($order->id,$product->id,$order->shipping_pin_code,$OrderDetail->id);
            // }  
        }
        $this->peer_percentage = $total_peer_commission;
        $this->master_percentage = $total_master_commission;

        $this->referal_code = $request->referal_code;
            // Update Referal Usage Commission
    if(!empty($request->referal_code)){
        $this->updateCommission($order->id);
    }


        if($requestPar['payment_type'] == "letzpay_payment"){

            $prod_desc = $this->productsSku($order->id);          

            $order_detalis = json_encode(array( 
                                            'AMOUNT' => $requestPar['amount'] * 100,
                                            "CURRENCY_CODE"=> "356",
                                            "CUST_EMAIL" => $shipping_address['email'],
                                            "CUST_NAME"  => $shipping_address['name'],
                                            "CUST_PHONE" => $shipping_address['phone'],
                                            "ORDER_ID" => $code,
                                            "PAY_ID" => env("LETZPAY_ID"),
                                            "PROD_DESC" => $prod_desc,
                                            "RETURN_URL"=> "https:\/\/uat.ekhadiindia.com\/letzpay\/payment\/response",
                                            "TXNTYPE"=> "SALE"
                                        ));
            // \LogActivity::addToPayment($order_detalis,$order->id,'success',  'success', 'post', '',$order->payment_type);

            $response = [
                'orderId' =>  $code,
                'amount' =>  $requestPar['amount']*100,
                'name' => $shipping_address['name'],
                'currency' => '356',
                'email' => $shipping_address['email'],
                'contactNumber' => $shipping_address['phone'],
                'address' =>$shipping_address['address'],
                'description' => 'Order Payment',
                'SALT' => env("LETZPAY_SALT"),
                'code' => $code
            ];



        }elseif($requestPar['payment_type'] == "razorpay"){

            if($request->amount_by_wallet > 0){
                $user_wallet = User::where('id', $request->user_id)->first();
                $last_wallet = $user_wallet->balance - $request->amount_by_wallet;
                $referall_balance = User::where('id', $request->user_id)->update([
                                            'balance' => $last_wallet,
                                        ]);
                    
                $this->createHistoryInWallet($request->user_id,$request->amount_by_wallet,$order->id);
            }
            
            //test key
            //  $key_id      ='rzp_test_ytRX8y25JljIS1';
            //  $keySecret   ='pcLhBxhSC1b5ad2ciRkJwB25';
            //  $api = new Api($key_id,$keySecret);
            //live key

            $live_key_id = env('RAZOR_KEY');//'rzp_live_l8DKZ6gXms4jgZ';
            $live_key_secret = env('RAZOR_SECRET');//'HTKO3E97mhe9RHQ2xccBYLIV';
           
            $api = new Api($live_key_id, $live_key_secret);
                $orderinfo = $api->order->create(array(
                    'receipt' => $code,
                    'amount' =>  round($requestPar['amount'],2)*100,
                    'currency' => 'INR',
                    'notes' => array(
                        'order_id' => $code,
                        'name' => $shipping_address['name']
                        )
                    )
                );
        

            $orderId = $orderinfo['id'];
            $orderinfo  = $api->order->fetch($orderId);
            $order_detalis = json_encode(array(     'id' => $orderinfo['id'],
                                            'entity' => $orderinfo['entity'],
                                            'amount' => $orderinfo['amount'],
                                            'currency' => $orderinfo['currency'],
                                            'receipt' => $orderinfo['receipt'],
                                            'status' => $orderinfo['status'],
                                            'attempts' => $orderinfo['attempts']
                                        ));

            $orderUpdate = Order::where('id', $order->id)->update(['order_create' => $order_detalis]);

            LogActivity::addToPayment($order_detalis,$request->platform,$order->id,'success',  'success', 'post', '', $order->payment_type);

            $requestParams = json_encode(array('id' => $code, 'amount' => $orderinfo['amount'] *100, 'currency' => 'INR'));

            $walletBalance = User::where('id', $request->user_id)->pluck('balance')->first();

            $response = [
                'orderId' => $orderinfo['id'],
                'razorpayId' => env('RAZOR_KEY'),
                // 'razorpayId' => $key_id,
                'amount' => $orderinfo['amount'],
                'name' => $shipping_address['name'],
                'currency' => 'INR',
                'email' => $shipping_address['email'],
                'contactNumber' => $shipping_address['phone'],
                'address' =>$shipping_address['address'],
                'description' => 'Order Payment',
                'SALT' => '',
                'code' => $code,
                'amount_by_wallet' => $request->amount_by_wallet,
                'wallet_balance' => $walletBalance
             ];

        }elseif($requestPar['payment_type'] == "cash_on_delivery"){
            return response()->json([
                'success' => true,
                'message' => 'success',
                'response' => [],
                'order_detail' => [],
                'shippingAddress' => $shipping_address,
                'banned' => 0
            ]);
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'success',
            'response' => $response,
            'order_detail' => json_decode($order_detalis),
            'shippingAddress' => $shipping_address,
            'banned' => 0
        ]);
        

        }catch(\Exception $e){
            echo $e;
            info($e);
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Unable to process your order. Try again later',
                'response' => [],
                'order_detail' => [],
                'shippingAddress' => [],
                'banned' => 0
            ]);
        }

    }


        //get SKU of product
        public function productsSku($id){
            $orderDetails = OrderDetail::where('order_id', $id)->get();
            $PROD_DESC = array();
            foreach ($orderDetails as $key => $value) {
                $products = Product::with('category')->with('subcategory')->where('id', $value->product_id)->first();
                $productStock = ProductStock::where('product_id', $value->product_id)->where('variant', $value->variant)->first();
                if(!empty($productStock)){
                    $sku = $productStock->sku;
                }else{
                    $productsStockInfo =  ProductStock::where('product_id', $value->product_id)->first();
                    $sku = $productsStockInfo->sku;
                }
    
                     $PROD_DESC[] = [ 
                              'CATEGORY_CODE' => $products->category->name,
                              'SKU_CODE' => $sku,
                              'PRODUCT_PRICE' => $products->unit_price,
                              'QTY' => $value->quantity,
                              'REFUND_DAYS' => '7',
                              'VENDOR_ID' => $products->seller_id
                            ];
    
            }
            return json_encode(array("PROD_DESC" => $PROD_DESC));
            
        }



        public function orderStore(Request $request)

        {
        
            $requestPar = $request->all();
        
            $user_id = $requestPar['user_id']; 
    
            $json_de_payment =  $requestPar['payment_detail'];
    
            $payment_status = $requestPar['payment_status'];
    
            $payment_type = $requestPar['payment_type'];
    
            $code = $requestPar['code'];
            if(!empty($request->referal_code)){
                $res = $this->referalCommissionCalculation($request);
    
                if($res == 400){
                    return response()->json([
    
                        'success' => false,
            
                        'message' => "something went wrong.",
            
                        'data'=> []
            
                    ]);
                }
                
            }
    
    
    
    
    
            $dataArray = array();
    
             if($payment_status == 'success'){
    
                $order = Order::where('code',$code)->first();
    
                if(!empty($order->coupon_id)){
    
                    CouponUsage::create([
    
                        'user_id' => $user_id,
    
                        'coupon_id' => $order->coupon_id
    
                    ]);
    
    
    
                }
    
               
    
                    if($payment_type == "razorpay"){
    
                        $orderId = $json_de_payment['order_id'];
                        //live key
                        // $live_key_id = 'rzp_live_l8DKZ6gXms4jgZ';
                        // $live_key_secret = 'HTKO3E97mhe9RHQ2xccBYLIV';
            
                        //  $api = new Api($live_key_id, $live_key_secret);
    
    
    
                        $key_id      = env('RAZOR_KEY');
    
                        $keySecret   =  env('RAZOR_SECRET');
    
                        $api = new Api($key_id,$keySecret );
    
              
    
                        $orderinfo  = $api->order->fetch($orderId);
    
                        $order_detalis = json_encode(array(     'id' => $orderinfo['id'],
    
                                                                'entity' => $orderinfo['entity'],
    
                                                                'amount' => $orderinfo['amount'],
    
                                                                'currency' => $orderinfo['currency'],
    
                                                                'receipt' => $orderinfo['receipt'],
    
                                                                'status' => $orderinfo['status'],
    
                                                                'attempts' => $orderinfo['attempts']
    
                                                            ));     
    
                                                            
    
                        $orderupdate = Order::where('code',$code)
    
                        ->update(['payment_status' => 'paid',
    
                                    'payment_details' => json_encode($json_de_payment)    
    
                                    ]);  
    
                        OrderDetail::where('order_id',$order->id)
    
                              ->update(['payment_status' => 'Paid',
    
                                        'delivery_status' => 'pending'    
    
                                        ]);                  
    
                                    
    
                        //send mail msg to customer and update order details                      
    
                        // $this->createInvoice($order->id);
    
    
    
                        //$user = User::findOrFail($request->user_id);
                        //$user->carts()->delete();
    
                        $deleteCart = DB::table('carts')->where('device_id', $request->device_id)->delete();
    
        
    
                        $status = true; 
    
                        $message = "your order has been placed.";
    
                        $dataArray['order_status'] = 1;
    
    
    
                    }elseif($payment_type == "letzpay_payment"){
    
                        $amount = $order->grand_total*100;
    
                        $orderId = $code;
    
                        $payId = $json_de_payment['id'];
    
                        $response = $this->letzpayPaymentStatusGet($amount,$orderId,$payId);
                        
    
                        $shipping_address = json_decode($order['shipping_address']);
    
                        $prod_desc = $this->productsSku($order->id); 
    
                        $order_detalis = json_encode(array(     'AMOUNT' => $order->grand_total * 100,
    
                                                                "CURRENCY_CODE"=> "356",
    
                                                                "CUST_EMAIL" => $shipping_address->email,
    
                                                                "CUST_NAME"  => $shipping_address->name,
    
                                                                "CUST_PHONE" => $shipping_address->phone,
    
                                                                "ORDER_ID" => $code,
    
                                                                "PAY_ID" => env("LETZPAY_ID"),
    
                                                                "PROD_DESC" => $prod_desc,
    
                                                                "RETURN_URL"=> "https:\/\/uat.ekhadiindia.com\/letzpay\/payment\/response",
    
                                                                "TXNTYPE"=> "SALE"
    
                                                            ));
    
                        if($request->response_code == 000 && $payment_status == 'success'){
    
                            $orderupdate = Order::where('code',$code)
    
                        ->update(['payment_status' => 'paid',
    
                                    'payment_details' => $response 
    
                                    ]);        
    
                                    
    
                        //send mail msg to customer and update order details                      
    
                        // $this->createInvoice($order->id);
    
    
    
                        $user = User::findOrFail($request->user_id);
    
    
    
                    
    
                        $user->carts()->delete();
    
                        $deleteCart = DB::table('carts')->where('user_id', $user_id)->delete();
    
        
    
                        $status = true; 
    
                        $message = "your order has been placed.";
    
                        $dataArray['order_status'] = 1;
    
    
    
                        }else{
    
                            $orderupdate = Order::where('code',$code)
    
                            ->update(['payment_status' => 'failed',
    
                                      'payment_details' => $response  
    
                                        ]); 
    
                            
    
                            $status = false; 
    
                            $message = "Your payment has been failed.";
    
                            $dataArray['order_status'] = 0; 
    
                                                            
    
                        
    
    
    
                        }
    
    
    
                    }
    
    
    
                    // \LogActivity::addToPayment($order_detalis,$request->platform,$order->id, json_encode($json_de_payment), 'success', 'get','',$payment_type);
    
    
    
                 
    
             }else{
    
                $status = false; 
    
                $message = "your order has not been placed.";
    
                $dataArray['order_status'] = 0;
    
             }
    
    
    
            return response()->json([
    
                'success' => $status,
    
                'message' => $message,
    
                'data'=> $dataArray
    
            ]);
    
        }


        public function referalCommissionCalculation($request){
         
            if(!empty($request->referal_code)){
                DB::beginTransaction();
    
                // $order_id = 340;
                $grandtotal = Order::where('code',$request->code)->first();
                
                $getPeerDetails = PeerPartner::where('code',$request->referal_code)->first();
                
                $referal_commission = $grandtotal['grand_total']*$getPeerDetails['commission']/100;
    
    
             try{
    
                    $referalusage = ReferalUsage::create([
    
                        'user_id' => $request->user_id,
    
                        'order_id' => $grandtotal['id'],
    
                        'partner_id' => $getPeerDetails['user_id'],
    
                        'referal_code' =>  $request->referal_code,
    
                        'discount_rate' => $getPeerDetails['discount'],
    
                        'discount_amount' => $request->referal_discount,
    
                        'commision_rate' => $getPeerDetails['commission']
    
                    ]);
    
    
    
                    $referalcommission = OrderReferalCommision::create([
    
                        'partner_id' => $getPeerDetails['user_id'],
    
                        'order_id' => $grandtotal['id'],
    
                        'order_amount' => $grandtotal['grand_total'],
    
                        'refral_code' => $request->referal_code,
    
                        'referal_code_commision'=> $referal_commission,
    
                        'referal_commision_discount' => $request->referal_discount
    
    
    
                    ]);
                    DB::commit();
                    return 200;
    
                }catch(\Exception $e){
                   echo $e;exit;
                    DB::rollback();
                    return 400;
    
             }
                
    
    
            }
    
            
    
    
    
        }


        private function letzpayPaymentStatusGet($amount, $orderId, $payId){
            $payId = $payId;
            $orderId = $orderId;
            $URL = env('LETZPAY_REFUND_URL');
            $amount = $amount;
            $hash = $this->genrateStatusHash($payId, $orderId, $amount); 
            $curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => $URL,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS =>"{ \r\n\"PAY_ID\": \"$payId\",  \r\n\"ORDER_ID\": \"$orderId\",  \r\n\"AMOUNT\": \"$amount\", \r\n\"TXNTYPE\": \"STATUS\", \r\n\"CURRENCY_CODE\":\"356\", \r\n\"HASH\":\"$hash\" \r\n}",
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json"
              ),
            ));
    
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
    
        }

    public function genrateStatusHash($payID, $orderID, $amount){
        $PAY_ID = $payID;
        $SALT_ID = $orderID;
        $letz_data = array();
        $hash           = "";
        $paramList      = array();
        $letz_data['PAY_ID'] = $payID;
        $letz_data['ORDER_ID'] = $orderID;
        $letz_data['AMOUNT'] = $amount;
        $letz_data['TXNTYPE'] = 'STATUS';
        $letz_data['CURRENCY_CODE'] = '356';
        ksort($letz_data);
        $build = http_build_query($letz_data);
        $builddecode = urldecode($build);
        $buildReplace = str_replace('&', '~', $builddecode);
        $buildAppend  = $buildReplace.$SALT_ID;
        $hashgenrated = hash('sha256', $buildAppend);
        $hashCase = strtoupper($hashgenrated);
        return $hashCase;
    }    


    public function razorpay_payment_link(Request $request){
        $curl = curl_init();
        $amount = $request->amount*100;
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/payment_links/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "upi_link": "true",
                "amount": '.$amount.',
                "currency": "INR",
                "reference_id": "#'.rand('10000000', '200000000').'",
                "description": "Payment for order no '.$request->order_code.'",
                "customer": {
                    "name": "'.$request->name.'",
                    "contact": "+91'.$request->phone.'",
                    "email": "'.$request->email.'"
                },
                
                "notify": {
                    "sms": true
                },
                "reminder_enable": true,
                "notes": {
                    "policy_name": "Rozana delivery payment"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization:Basic cnpwX2xpdmVfMmNyRkpNNTNRWnBQVFE6dkNRWDJwWXQxcmFiUjZDT0ZCaHRXRUk5',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        
        curl_close($curl);

        if(json_decode($response)->status=="created"){
            return response()->json([
                'success'=>true,
                'message'=>'Payment link send'
            ]);
        }
        return response()->json([
            'success'=>false,
            'message'=>'Payment link sending fail'
        ]);

    }

    public function updateStock($order_id){
        $order = Order::find($order_id);

         $order_details = OrderDetail::where('order_id',$order_id)->get();
         $pincode = $order->shipping_pin_code;

         // $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
         // $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');

         $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');

        
         $update = false;

         foreach($order_details as $key => $order_detail)
         {
            $stock = \App\MappingProduct::where(['product_id'=>$order_detail->product_id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->first();
            if(!empty($stock)){
                $new_stock = $stock->qty-$order_detail->quantity;
            $update = \App\MappingProduct::where(['product_id'=>$order_detail->product_id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->update(['qty'=>$new_stock]);
            }
            
            
        }
        return $update;

    }

    public function checkout_done(Request $request)
    {

        $this->referal_code = $request->referal_code;
        $order_id = Order::where('code',$request->code)->first()->id;
        $order = Order::findOrFail($order_id);
        $order->payment_details = json_encode($request->payment_detail);
        $order->log = 0;
        $order->order_status = $order->dofo_status == 0 ?'pending':'delivered';
        $order->save();
        if($order->payment_type=="razorpay")
        {
            $payment_response = json_decode($order->payment_details);
            $res = $this->captureAuthorizePaymentRazorpay($payment_response->id);
            //if($res->status=="captured")
            //{
                $order->payment_status="paid";
                $order->save();
           //}
        }
        

        if($order->payment_status == 'paid'){
         
                $FAmount = 0;
                $unit_price = 0;
                $peer_percentage = 0;
                // foreach ($order->orderDetails as $key => $value) {
                //     $order_details = OrderDetail::findorFail($value->id);
                //     $order_details->payment_status = 'paid';
                //     $order_details->save();
                    
                //     $id = $value->product_id;
                //     $product = Product::find($id);
                //     if(!empty($request->referal_code))
                //     {
                //         $this->ApplyPeerDiscountOld($order->id,$product->id,$order->shipping_pin_code,$value->id);
                //     }  

                    
                    //$id = $value->product_id;
                    //$product = Product::find($id);
                    //if(!empty($request->referal_code))
                    //{
                        //$this->ApplyPeerDiscountOld($order->id,$product->id,$order->shipping_pin_code,$value->id);
                    //}  
                           
                //}

                //UpdateReferalCommission
                    if(!empty($request->referal_code)){
                        $this->updateReferalCommission($order);
                        // $this->smsToMasterPeerOnOrderPlaced($request->referal_code);
                    }
    
                    // 10june by n
                    /*if(!empty($order->user_id)){
                        $user_wallet = User::where('id', $order->user_id)->first();
                        $last_wallet = $user_wallet->balance - $order->wallet_amount;
                        $referall_balance = User::where('id', $order->user_id)->update([
                                                    'balance' => $last_wallet,
                                                ]);
                            if($order->wallet_amount > 0){
                                $this->createHistoryInWallet($order->user_id,$order->wallet_amount,$order->id);
                            }
                        

                        }*/
                    }
                    
    
            $order->commission_calculated = 1;
            $order->wallet_refund_status = 0;
            $order->save();
        //Update SubOrder Table
        \App\SubOrder::where('order_id',$order->id)
        ->update([
            'payment_status'=>'paid',
            'pay_by'=>$order->payment_type,
            'payment_mode'=>'online'
        ]);

            $deleteCart = DB::table('carts')->where('device_id', $request->device_id)->delete();

            // $this->sendOrderMail($order);
            // SMSonOrderPlaced($order->id);
            SMSonOrderPlacedWithSlot($order->id);
            OrderPlaced::dispatch($order->id);
            return response()->json([
            'success' => true,
            // 'order'=>$this->OrderResponse($order->id),
            'order'=>$this->OrderResponseDetail($order->id),
            'message' => 'Your order has been placed successfully'
        ]);
    
    }

    
    public function cancelOrder(Request $request)
    {
      
        DB::beginTransaction();
        $order = Order::findOrfail($request->order_id);
        if(!empty($order->user_id)){
            $getUserWallet = User::where('id',$order->user_id)->first();
        }
        
        
        try{
        
            $order_detail = OrderDetail::where('order_id',$request->order_id)->update(['delivery_status'=>'cancel','payment_status'=>'refund']);

            // initiate refund
            if($order->payment_type=="razorpay" && $order->payment_status=="paid" && empty($order->wallet_amount))
            {

                $order->payment_status = 'refunded';
                $payment_response = json_decode($order->payment_details);
                $pay_id = $payment_response->id;
                $resPay = $this->getRemainAmountRazorpay($pay_id);

                $cardAmount = $resPay['amount']/100;
                if($cardAmount != 0){
                    $fullRefundResponse = $this->razorpayFullRefund($pay_id);
                    // dd($fullRefundResponse);
                    if($fullRefundResponse['status']=='200'){
                            $refund_response = [
                            'id'=> $fullRefundResponse['refund_response']['id'],
                            'amount'=> $fullRefundResponse['refund_response']['amount'],
                            'status'=> $fullRefundResponse['refund_response']['status']
                        ];
                    }else{
                        
                        return response()->json([
                            'success'=>false,
                            'message'=>'Something went wrong 1'
                        ]);
    
                    }
                    if(!empty($order->referal_discount)){
                        
                        $peerCommission = OrderReferalCommision::where('order_id',$order->id)->first();
                        $referalUsage = ReferalUsage::where('order_id',$order->id)->first();
                        $peerCommission->referal_commision_discount = 0;
                        $peerCommission->master_discount  = 0;
                        $peerCommission->save();
                        $referalUsage->discount_amount = 0;
                        $referalUsage->master_discount  = 0;
                        $referalUsage->save();
                    }

                }
                //sms
                smsOnOrderCancel($order->id);
                
            }

            elseif($order->payment_type=="wallet" && $order->payment_status=='paid')
            {

                $refund_response = "";
                $order->payment_status = 'refunded';
                $user_wallet = User::where('id', $order->user_id)->first();
                $last_wallet = $user_wallet->balance + $order->wallet_amount;
                $referall_balance = User::where('id', $order->user_id)->update([
                                            'balance' => $last_wallet,
                                        ]);
                                       
                    if($referall_balance){

                        $wallet = new \App\Wallet;
                        $wallet->user_id = $order->user_id;
                        $wallet->amount = $order->wallet_amount;
                        $wallet->order_id = $request->order_id;
                        $wallet->tr_type = 'credit';
                        $wallet->payment_method = 'refund';
                        $wallet->save();
                        
                        if(!empty($order->referal_discount)){
                            
                            $peerCommission = OrderReferalCommision::where('order_id',$order->id)->first();
                            $referalUsage = ReferalUsage::where('order_id',$order->id)->first();
                            $peerCommission->referal_commision_discount = 0;
                            $peerCommission->master_discount  = 0;
                            $peerCommission->save();
                            $referalUsage->discount_amount = 0;
                            $referalUsage->master_discount  = 0;
                            $referalUsage->save();
                        }
                    }
                    else{
                        return response()->json([
                            'success'=>false,
                            'message'=>'Something went wrong 1'
                        ]);
                    }
                    //sms
                    smsOnOrderCancel($order->id);

                }
            elseif($order->payment_type=='razorpay' && $order->payment_status=='paid' && !empty($order->wallet_amount))
            {
                // refund razorpay amount
                $order->payment_status = 'refunded';
                $payment_response = json_decode($order->payment_details);
                $pay_id = $payment_response->id;
                $resPay = $this->getRemainAmountRazorpay($pay_id);
                $cardAmount = $resPay['amount']/100;
                if($cardAmount != 0){
                    $fullRefundResponse = razorpayFullRefund($pay_id);
                    //dd($fullRefundResponse);
                    if($fullRefundResponse['status']=='200'){
                        $refund_response = [
                            'id'=> $fullRefundResponse['refund_response']['id'],
                            'amount'=> $fullRefundResponse['refund_response']['amount'],
                            'status'=> $fullRefundResponse['refund_response']['status']
                        ];
                    }elseif($fullRefundResponse['status'] == '404'){
                        $refund_response = [
                            'id'=> $fullRefundResponse['refund_response']['id'],
                            'amount'=> $fullRefundResponse['refund_response']['amount'],
                            'status'=> $fullRefundResponse['refund_response']['status']
                        ];

                    }else{
                        return response()->json([
                            'success'=>false,
                            'message'=>'Something went wrong 2'
                        ]);
    
                    }

                }

                // refund wallet amount to peer wallet balance 
                $getUserWallet->balance += $order->wallet_amount;
                if($getUserWallet->save()){
                    
                    $wallet = new \App\Wallet;
                    $wallet->user_id = $order->user_id;
                    $wallet->amount = $order->wallet_amount;
                    $wallet->order_id = $request->order_id;
                    $wallet->tr_type = 'credit';
                    $wallet->payment_method = 'refund';
                    $wallet->save();
                  
                    if(!empty($order->referal_discount)){
                        
                        $peerCommission = OrderReferalCommision::where('order_id',$order->id)->first();
                        $referalUsage = ReferalUsage::where('order_id',$order->id)->first();
                        $peerCommission->referal_commision_discount = 0;
                        $peerCommission->master_discount  = 0;
                        $peerCommission->save();
                        $referalUsage->discount_amount = 0;
                        $referalUsage->master_discount  = 0;
                        $referalUsage->save();
                    }
              
                }else{
                    return response()->json([
                        'success'=>false,
                        'message'=>'Something went wrong 3'
                    ]);
                }
                //sms
                smsOnOrderCancel($order->id);
                
            }
            elseif($order->payment_type=='cash_on_delivery' && $order->payment_status=='unpaid'){
                $refund_response = "";
                if(!empty($order->referal_discount)){
                        
                    $peerCommission = OrderReferalCommision::where('order_id',$order->id)->first();
                    $referalUsage = ReferalUsage::where('order_id',$order->id)->first();

                    if(!is_null($peerCommission)){
                        $peerCommission->referal_commision_discount = 0;
                        $peerCommission->master_discount  = 0;
                        $peerCommission->save();
                    }
                    if(!is_null($referalUsage)){
                        $referalUsage->discount_amount = 0;
                        $referalUsage->master_discount  = 0;
                        $referalUsage->save();
                    }
                    
                }
                //sms
                smsOnOrderCancel($order->id);
            }

            $order->order_status = 'cancel';
            $order->cancel_reason = $request->reason;
            $order->save();
            $cancellog = new CancelOrderLog;
            $cancellog->order_id = $order->id;
            if(isset($refund_response)){
                $cancellog->refund_details = json_encode($refund_response);
            }
            $cancellog->save();
            //update stock
            // updateStock($order->id);
            DB::commit();
            //email to customer and admin
            $notifaction = ['ordercode'=>$order->code];
            if(!empty($order->user_id))
            {
                $user = User::find($order->user_id);
                $notifaction['name'] = $user->name;
                //$user->notify(new cancelOrderMail($notifaction));
            }
            else
            {
                $email = json_decode($order->shippong_address)->email;
                $notifaction['name'] = json_decode($order->shipping_address)->name;
                //Notification::route('mail',$email)->notify(new cancelOrderMail($notifaction));
            }
            //sms to admin and customer 
            return response()->json([
                'success'=>true,
                'message'=>'Order Cancelled successfully.'
            ]);
        
    }catch (\Throwable $e) {
        dd($e);
        return response()->json([
            'success'=>false,
            "message"=>"something went wrong"
        ]);
    }
    }

    public function AppplyPeerDiscount($order_id,$product_id,$orderDetailId,$pincode,$referal_code){
        $ref_dis = 0;
        $peer_percentage = 0;
        $total_peer_percent = 0;
        $total_discount_percent = 0;
        $total_master_percent = 0;
        $master_percentage = 0;
        $id = $product_id;
        $shortId = "";
        $order = Order::findOrFail($order_id);
        if(!empty($pincode))
        { 
            // $distributorId = Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
            // $shortId = MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
        }

        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
          }else{
              $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
          }

                $product = Product::findOrFail($id);
                $price = $product->unit_price;

                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                $stock_price = $productstock->price;

                    if(!empty($pincode))
                    { 
                        // $distributorId = Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
                        // $shortId = MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
                        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
                    }

                    if(!empty($shortId)){
                        $productmap = MappingProduct::where(['sorting_hub_id'=>$shortId->sorting_hub_id,'product_id'=>$id])->first();
                        $price = $productmap['purchased_price'];
                        $stock_price = $productmap['selling_price'];

                        if($price == 0 || $stock_price == 0){
                            
                            $productold = Product::findOrFail($id);
                            $price = $productold->unit_price;
                            $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                            $stock_price = $productstock->price; 
                        }  

                    }
                
                $order_detail = OrderDetail::findOrFail($orderDetailId);
                $quantity = $order_detail->quantity;

                $main_discount = $stock_price - $price;
                
                $total_discount_percent += substr($peer_discount_check['customer_discount'], 1, -1);
                $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);

                $total_master_percent += substr($peer_discount_check['company_margin'], 1, -1);
                $master_percent = substr($peer_discount_check['company_margin'], 1, -1);
               // $master_last_price = ($main_discount * $master_percent)/100;
                $master_last_price = $peer_discount_check['master_commission'];
                $master_percentage += $master_last_price*$quantity;

               // $last_price = ($main_discount * $discount_percent)/100;
                $last_price = $peer_discount_check['customer_off'];
                $ref_dis += $last_price*$quantity;
                $total_peer_percent += substr($peer_discount_check['peer_discount'], 1, -1);
                $peer_percent = substr($peer_discount_check['peer_discount'], 1, -1);
                //$peer_last_price = ($main_discount * $peer_percent)/100; 
                $peer_last_price = $peer_discount_check['peer_commission'];
                $peer_percentage += $peer_last_price*$quantity;
                
                $last_subprice = $peer_discount_check['peer_commission'];
                $last_masterprice = $peer_discount_check['master_commission'];

                $order->referal_discount = $ref_dis;
                $order->save();

                if($order->referal_discount!=0){
                    $order_detail->peer_discount = $last_price*$quantity;
                    $order_detail->sub_peer = $last_subprice*$quantity;
                    $order_detail->master_peer = $last_masterprice*$quantity;
                }else{
                     $order_detail->peer_discount = 0;
                     $order_detail->sub_peer = 0;
                     $order_detail->master_peer = 0;
                }  
                $order_detail->save();
                
                //Add data in referal usage and orderReferral Commission
                if(!empty($order->user_id)){
                    $u_id = $order->user_id;
                }else{
                    $u_id = '';
                }

                    $partner_id = PeerPartner::where('code', $referal_code)->first()->id;
               
                    $total_amount = $order->grand_total;
                    $order->grand_total = $total_amount - $ref_dis;
                    $order->referal_discount =  $ref_dis;                  

                    $referal_usage = new ReferalUsage;
                    $referal_usage->user_id = $u_id;
                    $referal_usage->partner_id = $partner_id;
                    $referal_usage->order_id = $order->id;
                    $referal_usage->referal_code = $referal_code;
                    $referal_usage->discount_rate = $total_discount_percent;
                    $referal_usage->discount_amount = $peer_percentage;
                    $referal_usage->commision_rate = $total_peer_percent;
                    $referal_usage->master_discount = $master_percentage;
                    $referal_usage->master_percentage = $total_master_percent;
                    $referal_usage->save();
                    
                    $ReferalUsage = ReferalUsage::where('order_id', $order->id)->first();
                if(!empty($ReferalUsage)){

                    $OrderReferalCommision = new OrderReferalCommision;
                    $OrderReferalCommision->partner_id = $ReferalUsage->partner_id;
                    $OrderReferalCommision->order_id = $order->id;
                    $OrderReferalCommision->order_amount = $order->grand_total;
                    $OrderReferalCommision->refral_code = $ReferalUsage->referal_code;
                    $OrderReferalCommision->referal_code_commision = $ReferalUsage->commision_rate;
                    $OrderReferalCommision->referal_commision_discount = $ReferalUsage->discount_amount;
                    $OrderReferalCommision->master_commission = $ReferalUsage->master_percentage;
                    $OrderReferalCommision->master_discount = $ReferalUsage->master_discount;

                    $OrderReferalCommision->save();

        }
    }

    public function ApplyPeerDiscountOld($orderId,$product_id,$pincode,$orderDetailId){
        $order = Order::findOrFail($orderId);
        $id = $product_id;
        $shortId = "";
        if(!empty($pincode))
        { 
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
        }

        if(!empty($shortId))
        {
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
        }else{
              $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
            }

                $product = Product::findOrFail($id);
                $price = $product->unit_price;

                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                $stock_price = $productstock->price;

                    // if(!empty($pincode))
                    // { 
                    //     $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
                        
                    // }

                    if(!empty($shortId)){
                        $productmap = MappingProduct::where(['sorting_hub_id'=>$shortId->sorting_hub_id,'product_id'=>$id])->first();
                        $price = $productmap['purchased_price'];
                        $stock_price = $productmap['selling_price'];

                        if($price == 0 || $stock_price == 0){
                            
                            // $productold = Product::findOrFail($id);
                            $price = $product->unit_price;
                            // $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                            $stock_price = $productstock->price; 
                        }  

                    }

                $order_detail = orderDetail::findOrFail($orderDetailId);
                $quantity = $order_detail->quantity;

                $main_discount = $stock_price - $price;
                
                $this->total_discount_percent += substr($peer_discount_check['customer_discount'], 1, -1);
                $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);

                $this->total_master_percent += substr($peer_discount_check['company_margin'], 1, -1);
                $master_percent = substr($peer_discount_check['company_margin'], 1, -1);
                //$master_last_price = ($main_discount * $master_percent)/100;
                $master_last_price = $peer_discount_check['master_commission'];
                $this->master_percentage += $master_last_price*$quantity;

                //$last_price = ($main_discount * $discount_percent)/100;
                $last_price = $peer_discount_check['customer_off'];

                $total_discount_to_customer = $last_price*$quantity;
                $this->ref_dis += $last_price*$quantity;
                $this->total_peer_percent += substr($peer_discount_check['peer_discount'], 1, -1);
                $peer_percent = substr($peer_discount_check['peer_discount'], 1, -1);
                //$peer_last_price = ($main_discount * $peer_percent)/100; 
                $peer_last_price = $peer_discount_check['peer_commission'];
                $this->peer_percentage += $peer_last_price*$quantity;
                
                //$last_subprice = ($main_discount * $peer_percent)/100;
                $last_subprice = $peer_discount_check['peer_commission'];
                //$last_masterprice = ($main_discount * $master_percent)/100;
                $last_masterprice = $peer_discount_check['master_commission'];

                $order->referal_discount = ($order->referal_discount+$total_discount_to_customer);
                $order->save();
                if($order->referal_discount!=0){
                    // $order_detail->peer_discount = $last_price*$quantity;
                    $order_detail->sub_peer = $last_subprice*$quantity;
                    $order_detail->master_peer = $last_masterprice*$quantity;
                }else{
                     // $order_detail->peer_discount = 0;
                     $order_detail->sub_peer = 0;
                     $order_detail->master_peer = 0;
                }  
                $order_detail->save();
                
                //Add data in referal usage and orderReferral Commission
    }

    public function updateCommission($orderId){
        $order = Order::findOrFail($orderId);

        if(!empty($order->user_id)){
            $u_id = $order->user_id;
        }else{
            $u_id = '';
        }
            $partner_id = PeerPartner::where('code', $this->referal_code)->first()->user_id;

            $referal_usage = new ReferalUsage;
            $referal_usage->user_id = $u_id;
            $referal_usage->partner_id = $partner_id;
            $referal_usage->order_id = $order->id;
            $referal_usage->referal_code = $this->referal_code;
            $referal_usage->discount_rate = $this->total_discount_percent;
            $referal_usage->discount_amount = $this->peer_percentage;
            // $referal_usage->commision_rate = $this->total_peer_percent;
            $referal_usage->commision_rate = $this->peer_percentage;
            $referal_usage->master_discount = $this->master_percentage;
            $referal_usage->master_percentage = $this->total_master_percent;
            $referal_usage->save();
 
    }

    public function updateReferalCommission($order){
        $ReferalUsage = ReferalUsage::where('order_id', $order->id)->first();
        if(!empty($ReferalUsage)){

            $OrderReferalCommision = new OrderReferalCommision;
            $OrderReferalCommision->partner_id = $ReferalUsage->partner_id;
            $OrderReferalCommision->order_id = $order->id;
            $OrderReferalCommision->order_amount = $order->grand_total;
            $OrderReferalCommision->refral_code = $ReferalUsage->referal_code;
            $OrderReferalCommision->referal_code_commision = $ReferalUsage->commision_rate;
            $OrderReferalCommision->referal_commision_discount = $ReferalUsage->discount_amount;
            $OrderReferalCommision->master_commission = $ReferalUsage->master_percentage;
            $OrderReferalCommision->master_discount = $ReferalUsage->master_discount;

            $OrderReferalCommision->save();
        }
    }

    public function getRemainAmountRazorpay($pay_id){
        
        $response = array();
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $payment = $api->payment->fetch($pay_id);
        //echo ($payment->status);exit;
        return $payment;
        

    }

    public function razorpayFullRefund($pay_id){
        $response = array();
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $payment = $api->payment->fetch($pay_id);
        // dd($payment);
        if($payment['status'] == "captured" && $payment['status'] != "refunded"){
            $refund = $payment->refund();
            $response['status'] = 200;
            $response['refund_response'] = $refund;
        }else{
            $response['status'] = 404;
            $response['refund_response'] = $payment;
        }
        return $response;


    }

    public function captureAuthorizePaymentRazorpay($pay_id){
        $response = [];
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $payment = $api->payment->fetch($pay_id);
        if($payment->status=="authorized"){
            $response = $payment->capture(array('amount'=>$payment->amount, 'currency' => 'INR'));
        }
        
        return $response;


    }

    public function sendOrderMail($order){
        //stores the pdf for invoice
       $pdf = PDF::setOptions([
                       'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                       'logOutputFile' => storage_path('logs/log.htm'),
                       'tempDir' => storage_path('logs/')
                   ])->loadView('invoices.customer_invoice', compact('order'));
       $output = $pdf->output();
       file_put_contents('public/invoices/'.'Order#'.$order->code.'.pdf', $output);
       $array['view'] = 'emails.invoice';
       $array['subject'] = 'Rozana Order Placed - '.$order->code;
       $array['from'] = env('mail_from_address');
       $array['content'] = view('emails.new_order', compact('order'))->render(); //translate('Dear Customer, A new order has been placed. You can check your order details in the invoice attached below. Please reach out to us in the case of any queries on customercare@rozana.in');
       $array['file'] = 'public/invoices/Order#'.$order->code.'.pdf';
       $array['file_name'] = 'Order#'.$order->code.'.pdf';

       //sends email to customer with the invoice pdf attached

       //$product_id = $request->session()->get('cart')[0]['id'];
        $postal_code = json_decode($order->shipping_address)->postal_code; 
        $sorting_hub_id = ShortingHub::whereRaw(
                'JSON_CONTAINS(area_pincodes, \'["'.$postal_code.'"]\')'
            )->select('user_id')->first();
        $sh_id = $sorting_hub_id['user_id'];
        $sh_name = User::where('id', $sh_id)->first()->email;
           if(env('MAIL_USERNAME') != null){
            Mail::to(json_decode($order->shipping_address)->email)->queue(new InvoiceEmailManager($array));
               try {
                   //Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
                   //Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                   //Mail::to(User::where('id', $sh_id)->first()->email)->queue(new InvoiceEmailManager($array));
               } catch (\Exception $e) {

               }
           }

       }

       public function OrderResponse($id)
       {
        $order = DB::table('orders')
        ->LeftJoin('order_details','orders.id','=','order_details.order_id')
        ->RightJoin('products','order_details.product_id','=','products.id')
        ->where('orders.id','=',$id)  
        ->orderBy('orders.created_at', 'desc')
        ->select('orders.code','orders.referal_discount as discount','orders.total_shipping_cost as shipping_cost','order_details.delivery_status as delivery_code','orders.payment_type','orders.shipping_address','orders.wallet_amount','order_details.variation','order_details.price','order_details.quantity','order_details.tax as ptax','products.name','products.rating','products.unit_price','orders.coupon_discount','products.manage_by','products.id as product_id','products.thumbnail_img','order_details.id as orderdetail_id', 'orders.payment_status','orders.date', 'orders.grand_total','order_details.delivery_status')
        ->get();
        $tax = $order->sum('ptax');
        $total_price = $order->sum('price');
        $orders = $order->map(function($item) use($tax,$total_price){
            $item->totalOrderPrice = round($total_price,2);
            $item->tax = $tax;
            $item->shipping_address = json_decode($item->shipping_address);
            $item->date = date('d-m-Y',$item->date);
            if($item->payment_type=="wallet" || ($item->payment_type=="razorpay" && $item->wallet_amount!=0)){
                $item->grand_total = ($item->grand_total+$item->wallet_amount);
            }
            return $item;
           });
        return $orders;
       }

       // public function smsToMasterPeerOnOrderPlaced($peer_code){
       //  $partner_id = PeerPartner::where('code', $peer_code)->first();
       //  $master_phone = PeerPartner::where('id', $partner_id->parent)->first();
       //  $to = $master_phone->phone;
       //  $from = "RZANA";
       //  $tid = "1707163117081922481";

       //  $msg = "Hello Rozana Master Peer, we are pleased to inform you that the ".$peer_code." peer code has been used to place an order. Points will be credited to your account once the order is delivered. We want to ensure that you have a good experience and welcome any concerns or suggestions. You can call us on +91 9667018020. Team Rozana";
       //  mobilnxtSendSMS($to,$from,$msg,$tid);
       // }

       public function smsToMasterPeerOnOrderDelivered($partner_phone,$peer_code,$master_discount){
        $to = $partner_phone;
        $from = "RZANA";
        // $tid = "1707163117111494696";
        $tid = "1707164406638962447";

        // $msg = "Hello Rozana Master Peer, an order has been delivered to your customer using ".$peer_code." Peer Code. You have received ".$master_discount." points in your Rozana wallet. To review your points please log into your Rozana dashboard. Thank you for helping make Rozana a part of everyone’s daily lives. Feel free to reach out to us for any concerns or queries on +91 9667018020. Team Rozana";
        $msg = "Dear Master Peer, an order has been delivered using ".$peer_code." Peer Code. You have received ".$master_discount." points in your Rozana wallet. Team Rozana.";
        mobilnxtSendSMS($to,$from,$msg,$tid);
       }

       public function createHistoryInWallet($user_id,$wallet_amount,$id){
        $wallet = new \App\Wallet;
        $wallet->user_id = $user_id;
        $wallet->amount = $wallet_amount;
        $wallet->order_id = $id;
        $wallet->tr_type = 'debit';
        $wallet->payment_method = 'wallet';

        if($wallet->save()){
            return true;
        }
        return false;

    }

    public function OrderResponseDetail($id){
        $orderData = DB::table('orders')
                ->where('orders.id','=',$id) 
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.id as order_id','orders.code','orders.referal_discount as discount','orders.total_shipping_cost as shipping_cost','orders.order_status','orders.payment_type','orders.shipping_address','orders.wallet_amount','orders.coupon_discount','orders.payment_status','orders.date', 'orders.grand_total')->get();

        $order = DB::table('orders')
                ->LeftJoin('order_details','orders.id','=','order_details.order_id')
                ->RightJoin('products','order_details.product_id','=','products.id')
                ->where('orders.id','=',$id) 
                ->whereNull('order_details.deleted_at') 
                ->orderBy('orders.created_at', 'desc')
                ->select('order_details.delivery_status as delivery_code','order_details.id as order_detail_id','order_details.variation','order_details.price as price','order_details.quantity','order_details.tax as ptax',trans('products.name'),'products.rating','products.unit_price','orders.coupon_discount','products.manage_by','products.id as product_id','products.thumbnail_img','order_details.id as orderdetail_id','order_details.delivery_status','order_details.updated_at')->get();
        // $schedule = DB::table('sub_orders')
            // ->where('order_id','=',$id)->where('status',1)
            // ->get();

        // $deliveryType = DB::table('sub_orders')
            // ->where('order_id','=',$id)->where('status',1)->pluck('delivery_type')
            // ->first();

        // $is_fresh = 0;
        // $is_grocery = 0;
        // foreach($schedule as $data){
        //     if($data->delivery_name == 'fresh'){
        //         $is_fresh = 1;
        //     }
        //     if($data->delivery_name == 'grocery'){
        //         $is_grocery = 1;
        //     }
        // }

                $tax = $order->sum('ptax');
                $total_price = $order->sum('price');

                $orders = $orderData->map(function($item) use($order,$tax,$total_price){
                    $item->date = date('d-m-Y',$item->date);
                    $item->shipping_address = json_decode($item->shipping_address);
                    if($item->payment_type =="wallet" || ($item->payment_type=="razorpay" && $item->wallet_amount!=0)){
                        $item->grand_total = ($item->grand_total+$item->wallet_amount);
                    }
                    // $item->is_fresh = $is_fresh;
                    // $item->is_grocery = $is_grocery;
                    // $item->deliveryType = $deliveryType;
                    // $item->deliveryType = empty($deliveryType)?'':$deliveryType;
                    // if($item->deliveryType == 'scheduled'){
                        
                        // $slot = array();
                        // foreach($schedule as $value){
                        //     $slot[$value->delivery_name]['sub_order_code'] = $value->sub_order_code;
                        //     $slot[$value->delivery_name]['delivery_date'] = $value->delivery_date;
                        //     $slot[$value->delivery_name]['delivery_time'] = $value->delivery_time;
                        // }

                    // }else{
                    //     $slot = array();
                    //     $slot['fresh']['delivery_date'] = '';
                    //     $slot['fresh']['delivery_time'] = '';
                    //     $slot['grocery']['delivery_date'] = '';
                    //     $slot['grocery']['delivery_time'] = '';
                    // }
                    // $item->delivery_detail = $slot;

                    $item->details = $order->map(function($data) use($tax,$total_price){
                        $data->name = trans($data->name);
                        $data->reviewCount = \App\Review::where(['product_id'=>$data->product_id,'status'=>1])->get()->count();
                        $data->tax = $tax;
                        $check_return = \App\RefundRequest::where(['order_detail_id'=>$data->order_detail_id])->first();
                        $check_replacement = \App\ReplacementOrder::where(['order_detail_id'=>$data->order_detail_id])->first();
                        $replacement = "";
                        if(!is_null($check_replacement)){
                            if($check_replacement->approve==1){
                                 $replacement = "1";
                                 if($check_replacement->replaced==1){
                                    $replacement = "2";
                                }
                            }else{
                                $replacement = "0";
                            }
                        }
                        $return =0;
                        if(empty($check_return)){
                            // display request button
                            $return = 0; 
                        }elseif($check_return["sorting_hub_approval"] != 1 || $check_return["admin_approval"] != 1){
                            // Return already requested
                            $return = 1;
                        }elseif($check_return["sorting_hub_approval"] == 1 || $check_return["admin_approval"] == 1){
                            // Return request approved
                            $return = 2;
                        }
                        $data->return_status = $return;
                        $data->replacement_status = $replacement;
                        $ddate = Carbon::parse($data->updated_at);
                        $data->can_replace = true;
                        if($data->delivery_status=='delivered' && $ddate->diffInMinutes()>24*60)
                        {
                            $data->can_replace = false;
                        }
                        
                        return $data;
                        });
                    return $item;
                });

                return $orders;
    }

    public function takeOrder(Request $request)
       {
            $order = Order::where('code', $request->order_code)->first();
            $check = AssignOrder::where('order_id',$order->id)->first();

            $dboy = DeliveryBoy::where('user_id',$request->id)->first()['id'];

            if(!$check)
            {
                $assignment = new AssignOrder();

                $assignment->delivery_boy_id = $dboy;
                $assignment->order_id = $order->id;

                if($assignment->save())
                {
                    return response()->json([
                        'success'=>true,
                        //'order'=>[],
                        'message'=>"Order assigned successfully."
                    ]);
                }
                else{
                    return response()->json([
                        'success'=>false,
                        //'order'=>[],
                        'message'=>"Failed to assign order."
                    ]);
                }
            }
            else{
                return response()->json([
                    'success'=>false,
                    //'order'=>[],
                    'message'=>"Order already assigned."
                ]);
            }
       }

    public function updateOrderStatus(Request $request){
        $order_id = $request->order_id;
        $order_status = $request->order_status;

        if(empty($order_id) || empty($order_status)){
             return response()->json([
                    'success'=>false,
                    'message'=>"Order status or order id required."
                ]);
        }
        $order = Order::findOrFail($order_id);
        DB::beginTransaction();
        try{
            DB::table('orders')
                    ->where('id', $order_id) 
                    ->update( ['order_status' => $order_status] 
                );

            DB::table('order_details')
                    ->where('order_id', $order_id) 
                    ->update( ['delivery_status' => $order_status] 
                );

                if(($request->order_status == 'delivered' && $order->payment_status == 'paid') || ($request->order_status == 'delivered' && $order->payment_type == "cash_on_delivery")){
                
                  
                    $OrderReferalCommision = OrderReferalCommision::where('order_id', $order_id)->first();
                  
                    if(!empty($OrderReferalCommision) && $OrderReferalCommision->wallet_status == 0){
                        $partner = PeerPartner::where('user_id', $OrderReferalCommision->partner_id)->first();
                       
                         if(!empty($partner) && $partner->verification_status == 1 && $partner->parent != 0){
                           
                                $select_partner = PeerPartner::where('id', $partner->parent)->first();
                                $master_partner = User::find($select_partner->user_id);
                               
                                $mastertotal_balance = $master_partner->balance+$OrderReferalCommision->master_discount;
                                
                                $master_partner->balance = $mastertotal_balance;
                                $master_partner->save();
                                
                                //$this->smsToMasterPeerOnOrderDelivered($select_partner->phone,$select_partner->code,$OrderReferalCommision->master_discount);
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

            $sub_order = SubOrder::where('order_id',$order_id)->get();
            if($sub_order){
                DB::table('sub_orders')
                    ->where('order_id', $order_id) 
                    ->update( ['delivery_status' => $order_status] 
                );
            }
            
            DB::commit();

            return response()->json([
                    'success'=>true,
                    'message'=>"Order status updated successfully."
                ]);
        } catch(\Exception $e){
            echo $e;
            DB::rollback(); 
            info($e);
            return response()->json([
                'success'=>false,
                'message'=>"Status not updated."
            ]);
        }

    }
    
    public function walletRefund(Request $request){
        DB::beginTransaction();
        try{
                $user_wallet = User::where('id', $request->user_id)->first();
                $order = Order::where('code',$request->code)->first();

                $wallet = \App\Wallet::where('order_id',$order->id)
                                ->where('payment_method','refund')
                                ->where('user_id',$request->user_id)->get();

                if(count($wallet) > 0){
                    return response()->json([
                        'success'=>false,
                        'balance' => round($user_wallet->balance,2),
                        'message'=>'Wallet amount already refunded.'
                    ]);
                }else{
                        Order::where('code',$request->code)->update([
                        'wallet_refund_status' => 0,
                        //'order_status' => 'cancel'
                        ]);

                    
                    $last_wallet = $user_wallet->balance + $request->amount;
                    $referall_balance = User::where('id', $request->user_id)->update([
                                                'balance' => $last_wallet,
                                    ]);
                
                    $wallet = new \App\Wallet;
                    $wallet->user_id = $request->user_id;
                    $wallet->amount = $request->amount;
                    $wallet->order_id = $order->id;

                    $wallet->tr_type = 'credit';
                    $wallet->payment_method = 'refund';

                    $wallet->save();
                    DB::commit();
                    
                    return response()->json([
                        'success'=>true,
                        'balance' => round($last_wallet,2),
                        'message'=>'Wallet amount refund successfully.'
                    ]);
                }

                
              
            } catch(\Exception $e){
            echo $e;
            DB::rollback();
            info($e);
            return response()->json([
                'success'=>false,
                'order'=>[],
                'message'=>"Unable to process your order.Try again later"
            ]);
        }


    }  
}