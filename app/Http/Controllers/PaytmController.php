<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\OrderDetail;
use App\BusinessSetting;
use App\Seller;
use Session;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\WalletController;
use PaytmWallet;
use paytm\paytmchecksum\PaytmChecksum;
use Auth;
use Redirect;
use Carbon\Carbon;

class PaytmController extends Controller
{
    public function index(){
        if(Session::has('payment_type')){
            if(Session::get('payment_type') == 'cart_payment'){
                $order = Order::findOrFail(Session::get('order_id'));
                $amount = $order->grand_total;
                $shipping_info = json_decode($order->shipping_address);

            }
            elseif (Session::get('payment_type') == 'wallet_payment') {
               
            }

            $paytmParams = array();

            $paytmParams["body"] = array(
                "requestType"   => "Payment",
                "mid"           => env('PAYTM_MERCHANT_ID'),
                "websiteName"   => "WEBSTAGING",
                "orderId"       => $order->code,
                "callbackUrl"   => route('paytm.callback'),
                "txnAmount"     => array(
                    "value"     => $amount,
                    "currency"  => "INR",
                ),
                "userInfo"      => array(
                    "custId"    => "CUST_001",
                    "custName"    => $shipping_info->name,
                    "custEmail"    => $shipping_info->email,
                    "custPhone"    => $shipping_info->phone,
                ),
            );

            /*
            * Generate checksum by parameters we have in body
            * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
            */
            $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), env('PAYTM_MERCHANT_KEY'));


            $paytmParams["head"] = array(
                "signature"    => $checksum
            );

            $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

            /* for Staging */
           // $url = "https://securegw-stage.paytm.in/theia/api/v1/initiateTransaction?mid=".env('PAYTM_MERCHANT_ID')."&orderId=".$order->code;

            /* for Production */
             $url = "https://securegw.paytm.in/theia/api/v1/initiateTransaction?mid=".env('PAYTM_MERCHANT_ID')."&orderId=".$order->code;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json")); 
            $response = curl_exec($ch);
            $res = json_decode($response);
            return view('paytm.index',compact(['res','order','amount']));
        }
    }

    public function callback(Request $request){
        /* string we need to verify against checksum */  
        $body = ["mid"=>env('PAYTM_MERCHANT_ID'),"orderId"=>$request->ORDERID];

        /* checksum that we need to verify */
        $paytmChecksum = $request->CHECKSUMHASH;

        /**
        * Verify checksum
        * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
        */
        $isVerifySignature = PaytmChecksum::verifySignature($body, env('PAYTM_MERCHANT_KEY'), $paytmChecksum);
        $paytmResponse = json_encode($request->all());
        $order = Order::where('code',$request->ORDERID)->first();
        $order->payment_details = $paytmResponse;
        // dd( $paytmResponse);
        if($request->RESPCODE == '01' && $request->STATUS == 'TXN_SUCCESS'){
            $order->payment_status = 'paid';
            $order->order_status = 'pending';
            if($order->dofo_status == 0){
                $sendMail = new RazorpayController;
                $sendMail->sendOrderMail(Session::get('order_id'));
                SMSonOrderPlaced($order->id);
    
            }
        }else{
            $order->payment_status = 'unpaid';
            $order->order_status = 'pending';
            
        }
        $order->orderDetails()->update(['payment_status'=>$order->payment_status]);
        $order->save();

                       
        
        $checkoutController = new CheckoutController;

        return $checkoutController->checkout_done(Session::get('order_id'), $paytmResponse);


    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function credentials_index()
    {
        return view('paytm.index');
    }

    /**
     * Update the specified resource in .env
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_credentials(Request $request)
    {
        foreach ($request->types as $key => $type) {
                $this->overWriteEnvFile($type, $request[$type]);
        }

        flash("Settings updated successfully")->success();
        return back();
    }

    /**
    *.env file overwrite
    */
    public function overWriteEnvFile($type, $val)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $val = '"'.trim($val).'"';
            if(is_numeric(strpos(file_get_contents($path), $type)) && strpos(file_get_contents($path), $type) >= 0){
                file_put_contents($path, str_replace(
                    $type.'="'.env($type).'"', $type.'='.$val, file_get_contents($path)
                ));
            }
            else{
                file_put_contents($path, file_get_contents($path)."\r\n".$type.'='.$val);
            }
        }
    }


    public function updateTransactionStatus(){
        $getOrders = Order::where('payment_type','paytm')
                    ->whereDate('created_at','>',Carbon::today()->subDays(2))
                    ->orderBy('created_at','asc')
                    ->select('id','code','payment_type','payment_status','created_at')
                    ->get();
        try{

            $payment = [];
                    foreach($getOrders as $key => $value){


                        /* initialize an array */
                        $paytmParams = array();

                        /* body parameters */
                        $paytmParams["body"] = array(

                            /* Find your MID in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys */
                            "mid" => env('PAYTM_MERCHANT_ID'),

                            /* Enter your order id which needs to be check status for */
                            "orderId" => $value->code,
                        );

                        /**
                        * Generate checksum by parameters we have in body
                        * Find your Merchant Key in your Paytm Dashboard at https://dashboard.paytm.com/next/apikeys 
                        */
                        $checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), env('PAYTM_MERCHANT_KEY'));

                        /* head parameters */
                        $paytmParams["head"] = array(

                            /* put generated checksum value here */
                            "signature"	=> $checksum
                        );

                        /* prepare JSON string for request */
                        $post_data = json_encode($paytmParams, JSON_UNESCAPED_SLASHES);

                        /* for Staging */
                        //$url = "https://securegw-stage.paytm.in/v3/order/status";
                        /* for Production */
                         $url = "https://securegw.paytm.in/v3/order/status";

                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));  
                        $response = curl_exec($ch);
                        $resCon = json_decode($response);
                        $payment[] = $resCon;
                        if($resCon->body->resultInfo->resultCode == 01 && $resCon->body->resultInfo->resultStatus == "TXN_SUCCESS"){
                            $value->payment_status = 'paid';
                            

                        }else{
                            $value->payment_status = 'unpaid';
                        }
                        $value->save();
                        OrderDetail::where('order_id',$value->id)->update(['payment_status'=>$value->payment_status]);

            }

        }catch(\Exception $e){

            info('Order Id '.$value->code." Not found");

        }
        //info($payment);
        info("Paytm cron run");
        
    }
}
