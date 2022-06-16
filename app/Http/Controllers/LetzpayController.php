<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use Session;
use Cookie;
use Auth;
use Config;
use App\Product;
use App\ProductStock;
use App\OrderDetail;
use App\User;
use Flash;
use App\BusinessSetting;
use App\LogActivity;
use App\OrderLog;
use App\OrderDetailLog;

class LetzpayController extends Controller
{
	public $letzpay;
    public function __construct()
    {
       $this->letzpay =  Config::get('app.letzpay');
    }

    public function payWithLetzpay($request){
    	Cookie::queue(Cookie::make('data', json_encode(session()->all()), 3600));
 
		if(Session::has('payment_type')){
			$order = OrderLog::findOrFail(Session::get('order_id'));
			Session::get('order_id');
			$request->session()->save();
			$jsonResult = json_decode($order->shipping_address, true);
			$PAY_ID = $this->letzpay[0];
			$SALT_ID = $this->letzpay[1];
			$url = $this->letzpay[2];
			//$sku = $this->productsSku($order->id);
			$letz_data = array();
			$letz_data['PAY_ID'] = $PAY_ID;
			$letz_data['ORDER_ID'] = $order->code;
			$letz_data['TXNTYPE'] = "SALE";
			$letz_data['AMOUNT'] = $order->grand_total*100;
			$letz_data['CUST_NAME'] = $jsonResult["name"];
			$letz_data['CUST_PHONE'] = $jsonResult["phone"];
			$letz_data['CUST_EMAIL'] = $jsonResult["email"];
			$letz_data['PROD_DESC'] = "sku";
			$letz_data['CURRENCY_CODE'] = '356';
			$letz_data['RETURN_URL'] = url('letzpay/payment/response');

			$hash = "";
			$paramList = array();
			ksort($letz_data);
			$build = http_build_query($letz_data);
			$builddecode = urldecode($build);
			$buildReplace = str_replace('&', '~', $builddecode);
			$buildAppend = $buildReplace.$SALT_ID;
			$hashgenrated = hash('sha256', $buildAppend);
			$hashCase = strtoupper($hashgenrated);
			
			$data['hashdata'] = $hashCase;
			$letz_data['HASH'] = $hashCase;
			$data['paramList'] = $letz_data;
			$requestParams = json_encode($letz_data);
			LogActivity::addToPayment($requestParams, $order->id, 'success', 'success', 'post',$hashCase, 'letzpay_payment');
			return view('frontend.letzpay.letzpay_form')->with('result', $data)->with('url', $url);
}

}
  
    public function LetsPayResponse(Request $request)
    {	
    	
    	if(!empty(Session::get('order_id'))){
    		$orderID = Session::get('order_id');
		}else{
			$orderInfo = OrderLog::where('code', $_POST["ORDER_ID"])->first();
			if(!empty($orderInfo)){
				if(!empty($orderInfo->user_id))
				{
					$userInfo = User::where('id', $orderInfo->user_id)->first();
				Auth::login($userInfo);
				\Session::flash('success','Re-login');
				}
				
				$orderID =  $orderInfo->id;
	 		}
		}

    	$requestParams = json_encode($request->all());	
    	$response = json_encode($_POST);	
		$order_id = $this->store_order($orderID);
		//LogActivity::addToPayment($requestParams, $orderID, $response, 'success', 'get',$_POST["HASH"], 'letzpay_payment');
    	$payment_detalis = null;
    	try {

    		$payment_detalis = $_POST;
			$hash = $this->genrateResponseHash($_POST);

			if($hash == $_POST['HASH']){
				
				if($_POST['RESPONSE_CODE'] == '300'){
					\Session::put('error','Something Went Wrong!! Please Try again');	
					flash(translate('Something Went Wrong!! Please Try again'))->warning();			
					$response = json_encode($_POST);
				 	$requestParams = json_encode($request->all());		
					LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
					OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
					if(!empty(Session::get('order_id'))){
						return redirect('/checkout/payment_select');	
					}else{
						return redirect('/');	
					}
						
				}elseif($_POST['RESPONSE_CODE'] == '900'){
					\Session::put('error','Something Went Wrong!! Please Try again');
					flash(translate('Something Went Wrong!! Please Try again'))->warning();			
					$response = json_encode($_POST);
		 			$requestParams = json_encode($request->all());		
					LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
					OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
					if(!empty(Session::get('order_id'))){
						return redirect('/checkout/payment_select');	
					}else{
						return redirect('/');	
					}									
				}elseif($_POST['RESPONSE_CODE'] == '999'){
					\Session::put('error','Something Went Wrong!! Please Try again');
					$response = json_encode($_POST);
		 			$requestParams = json_encode($request->all());		
					LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
					OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
					flash(translate('Something Went Wrong!! Please Try again'))->warning();			
					if(!empty(Session::get('order_id'))){
						return redirect('/checkout/payment_select');	
					}else{
						return redirect('/');	
					}						
				}elseif($_POST['RESPONSE_CODE'] == '000' && $_POST['RESPONSE_MESSAGE'] == 'SUCCESS' && $_POST['STATUS'] == 'Captured'){	
					
					$checkoutController = new CheckoutController;
					return $checkoutController->checkout_done($order_id, $payment_detalis);						   						
				}elseif($_POST['RESPONSE_CODE'] == '025' || $_POST['RESPONSE_CODE'] == '024' || $_POST['RESPONSE_CODE'] == '023' || $_POST['RESPONSE_CODE'] == '022' || $_POST['RESPONSE_CODE'] == '021'|| $_POST['RESPONSE_CODE'] == '020'|| $_POST['RESPONSE_CODE'] == '019'|| $_POST['RESPONSE_CODE'] == '018' || $_POST['RESPONSE_CODE'] == '017'|| $_POST['RESPONSE_CODE'] == '016'|| $_POST['RESPONSE_CODE'] == '015' || $_POST['RESPONSE_CODE'] == '014' || $_POST['RESPONSE_CODE'] == '013' || $_POST['RESPONSE_CODE'] == '012'|| $_POST['RESPONSE_CODE'] == '011'|| $_POST['RESPONSE_CODE'] == '009'|| $_POST['RESPONSE_CODE'] == '008' || $_POST['RESPONSE_CODE'] == '007'|| $_POST['RESPONSE_CODE'] == '006'|| $_POST['RESPONSE_CODE'] == '005'|| $_POST['RESPONSE_CODE'] == '004' || $_POST['RESPONSE_CODE'] == '003'|| $_POST['RESPONSE_CODE'] == '002'|| $_POST['RESPONSE_CODE'] == '001' ){
					//faild case redirection

					//order cancel redirection
					\Session::put('error','Order Payment Has been Failed');
		 			$response = json_encode($_POST);
				 	$requestParams = json_encode($request->all());		
					LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
					OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
					flash(translate('Order Payment Has been Failed'))->error();			
					if(!empty(Session::get('order_id'))){
						return redirect('/checkout/payment_select');	
					}else{
						return redirect('/');	
					}
								
				}elseif($_POST['RESPONSE_CODE'] == '010' && $_POST['RESPONSE_MESSAGE'] == 'Cancelled by user' && $_POST['STATUS'] == 'Cancelled' ){
			 		if(!empty($_POST["PAYMENT_TYPE"])){
			 			//order cancel redirection for bank payment page
			 			\Session::put('error','Order Payment Has been Failed');
	 					$response = json_encode($_POST);
			 			$requestParams = json_encode($request->all());		
						LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
						OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
						flash(translate($_POST["RESPONSE_MESSAGE"]))->error();				
						if(!empty(Session::get('order_id'))){
							return redirect('/checkout/payment_select');	
						}else{
							return redirect('/');	
						}
					}else{
			 			//order cancel redirection
			 			\Session::put('error','Error while placing order');
			 			$response = json_encode($_POST);
					 	$requestParams = json_encode($request->all());		
						LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
						OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
						flash(translate($_POST["RESPONSE_MESSAGE"]))->error();			
						if(!empty(Session::get('order_id'))){
							return redirect('/checkout/payment_select');	
						}else{
							return redirect('/');	
						}
			 		}				
				}else{
					\Session::put('error','Error while placing order');
					$response = json_encode($_POST);
				 // 	$requestParams = json_encode($request->all());		
					LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
					OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
					flash(translate($_POST["RESPONSE_MESSAGE"]))->error();
					if(!empty(Session::get('order_id'))){
						return redirect('/checkout/payment_select');	
					}else{
						return redirect('/');	
					}
				}
			}else{
				\Session::put('error','Error while placing order');
				$response = json_encode($_POST);
			 	$requestParams = json_encode($request->all());		
				LogActivity::addToPayment($requestParams, $orderID, $response, 'failed', 'get',$_POST["HASH"], 'letzpay_payment');
				OrderLog::where('id', $orderID)->update([
                                            'wallet_amount' => 0,
                                        ]);
				flash(translate($_POST["RESPONSE_MESSAGE"]))->error();
				if(!empty(Session::get('order_id'))){
					return redirect('/checkout/payment_select');	
				}else{
					return redirect('/');	
				}
			}
	        
		}catch (\Exception $e) {
            return  $e->getMessage();
            \Session::put('error',$e->getMessage());
            flash(translate($e->getMessage()))->error();
            if(!empty(Session::get('order_id'))){
				return redirect('/checkout/payment_select');	
			}else{
				return redirect('/');	
			}
        }		
    }


    //genratehash For LetzPay
	public function genrateResponseHash($request){
	    $res = $request;
	    $PAY_ID = env('LETZPAY_ID');
	    $SALT_ID = env('LETZPAY_SALT');
	    $response = array();
	    unset($res["HASH"]);
	    $hash = "";
	    $paramList = array();
	    ksort($res);
	    $build = http_build_query($res);
	    $builddecode = urldecode($build);
	    $buildReplace = str_replace('&', '~', $builddecode);
	    $buildAppend = $buildReplace.$SALT_ID;
	    $hashgenrated = hash('sha256', $buildAppend);
	    $hashCase = strtoupper($hashgenrated);
		return $hashCase;
}

//products Sku
    public function productsSku($id){
    	$orderDetails = OrderDetail::where('order_id', $id)->get();
    	$business_settings = BusinessSetting::where('type', 'refund_request_time')->first();
    	if(!empty($business_settings)){
    		$refundDays = $business_settings->value;
    	}else{
    		$refundDays = '7';
    	}
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
    			$replaceSku = str_replace('&', '', $sku);
	     		$PROD_DESC[] = [ 
	                      'CATEGORY_CODE' => $products->category->name,
	                      'SKU_CODE' => $replaceSku,
	                      'PRODUCT_PRICE' => $products->unit_price,
	                      'QTY' => $value->quantity,
	                      'REFUND_DAYS' => $refundDays,
	                      'VENDOR_ID' => $products->seller_id
	                    ];

	    }
	    return json_encode(array("PROD_DESC" => $PROD_DESC));
    	
    }

	public function transactStatusLetzPay($id){
		
		$orderDetails = \App\OrderDetail::findOrFail($id);
		$order = \App\Order::findOrFail($orderDetails->order_id);
		$random = 'rm'.rand('10000000', '200000000');
		$productStock = \App\ProductStock::where('product_id', $orderDetails->product_id)->where('variant', $orderDetails->variant)->first();
	    if(!empty($productStock)){
	            $sku = $productStock->sku;
	    }else{
	            $productsStockInfo =  \App\ProductStock::where('product_id', $orderDetails->product_id)->first();
	            $sku = $productsStockInfo->sku;
	    }
	    if(!empty($order)){   
	        $param =  (object)json_decode($order->payment_details, true);
	        $replaceSku = str_replace('&', '', $sku);
	        $finalAmount = ($orderDetails->price-$orderDetails->peer_discount)+$orderDetails->shipping_cost;
	        $PAY_ID = env('LETZPAY_ID');
	        $SALT_ID = env('LETZPAY_SALT');
	        $URL = env('LETZPAY_REFUND_URL');

	        $letz_data = array();
	        $hash           = "";
	        $paramList      = array();
	        $refundAmount = $finalAmount * 100;
	        $letz_data['PAY_ID'] = $param->PAY_ID;
	        $letz_data['ORDER_ID'] = $param->ORDER_ID;
	        $letz_data['AMOUNT'] = $refundAmount;
	        $letz_data['TXNTYPE'] = 'REFUND';
	        $letz_data['CURRENCY_CODE'] = $param->CURRENCY_CODE;
	        $letz_data['REFUND_ORDER_ID'] = $random;
	        $letz_data['PG_REF_NUM'] = $param->PG_REF_NUM;
	        $letz_data['SKU_CODE'] = $replaceSku;
	        ksort($letz_data);
	        $build = http_build_query($letz_data);
	        $builddecode = urldecode($build);
	        $buildReplace = str_replace('&', '~', $builddecode);
	        $buildAppend  = $buildReplace.$SALT_ID;
	        $hashgenrated = hash('sha256', $buildAppend);
	        $hashCase = strtoupper($hashgenrated); 
	        $curl = curl_init();

	        curl_setopt_array($curl, array(
	          CURLOPT_URL => $URL,
	          CURLOPT_RETURNTRANSFER => true,
	          CURLOPT_ENCODING => '',
	          CURLOPT_MAXREDIRS => 10,
	          CURLOPT_TIMEOUT => 0,
	          CURLOPT_FOLLOWLOCATION => true,
	          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	          CURLOPT_CUSTOMREQUEST => 'POST',
	          CURLOPT_POSTFIELDS =>'{
	            "AMOUNT": '.$refundAmount.',
	            "CURRENCY_CODE": "356",
	            "ORDER_ID": "'.$param->ORDER_ID.'",
	            "PAY_ID": "'.$param->PAY_ID.'",
	            "PG_REF_NUM": "'.$param->PG_REF_NUM.'",
	            "REFUND_ORDER_ID": "'.$random.'",
	            "SKU_CODE": "'.$replaceSku.'",
	            "TXNTYPE": "REFUND",
	            "HASH": "'.$hashCase.'"
	            }',
	              CURLOPT_HTTPHEADER => array(
	                'Content-Type: application/json'
	              ),
	            ));

	        $response = curl_exec($curl);
	        curl_close($curl);
	        return json_decode($response, true);
	    }
    }

	public function genrateStatusHash($payID, $orderID, $amount){
	    $PAY_ID = env('LETZPAY_ID');
	    $SALT_ID = env('LETZPAY_SALT');
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

	public function store_order($log_id)
	{
		error_reporting(0);
		
		$orderLog = OrderLog::findOrFail($log_id);
		$order = new Order;
		$order->user_id = $orderLog->user_id;
		$order->guest_id = $orderLog->guest_id;
		$order->shipping_address = $orderLog->shipping_address;
		$order->shipping_pin_code = $orderLog->shipping_pin_code;
		$order->payment_type = $orderLog->payment_type;
		$order->payment_status = $orderLog->payment_status;
		$order->payment_details = $orderLog->payment_details;
		$order->payment_details = "pending";
		$order->order_status = "pending";
		$order->grand_total = $orderLog->grand_total;
		$order->coupon_discount = $orderLog->coupon_discount;
		$order->referal_discount = $orderLog->referal_discount;
		$order->wallet_amount = $orderLog->wallet_amount;
		$order->code = $orderLog->code;
		$order->date = $orderLog->date;
		$order->viewed = $orderLog->viewed;
		$order->delivery_viewed = $orderLog->delivery_viewed;
		$order->payment_status_viewed = $orderLog->payment_status_viewed;
		$order->commission_calculated = $orderLog->commission_calculated;
		$order->save();

		// save order detail
		$orderDetailLog = OrderDetailLog::where('order_id',$log_id)->get();
		
		
		foreach($orderDetailLog as $key => $detailLog){
			$orderDetail = new OrderDetail;
			$orderDetail->order_id = $order->id;
			$orderDetail->seller_id = $detailLog->seller_id;
			$orderDetail->product_id = $detailLog->product_id;
			$orderDetail->variation = $detailLog->variation;
			$orderDetail->price = $detailLog->price;
			$orderDetail->tax = $detailLog->tax;
			$orderDetail->shipping_cost = $detailLog->shipping_cost;
			$orderDetail->peer_discount = $detailLog->peer_discount;
			$orderDetail->quantity = $detailLog->quantity;
			$orderDetail->payment_status = $detailLog->payment_status;
			$orderDetail->delivery_status = "pending";
			$orderDetail->shipping_type = $detailLog->shipping_type;
			$orderDetail->pickup_point_id = $detailLog->pickup_point_id;
			$orderDetail->product_referral_code = $detailLog->product_referral_code;
			$orderDetail->save();
			
		}
		
		

return $order->id;
	}


}
