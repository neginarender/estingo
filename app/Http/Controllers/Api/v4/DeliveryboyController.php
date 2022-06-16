<?php

namespace App\Http\Controllers\Api\v4;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Order;
use App\AssignOrder;
use App\DeliveryBoy;
use App\User;
use Carbon\Carbon as Carbon;

class DeliveryboyController extends Controller{
	public function razorpayPaymentLinkWebHook(Request $request){
		$json = file_get_contents('php://input');
		$response = json_decode($json, true);
		//info($json);
		//info("Done Link");exit;
		//Sample response from razorpay
		// $response = array (
		// 	'account_id' => 'acc_Ftou9vu3p0QuVg',
		// 	'contains' => 
		// 	array (
		// 	  0 => 'payment_link',
		// 	  1 => 'order',
		// 	  2 => 'payment',
		// 	),
		// 	'created_at' => 1642689098,
		// 	'entity' => 'event',
		// 	'event' => 'payment_link.paid',
		// 	'payload' => 
		// 	array (
		// 	  'order' => 
		// 	  array (
		// 		'entity' => 
		// 		array (
		// 		  'amount' => 1000,
		// 		  'amount_due' => 0,
		// 		  'amount_paid' => 1000,
		// 		  'attempts' => 1,
		// 		  'created_at' => 1642689123,
		// 		  'currency' => 'INR',
		// 		  'entity' => 'order',
		// 		  'id' => 'order_Im1wQOpCrEzG2D',
		// 		  'notes' => 
		// 		  array (
		// 			'policy_name' => 'Rozana delivery payment',
		// 		  ),
		// 		  'offer_id' => NULL,
		// 		  'offers' => 
		// 		  array (
		// 			0 => 'offer_IavRwGiL5bELig',
		// 			1 => 'offer_Iavg5epnV6Y792',
		// 		  ),
		// 		  'receipt' => '#125804065',
		// 		  'status' => 'paid',
		// 		),
		// 	  ),
		// 	  'payment' => 
		// 	  array (
		// 		'entity' => 
		// 		array (
		// 		  'acquirer_data' => 
		// 		  array (
		// 			'rrn' => '202063642416',
		// 		  ),
		// 		  'amount' => 1000,
		// 		  'amount_refunded' => 0,
		// 		  'amount_transferred' => 0,
		// 		  'bank' => NULL,
		// 		  'base_amount' => 1000,
		// 		  'captured' => true,
		// 		  'card' => NULL,
		// 		  'card_id' => NULL,
		// 		  'contact' => NULL,
		// 		  'created_at' => 1642689276,
		// 		  'currency' => 'INR',
		// 		  'description' => NULL,
		// 		  'email' => NULL,
		// 		  'entity' => 'payment',
		// 		  'error_code' => NULL,
		// 		  'error_description' => NULL,
		// 		  'error_reason' => NULL,
		// 		  'error_source' => NULL,
		// 		  'error_step' => NULL,
		// 		  'fee' => 24,
		// 		  'fee_bearer' => 'platform',
		// 		  'id' => 'pay_Im1z7a64znNda4',
		// 		  'international' => false,
		// 		  'invoice_id' => NULL,
		// 		  'method' => 'upi',
		// 		  'notes' => 
		// 		  array (
		// 			'policy_name' => 'Rozana delivery payment',
		// 		  ),
		// 		  'order_id' => 'order_Im1wQOpCrEzG2D',
		// 		  'refund_status' => NULL,
		// 		  'status' => 'captured',
		// 		  'tax' => 4,
		// 		  'vpa' => '7388991991@paytm',
		// 		  'wallet' => NULL,
		// 		),
		// 	  ),
		// 	  'payment_link' => 
		// 	  array (
		// 		'entity' => 
		// 		array (
		// 		  'accept_partial' => false,
		// 		  'amount' => 1000,
		// 		  'amount_paid' => 1000,
		// 		  'cancelled_at' => 0,
		// 		  'created_at' => 1642689098,
		// 		  'currency' => 'INR',
		// 		  'customer' => 
		// 		  array (
		// 			'contact' => '+917388991991',
		// 			'email' => 'they2me@gmail.com',
		// 			'name' => 'Hasan',
		// 		  ),
		// 		  'description' => 'Payment for order no ORD4138713056512',
		// 		  'expire_by' => 0,
		// 		  'expired_at' => 0,
		// 		  'first_min_partial_amount' => 0,
		// 		  'id' => 'plink_Im1vzdA0NojSeR',
		// 		  'notes' => 
		// 		  array (
		// 			'policy_name' => 'Rozana delivery payment',
		// 		  ),
		// 		  'notify' => 
		// 		  array (
		// 			'email' => false,
		// 			'sms' => true,
		// 		  ),
		// 		  'order_id' => 'order_Im1wQOpCrEzG2D',
		// 		  'reference_id' => '#125804065',
		// 		  'reminder_enable' => true,
		// 		  'reminders' => 
		// 		  array (
		// 			'status' => 'failed',
		// 		  ),
		// 		  'short_url' => 'https://rzp.io/i/xPC1CsF',
		// 		  'status' => 'paid',
		// 		  'updated_at' => 1642689276,
		// 		  'upi_link' => true,
		// 		  'user_id' => '',
		// 		),
		// 	  ),
		// 	),
		// );
		//info("Done");
		$amount = $response['payload']['order']['entity']['amount']/100;
		$order_code = explode(' ',$response['payload']['payment_link']['entity']['description']);
		$order = Order::where('code',$order_code[4])->select('id','device_id')->first();
		$assign = AssignOrder::where('order_id',$order->id)->select('delivery_boy_id')->first();
		$dboy = DeliveryBoy::where('id',$assign->delivery_boy_id)->select('user_id')->first();
		$user = User::where('id',$dboy->user_id)->select('device_id')->first();
		//send push notification
		$notification = [
           'title'=>'Payment Recieved',
           'body'=>"Rs. ".$amount." Recieved for order ".$order_code[4]
            ];
		if(!empty($order->device_id)){
		$customer_notification = [
			'title'=>"Payment Recieved",
			'body'=>"We have recieved Rs. ".$amount." for order ".$order_code[4]
		];
			$this->notify($order->device_id,$customer_notification);
		}
		return $this->notify($user->device_id,$notification);
	}

	public function generateUpiPaymentLink(Request $request){
		// payment links only work with live mode 
		$api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));

		$api->paymentLink->create(array('upi_link'=>true,'amount'=>$request->amount, 'currency'=>'INR', 'accept_partial'=>true,
		'first_min_partial_amount'=>100, 'description' => 'For Rozana order payment.Your order code '.$request->order_code, 'customer' => array('name'=>$request->name,
		'email' => $request->email, 'contact'=>$request->phone),  'notify'=>array('sms'=>true, 'email'=>true) ,
		'reminder_enable'=>true ,'notes'=>array('policy_name'=> 'Rozana order payment')));
	}

	public function generateQRCode(Request $request){
		//current razorpay sdk does not have qr code functionality kindly update
		// $api = new Api(env('RAZOR_KEY'), env('RAZOR_SECRET'));
		// $api->qrCode->create(
		// 	array(
		// 		"type" => "upi_qr",
		// 		"name" => "Store_1", 
		// 		"usage" => "single_use",
		// 		"fixed_amount" => 1,
		// 		"payment_amount" => 300,
		// 		"description" => "For Store 1",
		// 		"close_by" => 1681615838,
		// 		"notes" => array("purpose" => "Test UPI QR code notes")
		// 	)
		// );
		$closed_by = Carbon::now()->addMinutes(15);
		$closed_by_unix = Carbon::parse($closed_by)->timestamp;
		$curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.razorpay.com/v1/payments/qr_codes/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
				"type": "upi_qr",
				"name": "'.$request->customer_name.'",
				"usage": "single_use",
				"fixed_amount": true,
				"payment_amount": "'.($request->amount*100).'",
				"description": "UPI QR Payment for order no '.$request->order_code.'",
				"close_by": "'.$closed_by_unix.'",
				"notes": {
				  "purpose": "UPI QR Payment for order no '.$request->order_code.'",
				  "order_id":"'.$request->order_code.'",
				  "contact":"'.$request->mobile_no.'",
				  "customer_name":"'.$request->customer_name.'",
				  "email":"'.$request->email.'"

				}
			  }',
            CURLOPT_HTTPHEADER => array(
                'Authorization:Basic cnpwX2xpdmVfMmNyRkpNNTNRWnBQVFE6dkNRWDJwWXQxcmFiUjZDT0ZCaHRXRUk5',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
       
        curl_close($curl);
		if(json_decode($response)->status=="active"){
			return response()->json([
				'success'=>true,
				'response'=>json_decode($response)
			]);
		}
            
        return response()->json([
            'success'=>false,
            'message'=>'GR Code not generated'
        ]);

	}

	public function razorpayQrCodeWebHook(Request $request){
		$json = file_get_contents('php://input');
		$response = json_decode($json,true);
		// sample response from razorpay after qr code payment
		// $response = array (
		// 	'entity' => 'event',
		// 	'account_id' => 'acc_Ftou9vu3p0QuVg',
		// 	'event' => 'qr_code.credited',
		// 	'contains' => 
		// 	array (
		// 	  0 => 'payment',
		// 	  1 => 'qr_code',
		// 	),
		// 	'payload' => 
		// 	array (
		// 	  'payment' => 
		// 	  array (
		// 		'entity' => 
		// 		array (
		// 		  'id' => 'pay_Img4t8lZmFx3kc',
		// 		  'entity' => 'payment',
		// 		  'amount' => 1000,
		// 		  'currency' => 'INR',
		// 		  'status' => 'captured',
		// 		  'order_id' => NULL,
		// 		  'invoice_id' => NULL,
		// 		  'international' => false,
		// 		  'method' => 'upi',
		// 		  'amount_refunded' => 0,
		// 		  'refund_status' => NULL,
		// 		  'captured' => true,
		// 		  'description' => 'QRv2 Payment',
		// 		  'card_id' => NULL,
		// 		  'bank' => NULL,
		// 		  'wallet' => NULL,
		// 		  'vpa' => '7388991991@paytm',
		// 		  'email' => NULL,
		// 		  'contact' => NULL,
		// 		  'notes' => 
		// 		  array (
		// 			'email' => 'they2me@gmail.com',
		// 			'contact' => '7388991991',
		// 			'purpose' => 'UPI QR Payment for order no ORD48548584589',
		// 			'order_id' => 'ORD4138713056512',
		// 			'customer_name' => 'Hasan',
		// 		  ),
		// 		  'fee' => 12,
		// 		  'tax' => 2,
		// 		  'error_code' => NULL,
		// 		  'error_description' => NULL,
		// 		  'error_source' => NULL,
		// 		  'error_step' => NULL,
		// 		  'error_reason' => NULL,
		// 		  'acquirer_data' => 
		// 		  array (
		// 			'rrn' => '202232257104',
		// 		  ),
		// 		  'created_at' => 1642830468,
		// 		),
		// 	  ),
		// 	  'qr_code' => 
		// 	  array (
		// 		'entity' => 
		// 		array (
		// 		  'id' => 'qr_Img3samcYftaWI',
		// 		  'entity' => 'qr_code',
		// 		  'created_at' => 1642830411,
		// 		  'name' => 'Hasan',
		// 		  'usage' => 'single_use',
		// 		  'type' => 'upi_qr',
		// 		  'image_url' => 'https://rzp.io/i/lhjM4OZ',
		// 		  'payment_amount' => 1000,
		// 		  'status' => 'active',
		// 		  'description' => 'UPI QR Payment for order no ORD48548584589',
		// 		  'fixed_amount' => true,
		// 		  'payments_amount_received' => 0,
		// 		  'payments_count_received' => 0,
		// 		  'notes' => 
		// 		  array (
		// 			'email' => 'they2me@gmail.com',
		// 			'contact' => '7388991991',
		// 			'purpose' => 'UPI QR Payment for order no ORD48548584589',
		// 			'order_id' => 'ORD4138713056512',
		// 			'customer_name' => 'Hasan',
		// 		  ),
		// 		  'customer_id' => NULL,
		// 		  'close_by' => 1642831310,
		// 		  'closed_at' => NULL,
		// 		  'close_reason' => NULL,
		// 		  'tax_invoice' => 
		// 		  array (
		// 		  ),
		// 		),
		// 	  ),
		// 	),
		// 	'created_at' => 1642830469,
		// );

		$amount = $response['payload']['payment']['entity']['amount']/100;
		$order_code = $response['payload']['payment']['entity']['notes']['order_id'];
		$order = Order::where('code',$order_code)->select('id','device_id')->first();
		$assign = AssignOrder::where('order_id',$order->id)->select('delivery_boy_id')->first();
		$dboy = DeliveryBoy::where('id',$assign->delivery_boy_id)->select('user_id')->first();
		$user = User::where('id',$dboy->user_id)->select('device_id')->first();
		//send push notification
		$notification = [
           'title'=>'Payment Recieved',
           'body'=>"Rs. ".$amount." Recieved for order ".$order_code
            ];
		if(!empty($order->device_id)){
		$customer_notification = [
			'title'=>"Payment Recieved",
			'body'=>"We have recieved Rs. ".$amount." for order ".$order_code
		];
			$this->notify($order->device_id,$customer_notification);
		}
		return $this->notify($user->device_id,$notification);
		

	}


	public function notify($to,$data){

        $api_key= env('FCM_KEY');
        $url="https://fcm.googleapis.com/fcm/send";
        $fields=json_encode(array('to'=>$to,'notification'=>$data));
    
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($fields));
    
        $headers = array();
        $headers[] = 'Authorization: key ='.$api_key;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
		return $result;
    }

	public function assignedOrders(Request $request){
		$dboy = DeliveryBoy::where('user_id',$request->id)->first()['id'];
		$new_orders = \App\SubOrder::LeftJoin('orders','orders.id','sub_orders.order_id')
		->where('assign_to',$dboy)
		->when($request->has('sub_order_code'),function($query) use($request) {
			return $query->where('sub_orders.sub_order_code',$request->sub_order_code);
		})
		->select('sub_orders.*','orders.shipping_address','orders.code')
		->orderBy('sub_orders.created_at')
		->get();

		if($new_orders->count()){
			return response()->json([
				'success'=>true,
				'data'=>$new_orders
			]);
		}

		return response()->json([
			'success'=>false,
			'data'=>[]
		]);
	}

	public function SubOrderDetail(Request $request){
		//$order_id = $request->order_id;
		$order_type = $request->order_type;
		$sub_order_id = $request->sub_order_code;

		$order = \App\SubOrder::LeftJoin('order_details','order_details.order_id','sub_orders.order_id')
		->LeftJoin('products','products.id','order_details.product_id')
		->where('sub_orders.sub_order_code',$sub_order_id)
		->where('order_details.order_type','=',$order_type)
		->select('order_details.product_id','order_details.variation','order_details.quantity','order_details.shipping_cost',
		'products.name','products.thumbnail_img','sub_orders.sub_order_code','sub_orders.payment_status','sub_orders.payment_mode',
		'sub_orders.payable_amount','sub_orders.created_at','sub_orders.order_status','sub_orders.delivery_status')
		//->orderBy('order_details.created_at')
		->get();
		if($order->count()){
			return response()->json([
				'success'=>true,
				'data'=>$order
			]);
		
		}
		return response()->json([
			'success'=>false,
			'data'=>[]
		]);

	}
}