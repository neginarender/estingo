<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Order;
use App\AssignOrder;
use App\DeliveryBoy;
use App\User;
use Carbon\Carbon as Carbon;
use App\Http\Resources\DeliveryBoyCollection;
use App\SubOrder;
use App\OrderDetail;
use DB;
use App\AssignSuborder;

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
		->leftjoin('order_status','order_status.id','sub_orders.order_status')
		->leftjoin('order_assign_suborder','order_assign_suborder.suborder_id','sub_orders.id')
		->where('order_assign_suborder.assign_to',$dboy)
		->when($request->has('sub_order_code'),function($query) use($request) {
			return $query->where('sub_orders.sub_order_code',$request->sub_order_code);
		})
		->when($request->has('order_status'),function($query) use($request){
			return $query->where('sub_orders.order_status',$request->order_status);
		})
		->select('sub_orders.*','order_status.name as order_status_name','orders.shipping_address','orders.code')
		->orderBy('sub_orders.created_at','desc')
		->paginate(10);

		$new_orders = $new_orders->map(function($item){
			$orderDetail = \App\OrderDetail::where('sub_order_id',$item->id)->get();
			$total_payable_amount =0;
			$no_of_items = 0;
			$total_customer_discount = 0;
			foreach($orderDetail as $detail){
				$total_payable_amount+=$detail->price-($detail->shipping_cost+$detail->peer_discount);
				$no_of_items+=$detail->quantity;
				$total_customer_discount+=$detail->peer_discount;
			}
			$item->no_of_items = $no_of_items;
			$item->payable_amount = $total_payable_amount;
			$item->customer_discount = $total_customer_discount;
			return $item;
		});
		return new DeliveryBoyCollection($new_orders);
	}

	public function SubOrderDetail(Request $request){
		//$order_id = $request->order_id;
		//$order_type = $request->order_type;
		$sub_order_id = $request->sub_order_code;
		
		$order = \App\SubOrder::LeftJoin('order_details','order_details.sub_order_id','sub_orders.id')
		->LeftJoin('products','products.id','order_details.product_id')
		->where('sub_orders.sub_order_code',$sub_order_id)
		->select('order_details.product_id','order_details.variation','order_details.quantity','order_details.shipping_cost',
		'products.name','products.thumbnail_img','sub_orders.id as sub_order_id','sub_orders.sub_order_code','sub_orders.payment_status','sub_orders.payment_mode',
		'sub_orders.payable_amount','sub_orders.created_at','sub_orders.order_status','sub_orders.delivery_status')
		->orderBy('order_details.created_at','desc')
		->get();
		$order = $order->map(function($item){
			$orderDetail = \App\OrderDetail::where('sub_order_id',$item->sub_order_id)->get();
			$total_payable_amount =0;
			$no_of_items = 0;
			$total_customer_discount = 0;
			foreach($orderDetail as $detail){
				$total_payable_amount+=$detail->price-($detail->shipping_cost+$detail->peer_discount);
				$no_of_items+=$detail->quantity;
				$total_customer_discount+=$detail->peer_discount;
			}
			$item->no_of_items = $no_of_items;
			$item->payable_amount = round($total_payable_amount,0,2);
			$item->customer_discount = round($total_customer_discount,0,2);
			return $item;
		});
		$order_status = \App\SubOrder::where('sub_order_code',$sub_order_id)->select('order_status','order_id')->first();
		$shipping_address = \App\Order::where('id',$order_status->order_id)->select('shipping_address')->first();
		$order_status = DeliveryBoyCollection::orderStatus($order_status['order_status']);
		if($order->count()){
			return response()->json([
				'success'=>true,
				'shipping_address'=>json_decode($shipping_address->shipping_address),
				'data'=>$order,
				'order_status'=> $order_status,
			]);
		
		}
		return response()->json([
			'success'=>false,
			'data'=>[],
			'order_status'=>[],
		]);

	}

	public function takeSubOrder(Request $request)
       {
            $order = \App\SubOrder::where('sub_order_code', $request->sub_order_code)->where('order_status',3)->first();
			if(!is_null($order)){
				$check = \App\SubOrder::where('sub_order_code',$request->sub_order_code)->whereNull('assign_to')->first();
				if(!is_null($check))
				{
					$dboy = DeliveryBoy::where('user_id',$request->id)->first()['id'];
					$assignment = \App\SubOrder::find($check->id);
					$assignment->assign_to = $dboy;
					
					if($assignment->save())
					{
						// create notitication for delivery body
						generateNotification($request->sub_order_code,$order->order_id,$request->id,$order->delivery_status);
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
			else{
				return response()->json([
					'success'=>false,
					//'order'=>[],
					'message'=>"Failed to assign order."
				]);
			}

            
       }

	public function updateSubOrderStatus(Request $request){
		DB::beginTransaction();
		$suborder = SubOrder::where('sub_order_code',$request->sub_order_code)->first();
		try{
			$order = Order::findOrFail($suborder->order_id);
			$order->delivery_viewed = '0';
			$order->save();
			$phone = $order->user['phone'];
			$message="";
			$address = array();
			if($request->order_status == 6){
				$otp  = random_int(1000, 9999);
				$suborder->otp = $otp;
				$suborder->save();
				$to = json_decode($order->shipping_address,true)['phone'];
				$from = "RZANA";
				// $tid  = "1707162443937828624"; 
				$tid  = "1707164406052847021"; 
				// $msg = "Your order ".$order->code." from Rozana is out for delivery, please share delivery code ".$otp." with the executive. For further queries call 9667018020 Thank you, Team Rozana";
				$msg = "Your order ".$order->code." from Rozana is out for delivery, please share delivery code ".$otp.". For help call 9667018020. Rozana";
				 mobilnxtSendSMS($to,$from,$msg,$tid);
			}

			if($request->status == 8 && empty($request->name)){
				if($request->otp!=$order->suborder){
					return response()->json([
						'success'=>false,
						'message'=>'Invalid OTP'
					]);
				}
				
			}

			if(!empty($request->name)){
				$address['name'] = $request->name;
				$address['mobile'] = $request->mobile;
				$suborder->delivered_to = json_encode($address);
			}
			$suborder->delivery_status = \App\OrderStatus::where('id',$request->order_status)->first()['name'];
			$suborder->order_status = $request->order_status;
			$suborder->save();

			$order_details = OrderDetail::where('sub_order_id',$suborder->id)->whereNull('deleted_at')->get();
			foreach($order_details as $key => $orderDetail){

				if($request->order_status == 8){
					$orderDetail->payment_status = "paid";
					$orderDetail->delivery_status = 'delivered';
				}elseif($request->order_status == 6){
					$orderDetail->delivery_status = 'on_delivery';
				}
				// if($request->status == 'delivered' && $order->payment_status == 'paid'){
				$orderDetail->save();
			}


			$total_sub_order = SubOrder::where('order_id',$suborder->order_id);
			$total_order_count = $total_sub_order->count();
			$total_delievred_order = $total_sub_order->where('order_status',8)->count();

			$order_status = "partially_delivered";
			if($total_delievred_order == $total_order_count){
				$order_status = 'delivered';
			}
			
			if($total_delievred_order == 0){
				$order_status = 'in_process';
			}
			$order->order_status = $order_status;
            $order->save();

			//commission start
			if(($request->order_status == 8 && $order->payment_status == 'paid') || ($request->order_status == 8 && $order->payment_type == "cash_on_delivery") && $order->order_status == 'delivered'){
						
		
					$OrderReferalCommision = OrderReferalCommision::where('order_id', $order->order_id)->first();

					if(!empty($OrderReferalCommision) && $OrderReferalCommision->wallet_status == 0){
						$partner = PeerPartner::where('user_id', $OrderReferalCommision->partner_id)->first();

						if(!empty($partner) && $partner->verification_status == 1 && $partner->parent != 0){
						
								$select_partner = PeerPartner::where('id', $partner->parent)->first();
								$master_partner = User::find($select_partner->user_id);
								$mastertotal_balance = $master_partner->balance+$OrderReferalCommision->master_discount;
								$master_partner->balance = $mastertotal_balance;
								$master_partner->save();

								// $this->smsToMasterPeerOnOrderDelivered($select_partner->phone,$select_partner->code,$OrderReferalCommision->master_discount);
								$this->smsToMasterPeerOnOrderDelivered($select_partner->phone,$partner->code,$OrderReferalCommision->master_discount);
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
								$wallet->order_id = $order->id;
								$wallet->save();

								$OrderReferalCommision->wallet_status = 1;
								$OrderReferalCommision->save();
							}
						}
				}
			}
			//commission end
		    DB::commit();
			return response()->json([
				'success'=>true,
				'message'=>'Order status updated']);
		}catch(\Exception $e){
			DB::rollback();
			return response()->json([
				'success'=>true,
				'message'=>$e->getMessage()]);
		}
		

	}

}