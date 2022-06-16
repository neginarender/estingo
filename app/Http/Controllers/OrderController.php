<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\OTPVerificationController;
use App\Http\Controllers\ClubPointController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\RazorpayController;
use App\Notifications\CancelOrderMail;
use App\Order;
use App\Product;
use App\Color;
use App\OrderDetail;
use App\CouponUsage;
use App\OtpConfiguration;
use App\User;
use App\BusinessSetting;
use App\CancelOrderLog;
use Auth;
use Session;
use DB;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Mail\EmailManager;
//use CoreComponentRepository;
use App\Traits\OrderTrait;
use App\Traits\AddToOrderTrait;
use App\ReferalUsage;
use App\PeerPartner;
use App\OrderReferalCommision;
use App\Wallet;
use PDF;
use App\PeerSetting;
use App\ProductStock;
use App\MappingProduct;
use App\ShortingHub;
use Cookie;
use Carbon\Carbon;
use App\Distributor;
use App\OrderLog;
use App\OrderDetailLog;
use App\OrderRemoveLog;
use Razorpay\Api\Api;
use App\Http\Controllers\DOFOController;
use App\DeliveryBoy;
use App\ReplacementOrder;

/* --- 25-09-2021 --*/
use Excel;
use Illuminate\Support\Str;
use App\OrdersExport;
/* -----*/

use App\AssignOrder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use App\SubOrder;
use App\Events\OrderPlacedEmail;
use App\RefundRequest;

class OrderController extends Controller
{
    use OrderTrait,AddToOrderTrait;

    /**
     * Display a listing of the resource to seller.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;

        $orders = DB::table('orders')
                    ->orderBy('code', 'desc')
                    ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                    ->where('order_details.seller_id', Auth::user()->id)
                    ->select('orders.id')
                    ->distinct();

        if ($request->payment_status != null){
            $orders = $orders->where('order_details.payment_status', $request->payment_status);
            $payment_status = $request->payment_status;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('order_details.delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if ($request->has('search')){
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }

        $orders = $orders->paginate(50);

        foreach ($orders as $key => $value) {
            $order = \App\Order::find($value->id);
            $order->viewed = 1;
            $order->save();
        }

        return view('frontend.seller.orders', compact('orders','payment_status','delivery_status', 'sort_search'));
    }

    /**
     * Display a listing of the resource to admin.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function admin_orders(Request $request)
    {  
        $id = Auth()->user()->id; 
        $msc = microtime(true);
        $payment_status = null;
        $delivery_status = null;
        $pay_type = null;
        $sort_search = null;
        $edit_view = null;
        $realdates = null;
        $admin_user_id = null;
        $global = DB::table('global_switch')->first();
        $orders = Order::when($global->access_switch == 0,function($query){
                       return $query->where('orders.dofo_status','=',0);
                    })
                    ->where('log',0)
                    ->orderBy('orders.created_at', 'desc');
        if(Auth()->user()->user_type != 'admin'){
            $orders = $orders->where('sorting_hub_id',Auth()->user()->id);
        }
        if ($request->payment_type != null){
            $orders = $orders->where('orders.payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }

        if ($request->edit_view != null){
            if($request->edit_view == 'viewed'){
                $orders = $orders->where(['orders.viewed'=>1,'orders.edited'=>0]);
            }elseif($request->edit_view == 'edited'){
                $orders = $orders->where(['orders.viewed'=>1,'orders.edited'=>1]);
            }
            
            $edit_view = $request->edit_view;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('orders.order_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if($request->pay_type!=null){
            $orders = $orders->where('orders.payment_type', $request->pay_type);
            $pay_type = $request->pay_type;
        }

        if ($request->has('search') && !empty($request->search)){
            $sort_search = $request->search;
            $orders = $orders->where(function($query) use($sort_search){
                $query->where('code', 'like', '%'.$sort_search.'%')
                ->orWhere('shipping_address->name','like', '%'.$sort_search.'%')
                ->orWhere('shipping_address->phone', 'like', '%'.$sort_search.'%');
            });
            
        }

        
        if($request->dateRangeStart != null && $request->dateRangeEnd != null && empty($request->search)){

            $newStartDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->dateRangeStart)->toDateTimeString(); 

            $start_time = date('Y-m-d', strtotime($newStartDate)); 

            $end_time = date('Y-m-d', strtotime($request->dateRangeEnd));

            $currentTime = time();

            $startTime = strtotime($start_time);

            $endTime = strtotime($end_time); 

            $days_between = ceil(abs($endTime - $startTime) / 86400);

            if($start_time == $end_time){

                $endDayFromCurrentDate = 0;

                $startDayFromCurrentDate = 0;  

            }         

            else{

                $startDayFromCurrentDate = ceil(abs($startTime - $currentTime) / 86400) -1;

                $endDayFromCurrentDate = null;

            }

            if($request->dateRangeEnd != date('d-m-Y')){

                $endDayFromCurrentDate = ceil(abs($endTime - $currentTime) / 86400) -1;

            }

           

             $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime($request->dateRangeStart)))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime($request->dateRangeEnd)));

            

            //print_r($currentTime);

        }else{

            $startDayFromCurrentDate = null;

            $endDayFromCurrentDate = null;

            $days_between = 89;

            $dt = \Carbon\Carbon::now();

            $dt->toDateTimeString();  

            if(empty($request->search)){

                //$orders = $orders->whereDate('orders.updated_at', '>', $dt->subDays($days_between)->format('Y-m-d'))->orwhereDate('orders.created_at', '>', $dt->subDays($days_between)->format('Y-m-d'));

            }

            



        }

         $orders = $orders->latest()->paginate(25);

        if(Auth()->user()->user_type == "staff"){
            foreach($orders as $k=>$v){
                if($v->payment_type == "letzpay_payment" && $v->payment_status == "unpaid"){
                    // unset($orders[$k]);

                }
            }

        }
         return view('orders.index', compact('orders','payment_status','delivery_status', 'admin_user_id', 'pay_type','realdates', 'days_between', 'endDayFromCurrentDate', 'startDayFromCurrentDate','sort_search','edit_view'));
    }


    //27-09-2021
    public function admin_referralorders(Request $request){
        $id = Auth()->user()->id;        
        $orderID = $this->orders($id);
        //CoreComponentRepository::instantiateShopRepository();

        $payment_status = null;
        $delivery_status = null;
        $pay_type = null;
        $sort_search = null;
        // $admin_user_id = User::on('mysql2')->where('user_type', 'admin')->first()->id;
        $admin_user_id = User::on('mysql')->where('user_type', 'admin')->first()->id;

        // $orders = DB::connection('mysql2')->table('orders')
        $orders = DB::connection('mysql')->table('orders')
                    ->where('used_referral_code', '1')
                    ->groupBy('user_id')
                    ->orderBy('created_at', 'desc')
                    ->get(); 
                    
        //$orders = $orders->paginate(10);

        return view('orders.referral', compact('orders','admin_user_id'));
    
    }

    public function sortinghub_today_orders(Request $request)
    {
    // echo $request->delivery_status;   
        $id = Auth()->user()->id;
        $orderID = $this->orders($id);

        $payment_status = null;
        $delivery_status = null;
        $sort_search = null;
        $pay_type = null;
        // $admin_user_id = User::on('mysql2')->where('user_type', 'admin')->first()->id;
        $admin_user_id = User::on('mysql')->where('user_type', 'admin')->first()->id;
        $from_date = date('Y-m-d');
        $to_date   = date('Y-m-d');
        $user_type="sorting_hub";
        // $orders = Order::on('mysql2')
        $orders = Order::on('mysql')
                    ->whereIn('id', $orderID)
                    ->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))
                    ->orderBy('id', 'desc')
                    //->join('order_details', 'orders.id', '=', 'order_details.order_id')
                    ->distinct('code');
                    

        if ($request->payment_type != null){
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
             $orders = $orders->where('order_status', $request->delivery_status);
            // $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if($request->pay_type!=null){
            $orders = $orders->where('payment_type', $request->pay_type);
            $pay_type = $request->pay_type;
        }
        // if ($request->has('search')){
        //     $sort_search = $request->search;
        //     $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        // }
        $orders = $orders->paginate(50);
        return view('orders.index', compact('orders','payment_status','delivery_status', 'sort_search', 'admin_user_id','user_type','pay_type'));
    }

    /**
     * Display a listing of the sales to admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function sales(Request $request)
    {
        //CoreComponentRepository::instantiateShopRepository();

        $sort_search = null;
        $orders = Order::orderBy('code', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $orders = $orders->where('code', 'like', '%'.$sort_search.'%');
        }
        $orders = $orders->paginate(50);
        return view('sales.index', compact('orders', 'sort_search'));
    }


    public function order_index(Request $request)
    {
        if (Auth::user()->user_type == 'staff' && Auth::user()->staff->pick_up_point != null) {
            //$orders = Order::where('pickup_point_id', Auth::user()->staff->pick_up_point->id)->get();
            $orders = DB::table('orders')
                        ->orderBy('code', 'desc')
                        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                        ->where('order_details.pickup_point_id', Auth::user()->staff->pick_up_point->id)
                        ->select('orders.id')
                        ->distinct()
                        ->paginate(50);

            return view('pickup_point.orders.index', compact('orders'));
        }
        else{
            //$orders = Order::where('shipping_type', 'Pick-up Point')->get();
            $orders = DB::table('orders')
                        ->orderBy('code', 'desc')
                        ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                        ->where('order_details.shipping_type', 'pickup_point')
                        ->select('orders.id')
                        ->distinct()
                        ->paginate(50);

            return view('pickup_point.orders.index', compact('orders'));
        }
    }

    public function pickup_point_order_sales_show($id)
    {
        if (Auth::user()->user_type == 'staff') {
            $order = Order::findOrFail(decrypt($id));
            return view('pickup_point.orders.show', compact('order'));
        }
        else{
            $order = Order::findOrFail(decrypt($id));
            return view('pickup_point.orders.show', compact('order'));
        }
    }

    /**
     * Display a single sale to admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function sales_show($id)
    {
        $order = Order::findOrFail(decrypt($id));
        return view('sales.show', compact('order'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $self = 0;
        if(Auth::check()){
            if(Auth::user()->user_type=='partner' && Auth::user()->peer_partner==1){
                $self = 1;
            }
        }

        $dofo = new DOFOController;
        $checkDOFO = $dofo->checkDOFO($request->session()->get('shipping_info'));
        $wallet = $request->wallet_insert_amount;

        $min_order_amount = (int)env("MIN_ORDER_AMOUNT");
        $free_shipping_amount = (int)env("FREE_SHIPPING_AMOUNT");
        if(!empty(Cookie::get('pincode')))
        { 
            $pincode = $request->session()->get('shipping_info')['postal_code'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');  
        }

        $referal_code = "";
        if(Session::has('referal_discount')){
            $referal_code = Session::get('referal_code');
        }

        DB::beginTransaction();
        try{
        $order = new Order;
        if($checkDOFO == 1){
           
            $order->guest_id = mt_rand(100000, 999999);
        }else{
            if(Auth::check()){
                $order->user_id = Auth::user()->id;
            }else{
                $order->guest_id = mt_rand(100000, 999999);
            }

        }    
        
        $lastorderID = Order::orderBy('id', 'desc')->first();
        if(!empty($lastorderID)){
            $orderId = $lastorderID->id;
        }else{
            $orderId = 1;
        }
        $currentDateTime = \Carbon\Carbon::now()->toDateTimeString();
        $datetime = "";
        $updatedatetime = "";
        
        $order->created_at = $currentDateTime;
        $order->updated_at = $currentDateTime;

        if($checkDOFO == 1){
            $datetime = $dofo->getDateTime($request);
            if(!empty($datetime)){
               
                $order->created_at = $datetime;
                $timestamp = strtotime($datetime);
                $date = date('d-m-Y', $timestamp);
                $time = date('H:i:s', $timestamp);

                if(strtotime($time) <= strtotime('20:00:00')){
                    $arr_time = explode(':',$time);
                    $ran_time = rand($arr_time[0]+1,19).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT);
                    $updatedatetime = date('Y-m-d H:i:s', strtotime("$date $ran_time"));
                }else{
                    
                    $ran_time = rand(7,20).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT);
                   
                    $new_date = date("d-m-Y", strtotime("$date +1 day"));
                    
                    $updatedatetime = date('Y-m-d H:i:s', strtotime("$new_date $ran_time"));
                }
                

                $order->updated_at = $updatedatetime;
               //dd( $order);
            }
 
        }

        $order->sorting_hub_id = $shortId['sorting_hub_id'];
        $order->referal_code = $referal_code;
        $order->shipping_address = json_encode($request->session()->get('shipping_info'));
        $order->shipping_pin_code = $request->session()->get('shipping_info')['postal_code'];
        $order->payment_type = $request->payment_option;
        $order->delivery_viewed = '0';
        $order->payment_status_viewed = '0';
        $order->code = 'ORD'.mt_rand(10000000,99999999).$orderId;
        if($checkDOFO == 1){
            if($request->has('order_code') && $request->order_code != null){
                $order->code = $request->order_code;
            }
        }
        $order->date = !empty($datetime)?strtotime($datetime):strtotime('now');
        // $order->wallet_amount = $request->wallet_insert_amount;
        $order->order_status = "pending";
        $total_price = 0;
       foreach (Session::get('cart') as $key => $cartItem)
       {
        $total_price += $cartItem['price']*$cartItem['quantity'];
       }

       $total_price = $total_price-session()->get('total_saving');
       if($request->payment_option!="cash_on_delivery"){
        $order->log = 1;
       }

       if($total_price>=$free_shipping_amount)
        {
            $order->total_shipping_cost = 0;
        }else{
            $order->total_shipping_cost = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
        }
      
        //28-09-2021 - Insert flag value for used_referral_code
        $order->used_referral_code = $request->session()->get('used_referral_code');
		
		//18-10-2021 - Insert delivery slot
        //$order->delivery_slot = $request->session()->get('delivery_slot'); 
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

            //calculate shipping is to get shipping costs of different types
            $admin_products = array();
            $seller_products = array();
            if($checkDOFO == 1){
                $dofo->deliverySchedule($order,Session::get('cart'));

            }else{
                $this->deliverySchedule($order);
            }
            
            //Order Details Storing
            $no_of_fresh_items = 0;
            $no_of_grocery_items = 0;
            $total_fresh_order_amount=0;
            $total_grocery_order_amount = 0;
            $total_grocery_customer_discount = 0;
            $total_fresh_customer_discount = 0;

            foreach (Session::get('cart') as $key => $cartItem){
                $product = Product::find($cartItem['id']);

                if($product->added_by == 'admin'){
                    array_push($admin_products, $cartItem['id']);
                }
                else{
                    $product_ids = array();
                    if(array_key_exists($product->user_id, $seller_products)){
                        $product_ids = $seller_products[$product->user_id];
                    }
                    array_push($product_ids, $cartItem['id']);
                    $seller_products[$product->user_id] = $product_ids;
                }

                $subtotal += $cartItem['price']*$cartItem['quantity'];
                $tax += $cartItem['tax']*$cartItem['quantity'];

                $product_variation = $cartItem['variant'];



                if(!empty($shortId)){ 
                    
                    $product_stock = MappingProduct::where(['sorting_hub_id' => $shortId['sorting_hub_id'],'product_id' => $cartItem['id']])->first();
                    if($product_stock['qty'] != 0){
                        $product_stock['qty'] -= $cartItem['quantity'];
                        //$product_stock->save();
                    }else{
                        if($product_variation != null){
                            $product_stock = $product->stocks->where('variant', $product_variation)->first();
                            $product_stock->qty -= $cartItem['quantity'];
                           // $product_stock->save();
                        }
                        else {
                            $product->current_stock -= $cartItem['quantity'];
                            //$product->save();
                        }

                    }


            
            
                }else{
                    if($product_variation != null){
                        $product_stock = $product->stocks->where('variant', $product_variation)->first();
                        $product_stock->qty -= $cartItem['quantity'];
                        //$product_stock->save();
                    }
                    else {
                        $product->current_stock -= $cartItem['quantity'];
                        //$product->save();
                    }
                }
            
                

                $order_detail = new OrderDetail;
                $order_detail->order_id  =$order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $product_variation;
                $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                // $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];
                $order_detail->created_at = $currentDateTime;
                $order_detail->updated_at = $currentDateTime;
                if(!empty($datetime)){
                
                    $order_detail->created_at = $datetime;
                    $order_detail->updated_at = $updatedatetime;
                }

                //Dividing Shipping Costs
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    $order_detail->shipping_cost = getShippingCost($key);
                    if($total_price>=$free_shipping_amount)
                    {
                        $order_detail->shipping_cost = 0;
                    }
                    $shipping += $order_detail->shipping_cost;
                }
                else{
                    $order_detail->shipping_cost = 0;
                    $order_detail->pickup_point_id = $cartItem['pickup_point'];
                }
                //End of storing shipping cost

                //
                $id = $cartItem['id'];
                if(!empty($shortId)){
                  $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
                }else{
                    $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
                }


                $product = Product::findOrFail($id);
                $price = $product->unit_price;

                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                $stock_price = $product->unit_price;
                if(!is_null($productstock)){
                    $stock_price = $productstock->price; 
                }

               

                    if(!empty($shortId)){
                        $productmap = MappingProduct::where(['sorting_hub_id'=>$shortId->sorting_hub_id,'product_id'=>$id])->first();
                        $price = $productmap['purchased_price'];
                        $stock_price = $productmap['selling_price'];

                        if($price == 0 || $stock_price == 0){
                            $id = $cartItem['id'];
                            $productold = Product::findOrFail($id);
                            $price = $productold->unit_price;
                            $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                            $stock_price = $productstock->price; 
                        }  

                    }
                

                $main_discount = $stock_price - $price;
                
                $total_discount_percent += substr($peer_discount_check['customer_discount'], 1, -1);
                $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);

                $total_master_percent += substr($peer_discount_check['company_margin'], 1, -1);
                $master_percent = substr($peer_discount_check['company_margin'], 1, -1);
               // $master_last_price = ($main_discount * $master_percent)/100;
                $master_last_price = $peer_discount_check['master_commission'];
                $master_percentage += $master_last_price*$cartItem['quantity'];
                
                $peer_commission = $peer_discount_check['peer_commission'];
                $master_commission = $peer_discount_check['master_commission'];
               // $last_price = ($main_discount * $discount_percent)/100;
                $last_price = $peer_discount_check['customer_off'];
                if($self==1){
                    $last_price = ($peer_commission+$master_commission+$peer_discount_check->customer_off);
                }
                $prices = $stock_price - $last_price;
                $ref_dis += $last_price*$cartItem['quantity'];

                $total_peer_percent += substr($peer_discount_check['peer_discount'], 1, -1);
                $peer_percent = substr($peer_discount_check['peer_discount'], 1, -1);
                //$peer_last_price = ($main_discount * $peer_percent)/100; 
                $peer_last_price = $peer_discount_check['peer_commission'];
                $peer_percentage += $peer_last_price*$cartItem['quantity'];
                // die;

                $order_detail->quantity = $cartItem['quantity'];

                //10july by neha
                //$last_subprice = ($main_discount * $peer_percent)/100;
                //$last_masterprice = ($main_discount * $master_percent)/100;
                $last_subprice = $peer_discount_check['peer_commission'];
                $last_masterprice = $peer_discount_check['master_commission'];
                $last_rozanamargin = $peer_discount_check['rozana_margin'];
                $last_margin = $peer_discount_check['margin'];
                
                //$order_detail->peer_discount = $last_price*$cartItem['quantity'];
                $id = $cartItem['id'];
                $customer_discount = 0;
                $product = Product::findOrFail($id);
                if(Session::has('referal_discount')){
                    // $taxquantity = $product->tax*$cartItem['quantity'];
                    // $tax = ($prices*$taxquantity)/100;
                    $customer_discount = $last_price*$cartItem['quantity'];
                    $taxp = ($prices*100)/(100+$product->tax);
                    $tax = (($taxp*$product->tax)/100)*$cartItem['quantity'];

                    $order_detail->peer_discount = $last_price*$cartItem['quantity'];
                    if($self==0){
                        $order_detail->sub_peer = $last_subprice*$cartItem['quantity'];
                        $order_detail->master_peer = $last_masterprice*$cartItem['quantity'];
                    }
                    // $order_detail->sub_peer = $last_subprice*$cartItem['quantity'];
                    // $order_detail->master_peer = $last_masterprice*$cartItem['quantity'];
                    $order_detail->orderrozana_margin = $last_rozanamargin*$cartItem['quantity'];
                    $order_detail->order_margin = $last_margin*$cartItem['quantity'];
                    $order_detail->tax = $tax;
                }else{
                     // $taxquantity = $product->tax*$cartItem['quantity'];
                     // $tax = ($cartItem['price']*$taxquantity)/100;
                     $customer_discount = 0;
                     $taxp = ($cartItem['price']*100)/(100+$product->tax);
                     $tax = (($taxp*$product->tax)/100)*$cartItem['quantity'];

                     $order_detail->peer_discount = 0;
                     $order_detail->sub_peer = 0;
                     $order_detail->master_peer = 0;
                     $order_detail->orderrozana_margin = $last_rozanamargin*$cartItem['quantity'];
                    $order_detail->order_margin = $last_margin*$cartItem['quantity'];
                     $order_detail->tax = $tax;
                }  

                if(isFreshInCategories($product->category_id) || isFreshInSubCategories($product->subcategory_id)){
                    $type="fresh";
                    $no_of_fresh_items += $cartItem['quantity'];
                    $total_fresh_order_amount += (($cartItem['price']*$cartItem['quantity'])+($cartItem['shipping']))-$customer_discount;
                    $total_fresh_customer_discount += $customer_discount;
                }else{
                    $type = "grocery";
                    $no_of_grocery_items += $cartItem['quantity'];
                    $total_grocery_order_amount += (($cartItem['price']*$cartItem['quantity'])+($cartItem['shipping']))-$customer_discount;
                    $total_grocery_customer_discount += $customer_discount;
                }
                
                $sub_order_id = \App\SubOrder::where('order_id',$order->id)->where('delivery_name',$type)->first();
                $order_detail->order_type = $type;
                $order_detail->sub_order_id = $sub_order_id->id;

                $order_detail->save();

                //$product->num_of_sale++;
                //$product->save();

                
            }
            // $schedule = json_decode(session()->get('delivery_schedule'),true);
            
            // $items = $schedule['items'];
            // foreach($items as $key => $item){
            //    if($item){
            //     if($key=="fresh"){
                  
            //         \App\SubOrder::where('delivery_name','fresh')->where('order_id',$order->id)->update([
            //             'no_of_items'=>$no_of_fresh_items,
            //             'payable_amount'=>$total_fresh_order_amount,
            //             'customer_discount'=>$total_fresh_customer_discount
            //         ]);
            //     }
            //     if($key=="grocery"){
            //         \App\SubOrder::where('delivery_name', 'grocery')->where('order_id',$order->id)->update([
            //             'no_of_items'=>$no_of_grocery_items,
            //             'payable_amount'=>$total_grocery_order_amount,
            //             'customer_discount'=>$total_grocery_customer_discount
            //         ]);
            //     }
            //    }
                
               
            // }
 // echo $peer_percentage; die;
            if($wallet!=0){
                $subtotal = $subtotal- $wallet;
                
            }else{
                $subtotal = $subtotal;
            }

            if($request->payment_option=="wallet"){
                 $total_amount = $subtotal + $shipping;
                 $order->grand_total = $request->wallet_insert_amount;
                 $order->test_grand_total = $request->wallet_insert_amount;
                 $order->payment_status = "paid";
                if(Session::has('referal_discount')){
                    $order->wallet_amount = $total_amount - $ref_dis;
                }else{
                    $order->wallet_amount = $total_amount;
                }    

            }else{
                 $order->grand_total = $subtotal + $shipping;
                 $order->test_grand_total = $subtotal + $shipping;
                 $order->wallet_amount = $request->wallet_insert_amount;
            }

            //$order->grand_total = $subtotal + $shipping;
            

            if(Session::has('referal_discount')){

                if(Auth::check()){
                    $u_id = Auth::user()->id;
                }else{
                    $u_id = '';
                }

                $partner_id = PeerPartner::where('code', Session::get('referal_code'))->first();
                $referal_discount = ($order->grand_total * Session::get('referal_discount')) / 100;
               
                    $total_amount = $subtotal + $shipping;
                    $order->grand_total = $total_amount - $ref_dis;
                    $order->referal_discount =  $ref_dis;                  
                    $order->test_grand_total = $total_amount - $ref_dis;
                    if($self==0){
                        $referal_usage = new ReferalUsage;
                        $referal_usage->user_id = $u_id;
                        $referal_usage->partner_id = Session::get('partner_id');
                        $referal_usage->order_id = $order->id;
                        $referal_usage->referal_code = Session::get('referal_code');
                        $referal_usage->discount_rate = $total_discount_percent;
                        $referal_usage->discount_amount = $peer_percentage;
                        $referal_usage->commision_rate = $total_peer_percent;
                        $referal_usage->master_discount = $master_percentage;
                        $referal_usage->master_percentage = $total_master_percent;
                        $referal_usage->created_at = $currentDateTime;
                        $referal_usage->updated_at = $currentDateTime;
                        if(!empty($datetime)){
                    
                            $referal_usage->created_at = $datetime;
                            $referal_usage->updated_at = $updatedatetime;
                        }
                        $referal_usage->save();
                    }
                    
                    
                    // if($partner_id->parent!=0){
                    //     $master_phone = PeerPartner::where('id', $partner_id->parent)->first();
                    //     $to = $master_phone->phone;
                    //     $from = "RZANA";
                    //     $tid  = "1707163117081922481"; 

                    //     $msg = "Hello Rozana Master Peer, we are pleased to inform you that the ".Session::get('referal_code')." peer code has been used to place an order. Points will be credited to your account once the order is delivered. We want to ensure that you have a good experience and welcome any concerns or suggestions. You can call us on +91 9667018020. Team Rozana";
                    //     if($checkDOFO == 0){
                    //     mobilnxtSendSMS($to,$from,$msg,$tid);
                    //     }
                    // }
                    
            }

            if(Session::has('coupon_discount')){
                // $order->grand_total -= Session::get('coupon_discount');
                // $order->coupon_discount = Session::get('coupon_discount');

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->coupon_id = Session::get('coupon_id');
                //$coupon_usage->save();
            }

            $order->save();

        if($request['payment_option'] != "cash_on_delivery"){
            //mobilnxtSendSMS($to,$from,$msg,$tid);
            if($checkDOFO == 0){

                //mobilnxtSendSMS($to,$from,$msg,$tid);
                foreach($seller_products as $key => $seller_product){
                    try {
                        Mail::to(\App\User::find($key)->email)->queue(new InvoiceEmailManager($array));
                    } catch (\Exception $e) {

                    }
                }

                if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated && \App\OtpConfiguration::where('type', 'otp_for_order')->first()->value){
                    try {
                        $otpController = new OTPVerificationController;
                        $otpController->send_order_code($order);
                    } catch (\Exception $e) {

                    }
                }

            }elseif($checkDOFO == 1){
                
                $order->dofo_status = 1;
                $order->order_status = 'delivered';
                if($order->save()){
                   OrderDetail::where('order_id',$order->id)->update([
                        'payment_status'=>'paid',
                        'delivery_status'=>'delivered'
                    ]);

                    if(!empty($shortId)){
                        $dofo->updateDeliveryBoy($shortId->sorting_hub_id,$order->id);
                    }
                }

            }
        }


            if($request['payment_option'] == "cash_on_delivery"){
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
                    $OrderReferalCommision->created_at = $currentDateTime;
                    $OrderReferalCommision->updated_at = $currentDateTime;
                    if(!empty($datetime)){
                
                        $OrderReferalCommision->created_at = $datetime;
                        $OrderReferalCommision->updated_at = $updatedatetime;
                    }

                    $OrderReferalCommision->save();
                }

                // $user_wallet = User::where('id', Auth::user()->id)->first();
                // $last_wallet = $user_wallet->balance - $order->wallet_amount;
                // $referall_balance = User::where('id', Auth::user()->id)->update([
                //                             'balance' => $last_wallet,
                //                         ]);

                if($checkDOFO == 0){
                    //$this->sendOrderMail($request,$order,$seller_products);
                    //mobilnxtSendSMS($to,$from,$msg,$tid);
                    //SMSonOrderPlaced($order->id);
                    SMSonOrderPlacedWithSlot($order->id);

                }elseif($checkDOFO == 1){
                    $order->payment_status = "paid";
                    $order->dofo_status = 1;
                    $order->order_status = 'delivered';
                    if($order->save()){
                       OrderDetail::where('order_id',$order->id)->update([
                            'payment_status'=>'paid',
                            'delivery_status'=>'delivered'
                        ]);

                        if(!empty($shortId)){
                            $dofo->updateDeliveryBoy($shortId->sorting_hub_id,$order->id);
                        }
                    }

                }
            }
            //sends email to customer with the invoice pdf attached
            // if(env('MAIL_USERNAME') != null){
            //     try {
            //         Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
            //         Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
            //     } catch (\Exception $e) {

            //     }
            // }
            //unlink($array['file']);

            $request->session()->put('order_id', $order->id);
            DB::commit();
        }

        } catch(\Exception $e){
            DB::rollback();
            info($e);
            dd($e);
            $request->session()->put('exception', $e->getMessage());
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
        
        $order = Order::findOrFail(decrypt($id));
        $order->viewed = 1;
        $order->save();
         
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $order = Order::findOrFail($id);
        //$res = Ecom_order_cancel($id); 
            // print_r($res); exit;
            // if(!empty($res)){
            //     $responseStatus = json_decode($res, true);

            //     if($responseStatus["responseMessage"] == "Success"){
            //         $orderCancel = Order::where('id', $id)->update([
            //                                 'canceled_ecom_status' => $responseStatus["responseMessage"],
            //                                 'canceled_ecom_response' => $res,
            //                             ]);

                   
            //     }
            //     else{
            //         $responseStatus = json_decode($res, true); 
            //         $orderCancel = Order::where('id', $id)->update([
            //                 'canceled_ecom_status' => $responseStatus["responseMessage"],
            //                 'canceled_ecom_response' => $res,
            //             ]);
            //     } 
            // }
        if($order != null){

            
            foreach($order->orderDetails as $key => $orderDetail){
               $orderDetail->delete();
            }
            $order->delete();
            flash(translate('Order has been deleted successfully'))->success();
        }
        else{
            flash(translate('Something went wrong'))->error();
        }
        return back();
    }

    public function order_details(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        //$order->viewed = 1;
        $order->save();
        return view('frontend.partials.order_details_seller', compact('order'));
    }

    public function update_delivery_status(Request $request)
    {
        $currentDateTime = \Carbon\Carbon::now()->toDateTimeString();
        $order = Order::findOrFail($request->order_id);
        $order->delivery_viewed = '0';
        $order->updated_at = $currentDateTime;
        $order->save();

        if(Auth::user()->user_type == 'seller'){
            foreach($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail){
                $orderDetail->delivery_status = $request->status;
                $orderDetail->updated_at = $currentDateTime;
                $orderDetail->save();
            }
        }
        else{
            foreach($order->orderDetails as $key => $orderDetail){
                $orderDetail->delivery_status = $request->status;
                $orderDetail->updated_at = $currentDateTime;

                if(($request->status == 'delivered' && $order->payment_status == 'paid') || ($request->status == 'delivered' && $order->payment_type == 'cash_on_delivery')){
                        $OrderReferalCommision = OrderReferalCommision::where('order_id', $order->id)->first();

                        if(!empty($OrderReferalCommision) && $OrderReferalCommision->wallet_status == 0){
                            $partner = PeerPartner::where('user_id', $OrderReferalCommision->partner_id)->first();

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
                                    $wallet->save();

                                    $OrderReferalCommision->wallet_status = 1;
                                    $OrderReferalCommision->save();
                                }
                            }
                    }
                }

                $orderDetail->save();
            }
            $order->order_status = $request->status;
            $order->save();

            if($order->email_sent==0){
                if(event(new OrderPlacedEmail($request->order_id))){
                    $order->email_sent = 1;
                    $order->save(); 
                }
               
            }
        }

        if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated && \App\OtpConfiguration::where('type', 'otp_for_delivery_status')->first()->value){
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_delivery_status($order);
            } catch (\Exception $e) {
            }
        }

        return 1;
    }

    public function update_payment_status(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $order->payment_status_viewed = '0';
        $order->save();

        if(Auth::user()->user_type == 'seller'){
            foreach($order->orderDetails->where('seller_id', Auth::user()->id) as $key => $orderDetail){
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }
        else{
            foreach($order->orderDetails as $key => $orderDetail){
                $orderDetail->payment_status = $request->status;
                $orderDetail->save();
            }
        }

        $status = 'paid';
        foreach($order->orderDetails as $key => $orderDetail){
            if($orderDetail->payment_status != 'paid'){
                $status = 'unpaid';
            }
        }
        $order->payment_status = $status;
        $order->save();


        if($order->payment_status == 'paid' && $order->commission_calculated == 0){
            if(\App\Addon::where('unique_identifier', 'seller_subscription')->first() == null || !\App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated){
                if ($order->payment_type == 'cash_on_delivery') {
                    if (BusinessSetting::where('type', 'category_wise_commission')->first()->value != 1) {
                        $commission_percentage = BusinessSetting::where('type', 'vendor_commission')->first()->value;
                        foreach ($order->orderDetails as $key => $orderDetail) {
                            $orderDetail->payment_status = 'paid';
                            $orderDetail->save();
                            // if($orderDetail->product->user->user_type == 'seller'){
                            //     $seller = $orderDetail->product->user->seller;
                            //     $seller->admin_to_pay = $seller->admin_to_pay - ($orderDetail->price*$commission_percentage)/100;
                            //     $seller->save();
                            // }
                        }
                    }
                    else{
                        foreach ($order->orderDetails as $key => $orderDetail) {
                            $orderDetail->payment_status = 'paid';
                            $orderDetail->save();
                            // if($orderDetail->product->user->user_type == 'seller'){
                            //     $commission_percentage = $orderDetail->product->category->commision_rate;
                            //     $seller = $orderDetail->product->user->seller;
                            //     $seller->admin_to_pay = $seller->admin_to_pay - ($orderDetail->price*$commission_percentage)/100;
                            //     $seller->save();
                            // }
                        }
                    }
                }
                elseif($order->manual_payment) {
                    if (BusinessSetting::where('type', 'category_wise_commission')->first()->value != 1) {
                        $commission_percentage = BusinessSetting::where('type', 'vendor_commission')->first()->value;
                        foreach ($order->orderDetails as $key => $orderDetail) {
                            $orderDetail->payment_status = 'paid';
                            $orderDetail->save();
                            if($orderDetail->product->user->user_type == 'seller'){
                                $seller = $orderDetail->product->user->seller;
                                $seller->admin_to_pay = $seller->admin_to_pay + ($orderDetail->price*(100-$commission_percentage))/100;
                                $seller->save();
                            }
                        }
                    }
                    else{
                        foreach ($order->orderDetails as $key => $orderDetail) {
                            $orderDetail->payment_status = 'paid';
                            $orderDetail->save();
                            if($orderDetail->product->user->user_type == 'seller'){
                                $commission_percentage = $orderDetail->product->category->commision_rate;
                                $seller = $orderDetail->product->user->seller;
                                $seller->admin_to_pay = $seller->admin_to_pay + ($orderDetail->price*(100-$commission_percentage))/100;
                                $seller->save();
                            }
                        }
                    }
                }
            }

            if (\App\Addon::where('unique_identifier', 'affiliate_system')->first() != null && \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated) {
                $affiliateController = new AffiliateController;
                $affiliateController->processAffiliatePoints($order);
            }

            if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated) {
                $clubpointController = new ClubPointController;
                $clubpointController->processClubPoints($order);
            }

            $order->commission_calculated = 1;
            $order->save();
        }

        if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated && \App\OtpConfiguration::where('type', 'otp_for_paid_status')->first()->value){
            try {
                $otpController = new OTPVerificationController;
                $otpController->send_payment_status($order);
            } catch (\Exception $e) {
            }
        }
        return 1;
    }


    public function sendOrderMail($request,$order,$seller_products){
        //stores the pdf for invoice
        $orderdetail = OrderDetail::where('order_id',$order->id)->get();
        $orderproducts = $orderdetail->groupBy(function($item){
            return (string)$item->product['tax'];
        })->sortKeys();
        $schedules = $order->sub_orders;
       $pdf = PDF::setOptions([
                       'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                       'logOutputFile' => storage_path('logs/log.htm'),
                       'tempDir' => storage_path('logs/')
                   ])->loadView('invoices.new_customer_invoice', compact(['orderdetail','order','orderproducts','schedules']));
       $output = $pdf->output();
       file_put_contents('public/invoices/'.'Order#'.$order->code.'.pdf', $output);
       $array['view'] = 'emails.invoice';
       $array['subject'] = 'Rozana Order Placed - '.$order->code;
       $array['from'] = env('mail_from_address');
       $array['content'] = translate('Dear Customer, A new order has been placed. You can check your order details in the invoice attached below. Please reach out to us in the case of any queries on customercare@rozana.in');
       $array['file'] = 'public/invoices/Order#'.$order->code.'.pdf';
       $array['file_name'] = 'Order#'.$order->code.'.pdf';


       foreach($seller_products as $key => $seller_product){
           try {
               Mail::to(\App\User::find($key)->email)->queue(new InvoiceEmailManager($array));
           } catch (\Exception $e) {

           }
       }

       if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated && \App\OtpConfiguration::where('type', 'otp_for_order')->first()->value){
           try {
               $otpController = new OTPVerificationController;
               $otpController->send_order_code($order);
           } catch (\Exception $e) {

           }
       }
       //sends email to customer with the invoice pdf attached

       //$product_id = $request->session()->get('cart')[0]['id'];
        $postal_code = $request->session()->get('shipping_info')['postal_code']; 
        $sorting_hub_id = ShortingHub::whereRaw(
                'JSON_CONTAINS(area_pincodes, \'["'.$postal_code.'"]\')'
            )->select('user_id')->first();
        $sh_id = $sorting_hub_id['user_id'];
        $sh_name = User::where('id', $sh_id)->first()->email;
           if(env('MAIL_USERNAME') != null){
            Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
            $array['subject'] = "New Order has been placed.";
            $array['content'] = translate('Dear Admin, A new order has been placed. You can check  order details in the invoice attached below.');
           // Mail::to(env("ADMIN_MAIL"))->queue(new InvoiceEmailManager($array));
               try {
                   //Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
                   //Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                   //Mail::to(User::where('id', $sh_id)->first()->email)->queue(new InvoiceEmailManager($array));
               } catch (\Exception $e) {

               }
           }

       }
    //21may2021
    public function show_distributor_order($id)
    {
       // echo $start_date = $request->input('start_date'); die;
        //$distributorids = decrypt($id);
        $id = decrypt($id);
        $exploded_id = explode("_",$id);
        $distributorids = $exploded_id[0];
        $sortinghub_ids = $exploded_id[1];

        $start_date = '';
        $end_date = '';
        $from_date = date('Y-m-d');
        $to_date   = date('Y-m-d');

        $s_time = '';
        $e_time = '';
        $start_time = "00:00:00";
        $end_time = "23:59:59";


        // DB::enableQueryLog();
        // $orders = Order::whereBetween(DB::raw('created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->select('id', 'created_at')->get(); 
        // dd(DB::getQueryLog());
        //  die;


        $orders = Order::whereBetween(DB::raw('orders.created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.delivery_status', '!=' , 'delivered')->groupBy('shipping_pin_code')->select('orders.shipping_pin_code')->get();         

        $test = array();
        foreach($orders as $key => $row)
        {
             if(Auth::user()->user_type=="staff"){
                $sorting_hub_id = ShortingHub::whereRaw(
                'JSON_CONTAINS(area_pincodes, \'["'.$row->shipping_pin_code.'"]\')'
            )->where('user_id', Auth::user()->id)->select('user_id')->first();
            }else{
                $sorting_hub_id = ShortingHub::whereRaw(
                'JSON_CONTAINS(area_pincodes, \'["'.$row->shipping_pin_code.'"]\')'
            )->select('user_id')->first();
            }

           array_push($test,$sorting_hub_id['user_id']);
        }
        $test = array_filter($test);
        $prduct_ids = OrderDetail::whereBetween(DB::raw('created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->where('order_details.delivery_status', '!=' , 'delivered')->pluck('product_id')->toArray();

        if(in_array($sortinghub_ids,$test)){

            
            // $mapped_product = MappingProduct::whereIn('product_id', $prduct_ids)->whereRaw('json_contains(distributors, \'['.$distributorids.']\')')->where('sorting_hub_id', $sortinghub_ids)->select('product_id')->get();
            $mapped_product = MappingProduct::whereIn('product_id', $prduct_ids)->where('distributor_id', $distributorids)->where('sorting_hub_id', $sortinghub_ids)->select('product_id')->get();
        } 
        if(!empty($mapped_product)){

            if(Auth::user()->user_type=="staff"){

                $sorting_hubs_all = ShortingHub::where('user_id', Auth::user()->id)->select('area_pincodes')->first();
                $all_id = json_decode($sorting_hubs_all->area_pincodes);
                // dd($sorting_hubs_all->area_pincodes);
                // DB::enableQueryLog();
                $all_orders = OrderDetail::whereBetween(DB::raw('order_details.created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->join('mapping_product', 'order_details.product_id', '=', 'mapping_product.product_id')->join('orders', 'order_details.order_id', '=', 'orders.id')->where('order_details.delivery_status', '!=' , 'delivered')->whereIn('order_details.product_id', $mapped_product)->selectRaw('SUM(order_details.quantity) as total_quantity, order_details.product_id, order_details.quantity, order_details.price, order_details.created_at, order_details.variation')->where('mapping_product.sorting_hub_id', Auth::user()->id)->whereIn('orders.shipping_pin_code',  $all_id)->groupBy('order_details.product_id')->orderBy('order_details.created_at', 'DESC')->get();
                // dd(DB::getQueryLog());
            }else{
                $all_orders = OrderDetail::whereBetween(DB::raw('created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->where('order_details.delivery_status', '!=' , 'delivered')->whereIn('product_id', $mapped_product)->selectRaw('SUM(quantity) as total_quantity, product_id, quantity, price, created_at, variation')->groupBy('product_id')->orderBy('created_at', 'DESC')->get();
            }
            
        }else{
            $all_orders = array();
        }
        return view('orders.show_distributor_order', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date','s_time', 'e_time'));
    }

    public function show_distributor_order_by_date(Request $request, $id)
    {
        $id = decrypt($id);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $id = $request->input('distributor_id');
        $exploded_id = explode("_",$id);
        $distributorids = $exploded_id[0];
        $sortinghub_ids = $exploded_id[1];
        

        $from_date = $start_date;
        $to_date   = $end_date;

        $ta=':00';
        $tb=':59';
        $start_time = $request->input('start_time')."".$ta;
        $end_time = $request->input('end_time')."".$tb;

        $s_time = $request->input('start_time');
        $e_time = $request->input('end_time');

        

        $orders = Order::whereBetween(DB::raw('orders.created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.delivery_status', '!=' , 'delivered')->groupBy('shipping_pin_code')->select('orders.shipping_pin_code')->get();  

        $test = array();
        foreach($orders as $key => $row)
        {
             if(Auth::user()->user_type=="staff"){
                $sorting_hub_id = ShortingHub::whereRaw(
                'JSON_CONTAINS(area_pincodes, \'["'.$row->shipping_pin_code.'"]\')'
            )->where('user_id', Auth::user()->id)->select('user_id')->first();
            }else{
                $sorting_hub_id = ShortingHub::whereRaw(
                'JSON_CONTAINS(area_pincodes, \'["'.$row->shipping_pin_code.'"]\')'
            )->select('user_id')->first();
            }
           array_push($test,$sorting_hub_id['user_id']);
        }
        $test = array_filter($test);
        $prduct_ids = OrderDetail::whereBetween(DB::raw('created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->where('order_details.delivery_status', '!=' , 'delivered')->pluck('product_id')->toArray();

        if(in_array($sortinghub_ids,$test)){

            $mapped_product = MappingProduct::whereIn('product_id', $prduct_ids)->whereRaw('json_contains(distributors, \'['.$distributorids.']\')')->where('sorting_hub_id', $sortinghub_ids)->select('product_id')->get();
            // $mapped_product = MappingProduct::whereIn('product_id', $prduct_ids)->where('distributor_id', $distributorids)->where('sorting_hub_id', $sortinghub_ids)->select('product_id')->get();

        } 

        if(!empty($mapped_product)){

             if(Auth::user()->user_type=="staff"){

                $sorting_hubs_all = ShortingHub::where('user_id', Auth::user()->id)->select('area_pincodes')->first();
                $all_id = json_decode($sorting_hubs_all->area_pincodes);
                // dd($sorting_hubs_all->area_pincodes);
                // DB::enableQueryLog();
                $all_orders = OrderDetail::whereBetween(DB::raw('order_details.created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->join('mapping_product', 'order_details.product_id', '=', 'mapping_product.product_id')->join('orders', 'order_details.order_id', '=', 'orders.id')->where('order_details.delivery_status', '!=' , 'delivered')->whereIn('order_details.product_id', $mapped_product)->selectRaw('SUM(order_details.quantity) as total_quantity, order_details.product_id, order_details.quantity, order_details.price, order_details.created_at, order_details.variation')->where('mapping_product.sorting_hub_id', Auth::user()->id)->whereIn('orders.shipping_pin_code',  $all_id)->groupBy('order_details.product_id')->orderBy('order_details.created_at', 'DESC')->get();
                //dd(DB::getQueryLog());
            }else{
                $all_orders = OrderDetail::whereBetween(DB::raw('created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))->where('order_details.delivery_status', '!=' , 'delivered')->whereIn('product_id', $mapped_product)->selectRaw('SUM(quantity) as total_quantity, product_id, quantity, price, created_at, variation')->groupBy('product_id')->orderBy('created_at', 'DESC')->get();
            }

            
        }else{
            $all_orders = array();
        }
        return view('orders.show_distributor_order', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 's_time', 'e_time'));
    }  
    public function show_peer_commission($id)
    {

        $id = decrypt($id);        

        $start_date = '';
        $end_date = '';
        $from_date = date('Y-m-d');
        $to_date   = date('Y-m-d');

        $peer_refferal_code = PeerPartner::where('id', $id)->select('code','user_id')->first();

        if(!empty($peer_refferal_code)){
           $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $peer_refferal_code->user_id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, refral_code, created_at, id')->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))->get();     
        }else{
            $all_orders = array();
        }       
        return view('orders.show_peer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date'));
    } 

    public function show_peer_commission_by_date(Request $request, $id)
    {

        $id = decrypt($id);        

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;

        $peer_refferal_code = PeerPartner::where('id', $id)->select('code','user_id')->first();

        if(!empty($peer_refferal_code)){
            // DB::enableQueryLog();
           $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('partner_id', $peer_refferal_code->user_id)->where('wallet_status', 1)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, refral_code, created_at, id')->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))->get(); 
           // dd(DB::getQueryLog());    
        }else{
            $all_orders = array();
        }       
        return view('orders.show_peer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date'));
    } 

   
    public function store_order_log(Request $request)
    {
        $min_order_amount = (int)env("MIN_ORDER_AMOUNT");
        $free_shipping_amount = (int)env("FREE_SHIPPING_AMOUNT");

        $wallet = $request->wallet_insert_amount;
        
        $order = new OrderLog;
        if(Auth::check()){
            $order->user_id = Auth::user()->id;
        }else{
            $order->guest_id = mt_rand(100000, 999999);
        }

        $lastorderID = OrderLog::orderBy('id', 'desc')->first();
        if(!empty($lastorderID)){
            $orderId = $lastorderID->id;
        }else{
            $orderId = 1;
        }

        $order->shipping_address = json_encode($request->session()->get('shipping_info'));
        $order->shipping_pin_code = $request->session()->get('shipping_info')['postal_code'];
        $order->payment_type = $request->payment_option;
        $order->delivery_viewed = '0';
        $order->payment_status_viewed = '0';
        $order->code = 'ORD'.mt_rand(10000000,99999999).$orderId;
        $order->date = strtotime('now');
        $order->wallet_amount = $request->wallet_insert_amount;
        $order->order_status = "pending";
       $total_price = 0;
       foreach (Session::get('cart') as $key => $cartItem)
       {
        $total_price += $cartItem['price']*$cartItem['quantity'];
       }
      

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

            //calculate shipping is to get shipping costs of different types
            $admin_products = array();
            $seller_products = array();

            //Order Details Storing
            foreach (Session::get('cart') as $key => $cartItem){
                $product = Product::find($cartItem['id']);

                if($product->added_by == 'admin'){
                    array_push($admin_products, $cartItem['id']);
                }
                else{
                    $product_ids = array();
                    if(array_key_exists($product->user_id, $seller_products)){
                        $product_ids = $seller_products[$product->user_id];
                    }
                    array_push($product_ids, $cartItem['id']);
                    $seller_products[$product->user_id] = $product_ids;
                }

                $subtotal += $cartItem['price']*$cartItem['quantity'];
                $tax += $cartItem['tax']*$cartItem['quantity'];

                $product_variation = $cartItem['variant'];



                if(!empty(Cookie::get('pincode'))){ 
                    $pincode = Cookie::get('pincode');
                    $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
  
                    $product_stock = MappingProduct::where(['sorting_hub_id' => $shortId['sorting_hub_id'],'product_id' => $cartItem['id']])->first();
                    if($product_stock['qty'] != 0){
                        $product_stock['qty'] -= $cartItem['quantity'];
                        //$product_stock->save();
                    }else{
                        if($product_variation != null){
                            $product_stock = $product->stocks->where('variant', $product_variation)->first();
                            $product_stock->qty -= $cartItem['quantity'];
                            //$product_stock->save();
                        }
                        else {
                            $product->current_stock -= $cartItem['quantity'];
                            //$product->save();
                        }

                    }


            
            
                }else{
                    if($product_variation != null){
                        $product_stock = $product->stocks->where('variant', $product_variation)->first();
                        $product_stock->qty -= $cartItem['quantity'];
                        $product_stock->save();
                    }
                    else {
                        $product->current_stock -= $cartItem['quantity'];
                        $product->save();
                    }
                }
            
                

                $order_detail = new OrderDetailLog;
                $order_detail->order_id  =$order->id;
                $order_detail->seller_id = $product->user_id;
                $order_detail->product_id = $product->id;
                $order_detail->variation = $product_variation;
                $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                $order_detail->shipping_type = $cartItem['shipping_type'];
                $order_detail->product_referral_code = $cartItem['product_referral_code'];

                //Dividing Shipping Costs
                if ($cartItem['shipping_type'] == 'home_delivery') {
                    $order_detail->shipping_cost = getShippingCost($key);
                    if($total_price>=$free_shipping_amount)
                    {
                        $order_detail->shipping_cost = 0;
                    }
                    $shipping += $order_detail->shipping_cost;
                }
                else{
                    $order_detail->shipping_cost = 0;
                    $order_detail->pickup_point_id = $cartItem['pickup_point'];
                }
                //End of storing shipping cost

                //
                $id = $cartItem['id'];
                if(!empty($shortId)){
                    $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
                }else{
                    $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
                }

                $product = Product::findOrFail($id);
                $price = $product->unit_price;

                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                $stock_price = $productstock->price;

                $shortId = "";
                    if(!empty(Cookie::get('pincode')))
                    { 
                        $pincode = Cookie::get('pincode');
                        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
  
                        
                    }

                    if(!empty($shortId)){
                        $productmap = MappingProduct::where(['sorting_hub_id'=>$shortId->sorting_hub_id,'product_id'=>$id])->first();
                        $price = $productmap['purchased_price'];
                        $stock_price = $productmap['selling_price'];

                        if($price == 0 || $stock_price == 0){
                            $id = $cartItem['id'];
                            $productold = Product::findOrFail($id);
                            $price = $productold->unit_price;
                            $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                            $stock_price = $productstock->price; 
                        }  

                    }
                

                $main_discount = $stock_price - $price;
                
                $total_discount_percent += substr($peer_discount_check['customer_discount'], 1, -1);
                $discount_percent = substr($peer_discount_check['customer_discount'], 1, -1);

                $total_master_percent += substr($peer_discount_check['company_margin'], 1, -1);
                $master_percent = substr($peer_discount_check['company_margin'], 1, -1);
                $master_last_price = ($main_discount * $master_percent)/100; 
                $master_percentage += $master_last_price*$cartItem['quantity'];

                $last_price = ($main_discount * $discount_percent)/100; 
                $ref_dis += $last_price*$cartItem['quantity'];

                $total_peer_percent += substr($peer_discount_check['peer_discount'], 1, -1);
                $peer_percent = substr($peer_discount_check['peer_discount'], 1, -1);
                $peer_last_price = ($main_discount * $peer_percent)/100; 
                $peer_percentage += $peer_last_price*$cartItem['quantity'];

                $order_detail->quantity = $cartItem['quantity'];
                $order_detail->peer_discount = $last_price*$cartItem['quantity'];
                $order_detail->save();

                $product->num_of_sale++;
                $product->save();

                
            }
            // echo $total_discount_percent;
            // echo '<br>';
            // echo $total_peer_percent;
            // echo '<br>';
            // echo $peer_percentage;
            // die;
            //$order->grand_total = $subtotal + $tax + $shipping;
             if($wallet!=0){
                    $subtotal = $subtotal- $wallet;
                    
                }else{
                    $subtotal = $subtotal;
                }

            $order->grand_total = $subtotal + $shipping;

            if(Session::has('referal_discount')){

                if(Auth::check()){
                    $u_id = Auth::user()->id;
                }else{
                    $u_id = '';
                }

                $partner_id = PeerPartner::where('code', Session::get('referal_code'))->first();
                $referal_discount = ($order->grand_total * Session::get('referal_discount')) / 100;
               
                       $total_amount = $subtotal + $shipping;
                       $order->grand_total = $total_amount - $ref_dis;
                       $order->referal_discount =  $ref_dis;                  

                    $referal_usage = new ReferalUsage;
                    $referal_usage->user_id = $u_id;
                    $referal_usage->partner_id = Session::get('partner_id');
                    $referal_usage->order_id = $order->id;
                    $referal_usage->referal_code = Session::get('referal_code');
                    $referal_usage->discount_rate = $total_discount_percent;
                    $referal_usage->discount_amount = $peer_percentage;
                    $referal_usage->commision_rate = $total_peer_percent;
                    $referal_usage->master_discount = $master_percentage;
                    $referal_usage->master_percentage = $total_master_percent;
                    $referal_usage->save();
            }

            if(Session::has('coupon_discount')){
                // $order->grand_total -= Session::get('coupon_discount');
                // $order->coupon_discount = Session::get('coupon_discount');

                $coupon_usage = new CouponUsage;
                $coupon_usage->user_id = Auth::user()->id;
                $coupon_usage->coupon_id = Session::get('coupon_id');
                $coupon_usage->save();
            }

            $order->save();
// die;
            //stores the pdf for invoice
      //       $pdf = PDF::setOptions([
      //                       'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
      //                       'logOutputFile' => storage_path('logs/log.htm'),
      //                       'tempDir' => storage_path('logs/')
      //                   ])->loadView('invoices.customer_invoice', compact('order'));
      //       $output = $pdf->output();
            // file_put_contents('public/invoices/'.'Order#'.$order->code.'.pdf', $output);

      //       $array['view'] = 'emails.invoice';
      //       $array['subject'] = 'Order Placed - '.$order->code;
      //       $array['from'] = env('MAIL_USERNAME');
      //       $array['content'] = translate('Hi. A new order has been placed. Please check the attached invoice.');
      //       $array['file'] = 'public/invoices/Order#'.$order->code.'.pdf';
      //       $array['file_name'] = 'Order#'.$order->code.'.pdf';

        if($request['payment_option'] != "cash_on_delivery"){
            //mobilnxtSendSMS($to,$from,$msg,$tid);
            foreach($seller_products as $key => $seller_product){
                try {
                    Mail::to(\App\User::find($key)->email)->queue(new InvoiceEmailManager($array));
                } catch (\Exception $e) {

                }
            }

            if (\App\Addon::where('unique_identifier', 'otp_system')->first() != null && \App\Addon::where('unique_identifier', 'otp_system')->first()->activated && \App\OtpConfiguration::where('type', 'otp_for_order')->first()->value){
                try {
                    $otpController = new OTPVerificationController;
                    $otpController->send_order_code($order);
                } catch (\Exception $e) {

                }
            }
        }


            if($request['payment_option'] == "cash_on_delivery"){
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

                // $user_wallet = User::where('id', Auth::user()->id)->first();
                // $last_wallet = $user_wallet->balance - $order->wallet_amount;
                // $referall_balance = User::where('id', Auth::user()->id)->update([
                //                             'balance' => $last_wallet,
                //                         ]);

                //$this->sendOrderMail($request,$order,$seller_products);
                //mobilnxtSendSMS($to,$from,$msg,$tid);
                //SMSonOrderPlaced($order->id);
            }
            //sends email to customer with the invoice pdf attached
            // if(env('MAIL_USERNAME') != null){
            //     try {
            //         Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
            //         Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
            //     } catch (\Exception $e) {

            //     }
            // }
            //unlink($array['file']);

            $request->session()->put('order_id', $order->id);
        }
    }

    public function cancel_order($id)
    {
        $order = Order::findOrfail(decrypt($id));
        return view('frontend.order_cancel',compact('order'));
    }

    public function cancelOrder(Request $request)
    {
        DB::beginTransaction();
        $order = Order::findOrfail($request->order_id);
        $getUserWallet = User::where('id',$order->user_id)->first();
        
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
                    $fullRefundResponse = razorpayFullRefund($pay_id);
                    //dd($fullRefundResponse);
                    if($fullRefundResponse['status']=='200'){
                            $refund_response = [
                            'id'=> $fullRefundResponse['refund_response']['id'],
                            'amount'=> $fullRefundResponse['refund_response']['amount'],
                            'status'=> $fullRefundResponse['refund_response']['status']
                        ];
                    }else{
                        
                        flash(translate('Something went wrong.'))->success();
                        return redirect()->back();
    
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
                $user_wallet = User::where('id', Auth::user()->id)->first();
                $last_wallet = $user_wallet->balance + $order->wallet_amount;
                $referall_balance = User::where('id', Auth::user()->id)->update([
                                            'balance' => $last_wallet,
                                        ]);
                    if($referall_balance){
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
                        flash(translate('Something went wrong.'))->success();
                        return redirect()->back();
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
                    }else{
                        flash(translate('Something went wrong.'))->success();
                        return redirect()->back();
    
                    }

                }

                // refund wallet amount to peer wallet balance 
                $getUserWallet->balance += $order->wallet_amount;
                if($getUserWallet->save()){
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
                   
                    flash(translate('Something went wrong.'))->success();
                    return redirect()->back();
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
            }

            $order->order_status = 'cancel';
            
            $order->save();
            $cancellog = new CancelOrderLog;
            $cancellog->order_id = $order->id;
            if(isset($refund_response)){
                $cancellog->refund_details = json_encode($refund_response);
            }
            $cancellog->save();
            //update stock
            updateStock($order->id);
            DB::commit();
            //email to customer and admin
            $notifaction = ['ordercode'=>$order->code];
            if(!empty($order->user_id))
            {
                $user = User::find($order->user_id);
                $notifaction['name'] = $user->name;
                $user->notify(new cancelOrderMail($notifaction));
            }
            else
            {
                $email = json_decode($order->shippong_address)->email;
                $notifaction['name'] = json_decode($order->shipping_address)->name;
                Notification::route('mail',$email)->notify(new cancelOrderMail($notifaction));
            }
            //sms to admin and customer 
            Session::flash('success','Order Cancelled');
            return redirect()->route('purchase_history.index');
        
    }catch (\Throwable $e) {
        
        DB::rollback();
        flash(translate('Something went wrong.'))->success();
        throw $e;
        return redirect()->back();
    }
    }

    public function removeOrderProduct($id){
        $min_order_amount = (int)env("MIN_ORDER_AMOUNT");
        $free_shipping_amount = (int)env("FREE_SHIPPING_AMOUNT");

        DB::beginTransaction();
        $orderDetail = OrderDetail::where('id',$id)->first();
        // echo $orderDetail->product_id;
        // die;
        $order = Order::where('id',$orderDetail->order_id)->first();
        $order->edited = 1;
        $order->save();
        //$total_price = $orderDetail->price*$orderDetail->quantity;
        $total_price = $orderDetail->price;
        $getUserWallet = User::where('id',$order->user_id)->first();
        $shipping_cost = $orderDetail->shipping_cost;
        $peer_discount = 0.00;
        
        
        try {
            if(!empty($orderDetail->peer_discount)){
                $peer_discount = $orderDetail->peer_discount;
                $peerCommission = OrderReferalCommision::where('order_id',$order->id)->first();
                if(!is_null($peerCommission)){
                    $referalUsage = ReferalUsage::where('order_id',$order->id)->first();
                    $peerCommission->order_amount -= ($total_price - $orderDetail->peer_discount);
                    $peerCommission->referal_commision_discount -= $orderDetail->sub_peer;
                    $peerCommission->master_discount  -= $orderDetail->master_peer;
                    $peerCommission->save();
                    $referalUsage->discount_amount -= $orderDetail->sub_peer;
                    $referalUsage->master_discount  -= $orderDetail->master_peer;
                    $referalUsage->save();
                }
            }
            $refund_response = array();
            $refund_amount = $total_price - $orderDetail->peer_discount;
            if($order->payment_type == "razorpay" && empty($order->wallet_amount)){
                $payment_response = json_decode($order->payment_details);
                $pay_id = $payment_response->id;
                $res = razorpayPartialRefund($pay_id,$refund_amount);
                if($res['status'] == 200){
                    $refund_response = [
                        'id'=> $res['refund_response']['id'],
                        'amount'=> $res['refund_response']['amount'],
                        'payment_id'=> $res['refund_response']['payment_id'],
                        'status'=> $res['refund_response']['status']
                    ];
                }else{
                    flash(translate('Something went wrong.'))->success();
                    return redirect()->back();

                }
                

            }elseif($order->payment_type == "wallet"){
                
               
                $getUserWallet->balance += $refund_amount;
                if($getUserWallet->save()){
                    $order->wallet_amount -= $refund_amount;

                }else{
                    flash(translate('Something went wrong.'))->success();
                    return redirect()->back();
                }

            }elseif($order->payment_type == "razorpay" && !empty($order->wallet_amount)){
                $payment_response = json_decode($order->payment_details);
                $pay_id = $payment_response->id;
                $resPay = $this->getRemainAmountRazorpay($pay_id);
                if(!empty($resPay)){
                    
                    $cardAmount = $resPay['amount']/100;
                    if(isset($resPay['amount_refunded']) && $resPay['refund_status'] != null && $resPay['amount_refunded'] != null){
                        $refunded_amount = $resPay['amount_refunded']/100;
                        if($resPay['refund_status'] == "partial"){
                            $cardAmount = $cardAmount-$refunded_amount;

                        }else{
                            $cardAmount = 0;
                        }
                        
                    

                    }
                    if($refund_amount <= $cardAmount){
                        $res = razorpayPartialRefund($pay_id,$refund_amount);
                        if($res['status'] == 200){
                            $refund_response = [
                                'id'=> $res['refund_response']['id'],
                                'amount'=> $res['refund_response']['amount'],
                                'payment_id'=> $res['refund_response']['payment_id'],
                                'status'=> $res['refund_response']['status']
                            ];
                        }else{
                            flash(translate('Something went wrong.'))->success();
                            return redirect()->back();

                        }
                        

                    }elseif($refund_amount > $cardAmount){
                        $refund_amount_wallet = $refund_amount - $cardAmount;
                        
                        if($cardAmount != 0){
                            $fullRefundResponse = razorpayFullRefund($pay_id);
                            if($fullRefundResponse['status'] == 200){
                                $refund_response = [
                                    'id'=> $fullRefundResponse['refund_response']['id'],
                                    'amount'=> $fullRefundResponse['refund_response']['amount'],
                                    'payment_id'=> $fullRefundResponse['refund_response']['payment_id'],
                                    'status'=> $fullRefundResponse['refund_response']['status']
                                ];
                            }else{
                                flash(translate('Something went wrong.'))->success();
                                return redirect()->back();
            
                            }

                        }
                        $getUserWallet->balance += $refund_amount_wallet;
                        if($getUserWallet->save()){
                            $order->wallet_amount -= $refund_amount_wallet;
        
                        }else{
                            flash(translate('Something went wrong.'))->success();
                            return redirect()->back();
                        }

                    }

                }else{
                    flash(translate('Something went wrong.'))->success();
                    return redirect()->back();
                }
            }

            $order->grand_total -=  $refund_amount;
            $order->referal_discount -= $peer_discount;
            
            if($order->save()){
                if(empty($shipping_cost)){
                    if($order->grand_total<$free_shipping_amount){
                        $shipping_cost = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
                    }
                    
                }
                $orderDeatilLog = OrderRemoveLog::create([
                    'order_id' => $orderDetail->order_id,
                    'order_detail_id' => $orderDetail->id,
                    'product_id' => $orderDetail->product_id,
                    'price' => $orderDetail->price,
                    'shipping_cost' => $orderDetail->shipping_cost,
                    'peer_discount' => $orderDetail->peer_discount,
                    'payment_response' => json_encode($refund_response),
                    'payment_type' => $order->payment_type,
                    'refund_amount' => $refund_amount,
                    'quantity' => $orderDetail->quantity,
                    'payment_status' => $orderDetail->payment_status,
                    'delivery_status' => $orderDetail->delivery_status,
                    'shipping_type' => $orderDetail->shipping_type,
                    'pickup_point_id' => $orderDetail->pickup_point_id,
                    'product_referral_code' => $orderDetail->product_referral_code
                ]);
                    $orderDetail->delete();
                    if(!empty($shipping_cost)){
                        $getOrderDetail = OrderDetail::where('order_id',$order->id)->get();
                        foreach($getOrderDetail as $k=>$v){
                            $getOrderDetail[$k]->shipping_cost += $shipping_cost/count($getOrderDetail);
                            $getOrderDetail[$k]->save();
    
                        }
                        

                    }
                    
                    $OrdDetail = OrderDetail::where('order_id',$order->id)->whereNull('deleted_at');

                    updateFinalOrder($order->id,$OrdDetail->sum('quantity'),$order->grand_total,$order->referal_discount);
                    $no_of_items_of_sub_order = $OrdDetail->where('order_type',$orderDetail->order_type)->sum('quantity');
                    $total_payable_amount_of_sub_order = $OrdDetail->where('order_type',$orderDetail->order_type)->sum('price')-($OrdDetail->where('order_type',$orderDetail->order_type)->sum('shipping_cost')+$OrdDetail->where('order_type',$orderDetail->order_type)->sum('peer_discount'));
                    $total_discount_of_sub_order = $OrdDetail->where('order_type',$orderDetail->order_type)->sum('peer_discount');
                    updateSubOrder($no_of_items_of_sub_order,$orderDetail->order_type,$order->id,$total_payable_amount_of_sub_order,$total_discount_of_sub_order);

                    DB::commit();
                    // flash(translate('Order has been deleted successfully'))->success();
                    flash(translate('Product has been removed.'))->success();
                    return redirect()->back();
            }
            
        } catch (\Throwable $e) {
            DB::rollback();
            flash(translate('Something went wrong.'))->success();
            throw $e;
            return redirect()->back();
        }

    }


    public function getRemainAmountRazorpay($pay_id){
        $response = array();
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        $payment = $api->payment->fetch($pay_id);
        return $payment;
        

    }


    public function order_replacement($id)
    {
        $order = Order::findOrfail(decrypt($id));
        return view('frontend.replacement.order_replacement',compact('order'));
    }

    public function getOrderReplaceDetail(REQUEST $request){
        $orderDeatilId = $request->product_detail_id;

        $orderDetail = OrderDetail::where('id',$orderDeatilId)->first();
        return $orderDetail;

    }


    public function storeOrderReplace(REQUEST $request){

       $replaceOrder = new ReplacementOrder;
       $imageName = [];
       $replaceOrder->order_id = $request->order_id;
       $replaceOrder->order_detail_id = $request->order_details;
       $replaceOrder->message = $request->reason;
       if(!empty($request->photo)){
           foreach($request->photo as $key=>$value){
            $imageName[$key] = $value->store('frontend/images');
           }
       }
       $replaceOrder->photos = json_encode($imageName);
       if($replaceOrder->save()){
        flash(translate('Message has been sent successfully'))->success();
        

       }else{
        flash(translate('Something went wrong'))->error();
       }
       return back();


        
    }

    //25-09-2021
    /*public function orders_export(){

        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

        ini_set('max_execution_time', -1);
        return Excel::download(new OrdersExport($sorting_hub_id,$from,$to,$deliveryStatus,$payStatus,$paymentStatus), 'inhouseorders.xlsx');
    }*/

    //27-09-2021
    public function orders_export_all(){
        $sorting_hub_id = Auth()->user()->id;
        ini_set('max_execution_time', -1);
        return Excel::download(new OrdersExport($sorting_hub_id), 'inhouseorders.xlsx');
    }

    public function orders_export(){
       

        ini_set('memory_limit','1024M');
        set_time_limit(0); //You can use 0 to remove limits

        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

        // dd($from);
        // ini_set('max_execution_time', -1);
        // return Excel::download(new OrdersExport($sorting_hub_id,$from,$to,$deliveryStatus,$payStatus,$paymentStatus), 'inhouseorders.xlsx');

        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $sorting_hub_id = $sorting_hub_id;
            // $sorting_hub = ShortingHub::on('mysql2')->where('user_id', $sorting_hub_id)->first();
            $sorting_hub = ShortingHub::on('mysql')->where('user_id', $sorting_hub_id)->first();
            $result = json_decode($sorting_hub['area_pincodes']);
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        if(isset($result)){
            // $orders = Order::on('mysql2')->where('dofo_status',0)->whereIn('shipping_pin_code', $result)->where('log',0);
            $orders = Order::on('mysql')->where('dofo_status',0)->whereIn('shipping_pin_code', $result)->where('log',0);
            if(isset($from)){
                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                }

                if($from != $to){
                    $orders = $orders->whereBetween('created_at', [$from, $to]);
                }else{
                    $orders = $orders->whereDate('created_at',$from);
                }
            }
            if(isset($deliveryStatus) && $deliveryStatus != NULL){
                $orders = $orders->where('order_status', $deliveryStatus);
            }

            if(isset($payStatus) && $payStatus != NULL){
                $orders = $orders->where('payment_type', $payStatus);
            }

            if(isset($paymentStatus) && $paymentStatus != NULL){
                $orders = $orders->where('payment_status', $paymentStatus);
            }

            $orders = $orders->orderBy('created_at','desc')->get();
            
        }else{

            if(isset($from)){

                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                    // $to = date('Y-m-d',strtotime($to));
                }
                
                if($from != $to){
                    // $orders = Order::on('mysql2')->where('dofo_status',0)->whereBetween('created_at', [$from, $to]);
                    $orders = Order::where('dofo_status',0)->whereBetween('created_at', [$from, $to]);
                }else{
                    // $orders = Order::on('mysql2')->where('dofo_status',0)->whereDate('created_at',$from);
                    $orders = Order::where('dofo_status',0)->whereDate('created_at',$from);
                }

                if(isset($deliveryStatus) && $deliveryStatus != NULL){
                    $orders = $orders->where('order_status', $deliveryStatus);
                }

                if(isset($payStatus) && $payStatus != NULL){
                    $orders = $orders->where('payment_type', $payStatus);
                }

                if(isset($paymentStatus) && $paymentStatus != NULL){
                    $orders = $orders->where('payment_status', $paymentStatus);
                }
                // DB::enableQueryLog();
                $orders = $orders->where('log',0)->orderBy('created_at','desc')->get();
                // dd(DB::getQueryLog());
            }else{
                // $orders = Order::on('mysql2')->where('dofo_status',0)->all();
                $orders = Order::on('mysql')->where('dofo_status',0)->all();
            }
            
        }
        // echo '<pre>';
        // print_r($orders);
        // die;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        ini_set('max_execution_time', -1);
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Order Code');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Num. of Products');
        $sheet->setCellValue('E1', 'Customer');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Pin Code');
        $sheet->setCellValue('H1', 'Phone Number');
        // $sheet->setCellValue('H1', 'HSN Code');
        $sheet->setCellValue('I1', 'Sorting HUB');
        // $sheet->setCellValue('J1', 'Product  Name');
        // $sheet->setCellValue('K1', 'Qty');
        // $sheet->setCellValue('L1', 'GST Rate');
        // $sheet->setCellValue('M1', 'Price');
        // $sheet->setCellValue('N1', 'Discount Price');        
        // $sheet->setCellValue('J1', 'Shipping Cost');
        $sheet->setCellValue('J1', 'Payment Mode');
        $sheet->setCellValue('K1', 'Total Amount');
        $sheet->setCellValue('L1', 'Delivery Status');
        $sheet->setCellValue('M1', 'Delivery Date');
        $sheet->setCellValue('N1', 'Payment Method');
        $sheet->setCellValue('O1', 'Payment Status');
        $sheet->setCellValue('P1', 'Email');
        $sheet->setCellValue('Q1', 'Peer Code');
        $sheet->setCellValue('R1', 'Delivery Boy');
        $sheet->setCellValue('S1', 'Delivery Slot');
        $sheet->setCellValue('T1', 'Delivery Time');

        $i = 0;

        foreach($orders as $key => $order)
        {
            
        $date = date("d/m/Y h:i:s A", $order->date);

        $numProduct = $order->orderDetails->where('order_id', $order->id)->sum('quantity');
        
        // $delivery_peercode = ReferalUsage::on('mysql2')->where('order_id',$order->id)->first('referal_code');
        $delivery_peercode = ReferalUsage::on('mysql')->where('order_id',$order->id)->first('referal_code');
        
        if(!empty($delivery_peercode)){
            $peercode = $delivery_peercode->referal_code;
        }else{
            $peercode = 'NA';
        }
        

        $address = json_decode($order->shipping_address);
        $phone = "";

        if($order->user != null){

            // $customer = $order->user->name.' '.@$address->phone;
            $customer = $order->user->name;
            $phone = @$address->phone;
        }else{
            if(!empty($address->name) && !empty($address->phone)){
                $customer = 'Guest-'.$address->name.''.$address->phone;
                $phone = $address->phone;
            }else{
                $customer = 'Guest';
                $phone = '';
            }
        }

        $customer_detail = $customer;
        
        // $getAssignedBoy = AssignOrder::on('mysql2')->where('order_id',$order->id)->first('delivery_boy_id');
        $getAssignedBoy = AssignOrder::on('mysql')->where('order_id',$order->id)->first('delivery_boy_id');

        if($getAssignedBoy != NULL){
            // $deliveryBoy = DeliveryBoy::on('mysql2')->where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
            $deliveryBoy = DeliveryBoy::on('mysql')->where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
            // $deliveryBoyName = User::on('mysql2')->where('id',$deliveryBoy['user_id'])->first('name');
            $deliveryBoyName = User::on('mysql')->where('id',$deliveryBoy['user_id'])->first('name');
            $deliveryBoyName = $deliveryBoyName['name'];
        }else{
            $deliveryBoyName = ' ';
        }
        
        // $sortingHub = ShortingHub::on('mysql2')->whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
        $sortingHub = ShortingHub::on('mysql')->whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
            if(!empty($sortingHub)){
              $sortingHub = $sortingHub->user->name;

            }else{
                $sortingHub = "Not Available";
            }


        if($order->wallet_amount == 0){
            $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('price') + $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('shipping_cost') - $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('peer_discount');
        }else{
            $total_amount = $order->orderDetails->where('order_id', $order->id)->sum('price') + $order->orderDetails->where('order_id', $order->id)->sum('shipping_cost'); 
        }

        if($order->referal_discount > 0){
              $referral = $order->referal_discount;
              $total_discount = $order->orderDetails->where('order_id', $order->id)->sum('peer_discount');
        }
        if($order->wallet_amount > 0){
            $wallet = $order->wallet_amount;
            // $total_amount = $total_amount - $wallet;
            if(!empty($total_discount)){
                $total_amount = $total_amount - $total_discount;
            }else{
                $total_amount = $total_amount;
            }
            
        }

        if($order->payment_type=='wallet'){
            $total_amount = $order->wallet_amount;
        }
         
        $amount = single_price($total_amount);
        
        $deliveryStatus = ucfirst(str_replace('_', ' ', $order->order_status));

        if($deliveryStatus == 'pending'){
            $deliveryDate = '';
        }else{
            $deliveryDate = date('d/m/Y H:i:s', strtotime($order->updated_at));
        }
        
        $paymentType = ucfirst(str_replace('_', ' ', $order->payment_type));

        if(!empty($address->address)){
            $user_address = $address->address;
        }else{
            $user_address = "";
        }

        $slot = array(); 
        $d_slottime = array();
        foreach($order->sub_orders as $key => $value)
        {
            if(($value['delivery_name']==null) || ($value['delivery_name']=='')){
                $d_name = 'NA';
            }else{
                $d_name = strtoupper($value['delivery_name']);
            }

            if(($value['delivery_type']==null) || ($value['delivery_type']=='')){
                $d_type = 'NA';
            }else{
                $d_type = strtoupper($value['delivery_type']);
            }

            if(($value['delivery_date']==null) || ($value['delivery_date']=='')){
                $d_date = 'NA';
            }else{
                $d_date = date('d M, Y',strtotime($value['delivery_date']));
            }

            if(($value['delivery_time']==null) || ($value['delivery_time']=='')){
                $d_time = 'NA';
            }else{
                $d_time = strtoupper($value['delivery_time']);
            }

           array_push($slot, $d_name.'-'.$d_type); 
           array_push($d_slottime, $d_date.' '.$d_time);         
        }
        $slot = implode(',',$slot);
        $d_slottime = implode(',',$d_slottime);

        // $total_shipping = OrderDetail::on('mysql2')->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('shipping_cost');
        $total_shipping = OrderDetail::on('mysql')->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('shipping_cost');
            // foreach($order->orderDetails as $k=>$v)
            // {
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $order->code);
                $sheet->setCellValue('C'.($i+2), $date);
                $sheet->setCellValue('D'.($i+2), $numProduct);
                $sheet->setCellValue('E'.($i+2), $customer_detail);
                $sheet->setCellValue('F'.($i+2), $user_address);
                $sheet->setCellValue('G'.($i+2), $order->shipping_pin_code);
                $sheet->setCellValue('H'.($i+2),  $phone); 
                // $sheet->setCellValue('H'.($i+2), 'hsn');
                $sheet->setCellValue('I'.($i+2), $sortingHub);
                // $sheet->setCellValue('J'.($i+2), 'name');
                // $sheet->setCellValue('K'.($i+2), 'quantity');
                // $sheet->setCellValue('L'.($i+2), 'tax');
                // $sheet->setCellValue('M'.($i+2), 'price');
                // $sheet->setCellValue('N'.($i+2), 'll');
                // $sheet->setCellValue('J'.($i+2), $total_shipping);
                $sheet->setCellValue('J'.($i+2), $order->payment_type);
                $sheet->setCellValue('K'.($i+2), $amount);
                $sheet->setCellValue('L'.($i+2), $deliveryStatus);
                $sheet->setCellValue('M'.($i+2), $deliveryDate);
                $sheet->setCellValue('N'.($i+2), $paymentType);
                $sheet->setCellValue('O'.($i+2), $order->payment_status);
                $sheet->setCellValue('P'.($i+2), @$address->email);
                $sheet->setCellValue('Q'.($i+2),  $peercode); 
                $sheet->setCellValue('R'.($i+2),  $deliveryBoyName);
                $sheet->setCellValue('S'.($i+2),  @$slot); 
                $sheet->setCellValue('T'.($i+2),  @$d_slottime);               
                $i++;

            // }
       
        }

        $filename = "inhouseorders.xlsx";
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    public function orders_productexport(){
        echo "hello";die;

        ini_set('memory_limit','1024M');
        set_time_limit(0); //You can use 0 to remove limits
        
        
        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

        // ini_set('max_execution_time', -1);
        // return Excel::download(new OrdersExport($sorting_hub_id,$from,$to,$deliveryStatus,$payStatus,$paymentStatus), 'inhouseorders.xlsx');

        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $sorting_hub_id = $sorting_hub_id;
            // $sorting_hub = ShortingHub::on('mysql2')->where('user_id', $sorting_hub_id)->first();
            $sorting_hub = ShortingHub::on('mysql')->where('user_id', $sorting_hub_id)->first();
            $result = json_decode($sorting_hub['area_pincodes']);
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        if(isset($result)){
            // $orders = Order::on('mysql2')->whereIn('shipping_pin_code', $result)->where('log',0);
            $orders = Order::on('mysql')->whereIn('shipping_pin_code', $result)->where('log',0);
            if(isset($from)){
                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                }

                if($from != $to){
                    $orders = $orders->whereBetween('created_at', [$from, $to]);
                }else{
                    $orders = $orders->whereDate('created_at',$from);
                }
            }
            if(isset($deliveryStatus) && $deliveryStatus != NULL){
                $orders = $orders->where('order_status', $deliveryStatus);
            }

            if(isset($payStatus) && $payStatus != NULL){
                $orders = $orders->where('payment_type', $payStatus);
            }

            if(isset($paymentStatus) && $paymentStatus != NULL){
                $orders = $orders->where('payment_status', $paymentStatus);
            }

            $orders = $orders->orderBy('created_at','desc')->get();
            
        }else{

            if(isset($from)){

                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                    // $to = date('Y-m-d',strtotime($to));
                }
                
                if($from != $to){
                    // $orders = Order::on('mysql2')->whereBetween('created_at', [$from, $to]);
                    $orders = Order::on('mysql')->whereBetween('created_at', [$from, $to]);
                }else{
                    // $orders = Order::on('mysql2')->whereDate('created_at',$from);
                    $orders = Order::on('mysql')->whereDate('created_at',$from);
                }

                if(isset($deliveryStatus) && $deliveryStatus != NULL){
                    $orders = $orders->where('order_status', $deliveryStatus);
                }

                if(isset($payStatus) && $payStatus != NULL){
                    $orders = $orders->where('payment_type', $payStatus);
                }

                if(isset($paymentStatus) && $paymentStatus != NULL){
                    $orders = $orders->where('payment_status', $paymentStatus);
                }
                // DB::enableQueryLog();
                $orders = $orders->where('log',0)->orderBy('created_at','desc')->get();
                // dd(DB::getQueryLog());
            }else{
                // $orders = Order::on('mysql2')->all();
                $orders = Order::on('mysql')->all();
            }
            
        }


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        ini_set('max_execution_time', -1);
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Order Code');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Num. of Products');
        $sheet->setCellValue('E1', 'Customer');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Pin Code');
        $sheet->setCellValue('H1', 'HSN Code');
        $sheet->setCellValue('I1', 'Sorting HUB');
        $sheet->setCellValue('J1', 'Product  Name');
        $sheet->setCellValue('K1', 'Qty');
        $sheet->setCellValue('L1', 'GST Rate');
        $sheet->setCellValue('N1', 'Discount Price');
        $sheet->setCellValue('M1', 'Price');
        $sheet->setCellValue('O1', 'Shipping Cost');
        $sheet->setCellValue('P1', 'Payment Mode');
        $sheet->setCellValue('Q1', 'Amount');
        $sheet->setCellValue('R1', 'Delivery Status');
        $sheet->setCellValue('S1', 'Delivery Date');
        $sheet->setCellValue('T1', 'Payment Method');
        $sheet->setCellValue('U1', 'Payment Status');
        $sheet->setCellValue('V1', 'Email');
        $sheet->setCellValue('W1', 'Peer Code');
        $sheet->setCellValue('X1', 'Phone Number');
        $sheet->setCellValue('Y1', 'Delivery Slot');
        $i = 0;

        foreach($orders as $key => $order)
        {
            
        $date = date("d/m/Y h:i:s A", $order->date);

        $numProduct = $order->orderDetails->where('order_id', $order->id)->sum('quantity');
        
        // $delivery_peercode = ReferalUsage::on('mysql2')->where('order_id',$order->id)->first('referal_code');
        $delivery_peercode = ReferalUsage::on('mysql')->where('order_id',$order->id)->first('referal_code');
        
        if(!empty($delivery_peercode)){
            $peercode = $delivery_peercode->referal_code;
        }else{
            $peercode = 'NA';
        }
        

        $address = json_decode($order->shipping_address);
        $phone = "";

        if($order->user != null){

            $customer = $order->user->name.' '.@$address->phone;
            $phone = @$address->phone;
        }else{
            if(!empty($address->name) && !empty($address->phone)){
                $customer = 'Guest-'.$address->name.''.$address->phone;
                $phone = $address->phone;
            }else{
                $customer = 'Guest';
                $phone = '';
            }
        }

        $customer_detail = $customer;
        
        // $getAssignedBoy = AssignOrder::on('mysql2')->where('order_id',$order->id)->first('delivery_boy_id');
        $getAssignedBoy = AssignOrder::on('mysql')->where('order_id',$order->id)->first('delivery_boy_id');

        if($getAssignedBoy != NULL){
            // $deliveryBoy = DeliveryBoy::on('mysql2')->where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
            $deliveryBoy = DeliveryBoy::on('mysql')->where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
            // $deliveryBoyName = User::on('mysql2')->where('id',$deliveryBoy['user_id'])->first('name');
            $deliveryBoyName = User::on('mysql')->where('id',$deliveryBoy['user_id'])->first('name');
            $deliveryBoyName = $deliveryBoyName['name'];
        }else{
            $deliveryBoyName = ' ';
        }
        
        // $sortingHub = ShortingHub::on('mysql2')->whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
        $sortingHub = ShortingHub::on('mysql')->whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
            if(!empty($sortingHub)){
              $sortingHub = $sortingHub->user->name;

            }else{
                $sortingHub = "Not Available";
            }
         
        if($order->wallet_amount == 0){
            $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('price') + $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('shipping_cost') - $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('peer_discount');
        }else{
            $total_amount = $order->orderDetails->where('order_id', $order->id)->sum('price') + $order->orderDetails->where('order_id', $order->id)->sum('shipping_cost'); 
        }
 
        if($order->referal_discount > 0){
              $referral = $order->referal_discount;
        }

        if($order->wallet_amount > 0){
            $wallet = $order->wallet_amount;
            $total_amount = $total_amount - $wallet;
        }
         
        $amount = single_price($total_amount);
        
        $deliveryStatus = ucfirst(str_replace('_', ' ', $order->order_status));

        if($deliveryStatus == 'pending'){
            $deliveryDate = '';
        }else{
            $deliveryDate = date('d/m/Y H:i:s', strtotime($order->updated_at));
        }
        
        $paymentType = ucfirst(str_replace('_', ' ', $order->payment_type));

        if(!empty($address->address)){
            $user_address = $address->address;
        }else{
            $user_address = "";
        }

        // $ordertype = OrderDetail::on('mysql2')->where('order_id',$order->id)->select('order_type')->first();
        $ordertype = OrderDetail::on('mysql')->where('order_id',$order->id)->select('order_type')->first();
        if($ordertype['order_type']!=''){
                // dd($ordertype->order_type);
                if($ordertype['order_type'] == 'fresh'){
                    $schedule = SubOrder::where('order_id',$order->id)->where('delivery_name', $ordertype->order_type)->where('status',1)->first();
                    if($schedule['delivery_type'] == 'normal'){
                        if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                            $d_name = 'NA';
                        }else{
                            $d_name = strtoupper($schedule['delivery_name']);
                        }
                        
                        if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                            $d_type = 'NA';
                        }else{
                            $d_type = strtoupper($schedule['delivery_type']);
                        }


                        if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                            $d_date = 'NA';
                        }else{
                            $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                        }

                        if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                            $d_time = 'NA';
                        }else{
                            $d_time = $schedule['delivery_time'];
                        }
                        $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                    }else{
                        if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                            $d_name = 'NA';
                        }else{
                            $d_name = strtoupper($schedule['delivery_name']);
                        }
                        
                        if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                            $d_type = 'NA';
                        }else{
                            $d_type = strtoupper($schedule['delivery_type']);
                        }


                        if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                            $d_date = 'NA';
                        }else{
                            $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                        }

                        if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                            $d_time = 'NA';
                        }else{
                            $d_time = $schedule['delivery_time'];
                        }
                        $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                    }
                        
                }else{
                    // $schedule = SubOrder::on('mysql2')->where('order_id',$order->id)->where('delivery_name', $ordertype->order_type)->where('status',1)->first();
                    $schedule = SubOrder::on('mysql')->where('order_id',$order->id)->where('delivery_name', $ordertype->order_type)->where('status',1)->first();
                    if($schedule['delivery_type'] == 'normal'){
                        if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                            $d_name = 'NA';
                        }else{
                            $d_name = strtoupper($schedule['delivery_name']);
                        }
                        
                        if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                            $d_type = 'NA';
                        }else{
                            $d_type = strtoupper($schedule['delivery_type']);
                        }


                        if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                            $d_date = 'NA';
                        }else{
                            $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                        }

                        if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                            $d_time = 'NA';
                        }else{
                            $d_time = $schedule['delivery_time'];
                        }
                        $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                    }else{
                        if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                            $d_name = 'NA';
                        }else{
                            $d_name = strtoupper($schedule['delivery_name']);
                        }
                        
                        if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                            $d_type = 'NA';
                        }else{
                            $d_type = strtoupper($schedule['delivery_type']);
                        }


                        if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                            $d_date = 'NA';
                        }else{
                            $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                        }

                        if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                            $d_time = 'NA';
                        }else{
                            $d_time = $schedule['delivery_time'];
                        }
                        $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                    }
                     
                }
            }else{
                $slot = 'NA';
            }

          // dd($slot);

            foreach($order->orderDetails as $k=>$v)
            {
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $order->code);
                $sheet->setCellValue('C'.($i+2), $date);
                $sheet->setCellValue('D'.($i+2), $numProduct);
                $sheet->setCellValue('E'.($i+2), $customer_detail);
                $sheet->setCellValue('F'.($i+2), $user_address);
                $sheet->setCellValue('G'.($i+2), $order->shipping_pin_code);
                $sheet->setCellValue('H'.($i+2), @$v->product->hsn_code);
                $sheet->setCellValue('I'.($i+2), $sortingHub);
                $sheet->setCellValue('J'.($i+2), @$v->product['name']);
                $sheet->setCellValue('K'.($i+2), $v->quantity);
                $sheet->setCellValue('L'.($i+2), $v->product['tax']);
                $sheet->setCellValue('M'.($i+2), $v->price);
                $sheet->setCellValue('N'.($i+2), ($v->price-$v->peer_discount));
                $sheet->setCellValue('O'.($i+2), $v->shipping_cost);
                $sheet->setCellValue('P'.($i+2), $order->payment_type);
                $sheet->setCellValue('Q'.($i+2), $amount);
                $sheet->setCellValue('R'.($i+2), $deliveryStatus);
                $sheet->setCellValue('S'.($i+2), $deliveryDate);
                $sheet->setCellValue('T'.($i+2), $paymentType);
                $sheet->setCellValue('U'.($i+2), $order->payment_status);
                $sheet->setCellValue('V'.($i+2), @$address->email);
                $sheet->setCellValue('W'.($i+2),  $peercode); 
                $sheet->setCellValue('X'.($i+2),  $phone); 
                $sheet->setCellValue('Y'.($i+2),  $slot);
                $i++;

            }
       
        }

        $filename = "inhouseorders.xlsx";
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

    public function deliverySchedule($orderId){
        
        $schedule = json_decode(session()->get('delivery_schedule'),true);
        $items = $schedule['items'];
        $currentDateTime = Carbon::now();
            foreach($items as $key => $item){
                
                if($item){
                    $orderSchedule = new SubOrder;
                    $orderSchedule->order_id = $orderId->id;
                    $orderSchedule->delivery_name = $key;
                    $orderSchedule->delivery_type = $schedule['delivery_type'];
                    $orderSchedule->delivery_date = date('Y-m-d',strtotime($schedule['delivery_date_'.$key]));
                    $orderSchedule->delivery_time = $schedule['delivery_slot_'.$key];
                    $orderSchedule->status = 1;
                    $orderSchedule->created_at = $orderId->created_at;
                    $orderSchedule->updated_at = $orderId->updated_at;
                    $orderSchedule->save();
                }
                
            }
   // return true;
    
}

public function admin_recurring_orders(Request $request)
    {     
        $id = Auth()->user()->id;
        if(!empty($request->sorting_hub_id)){
            $id = $request->sorting_hub_id;
        }
        $pincodes = [];
        $district = 0;
        if(!empty($request->district)){
            // $pincodes = \App\Area::on('mysql2')->where('district_id',$request->district)->pluck('pincode')->toArray();
            $pincodes = \App\Area::on('mysql')->where('district_id',$request->district)->pluck('pincode')->toArray();
            $pincodes = array_unique($pincodes);
            $district = $request->district;
        }
        
        $orderID = $this->orders($id,$pincodes);
        $payment_status = null;
        $delivery_status = null;
        $pay_type = null;
        $sort_search = null;        
        $customer_search = null;
        // $admin_user_id = User::on('mysql2')->where('user_type', 'admin')->first()->id;
        $admin_user_id = User::on('mysql')->where('user_type', 'admin')->first()->id;


        // $orders = DB::connection('mysql2')->table('orders')
        $orders = DB::connection('mysql')->table('orders')
                    ->whereIn('orders.id', $orderID)
                    ->orderBy('orders.id', 'desc')
                    ->select('orders.*')
                    ->where('grand_total', '!=' , 0.00)
                    ->whereNotNull('subscribed_id')
                    ->distinct('code');
                    

        if ($request->payment_type != null){
            $orders = $orders->where('orders.payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('orders.order_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if($request->pay_type!=null){
            $orders = $orders->where('orders.payment_type', $request->pay_type);
            $pay_type = $request->pay_type;
        }

        if ($request->has('search') && !empty($request->search)){
            $sort_search = $request->search;
            $orders = $orders->where(function($query) use($sort_search){
                $query->where('code', '=', $sort_search)
                ->orWhere('shipping_address->name','like', '%'.$sort_search.'%')
                ->orWhere('shipping_address->phone', 'like', '%'.$sort_search.'%');
            });

        }
        
        if($request->dateRangeStart != null && $request->dateRangeEnd != null && empty($request->search) && empty($request->customersearch)){

            $newStartDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->dateRangeStart)->toDateTimeString(); 

            $start_time = date('Y-m-d', strtotime($newStartDate)); 

            $end_time = date('Y-m-d', strtotime($request->dateRangeEnd));

            $currentTime = time();

            $startTime = strtotime($start_time);

            $endTime = strtotime($end_time); 

            $days_between = ceil(abs($endTime - $startTime) / 86400);

            if($start_time == $end_time){

                $endDayFromCurrentDate = 0;

                $startDayFromCurrentDate = 0;  

            }         

            else{

                $startDayFromCurrentDate = ceil(abs($startTime - $currentTime) / 86400) -1;

                $endDayFromCurrentDate = null;

            }

            if($request->dateRangeEnd != date('d-m-Y')){

                $endDayFromCurrentDate = ceil(abs($endTime - $currentTime) / 86400) -1;

            }
             $orders = $orders->whereDate('orders.created_at', '>=', date('Y-m-d', strtotime($request->dateRangeStart)))->whereDate('orders.created_at', '<=', date('Y-m-d', strtotime($request->dateRangeEnd)));
            //print_r($currentTime);

        }else{

            $startDayFromCurrentDate = null;

            $endDayFromCurrentDate = null;

            $days_between = 89;

            $dt = \Carbon\Carbon::now();

            $dt->toDateTimeString();  

            if(empty($request->search)){

               // $orders = $orders->whereDate('orders.updated_at', '>', $dt->subDays($days_between)->format('Y-m-d'))->orwhereDate('orders.created_at', '>', $dt->subDays($days_between)->format('Y-m-d'));

            }


        }
        //$orders = $orders->toSql();
        // echo '<pre>';
        // print_r($orders); die;
         $orders = $orders->paginate(25);
        
        
        // $sorting_hubs = \App\ShortingHub::on('mysql2')->where('status',1)->select('id','user_id')->get();
        $sorting_hubs = \App\ShortingHub::on('mysql')->where('status',1)->select('id','user_id')->get();
        $sorting_hub_id = $request->sorting_hub_id;
        return view('orders.recurring', compact('orders','payment_status','delivery_status', 'admin_user_id', 'pay_type', 'days_between', 'endDayFromCurrentDate', 'startDayFromCurrentDate','sort_search','sorting_hubs','sorting_hub_id','district'));
    }

    //recurring order export by date
    public function orders_recurring_export(){

        ini_set('memory_limit','1024M');
        set_time_limit(0); //You can use 0 to remove limits

        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];

        // dd($from);
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

        $pincodes = [];
        $district = 0;
        if(!empty($_GET['district'])){
            $pincodes = \App\Area::where('district_id',$_GET['district'])->pluck('pincode')->toArray();
            $pincodes = array_unique($pincodes);
            $district = $_GET['district'];
        }
        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $sorting_hub_id = $sorting_hub_id;
            $sorting_hub = ShortingHub::where('user_id', $sorting_hub_id)->first();
            $result = json_decode($sorting_hub['area_pincodes']);
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        if(isset($result)){
            if(!empty($_GET['district'])){
                $orders = Order::whereIn('shipping_pin_code', $pincodes)->where('log',0)->whereNotNull('subscribed_id');
            }else{
                $orders = Order::whereIn('shipping_pin_code', $result)->where('log',0)->whereNotNull('subscribed_id');
            }    
            
            if(isset($from)){
                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                }

                if($from != $to){
                    $orders = $orders->whereBetween('created_at', [$from, $to]);
                }else{
                    $orders = $orders->whereDate('created_at',$from);
                }
            }
            if(isset($deliveryStatus) && $deliveryStatus != NULL){
                $orders = $orders->where('order_status', $deliveryStatus);
            }

            if(isset($payStatus) && $payStatus != NULL){
                $orders = $orders->where('payment_type', $payStatus);
            }

            if(isset($paymentStatus) && $paymentStatus != NULL){
                $orders = $orders->where('payment_status', $paymentStatus);
            }

            $orders = $orders->orderBy('created_at','desc')->get();
            
        }else{

            if(isset($from)){

                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                }

                // if($from != $to){
                //     $orders = Order::whereBetween('created_at', [$from, $to]);
                // }else{
                //     $orders = Order::whereDate('created_at',$from);
                // }

                if($from != $to){
                    // dd('f');
                      if(!empty($_GET['district'])){
                        $orders = Order::whereBetween('created_at', [$from, $to])->whereIn('shipping_pin_code', $pincodes);
                      }else{
                        $orders = Order::whereBetween('created_at', [$from, $to]);
                      }
                }else{
                     // dd('g');
                      if(!empty($_GET['district'])){
                        $orders = Order::whereDate('created_at',$from)->whereIn('shipping_pin_code', $pincodes);
                      }else{
                        // DB::enableQueryLog();
                       $orders = Order::whereDate('created_at',$from);
                        // dd(DB::getQueryLog());

                      }
                }

                if(isset($deliveryStatus) && $deliveryStatus != NULL){
                    $orders = $orders->where('order_status', $deliveryStatus);
                }

                if(isset($payStatus) && $payStatus != NULL){
                    $orders = $orders->where('payment_type', $payStatus);
                }

                if(isset($paymentStatus) && $paymentStatus != NULL){
                    $orders = $orders->where('payment_status', $paymentStatus);
                }
                // DB::enableQueryLog();
                $orders = $orders->where('log',0)->whereNotNull('subscribed_id')->orderBy('created_at','desc')->get();
                // dd(DB::getQueryLog());
            }else{
                $orders = Order::whereNotNull('subscribed_id')->where('log',0)->all();
            }
            
        }
        // echo '<pre>';
        // print_r($orders);
        // die;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        ini_set('max_execution_time', -1);
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Order Code');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Num. of Products');
        $sheet->setCellValue('E1', 'Customer');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Pin Code');
        $sheet->setCellValue('H1', 'Phone Number');
        $sheet->setCellValue('I1', 'Sorting HUB');
        $sheet->setCellValue('J1', 'Payment Mode');
        $sheet->setCellValue('K1', 'Total Amount');
        $sheet->setCellValue('L1', 'Delivery Status');
        $sheet->setCellValue('M1', 'Delivery Date');
        $sheet->setCellValue('N1', 'Payment Method');
        $sheet->setCellValue('O1', 'Payment Status');
        $sheet->setCellValue('P1', 'Email');
        $sheet->setCellValue('Q1', 'Peer Code');
        $sheet->setCellValue('R1', 'Delivery Boy');
        $sheet->setCellValue('S1', 'Delivery Slot');
        $sheet->setCellValue('T1', 'Delivery Time');

        $i = 0;
        foreach($orders as $key => $order)
        {
            
        $date = date("d/m/Y h:i:s A", $order->date);

        $numProduct = $order->orderDetails->where('order_id', $order->id)->sum('quantity');
        
        $delivery_peercode = ReferalUsage::where('order_id',$order->id)->first('referal_code');
        
        if(!empty($delivery_peercode)){
            $peercode = $delivery_peercode->referal_code;
        }else{
            $peercode = 'NA';
        }
        

        $address = json_decode($order->shipping_address);
        $phone = "";

        if($order->user != null){
            $customer = $order->user->name;
            $phone = @$address->phone;
        }else{
            if(!empty($address->name) && !empty($address->phone)){
                $customer = 'Guest-'.$address->name.''.$address->phone;
                $phone = $address->phone;
            }else{
                $customer = 'Guest';
                $phone = '';
            }
        }

        $customer_detail = $customer;
        
        $getAssignedBoy = AssignOrder::where('order_id',$order->id)->first('delivery_boy_id');

        if($getAssignedBoy != NULL){
            $deliveryBoy = DeliveryBoy::where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
            $deliveryBoyName = User::where('id',$deliveryBoy['user_id'])->first('name');
            $deliveryBoyName = $deliveryBoyName['name'];
        }else{
            $deliveryBoyName = ' ';
        }
        
        $sortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
            if(!empty($sortingHub)){
              $sortingHub = $sortingHub->user->name;

            }else{
                $sortingHub = "Not Available";
            }


        if($order->wallet_amount == 0){
            $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('price') + $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('shipping_cost') - $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('peer_discount');
        }else{
            $total_amount = $order->orderDetails->where('order_id', $order->id)->sum('price') + $order->orderDetails->where('order_id', $order->id)->sum('shipping_cost'); 
        }

        if($order->referal_discount > 0){
              $referral = $order->referal_discount;
              $total_discount = $order->orderDetails->where('order_id', $order->id)->sum('peer_discount');
        }
        if($order->wallet_amount > 0){
            $wallet = $order->wallet_amount;
            if(!empty($total_discount)){
                $total_amount = $total_amount - $total_discount;
            }else{
                $total_amount = $total_amount;
            }
            
        }

        if($order->payment_type=='wallet'){
            $total_amount = $order->wallet_amount;
        }
         
        $amount = single_price($total_amount);
        
        $deliveryStatus = ucfirst(str_replace('_', ' ', $order->order_status));

        if($deliveryStatus == 'pending'){
            $deliveryDate = '';
        }else{
            $deliveryDate = date('d/m/Y H:i:s', strtotime($order->updated_at));
        }
        
        $paymentType = ucfirst(str_replace('_', ' ', $order->payment_type));

        if(!empty($address->address)){
            $user_address = $address->address;
        }else{
            $user_address = "";
        }

        $slot = array(); 
        $d_slottime = array();
        foreach($order->sub_orders as $key => $value)
        {
            if(($value['delivery_name']==null) || ($value['delivery_name']=='')){
                $d_name = 'NA';
            }else{
                $d_name = strtoupper($value['delivery_name']);
            }

            if(($value['delivery_type']==null) || ($value['delivery_type']=='')){
                $d_type = 'NA';
            }else{
                $d_type = strtoupper($value['delivery_type']);
            }

            if(($value['delivery_date']==null) || ($value['delivery_date']=='')){
                $d_date = 'NA';
            }else{
                $d_date = date('d M, Y',strtotime($value['delivery_date']));
            }

            if(($value['delivery_time']==null) || ($value['delivery_time']=='')){
                $d_time = 'NA';
            }else{
                $d_time = strtoupper($value['delivery_time']);
            }

           array_push($slot, $d_name.'-'.$d_type); 
           array_push($d_slottime, $d_date.' '.$d_time);         
        }
        $slot = implode(',',$slot);
        $d_slottime = implode(',',$d_slottime);

        $total_shipping = OrderDetail::where('order_id', $order->id)->where('delivery_status','!=','return')->sum('shipping_cost');
            
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $order->code);
                $sheet->setCellValue('C'.($i+2), $date);
                $sheet->setCellValue('D'.($i+2), $numProduct);
                $sheet->setCellValue('E'.($i+2), $customer_detail);
                $sheet->setCellValue('F'.($i+2), $user_address);
                $sheet->setCellValue('G'.($i+2), $order->shipping_pin_code);
                $sheet->setCellValue('H'.($i+2),  $phone); 
                $sheet->setCellValue('I'.($i+2), $sortingHub);
                $sheet->setCellValue('J'.($i+2), $order->payment_type);
                $sheet->setCellValue('K'.($i+2), $amount);
                $sheet->setCellValue('L'.($i+2), $deliveryStatus);
                $sheet->setCellValue('M'.($i+2), $deliveryDate);
                $sheet->setCellValue('N'.($i+2), $paymentType);
                $sheet->setCellValue('O'.($i+2), $order->payment_status);
                $sheet->setCellValue('P'.($i+2), @$address->email);
                $sheet->setCellValue('Q'.($i+2),  $peercode); 
                $sheet->setCellValue('R'.($i+2),  $deliveryBoyName);
                $sheet->setCellValue('S'.($i+2),  @$slot); 
                $sheet->setCellValue('T'.($i+2),  @$d_slottime);               
                $i++;
        }

        $filename = "inhouseorders.xlsx";
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }


    public function admin_recurring_refunds(Request $request)
    {   
        $sort_search = '';  
        // $orders = DB::connection('mysql2')->table('subscribed_orders')
        $orders = DB::connection('mysql')->table('subscribed_orders')
                        ->orderBy('subscribed_orders.id', 'desc')
                        ->join('products', 'subscribed_orders.product_id', '=', 'products.id')
                        ->join('product_stocks', 'subscribed_orders.product_id', '=', 'product_stocks.product_id')
                        ->join('refund_requests', 'subscribed_orders.id', '=', 'refund_requests.subscribed_id')
                        ->whereNotNull('refund_requests.subscribed_id')
                        ->select('subscribed_orders.*', 'products.slug', 'products.name', 'products.thumbnail_img', 'product_stocks.sku', 'refund_requests.refund_amount', 'refund_requests.subscribed_id as r_id', 'refund_requests.refund_status')
                        ->paginate(50);
        // dd($orders);                
        return view('orders.recurring_refunds', compact('orders','sort_search'));
    }
      
    public function transfer_amount($id)
    {

        $orderCancel = RefundRequest::where('subscribed_id', $id)->update([
                        'refund_status' => 1
                    ]);
        $usersid = RefundRequest::where('subscribed_id', $id)->select('user_id','refund_amount')->first();
        $users_data = User::where('id', $usersid->user_id)->select('id','balance')->first();
        $balance = $users_data->balance + $usersid->refund_amount;

        $wallet = new Wallet;
        $wallet->user_id = $users_data->id;
        $wallet->amount = $usersid->refund_amount;
        // $wallet->payment_method = 'wallet';
        $wallet->payment_method = 'refund';
        $wallet->subscribed_id = $id;
        $wallet->tr_type = 'credit';
        $wallet->save();

        $userdata = User::where('id', $usersid->user_id)->update([
                        'balance' => $balance
                    ]);
        flash(translate('Amount Refunded successfully'))->success();
        return back();
    } 
    
    public function html_data_table(REQUEST $request){
        $sort_search = null;
        $start_date = null;
        $end_date = null;
        $user = Auth()->user();
        $orders = DB::table('final_orders');
        if($user->user_type == 'staff'){
            $orders = $orders->where('sortinghub_id',$user->id);
        }
        if ($request->has('search') && !empty($request->search)){
            
            $sort_search = $request->search;
            $orders = $orders->where(function($query) use($sort_search){
                $query->where('order_code', 'like', '%'.$sort_search.'%')
                ->orWhere('shipping_address->name','like', '%'.$sort_search.'%')
                ->orWhere('shipping_address->phone', 'like', '%'.$sort_search.'%');
            });
            
        }

        if ($request->has('start_date') && $request->has('end_date')){
            if(!empty($request->start_date) && !empty($request->end_date)){
            $start_date = $request->start_date; 
            $end_date = $request->end_date;
            $orders = $orders->whereDate('order_date', '>=', $start_date)->whereDate('order_date', '<=', $end_date);
            }
        }
        $orders = $orders->orderBy('order_id', 'desc')->paginate(25);

        // dd($orders);
        return view('htmltable',compact('orders','sort_search','start_date','end_date'));
    }

    public function showhtml($id)
    {
        
        $order = Order::findOrFail(decrypt($id));
        $order->viewed = 1;
        $order->save();
         
        return view('orders.showhtml', compact('order'));
    }

    public function orders_productexport_final(){
        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

        if(empty($from) ||empty($to)){
            flash(translate('Please select start date and end date.'))->error();
            return back(); 
        } 
        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $result = true;
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        // $orders = DB::connection('mysql2')->table('final_orders')
        $orders = DB::connection('mysql')->table('final_orders')
                          ->leftjoin('order_details','final_orders.order_id','=','order_details.order_id')
                          ->leftjoin('products','products.id','=','order_details.product_id')
                          ->leftjoin('users','users.id','=','final_orders.sortinghub_id');
                          $from = date('Y-m-d',strtotime($from));
                          $to = date('Y-m-d',strtotime($to.' +1 day'));

        if(isset($result)){
            $orders = $orders->where('sortinghub_id', $sorting_hub_id);
            
            if(isset($from)){
                // if($from == $to){
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to));
                // }else{
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to.' +1 day'));
                // }

                if($from != $to){
                    $orders = $orders->where('final_orders.order_date','>=', $from)->where('final_orders.order_date','<=', $to);
                }else{
                    $orders = $orders->whereDate('final_orders.order_date',$from);
                }
            }
            
        }else{

            if(isset($from)){

                if($from != $to){
                    $orders = $orders->where('final_orders.order_date','>=', $from)->where('final_orders.order_date','<=', $to);
                }else{
                    $orders = $orders->whereDate('final_orders.order_date',$from);
                }
            }
            
        }
        $orders = $orders->orderBy('final_orders.order_date','desc')
                          ->select('final_orders.order_code','final_orders.order_code','final_orders.order_date','final_orders.order_date','order_details.quantity as qty','final_orders.no_of_items','final_orders.shipping_address','final_orders.shipping_address','final_orders.pincode','products.hsn_code','products.name as product_name','users.name as shorting_hub','products.tax','order_details.peer_discount','order_details.price','order_details.shipping_cost','final_orders.payment_method','final_orders.payment_status','final_orders.grand_total','final_orders.grand_total','order_details.delivery_status','order_details.updated_at','final_orders.referal_code')
                          ->get();
        


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Order Code');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Num. of Products');
        $sheet->setCellValue('E1', 'Customer');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Pin Code');
        $sheet->setCellValue('H1', 'HSN Code');
        $sheet->setCellValue('I1', 'Sorting HUB');
        $sheet->setCellValue('J1', 'Product  Name');
        $sheet->setCellValue('K1', 'Qty');
        $sheet->setCellValue('L1', 'GST Rate');
        $sheet->setCellValue('N1', 'Discount Price');
        $sheet->setCellValue('M1', 'Price');
        $sheet->setCellValue('O1', 'Shipping Cost');
        $sheet->setCellValue('P1', 'Payment Mode');
        $sheet->setCellValue('Q1', 'Amount');
        $sheet->setCellValue('R1', 'Delivery Status');
        $sheet->setCellValue('S1', 'Delivery Date');
        $sheet->setCellValue('T1', 'Payment Method');
        $sheet->setCellValue('U1', 'Payment Status');
        $sheet->setCellValue('V1', 'Email');
        $sheet->setCellValue('W1', 'Peer Code');
        $sheet->setCellValue('X1', 'Phone Number');
        $i = 0;

            foreach($orders as $k=>$v)
            {
                $date =  $v->order_date;
                $customer_detail = json_decode($v->shipping_address);
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $v->order_code);
                $sheet->setCellValue('C'.($i+2), $date);
                $sheet->setCellValue('D'.($i+2), $v->no_of_items);
                $sheet->setCellValue('E'.($i+2), $customer_detail->name);
                $sheet->setCellValue('F'.($i+2), $customer_detail->address);
                $sheet->setCellValue('G'.($i+2), $v->pincode);
                $sheet->setCellValue('H'.($i+2), $v->hsn_code);
                $sheet->setCellValue('I'.($i+2), $v->shorting_hub);
                $sheet->setCellValue('J'.($i+2), $v->product_name);
                $sheet->setCellValue('K'.($i+2), $v->qty);
                $sheet->setCellValue('L'.($i+2), $v->tax);
                $sheet->setCellValue('M'.($i+2), $v->price);
                $sheet->setCellValue('N'.($i+2), $v->peer_discount);
                $sheet->setCellValue('O'.($i+2), $v->shipping_cost);
                $sheet->setCellValue('P'.($i+2), $v->payment_method);
                $sheet->setCellValue('Q'.($i+2), $v->grand_total);
                $sheet->setCellValue('R'.($i+2), $v->delivery_status);
                $sheet->setCellValue('S'.($i+2), $v->updated_at);
                $sheet->setCellValue('T'.($i+2), $v->payment_method);
                $sheet->setCellValue('U'.($i+2), $v->payment_status);
                $sheet->setCellValue('V'.($i+2), $customer_detail->email);
                $sheet->setCellValue('W'.($i+2), $v->referal_code); 
                $sheet->setCellValue('X'.($i+2), $customer_detail->phone); 
                $i++;

            }

        $filename = "inhouseorders.xlsx";
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
         
    }

    public function final_orders_export(){

        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];


        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $sorting_hub_id = $sorting_hub_id;
            $result = true;
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        // $orders = DB::connection('mysql2')->table('final_orders')
        $orders = DB::connection('mysql')->table('final_orders')
                        //    ->leftjoin('orders','orders.id','=','final_orders.order_id')
                        //   ->leftjoin('products','products.id','=','order_details.product_id')
                          ->leftjoin('users','users.id','=','final_orders.sortinghub_id');
        $from = date('Y-m-d',strtotime($from));
        $to = date('Y-m-d',strtotime($to.' +1 day'));

        if(isset($result)){
            $orders = $orders->where('sortinghub_id',$sorting_hub_id);
            if(isset($from)){
                // if($from == $to){
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to));
                // }else{
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to.' +1 day'));
                // }

                if($from != $to){
                    $orders = $orders->whereBetween('final_orders.order_date', [$from, $to]);
                }else{
                    $orders = $orders->whereDate('final_orders.order_date',$from);
                }
            }
            
        }else{

            if(isset($from)){
                $orders = $orders->whereBetween('final_orders.order_date', [$from, $to]);
                // dd(DB::getQueryLog());
            }
            
        }

        $orders = $orders->orderBy('final_orders.order_date','desc')
                          ->select('final_orders.order_code','final_orders.delivery_status','final_orders.order_id','final_orders.order_date','final_orders.order_date','final_orders.no_of_items','final_orders.shipping_address','final_orders.shipping_address','final_orders.pincode','users.name as shorting_hub','final_orders.payment_method','final_orders.payment_status','final_orders.grand_total','final_orders.grand_total','final_orders.referal_code')
                          ->get();

        // echo '<pre>';
        // print_r($orders);
        // die;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Order Code');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Num. of Products');
        $sheet->setCellValue('E1', 'Customer');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Pin Code');
        $sheet->setCellValue('H1', 'Phone Number');
        $sheet->setCellValue('I1', 'Sorting HUB');
        $sheet->setCellValue('J1', 'Payment Mode');
        $sheet->setCellValue('K1', 'Total Amount');
        $sheet->setCellValue('L1', 'Delivery Status');
        $sheet->setCellValue('O1', 'Payment Status');
        $sheet->setCellValue('P1', 'Email');
        $sheet->setCellValue('Q1', 'Peer Code');
        $sheet->setCellValue('R1', 'Delivery Boy');
        // $sheet->setCellValue('S1', 'Delivery Slot');
        // $sheet->setCellValue('T1', 'Delivery Time');

        $i = 0;

        foreach($orders as $key => $order)
        {

               $numProduct = OrderDetail::where('order_id', $order->order_id)->sum('quantity');
               $customer_detail = json_decode($order->shipping_address);
               
               $delivery_boy = AssignOrder::where('order_id',$order->order_id)->first();
               
               if(!empty($delivery_boy)){
                $delivery_boy_details = DeliveryBoy::where('id',$delivery_boy->delivery_boy_id)->first();
                    if(!empty($delivery_boy_details->user_id)){
                        $delivery_boy = User::where('id',$delivery_boy_details->user_id)->first('name');
                        $delivery_boy_name = $delivery_boy['name'];
                    }else{
                        $delivery_boy_name = 'NA';
                    }
                
               }else{
                $delivery_boy_name = 'NA';
               }
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $order->order_code);
                $sheet->setCellValue('C'.($i+2), $order->order_date);
                $sheet->setCellValue('D'.($i+2), $numProduct);
                $sheet->setCellValue('E'.($i+2), $customer_detail->name);
                $sheet->setCellValue('F'.($i+2), $customer_detail->address);
                $sheet->setCellValue('G'.($i+2), $order->pincode);
                $sheet->setCellValue('H'.($i+2), $customer_detail->phone); 
                $sheet->setCellValue('I'.($i+2), $order->shorting_hub);
                $sheet->setCellValue('J'.($i+2), $order->payment_method);
                $sheet->setCellValue('K'.($i+2), $order->grand_total);
                $sheet->setCellValue('L'.($i+2), $order->delivery_status);
                $sheet->setCellValue('O'.($i+2), $order->payment_status);
                $sheet->setCellValue('P'.($i+2), $customer_detail->email);
                $sheet->setCellValue('Q'.($i+2), $order->referal_code); 
                $sheet->setCellValue('R'.($i+2), $delivery_boy_name);
                // $sheet->setCellValue('S'.($i+2),  @$slot); 
                // $sheet->setCellValue('T'.($i+2),  @$d_slottime);               
                $i++;
       
        }

        $filename = "inhouseorders.xlsx";
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

}
