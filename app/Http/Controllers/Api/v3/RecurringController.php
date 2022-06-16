<?php

namespace App\Http\Controllers\Api\v3;
use App\Http\Resources\v3\RecurringCollection;
use Illuminate\Http\Request;
use App\Models\Order;

use App\Models\Cart;
use App\Product;
use App\Models\OrderDetail;

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
use App\SubscribedOrder;
use App\Address;
use App\RefundRequest;

class RecurringController extends Controller
{
    public $ref_dis = 0;
    public $peer_percentage = 0;
    public $total_peer_percent = 0;
    public $total_discount_percent = 0;
    public $total_master_percent = 0;
    public $master_percentage = 0;
    public $referal_code = "";

    public $min_order_amount = 0;
    public $free_shipping_amount = 0;
    public function __construct(){
        $this->min_order_amount =  (int) env("MIN_ORDER_AMOUNT");
        $this->free_shipping_amount = (int) env("FREE_SHIPPING_AMOUNT");
    }

    use OrderTrait;

    // public function checkRecurOrderPayment(){
    //     // get unpaid razorpay order and log 1 
    //     // $startDate = "2021-11-15";
    //      $orders = SubscribedOrder::where('status','1')->select('id')->first();

            
    //             $product = SubscribedOrder::findOrFail($orders->id);
    //             $product->status = 0;
    //             $product->save();
         
    //      // info($payment);
    //      info("Recurring Order cron run");
    //  }

    public function OrderRecurringPayment()
    {
        $currentDateTime = Carbon::now();
        $end_date = date("Y-m-d",strtotime($currentDateTime));
        $start_date = date("Y-m-d",strtotime($currentDateTime));
        $subscribed_orders = SubscribedOrder::where('status','1')->whereDate('start_date', '<=', $start_date)->whereDate('end_date', '>=', $end_date)->get();

        if(count($subscribed_orders) > 0){

                foreach($subscribed_orders as $key => $row){
                        $order = new Order;
                        $order->user_id = $row->user_id;
                        $order->platform = $row->platform;
                        $order->subscribed_id = $row->id;
                        $order->grand_total = $row->total;
                        $order->wallet_amount = $row->total;
                        $order->test_grand_total = $row->total;

                        $lastorderID = Order::orderBy('id', 'desc')->first();
                        if(!empty($lastorderID)){
                            $orderId = $lastorderID->id;
                        }else{
                            $orderId = 1;
                        }

                        $address = Address::where('id', $row->address_id)->first();
                        $users = User::where('id', $row->user_id)->first();

                        $myObj = array(
                            "name" => $users->name,
                            "email" => $users->email,
                            "address" => $address->address,
                            "country" => $address->country,
                            "city" => $address->city,
                            "postal_code" => $address->postal_code,
                            "phone" => $address->phone,
                            "state" => $address->state,
                            "checkout_type" => "logged",
                        );

                        

                        $datetime = "";

                        $order->shipping_address = json_encode($myObj);
                        $order->billing_address = json_encode($myObj);
                        $order->shipping_pin_code = $row->pincode;
                        $order->payment_type = 'wallet';
                        $order->payment_status = $row->payment_status;
                        $order->delivery_viewed = '0';
                        $order->payment_status_viewed = '0';
                        $order->code = 'ORD'.mt_rand(10000000,99999999).$orderId;
                        $order->date = !empty($datetime)?strtotime($datetime):strtotime('now');
                        $order->order_status = "pending";
                        $total_price = $row->total;
                        $order->used_referral_code = empty($row->peer)?0:1;
                        $order->delivery_type = 'normal';

                        //start
                        if($order->save()){
                            $subtotal = 0;
                            $tax = 0;
                            $shipping = 0;
                            $ref_dis = 0;
                            $peer_percentage = 0;
                            $total_peer_percent = 0;
                            $total_discount_percent = 0;
                            $total_master_percent = 0;
                            $master_percentage = 0;

                                $product_stock = ProductStock::where('product_id', $row->product_id)->first();
                                $product_variation = $product_stock->variant;

                                $product = Product::where('id', $row->product_id)->first();

                                $productmap_stock = MappingProduct::where(['sorting_hub_id' => $row->sorting_id,'product_id' => $row->product_id])->first();
                                if($productmap_stock['qty'] != 0){
                                    $productmap_stock['qty'] -= $row->quantity;
                                    $productmap_stock->save();
                                }else{
                                    if($product_variation != null){
                                        $product_stock->qty -= $row->quantity;
                                        $product_stock->save();
                                    }
                                    else {
                                        $product->current_stock -= $row->quantity;
                                        $product->save();
                                    }
                                }    

                                if( $product->category_id == '18'|| $product->category_id =='26' || $product->category_id =='34' || $product->subcategory_id == '129' || $product->subcategory_id == '67' || $product->category_id =='33' || $product->category_id =='38' || $product->cstegory_id=='39' || $product->category_id =='40'){
                                    $ordertype = "fresh";
                                }else{
                                    $ordertype = "grocery";
                                }

                                $delivery_date = date("Y-m-d",strtotime($currentDateTime));
                                $deliverytype = trans("normal");
                                $payment_status = trans("paid");
                                $delivery_status = "pending";  
                                $delivery_time = "07:00 AM - 11:00 AM";

                                 $schedule =  SubOrder::create(['order_id' => $order->id,
                                'delivery_name' => trans($ordertype),
                                'delivery_type' => trans($deliverytype),  
                                'delivery_date' => $delivery_date,
                                'delivery_time' => $delivery_time,  
                                'status' => 1,
                                'payment_status' => trans($payment_status),
                                'delivery_status' =>trans($delivery_status)]);

                                //order detail
                                $order_detail = new OrderDetail;
                                $order_detail->order_id = $order->id;
                                $order_detail->order_type = $ordertype;
                                $order_detail->seller_id = $product->user_id;
                                $order_detail->product_id = $row->product_id;
                                $order_detail->variation = $product_variation;
                                $order_detail->price = $row->total;
                                // $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                                $order_detail->shipping_type = 'wallet';
                                $order_detail->product_referral_code = '';
                                $order_detail->shipping_cost = 0;
                                $order_detail->payment_status = $payment_status;

                                //for peer
                                $id = $row->product_id;
                            
                            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $row->sorting_id. '"]\')')->latest('id')->first();
                           

                            $product = Product::findOrFail($id);
                            $price = $product->unit_price;

                            $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                            $stock_price = $product->unit_price;
                            if(!is_null($productstock)){
                                $stock_price = $productstock->price; 
                            }       

                               
                            $productmap = MappingProduct::where(['sorting_hub_id'=>$row->sorting_id,'product_id'=>$id])->first();
                            $price = $productmap['purchased_price'];
                            $stock_price = $productmap['selling_price'];

                            if($price == 0 || $stock_price == 0){
                                 $id = $row->product_id;
                                $productold = Product::findOrFail($id);
                                $price = $productold->unit_price;
                                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                                $stock_price = $product->unit_price;
                                if(!is_null($productstock)){
                                    $stock_price = $productstock->price; 
                                }  
                            }                   
                            

                            $main_discount = $stock_price - $price;
                            
                            $total_discount_percent += substr($peer_discount_check['customer_discount'], 1, -1);
                            $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);

                            $total_master_percent += substr($peer_discount_check['company_margin'], 1, -1);
                            $master_percent = substr($peer_discount_check['company_margin'], 1, -1);
                            $master_last_price = $peer_discount_check['master_commission'];
                            $master_percentage += $master_last_price*$row->quantity;

                            $last_price = $peer_discount_check['customer_off'];
                            $prices = $stock_price - $last_price;
                            $ref_dis += $last_price*$row->quantity;

                            $total_peer_percent += substr($peer_discount_check['peer_discount'], 1, -1);
                            $peer_percent = substr($peer_discount_check['peer_discount'], 1, -1);
                            $peer_last_price = $peer_discount_check['peer_commission'];
                            $peer_percentage += $peer_last_price*$row->quantity;
                            // die;

                            $order_detail->quantity = $row->quantity;

                            //10july by neha
                            $last_subprice = $peer_discount_check['peer_commission'];
                            $last_masterprice = $peer_discount_check['master_commission'];
                            $last_rozanamargin = $peer_discount_check['rozana_margin'];
                            $last_margin = $peer_discount_check['margin'];  

                            $taxp = ($prices*100)/(100+$product->tax);
                            $tax = (($taxp*$product->tax)/100)*$row->quantity;

                            $order_detail->peer_discount = $last_price*$row->quantity;
                            $order_detail->sub_peer = $last_subprice*$row->quantity;
                            $order_detail->master_peer = $last_masterprice*$row->quantity;
                            $order_detail->orderrozana_margin = $last_rozanamargin*$row->quantity;
                            $order_detail->order_margin = $last_margin*$row->quantity;
                            $order_detail->tax = $tax;

                            $order_detail->save();
                            $product->num_of_sale++;
                            $product->save();
                            
                            //peer partnr
                            $partner_id = PeerPartner::where('code', $row->peer)->first();

                            $order->referal_discount =  $ref_dis;
                            if(!is_null($partner_id)){
                            $referal_usage = new ReferalUsage;
                            $referal_usage->user_id = $row->user_id;
                            $referal_usage->partner_id = $partner_id->id;
                            $referal_usage->order_id = $order->id;
                            $referal_usage->referal_code = $row->peer;
                            $referal_usage->discount_rate = $total_discount_percent;
                            $referal_usage->discount_amount = $peer_percentage;
                            $referal_usage->commision_rate = $total_peer_percent;
                            $referal_usage->master_discount = $master_percentage;
                            $referal_usage->master_percentage = $total_master_percent;
                            $referal_usage->save();

                            $master_phone = PeerPartner::where('id', $partner_id->parent)->first();
                        }
                            // $to = $master_phone->phone;
                            // $from = "RZANA";
                            // $tid  = "1707163117081922481"; 

                            // $msg = "Hello Rozana Master Peer, we are pleased to inform you that the ".$row->peer." peer code has been used to place an order. Points will be credited to your account once the order is delivered. We want to ensure that you have a good experience and welcome any concerns or suggestions. You can call us on +91 9667018020. Team Rozana";
                            //     mobilnxtSendSMS($to,$from,$msg,$tid);

                            $order->save();

                            $order = Order::findOrFail($order->id);
                            // $this->sendOrderMail($order);
                            // SMSonOrderPlaced($order->id);
                            info("Recurring Order cron run");
                        
                            }
                    // }else{
                    //     // dd('no');
                    //     $subscribed_status = SubscribedOrder::findOrFail($row->id);
                    //     $subscribed_status->status = 0;
                    //     $subscribed_status->save();
                    //     //fail template
                    //     $user_phone = user::where('id', $row->user_id)->first();
                    //     $to = $user_phone->phone;
                    //     // $from = "RZANA";
                    //     // $tid  = "1707163117081922481"; 
                    //     // $msg = "Hello Rozana Master Peer, we are pleased to inform you that the ".$row->peer." peer code has been used to place an order. Points will be credited to your account once the order is delivered. We want to ensure that you have a good experience and welcome any concerns or suggestions. You can call us on +91 9667018020. Team Rozana";
                    //     // mobilnxtSendSMS($to,$from,$msg,$tid);

                    //     //fail mail
                    //     $detail = array(
                    //             'email'=>$user_phone->email
                    //         );

                    //     $user_phone->notify(new FailEmail($detail));
                    //     info("Recurring wallet balance low");
                    // }            

                } 
                   
        }else{
            info("No recurring cron order found");
        }        
    }
    
       
    public function orderRecurring(Request $request){
        DB::beginTransaction();
        try{
            $subscribedID = SubscribedOrder::orderBy('id', 'desc')->first();
                if(!empty($subscribedID)){
                    $subscribedId = $subscribedID->id;
                }else{
                    $subscribedId = 1;
                }

            $subscribed_code = 'REC'.mt_rand(10000000,99999999).$subscribedId;
            $order_subscribed = new SubscribedOrder;
            $order_subscribed->subscribed_code = $subscribed_code;
            $order_subscribed->platform = $request->platform;
            $order_subscribed->user_id = $request->user_id;
            $order_subscribed->product_id = $request->product_id;
            $order_subscribed->sorting_id = $request->sorting_id;
            $order_subscribed->address_id = $request->address_id;
            $order_subscribed->stock_price = $request->stock_price;
            $order_subscribed->quantity = $request->quantity;
            $order_subscribed->price = $request->price;
            $order_subscribed->total = $request->total;
            $order_subscribed->pincode = $request->pincode;
            $order_subscribed->peer = $request->peer;
            $order_subscribed->start_date = $request->start_date;
            $order_subscribed->end_date = $request->end_date;
            $order_subscribed->plan_type = $request->plan_type;
            $order_subscribed->amount_by_wallet = $request->amount_by_wallet;
            $order_subscribed->payable_amount = $request->payable_amount;
            $order_subscribed->days = $request->days;
            $order_subscribed->payment_mode = $request->payment_type;
            $order_subscribed->payment_status = "Paid";
            $order_subscribed->tax = $request->tax;
            $order_subscribed->discount = $request->discount;
            $order_subscribed->shipping_cost = (double)$request->shipping_cost;
            $order_subscribed->status = 1;
            $order_subscribed->save();

            if(isset($request->user_id) && $request->payment_type =="wallet"){
                $user = User::findOrFail($request->user_id);
                $phone = Address::where('id', $request->address_id)->first()->phone;
                // $phone = "7479868457";
                $balance = $user->balance-$request->amount_by_wallet;
                $user->balance  = $balance;
                $user->save();

                $wallet = new \App\Wallet;
                $wallet->user_id = $request->user_id;
                $wallet->amount = $request->amount_by_wallet;
                $wallet->subscribed_id = $order_subscribed->id;
                $wallet->tr_type = 'debit';
                $wallet->payment_method = 'wallet';
                $wallet->save();
            }
            DB::commit();
            send_sms_recurring_order($phone,date('d-m-Y',strtotime($request->start_date)),date('d-m-Y',strtotime($request->end_date)),$order_subscribed->subscribed_code);
            return response()->json([
                'success' => true,
                'data' => $this->recurringOrderHistory($order_subscribed->id),
                'message' => trans('Your order has been subscribed successfully.')
                
            ]);
        }
        catch(\Exception $e){
            echo $e;
            DB::rollback();
            info($e);
            return response()->json([
                'success'=>false,
                'message'=>trans("Unable to process your order.Try again later")
            ]);
        }

        
   }

   public function orderRecurringByOnlinePay(Request $request){
        // $walletuser = User::where('id', $request->user_id)->select('balance')->first();
        DB::beginTransaction();
        try{
            $subscribedID = SubscribedOrder::orderBy('id', 'desc')->first();
                if(!empty($subscribedID)){
                    $subscribedId = $subscribedID->id;
                }else{
                    $subscribedId = 1;
                }

            $subscribed_code = 'REC'.mt_rand(10000000,99999999).$subscribedId;
            $order_subscribed = new SubscribedOrder;
            $order_subscribed->subscribed_code = $subscribed_code;
            $order_subscribed->platform = $request->platform;
            $order_subscribed->user_id = $request->user_id;
            $order_subscribed->product_id = $request->product_id;
            $order_subscribed->sorting_id = $request->sorting_id;
            $order_subscribed->address_id = $request->address_id;
            $order_subscribed->stock_price = $request->stock_price;
            $order_subscribed->quantity = $request->quantity;
            $order_subscribed->price = $request->price;
            $order_subscribed->total = $request->total;
            $order_subscribed->pincode = $request->pincode;
            $order_subscribed->peer = $request->peer;
            $order_subscribed->start_date = $request->start_date;
            $order_subscribed->end_date = $request->end_date;
            $order_subscribed->plan_type = $request->plan_type;
            $order_subscribed->days = $request->days;
            $order_subscribed->payable_amount = $request->payable_amount;
            $order_subscribed->payment_mode = $request->payment_type;
            $order_subscribed->payment_status = "unpaid";
            $order_subscribed->tax = $request->tax;
            $order_subscribed->discount = $request->discount;
            $order_subscribed->shipping_cost = (double)$request->shipping_cost;
            $order_subscribed->status = 0;
            $order_subscribed->save();

            $subscribed_id = $order_subscribed->id;
            $code = $request->peer;
            $amount = $request->payable_amount;

            $shipping_address = Address::where('id', $request->address_id)->first();

            if(isset($request->user_id) && $request->payment_type =="razorpay"){
                $live_key_id = env('RAZOR_KEY');//'rzp_live_l8DKZ6gXms4jgZ';
                $live_key_secret = env('RAZOR_SECRET');//'HTKO3E97mhe9RHQ2xccBYLIV';
               
                $api = new Api($live_key_id, $live_key_secret);
                    $orderinfo = $api->order->create(array(
                        'receipt' => $subscribed_id,
                        'amount' =>  round($amount,2)*100,
                        'currency' => 'INR',
                        'notes' => array(
                            'order_id' => $subscribed_id,
                            'name' => $shipping_address['name']
                            )
                        )
                    );
                $orderId = $orderinfo['id'];
                $orderinfo  = $api->order->fetch($orderId);

                $order_details = json_encode(array(     'id' => $orderinfo['id'],
                                            'entity' => $orderinfo['entity'],
                                            'amount' => $orderinfo['amount'],
                                            'currency' => $orderinfo['currency'],
                                            'receipt' => $orderinfo['receipt'],
                                            'status' => $orderinfo['status'],
                                            'attempts' => $orderinfo['attempts']
                                        ));

                $subscribedOrderUpdate = SubscribedOrder::where('id', $order_subscribed->id)->update(['order_create' => $order_details]);
                // $orderId = NULL;

                // LogActivity::addToPayment($order_details,$request->platform,$orderId,'success','success', 'post', '', $request->payment_type,$subscribed_id);

                $requestParams = json_encode(array('id' => $subscribed_id, 'amount' => $orderinfo['amount'] *100, 'currency' => 'INR'));

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
                    'code' => $subscribed_id
                ];

            }
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'success',
                'response' => $response,
                'order_detail' => json_decode($order_details),
                'shippingAddress' => $shipping_address
            ]);
        }
        catch(\Exception $e){
            echo $e;
            DB::rollback();
            info($e);
            return response()->json([
                'success'=>false,
                'message'=>"Unable to process your recurring order.Try again later"
            ]);
        }

        
   }

   public function recurringStore(Request $request){
        
            $requestPar = $request->all();
            $user_id = $requestPar['user_id']; 
            $json_de_payment =  $requestPar['payment_detail'];
            $payment_status = $requestPar['payment_status'];
            $payment_type = $requestPar['payment_type'];
            $code = $requestPar['code'];
    
            $dataArray = array();
            if($payment_status == 'success'){
                $order = SubscribedOrder::where('id',$code)->first();
                    if($payment_type == "razorpay"){
                        $orderId = $json_de_payment['order_id'];

                        $key_id      = env('RAZOR_KEY');
                        $keySecret   =  env('RAZOR_SECRET');
                        $api = new Api($key_id,$keySecret );
                        $orderinfo  = $api->order->fetch($orderId);
                        $order_details = json_encode(array( 'id' => $orderinfo['id'],
                                                            'entity' => $orderinfo['entity'],
                                                            'amount' => $orderinfo['amount'],
                                                            'currency' => $orderinfo['currency'],
                                                            'receipt' => $orderinfo['receipt'],
                                                            'status' => $orderinfo['status'],
                                                            'attempts' => $orderinfo['attempts']
                                                            ));
                                                            
                        // $orderupdate = SubscribedOrder::where('id',$code)
                        // ->update(['payment_status' => 'Paid',
                        //             'payment_details' => json_encode($json_de_payment) ,
                        //             'status' => 1   
                        //         ]);  
                        // $phone = Address::where('id', $order->address_id)->first()->phone;
                        // $phone = "7479868457";
                        // send_sms_recurring_order($phone,date('d-m-Y',strtotime($order->start_date)),date('d-m-Y',strtotime($order->end_date)),$order->subscribed_code); 
    
                        // $status = true; 
                        // $data = $this->recurringOrderHistory($code);
                        // $message = "your order has been subscribed.";

                        if($orderinfo['status'] == 'paid'){
                            $orderupdate = SubscribedOrder::where('id',$code)
                            ->update(['payment_status' => 'Paid',
                                        'payment_details' => json_encode($json_de_payment) ,
                                        'status' => 1   
                                    ]);  

                            $phone = Address::where('id', $order->address_id)->first()->phone;
                            send_sms_recurring_order($phone,date('d-m-Y',strtotime($order->start_date)),date('d-m-Y',strtotime($order->end_date)),$order->subscribed_code);
        
                            $status = true; 
                            $data = $this->recurringOrderHistory($code);
                            $message = "your order has been subscribed.";

                        // }elseif($orderinfo['status'] == 'authorized'){

                        }else{
                            SubscribedOrder::where('id',$code)->delete();
                            $status = false; 
                            $data = '';
                            $message = "Payment failed,your order has not been subscribed, Try again.";
                        }
                    }
    
            }else{
                $status = false; 
                $data = '';
                $message = "your order has not been subscribed.";
            }
            return response()->json([
                'success' => $status,
                'data' => $data,
                'message' => $message,
            ]);
    
        
   }

   public function recurringList(Request $request){
        if(isset($_SERVER['HTTP_SORTINGHUBID']) && !empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            $products = MappingProduct::where('sorting_hub_id',$shortId)->where('recurring_status',1)->where('published',1)->get();

            return new RecurringCollection($this->recurring_products_with_peer_price($products,$shortId));
        }else{
                return [
                'success' => true,
                'status' => 200,
                'data' => []
            ];
        }
        
        
   }

   public function recurring_products_with_peer_price($products,$shortId){
        $quantity = 0;

        foreach($products as $key => $product){
        $customer_discount = 0;
        $discount_percentage = 0;

        $detail = \App\Product::where('id',$product->product_id)->first();

        $priceDetail = calculatePrice($product->product_id,$shortId);

        $variant = "";
        $pvariant = \App\ProductStock::where('product_id',$product->product_id)->first();
        if(!is_null($pvariant))
        {
            $variant = $pvariant->variant;
        }

        // $products[$key]['quantity'] = $quantity;
        $products[$key]['variant'] = $variant;
        $products[$key]['name'] = $detail['name'];
        $products[$key]['category_id'] = $detail['category_id'];
        $products[$key]['subcategory_id'] = $detail['subcategory_id'];
        $products[$key]['subsubcategory_id'] = $detail['subsubcategory_id'];
        $products[$key]['photos'] = $detail['photos'];
        $products[$key]['thumbnail_img'] = $detail['thumbnail_img'];
        $products[$key]['unit'] = $detail['unit'];
        $products[$key]['tax'] = $detail['tax'];
        $products[$key]['max_purchase_qty'] = $detail['max_purchase_qty'];
        $products[$key]['description'] = $detail['description'];
        $products[$key]['slug'] = $detail['slug'];
        $products[$key]['MRP'] = $priceDetail['MRP'];
        $products[$key]['selling_price'] = $priceDetail['selling_price'];
        $products[$key]['customer_off'] = $priceDetail['customer_off'];
        $products[$key]['customer_discount'] = $priceDetail['customer_discount'];
        $products[$key]['discount_type'] = $priceDetail['discount_type'];
        }
        return $products;
    }
   

   // public function sendOrderMail($order){
   //      //stores the pdf for invoice
   //     $pdf = PDF::setOptions([
   //                     'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
   //                     'logOutputFile' => storage_path('logs/log.htm'),
   //                     'tempDir' => storage_path('logs/')
   //                 ])->loadView('invoices.customer_invoice', compact('order'));
   //     $output = $pdf->output();
   //     file_put_contents('public/invoices/'.'Order#'.$order->code.'.pdf', $output);
   //     $array['view'] = 'emails.invoice';
   //     $array['subject'] = 'Rozana Order Placed - '.$order->code;
   //     $array['from'] = env('mail_from_address');
   //     $array['content'] = view('emails.new_order', compact('order'))->render();
   //     $array['file'] = 'public/invoices/Order#'.$order->code.'.pdf';
   //     $array['file_name'] = 'Order#'.$order->code.'.pdf';

   //     //sends email to customer with the invoice pdf attached

   //      $postal_code = json_decode($order->shipping_address)->postal_code; 
   //      $sorting_hub_id = ShortingHub::whereRaw(
   //              'JSON_CONTAINS(area_pincodes, \'["'.$postal_code.'"]\')'
   //          )->select('user_id')->first();
   //      $sh_id = $sorting_hub_id['user_id'];
   //      $sh_name = User::where('id', $sh_id)->first()->email;
   //         if(env('MAIL_USERNAME') != null){
   //          Mail::to(json_decode($order->shipping_address)->email)->queue(new InvoiceEmailManager($array));
   //             try {
   //                 //Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
   //                 //Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
   //                 //Mail::to(User::where('id', $sh_id)->first()->email)->queue(new InvoiceEmailManager($array));
   //             } catch (\Exception $e) {

   //             }
   //         }

   //     }

    public function recurringPrice(Request $request){
        $shortId = $_SERVER['HTTP_SORTINGHUBID'];
        $priceDetail = calculatePrice($request->product_id,$shortId);

        $stock_price = $priceDetail['MRP'];
        $price = $priceDetail['selling_price'];
        $discount = $priceDetail['customer_off'];
        $discount_percentage = json_decode($priceDetail['customer_discount']);
        $discount_type = json_decode($priceDetail['discount_type']);
        $mrp = 0;
        $total_tax = 0;
        $total_discount = 0;
        $final_amount = 0;
        
        $mrp += $stock_price*$request->quantity;
        $total_discount +=$discount*$request->quantity;
        $total_tax += ($stock_price-$discount)*$request->tax/100;

        $pay = $price*$request->quantity;
        $final_amount = round(($mrp-$total_discount),2);
        $shipping_cost = ($final_amount>=$this->free_shipping_amount) ? 0: BusinessSetting::where('type','flat_rate_shipping_cost')->first()->value;

        $grand_total = round(($final_amount+$shipping_cost)*$request->days,2);
        return response()->json([
            'status' => true,
            'peercode'=> isset($_SERVER['HTTP_PEER'])?$_SERVER['HTTP_PEER']:"",
            'days' => (integer)$request->days,
            'total_mrp'=>round($mrp*$request->days,2),
            'total_tax'=>round($total_tax*$request->days,2),
            'total_discount'=>round($total_discount*$request->days,2),
            'shipping_cost' => (double)$shipping_cost*$request->days,
            'final_amount'=>(double)$grand_total
        ]);
    }

    public function recurringOrderList(Request $request){
        $subOrder = array();
        $subscriberOrder = SubscribedOrder::where('user_id',$request->user_id)->where('payment_status','Paid')->latest()->get();
        foreach($subscriberOrder as $key => $value){
            $sub['subscribed_id'] = $value->id;
            $sub['subscribed_code'] = $value->subscribed_code;
            $sub['product_id'] = $value->product_id;
            $sub['sortinghub_id'] = $value->sorting_id;
            $sub['address_id'] = $value->address_id;
            $sub['days'] = (integer)$value->days;
            // if($value->days == 7){
            //     $plan_type = 'Weekly';
            // }else if($value->days == 30){
            //     $plan_type = 'Monthly';
            // }else{
            //     $plan_type = 'Custom';
            // }
            $sub['plan_type'] = $value->plan_type;
            $sub['start_date'] = date('d-m-Y',strtotime($value->start_date));
            $sub['end_date'] = date('d-m-Y',strtotime($value->end_date));
            $sub['amount_by_wallet'] = $value->amount_by_wallet;
            $sub['stock_price'] = $value->stock_price;
            $sub['price'] = $value->price;
            $sub['total'] = $value->total;
            $sub['quantity'] = $value->quantity;
            $sub['tax'] = $value->tax;
            $sub['discount'] = round($value->discount,2);
            $sub['shipping_cost'] = (double)$value->shipping_cost;
            $sub['status'] = (integer)$value->status;
            $subOrder[] = $sub;
        }
        return response()->json([
            'success' => true,
            'status' => 200,
            'data'=> $subOrder
        ]);
    }

    public function recurringOrderDetail(Request $request){
        $data = array();
        $subscribed = SubscribedOrder::where('id',$request->subscribed_id)->first();
        if(!empty($subscribed)){
            $data['subscribed_id'] = $subscribed->id;
            $data['subscribed_code'] = $subscribed->subscribed_code;
            $data['platform'] = $subscribed->platform;
            $data['user_id'] = $subscribed->user_id;
            $data['product_id'] = $subscribed->product_id;
            $data['sorting_id'] = $subscribed->sorting_id;
            $data['address_id'] = $subscribed->address_id;
            $data['stock_price'] = $subscribed->stock_price;
            $data['quantity'] = $subscribed->quantity;
            $data['price'] = $subscribed->price;
            $data['total'] = $subscribed->total;
            $data['pincode'] = $subscribed->pincode;
            $data['peer'] = $subscribed->peer;
            $data['start_date'] = date('d-m-Y',strtotime($subscribed->start_date));
            $data['days'] = (integer)$subscribed->days;
            $data['end_date'] = date('d-m-Y',strtotime($subscribed->end_date));
            $data['plan_type'] = $subscribed->plan_type;
            $data['amount_by_wallet'] = $subscribed->amount_by_wallet;
            $data['payable_amount'] = $subscribed->payable_amount;
            $data['payment_mode'] = $subscribed->payment_mode;
            $data['payment_status'] = $subscribed->payment_status;
            $data['tax'] = $subscribed->tax;
            $data['discount'] = $subscribed->discount;
            $data['shipping_cost'] = (double)$subscribed->shipping_cost;
            $data['status'] = $subscribed->status;
            

            // if($subscribed->days == 7){
            //     $data['plan_type'] = 'Weekly';
            // }else if($subscribed->days == 30){
            //     $data['plan_type'] = 'Monthly';
            // }else{
            //     $data['plan_type'] = 'Custom';
            // }

            $productDetail = Product::where('id',$subscribed->product_id)->first();
            $data['name'] = trans($productDetail->name);
            $data['thumbnail_img'] = $productDetail->thumbnail_img;

            $variant = "";
            $pvariant = \App\ProductStock::where('product_id',$subscribed->product_id)->first();
            if(!is_null($pvariant))
            {
                $variant = $pvariant->variant;
            }
            $arr = array();
            $orders = \App\Order::where('subscribed_id',$subscribed->id)->get();

            if(!empty($orders)){
                foreach($orders as $row){
                    $order['order_id'] = $row->id;
                    $order['code'] = $row->code;
                    $order['order_status'] = $row->order_status;
                    $subOrder = \App\SubOrder::where('order_id',$row->id)->first();
                    $order['sub_order'] = $subOrder->sub_order_code;
                    $order['delivery_name'] = $subOrder->delivery_name;
                    $order['delivery_date'] = $subOrder->delivery_date;
                    $order['delivery_time'] = $subOrder->delivery_time;
                    $arr[] = $order;
                }
            }
            $delivered  = \App\Order::where('subscribed_id',$subscribed->id)->where('order_status','Delivered')->get()->count();
            $pending  = \App\Order::where('subscribed_id',$subscribed->id)->where('order_status','pending')->get()->count();
            $remaining = $subscribed->days - ($delivered + $pending);
            $data['total_order'] = $subscribed->days;
            $data['total_delivered'] = $delivered;
            $data['total_pending'] = $pending;
            $data['total_remaining'] = $remaining;

            $data['variant'] =  $variant;

            $add = \App\Address::where('id',$subscribed->address_id)->first();
            $address = array();
            $address['name'] = $add->name;
            $address['address'] = $add->address;
            $address['country'] = $add->country;
            $address['state'] = $add->state;
            $address['city'] = $add->city;
            $address['postal_code'] = $add->postal_code;
            $address['tag'] = is_null($add->tag)?'Other':$add->tag;
            $address['phone'] = $add->phone;
            $data['address'] = $address;
            $data['order'] = isset($arr)?$arr:[];
            return response()->json([
                'status' => true,
                'message'=> 'Subscribed detail.',
                'data' => $data
            ]);
        }else{
            return response()->json([
                'status' => true,
                'message'=> 'Subscribed ID not exist.',
                'data' => $data
            ]);
        }
    }
    
    public function recurringOrderHistory($id){
        $data = array();
        $subscribed = SubscribedOrder::where('id',$id)->first();

        if(!empty($subscribed)){
            $data['subscribed_id'] = $subscribed->id;
            $data['subscribed_code'] = $subscribed->subscribed_code;
            $data['platform'] = $subscribed->platform;
            $data['user_id'] = $subscribed->user_id;
            $data['product_id'] = $subscribed->product_id;
            $data['sorting_id'] = $subscribed->sorting_id;
            $data['address_id'] = $subscribed->address_id;
            $data['stock_price'] = $subscribed->stock_price;
            $data['quantity'] = $subscribed->quantity;
            $data['price'] = $subscribed->price;
            $data['total'] = $subscribed->total;
            $data['pincode'] = $subscribed->pincode;
            $data['peer'] = $subscribed->peer;
            $data['start_date'] = date('d-m-Y',strtotime($subscribed->start_date));
            $data['days'] = (integer)$subscribed->days;
            $data['end_date'] = date('d-m-Y',strtotime($subscribed->end_date));
            $data['plan_type'] = $subscribed->plan_type;
            $data['amount_by_wallet'] = $subscribed->amount_by_wallet;
            $data['payable_amount'] = $subscribed->payable_amount;
            $data['payment_mode'] = $subscribed->payment_mode;
            $data['payment_status'] = $subscribed->payment_status;
            $data['payment_status'] = $subscribed->payment_status;

            $data['tax'] = $subscribed->tax;
            $data['discount'] = $subscribed->discount;
            $data['shipping_cost'] = (double)$subscribed->shipping_cost;
            $data['status'] = $subscribed->status;

            // if($subscribed->days == 7){
            //     $data['plan_type'] = 'Weekly';
            // }else if($subscribed->days == 30){
            //     $data['plan_type'] = 'Monthly';
            // }else{
            //     $data['plan_type'] = 'Custom';
            // }

            $productDetail = Product::where('id',$subscribed->product_id)->first();
            $data['name'] = trans($productDetail->name);
            $data['thumbnail_img'] = $productDetail->thumbnail_img;

            $delivered  = \App\Order::where('subscribed_id',$subscribed->id)->where('order_status','Delivered')->get()->count();
            $pending  = \App\Order::where('subscribed_id',$subscribed->id)->where('order_status','pending')->get()->count();
            $remaining = $subscribed->days - ($delivered + $pending);
            $data['total_order'] = $subscribed->days;
            $data['total_delivered'] = $delivered;
            $data['total_pending'] = $pending;
            $data['total_remaining'] = $remaining;

            $variant = "";
            $pvariant = \App\ProductStock::where('product_id',$subscribed->product_id)->first();
            if(!is_null($pvariant))
            {
                $variant = $pvariant->variant;
            }
            $arr = array();
            $orders = \App\Order::where('subscribed_id',$subscribed->id)->get();
            if(!empty($orders)){
                foreach($orders as $row){
                    $order['order_id'] = $row->id;
                    $order['code'] = $row->code;
                    $order['order_status'] = $row->order_status;
                    $subOrder = \App\SubOrder::where('order_id',$row->id)->first();
                    $order['sub_order'] = $subOrder->sub_order_code;
                    $order['delivery_name'] = $subOrder->delivery_name;
                    $order['delivery_date'] = $subOrder->delivery_date;
                    $order['delivery_time'] = $subOrder->delivery_time;
                    $arr[] = $order;
                }
            }

            $data['variant'] =  $variant;

            $add = \App\Address::where('id',$subscribed->address_id)->first();
            $address = array();
            $address['name'] = $add->name;
            $address['address'] = $add->address;
            $address['country'] = $add->country;
            $address['state'] = $add->state;
            $address['city'] = $add->city;
            $address['postal_code'] = $add->postal_code;
            $address['tag'] = is_null($add->tag)?'Other':$add->tag;
            $address['phone'] = $add->phone;
            $data['address'] = $address;
            $data['order'] = isset($arr)?$arr:[];
            
        }
        return $data;
    }
     
    public function unsubscribed(Request $request){
        $refund = RefundRequest::where('subscribed_id',$request->subscribed_id)->first();
        if(empty($refund)){
            $subscribed = \App\SubscribedOrder::where('id',$request->subscribed_id)->update(['status'=> 0]);
            $detail = SubscribedOrder::where('id',$request->subscribed_id)->first();
            $phone = Address::where('id', $detail->address_id)->first()->phone;;
            $order = Order::where('subscribed_id',$request->subscribed_id)->get();
            if(count($order) > 0){
                $sum = Order::where('subscribed_id',$request->subscribed_id)
                                ->sum('grand_total');
                $refund_amount = $detail->payable_amount - $sum;
            }else{
                $refund_amount = $detail->payable_amount;
            }

            
            if($refund_amount > 0){
                $refundRequest = new RefundRequest;
                $refundRequest->user_id = $detail->user_id;
                $refundRequest->subscribed_id = $request->subscribed_id;
                $refundRequest->refund_amount = $refund_amount;
                if($refundRequest->save()){
                    send_sms_unsuscribed($phone,$detail->subscribed_code,$refundRequest->refund_amount);
                    return response()->json([
                        'status'=>true,
                        'message'=>'Unsubscribed and Refund request send successfully.',
                        'refundId' => $refundRequest->id,
                        'amount' => $refund_amount
                    ]);
                }
            }
        }else{
            if($refund->refund_status == 0){
                $message = 'Already requested for refund.';
            }else{
                $message = 'Refund completed.';
            }
            return response()->json([
                'status'=>false,
                'message'=>$message,
                'refundId' => $refund->id,
                'amount' => $refund->refund_amount
            ]);
        }
    }
}