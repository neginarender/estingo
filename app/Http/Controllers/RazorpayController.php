<?php



namespace App\Http\Controllers;



use Illuminate\Http\Request;



use Session;

use Redirect;

use App\Order;

use App\Seller;

use Razorpay\Api\Api;

use Illuminate\Support\Facades\Input;

use App\CustomerPackage;

use App\SellerPackage;

use App\Http\Controllers\CustomerPackageController;

use Auth;

use App\LogActivity;

use Config;

use Flash;

use DB;
use PDF;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Traits\WalletTrait;
use Cookie;
use App\Events\OrderPlacedEmail;
use App\Wallet;
use App\WalletLog;
use Carbon\Carbon;
class RazorpayController extends Controller

{
    public $api;
    use WalletTrait;
    public function __construct(){
        $this->api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
    }

    public function payWithRazorpay($request)

    {
        

        if(Session::has('payment_type')){

            if(Session::get('payment_type') == 'cart_payment'){

                $order = Order::findOrFail(Session::get('order_id'));



                $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));

                $orderinfo = $api->order->create(array(

                    'receipt' => $order->code,

                    'amount' => $order->grand_total * 100,

                    'currency' => 'INR'

                    )

                );

                $orderId = $orderinfo['id']; // Get the created Order ID

                

           

                $orderinfo  = $api->order->fetch($orderId);

                $order_detalis = json_encode(array('id' => $orderinfo['id'],

                    'entity' => $orderinfo['entity'],

                    'amount' => $orderinfo['amount'],

                    'currency' => $orderinfo['currency'],

                    'receipt' => $orderinfo['receipt'],

                    'status' => $orderinfo['status'],

                    'attempts' => $orderinfo['attempts']

                ));

                $orderUpdate = Order::where('id', Session::get('order_id'))->update(['order_create' => $order_detalis]);

                $requestParams = json_encode(array('id' => $order->code, 'amount' => $order->grand_total*100, 'currency' => 'INR'));

                LogActivity::addToPayment($order_detalis, $order->id, 'success', 'success', 'post', '', 'razorpay');

                // return view('frontend.razor_wallet.order_payment_Razorpay', compact('order'))->with('orderId', $orderinfo['id']);

                $response = [

                        'receipt' => $order->code,

                        'orderId' => $orderinfo['id'],

                        'razorpayId' => env('RAZOR_KEY'),

                        'amount' => $order->grand_total*100,

                        'name' => json_decode($order->shipping_address)->name,

                        'currency' => 'INR',

                        'email' => json_decode($order->shipping_address)->email,

                        'contactNumber' => json_decode($order->shipping_address)->phone,

                        'address' =>json_decode($order->shipping_address)->address,

                        'description' => 'Order Payment',

                    ];



                return view('frontend.razor_wallet.razor-pay',compact('response'));

                // return view('frontend.razor_wallet.order_payment_Razorpay', compact('order'));

            }

            elseif (Session::get('payment_type') == 'wallet_payment') {
                // dd(env('RAZOR_KEY'));

                $receiptId = rand(100000, 999999);
                $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));

                 $order = $api->order->create(array(
                    'receipt' => $receiptId,
                    'amount' => $request->all()['amount'] * 100,
                    'currency' => 'INR'
                    )
                );
                 // dd($order->id);

                  $response = [
                    'orderId' => $order['id'],
                    'razorpayId' => env('RAZOR_KEY'),
                    'amount' => $request->all()['amount'] * 100,
                    'name' => Auth::user()->name,
                    'currency' => 'INR',
                    'email' => Auth::user()->email,
                    'contactNumber' => Auth::user()->phone,
                    'address' => 'Testing address',
                    'description' => 'Testing description',
                    'receipt' => $receiptId,
                ];

                // dd($response);
                //insert data in wallet
                $wallet = new WalletLog;
                $wallet->user_id = Auth::user()->id;
                $wallet->amount = $request->all()['amount'];
                $wallet->payment_method = 'wallet';
                $wallet->order_id = $receiptId;
                // $wallet->wallet_order_id = $order->id;
                $wallet->tr_type = 'created';
                $wallet->save();

                
                Session::put('ord_id', $receiptId);
                return view('frontend.razor_wallet.wallet_payment_Razorpay',compact('response'));
                // return view('payment-page',compact('response'));
            }

            elseif (Session::get('payment_type') == 'customer_package_payment') {

                return view('frontend.razor_wallet.customer_package_payment_Razorpay');

            }

            elseif (Session::get('payment_type') == 'seller_package_payment') {

                return view('frontend.razor_wallet.seller_package_payment_Razorpay');

            }

        }



    }



    public function payment(Request $request)

    {



        $signatureStatus = $this->SignatureVerify(

            $request->all()['rzp_signature'],

            $request->all()['rzp_paymentid'],

            $request->all()['rzp_orderid']

        );


        // dd(Session::get('order_id'));
        if($signatureStatus == true)

        {            

            if (Session::get('payment_type') == 'wallet_payment') {
                $order = Wallet::where('order_id', Session::get('order_id'));
            }else{
                 $order = Order::findOrFail(Session::get('order_id'));
            }    
            //Input items of form
            // dd($order);
            $input = $request->all();

            $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));

            $payment = $api->payment->fetch($input['rzp_paymentid']);           

            if(count($input)  && !empty($input['rzp_paymentid'])) {

                $payment_detalis = json_encode(array(

                                                    'id' => $payment['id'],

                                                    'order_id' => $payment['order_id'],

                                                    'method' => $payment['method'], 

                                                    'status' => $payment['status'], 

                                                    'email' => $payment['email'],

                                                    'contact' => $payment['contact'],

                                                    'amount' => $payment['amount'],

                                                    'currency' => $payment['currency']));

                 $requestParams = json_encode($request);   

                 // dd($requestParams);

                LogActivity::addToPayment($requestParams, Session::get('order_id'), $payment_detalis, 'success', 'get','', 'razorpay','');

           

            }



            // Do something here for store payment details in database...

            if(Session::has('payment_type')){

                if(Session::get('payment_type') == 'cart_payment'){ 
                   
                    if($order->dofo_status == 0){
                       // $this->sendOrderMail(Session::get('order_id'));
                        //event(new OrderPlacedEmail(Session::get('order_id')));
                        // SMSonOrderPlaced($order->id);
                        SMSonOrderPlacedWithSlot($order->id);

                    }               
                    
                    $checkoutController = new CheckoutController;

                    return $checkoutController->checkout_done(Session::get('order_id'), $payment_detalis);

                }

            }

            }else{

                flash("something went wrong!!")->error();

            return redirect('checkout/payment_select');

        }if(Session::has('payment_type')){

            if (Session::get('payment_type') == 'wallet_payment') {

                $walletController = new WalletController;

                return $walletController->wallet_payment_done(Session::get('payment_data'), $payment_detalis);

            }

            elseif (Session::get('payment_type') == 'customer_package_payment') {

                $customer_package_controller = new CustomerPackageController;

                return $customer_package_controller->purchase_payment_done(Session::get('payment_data'), $payment);

            }

            elseif (Session::get('payment_type') == 'seller_package_payment') {

                $seller_package_controller = new SellerPackageController;

                return $seller_package_controller->purchase_payment_done(Session::get('payment_data'), $payment);

            }

        }

       

    }

    public function paymentFail(Request $request){
        
            $input = $request->all();
            //dd($input);
                      
            $banned = 0;
            $reason = "Transaction is declined by payment gateway";
            // clear cart 
            if(count($input)  && $input['rzp_fail_paymentid'] != "undefined" && !empty($input['rzp_fail_paymentid'])) {
                $payment = $this->api->payment->fetch($input['rzp_fail_paymentid']); 
                $reason = $input['rzp_fail_desc'];
                if($input['rzp_fail_reason']=="authentication_failed" || $input['rzp_fail_reason']=="authorisation_declined_by_psp" || $input['rzp_fail_reason']=="payment_risk_check_failed"){
                    $payment_detalis = json_encode(array(

                                                    'id' => $payment['id'],

                                                    'order_id' => $payment['order_id'],

                                                    'method' => $payment['method'], 

                                                    'status' => $payment['status'], 

                                                    'email' => $payment['email'],

                                                    'contact' => $payment['contact'],

                                                    'amount' => $payment['amount'],

                                                    'currency' => $payment['currency']));
                   
                 $requestParams = json_encode($request->all()); 
                 $order = \App\PaymentLog::where('order_id',Session::get('order_id'))->where('status','fail')->first();  
                 if(is_null($order)){
                    LogActivity::addToPayment($requestParams, Session::get('order_id'), $payment_detalis, 'fail', 'post','', 'razorpay');
                 }   

                if(Auth::check()){
                    $user = Auth::user()->id;
                    if($user){
                        $activity = \App\PaymentLog::where('user_id',$user)->where('status','fail')->whereDate('created_at','=',date('Y-m-d'))->count();
                        if($activity>=2){
                            // banned this customer 
                            \App\User::where('id',$user)->update(['banned'=>1]);
                            \Auth::logout();
                            Cookie::queue(Cookie::forget('auth_id'));
                            $banned = 1;
                            setcookie('cart', collect([]),time()+60*60*24*30,'/');
                            Session::put('cart', collect([]));
                        }
                    }
                }
            }

            }

            return view('frontend.razor_wallet.razorpay-payment-fail',compact('banned','reason'));

    }

    public function paymentFailView(){
        return view('frontend.razor_wallet.razorpay-payment-fail');
    }

      // In this function we return boolean if signature is correct

    private function SignatureVerify($_signature,$_paymentId,$_orderId)

    {

        try

        {

            $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));

            //$api = new Api("rzp_test_VsNGSNhB9SZ4rI", "6LkqXthu4LpjDDRuRjgW0nDf");

            $attributes  = array('razorpay_signature'  => $_signature,  'razorpay_payment_id'  => $_paymentId ,  'razorpay_order_id' => $_orderId);

            $order  = $api->utility->verifyPaymentSignature($attributes);

            return true;

        }

        catch(\Exception $e)

        {

            return false;

        }

    }

     //razorpay return payment function
    public function returnOrderPayRefundRazorpay($id){
        $order = Order::findOrFail($id);
        $param =  (object)json_decode($order->payment_details, true);
        $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
        //$order_detail = OrderDetail::where('id', $orderID)->where('order_id', $id)->first();
        $refund_amount = $order->grand_total * 100;
        $refund_details = null;
        try {
            $refund = $api->refund->create(array('payment_id' =>  $param->id, 'amount'=> $refund_amount));           
            $refund_details = json_encode(array(
                            'id' => $refund['id'], 
                            'entity' => $refund['entity'], 
                            'amount' => $refund['amount'],  
                            'currency' => $refund['currency'], 
                            'payment_id' => $refund['payment_id'], 
                            'receipt' => $refund['receipt'],  
                            'status' => $refund['status'],  
                            'speed_processed' => $refund['speed_processed'],  
                            'speed_requested' => $refund['speed_requested'], 
                            'created_at' => $refund['created_at'] 
                    ));    
                return $refund_details;
            } catch (\Exception $e) {
                return  $e->getMessage();
                \Session::put('error',$e->getMessage());
                return 400;
            } 
    }

    public function sendOrderMail($order_id)
    {
        $order = Order::where('id',$order_id)->first();
        $orderdetail = \App\OrderDetail::where('order_id',$order->id)->get();
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
                    $array = array(); 
                    $array['view'] = 'emails.invoice';
                    $array['subject'] = 'Rozana Order Placed - '.$order->code;
                    $array['from'] = env('mail_from_address');
                    $array['content'] = translate('Dear Customer, A new order has been placed. You can check your order details in the invoice attached below. Please reach out to us in the case of any queries on customercare@rozana.in');
                    $array['file'] = 'public/invoices/Order#'.$order->code.'.pdf';
                    $array['file_name'] = 'Order#'.$order->code.'.pdf';
            
                    if(env('MAIL_USERNAME') != null){
                        try {
                            Mail::to(json_decode($order->shipping_address)->email)->queue(new InvoiceEmailManager($array));
                            $array['subject'] = "New Order has been placed.";
                            $array['content'] = translate('Dear Admin, A new order has been placed. You can check  order details in the invoice attached below.');
                            //Mail::to(env("ADMIN_MAIL"))->queue(new InvoiceEmailManager($array));
                            return true;
                        } catch (Exception $e) {
                            echo $e->getMessage();
            
                        }
                    }

    }

    public function checkRazorpayOrderPayment(){
        // get unpaid razorpay order and log 1 
        //$startDate = "2021-11-15";
         $date = Carbon::today()->subDays(7);
         $orders = Order::where(['payment_type'=>'razorpay','payment_status'=>'unpaid'])->whereNotNull('order_create')->where('created_at','>=',$date)->orderBy('created_at','desc')->get();
         //dd($orders);
         $payment = [];
         //dd($orders);
         foreach($orders as $key => $order){
             
             if(!is_null($order->order_create)){
                 $razorpay_order_id = json_decode($order->order_create)->id;
                 try{
                     $check_payment = $this->api->order->fetch($razorpay_order_id)->payments();
                     if($check_payment->count>0){
                         //$payment_details = $check_payment->items[0];
                         foreach( $check_payment->items as $kk => $payment_details){
                         if($payment_details->status=="captured" || $payment_details->status=="authorize"){
                            $response = [
                                'id'=>$payment_details->id,
                                'order_id'=>$payment_details->order_id,
                                'method'=>$payment_details->method,
                                'status'=>$payment_details->status,
                                'email'=>$payment_details->email,
                                'contact'=>$payment_details->contact,
                                'amount'=>$payment_details->amount,
                                'currency'=>$payment_details->currency,
                                'order_code'=>$order->code
                            ];
                            $payment[] = $response;
                            //Update order payment status
                            \App\Order::where('id',$order->id)->update(['payment_status'=>'paid','log'=>0,'payment_details'=>json_encode($response)]);
                            \App\OrderDetail::where('order_id',$order->id)->update(['payment_status'=>'paid']);
                            //Update referal Commission
                            $ReferalUsage = \App\ReferalUsage::where('order_id', $order->id)->first();
                            if(!empty($ReferalUsage)){
    
                                $OrderReferalCommision = new \App\OrderReferalCommision;
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
                        }
                         
                     }
                       
                 } catch(\Exception $e){
                     info('Order Id '.$razorpay_order_id." Not found");
                 }
                
                
                    
             }
 
         }
         info($payment);
         info("Payment cron run");
     }

}

