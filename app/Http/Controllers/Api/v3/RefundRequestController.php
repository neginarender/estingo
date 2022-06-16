<?php

namespace App\Http\Controllers\Api\v3;

use Illuminate\Http\Request;
use App\Http\Controllers\LetzpayController;
use App\Http\Controllers\OrderController;
use App\BusinessSetting;
use App\RefundRequest;
use App\OrderDetail;
use App\Order;
use App\Seller;
use App\Wallet;
use App\User;
use App\ShortingHub;
use Auth;
use DB;
use App\Mail\ReturnOrder;
use App\Mail\InvoiceEmailManager;
use App\Mail\EmailManager;
use Mail;
use App\Product;
use App\OrderReferalCommision;
use App\ReferalUsage;

class RefundRequestController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    //Store Customer Refund Request
    public function request_store(Request $request, $id)
    {
        $order_detail = OrderDetail::where('id', $id)->first();
        $refund = new RefundRequest;
        $refund->user_id = Auth::user()->id;
        $refund->order_id = $order_detail->order_id;
        $refund->order_detail_id = $order_detail->id;
        $refund->seller_id = $order_detail->seller_id;
        $refund->seller_approval = 0;
        $refund->reason = $request->reason;
        $refund->admin_approval = 0;
        $refund->admin_seen = 0;
        $refund->refund_amount = $order_detail->price + $order_detail->tax;
        $refund->refund_status = 0;
        if ($refund->save()) {
            flash("Refund Request has been sent successfully")->success();
            return redirect()->route('purchase_history.index');
        }
        else {
            flash("Something went wrong")->error();
            return back();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function vendor_index()
    {
        $refunds = RefundRequest::where('seller_id', Auth::user()->id)->latest()->paginate(10);
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            return view('refund_request.frontend.recieved_refund_request.index', compact('refunds'));
        }
        else {
            return view('refund_request.frontend.recieved_refund_request.index', compact('refunds'));
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function customer_index()
    {
        $refunds = RefundRequest::where('user_id', Auth::user()->id)->latest()->paginate(10);
        return view('refund_request.frontend.refund_request.index', compact('refunds'));
    }

    //Set the Refund configuration
    public function refund_config()
    {
        return view('refund_request.config');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refund_time_update(Request $request)
    {
        $business_settings = BusinessSetting::where('type', $request->type)->first();
        if ($business_settings != null) {
            $business_settings->value = $request->value;
            $business_settings->save();
        }
        else {
            $business_settings = new BusinessSetting;
            $business_settings->type = $request->type;
            $business_settings->value = $request->value;
            $business_settings->save();
        }
        flash("Refund Request sending time has been updated successfully")->success();
        return back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refund_sticker_update(Request $request)
    {
        $business_settings = BusinessSetting::where('type', $request->type)->first();
        if ($business_settings != null) {
            if($request->hasFile('logo')){
                $business_settings->value = $request->file('logo')->store('frontend/refund_sticker');
            }
            $business_settings->save();
        }
        else {
            $business_settings = new BusinessSetting;
            $business_settings->type = $request->type;
            if($request->hasFile('logo')){
                $business_settings->value = $request->file('logo')->store('frontend/refund_sticker');
            }
            $business_settings->save();
        }
        flash("Refund Sticker has been updated successfully")->success();
        return back();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin_index()
    {
        $refunds = RefundRequest::where('refund_status', 0)->latest()->paginate(15);
        return view('refund_request.index', compact('refunds'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paid_index()
    {
        $refunds = RefundRequest::where('refund_status', 1)->latest()->paginate(15);
        return view('refund_request.paid_refund', compact('refunds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function request_approval_vendor(Request $request)
    {
        $refund = RefundRequest::findOrFail($request->el);
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            $refund->seller_approval = 1;
            $refund->admin_approval = 1;
        }
        else {
            $refund->seller_approval = 1;
        }

        if ($refund->save()) {
            return 1;
        }
        else {
            return 0;
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refund_pay(Request $request)
    {
        $refund = RefundRequest::findOrFail($request->el);
        if ($refund->seller_approval == 1) {
            $seller = Seller::where('user_id', $refund->seller_id)->first();
            if ($seller != null) {
                $seller->admin_to_pay -= $refund->refund_amount;
            }
            $seller->save();
        }
        $wallet = new Wallet;
        $wallet->user_id = $refund->user_id;
        $wallet->amount = $refund->refund_amount;
        $wallet->payment_method = 'Refund';
        $wallet->payment_details = 'Product Money Refund';
        $wallet->save();
        $user = User::findOrFail($refund->user_id);
        $user->balance += $refund->refund_amount;
        $user->save();
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            $refund->admin_approval = 1;
            $refund->refund_status = 1;
        }
        if ($refund->save()) {
            return 1;
        }
        else {
            return 0;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function refund_request_send_page($id)
    {

        $order_detail = OrderDetail::findOrFail($id);

        if ($order_detail->product_id != null && $order_detail->delivery_status == 'pending') {

            $refund_amount = ($order_detail->price-$order_detail->peer_discount)+$order_detail->shipping_cost;

            return response()->json([
                'status'=>true,
                'data'=>[
                    'id'=>$id,
                    'product_name'=>$order_detail->product->name,
                    'amount'=>round($refund_amount,2),
                    'order_code'=>$order_detail->order->code
                ]
            ]);
        }
        
        return response()->json([
            'status'=>false,
            'data'=>[]
        ]);
    }

    /**
     * Show the form for view the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //Shows the refund reason
    public function reason_view($id)
    {
        $refund = RefundRequest::findOrFail($id);
        if (Auth::user()->user_type == 'admin' || Auth::user()->user_type == 'staff') {
            if ($refund->orderDetail != null) {
                $refund->admin_seen = 1;
                $refund->save();
                return view('refund_request.reason', compact('refund'));
            }
        }
        else {
            return view('refund_request.frontend.refund_request.reason', compact('refund'));
        }
    }

    // public function refund_request_sends(Request $request)
    // {
    //     $id = $request->id;
    //     $order_detail = OrderDetail::findOrFail($id);
    //     $order = Order::findorFail($order_detail->order_id);
    //     if ($order_detail->product_id != null && $order_detail->delivery_status == 'pending') {

    //             $data['user_id'] = Auth::user()->id;
    //             $data['order_id'] = $order_detail->order_id;
    //             $data['order_detail_id'] = $id;

    //             $count_order = DB::table('orders')
    //                                ->leftjoin('order_details','orders.id','=','order_details.order_id')  
    //                                ->where('order_details.delivery_status','==','returned')->where('orders.id','=',$order_detail->order_id)
    //                                ->get();
    //             if($order->referal_discount == null){
    //                 if(count($order->orderDetails) ==1){
    //                     $refund_amount = $order->grand_total;
    //                 }else{
    //                     if(count($count_order)>0){
    //                         $refunded_order_amount = RefundRequest::where('order_id',$order_detail->order_id)->sum();
    //                         $remain_order = count($order->orderDetails) - count($count_order);
    //                         if($remain_order == 1){
    //                             $refund_amount = $order->grand_total - $refunded_order_amount;
    //                         }else{
    //                             $refund_amount = $order_detail->price;

    //                         }
                            
    //                     }else{
    //                         $refund_amount = $order_detail->price;

    //                     }

    //                 }


                    
    //             }else{
    //                 if(count($order->orderDetails) ==1){
    //                     $refund_amount = $order->grand_total;
    //                 }else{
    //                     if(count($count_order)>0){
    //                         $refunded_order_amount = RefundRequest::where('order_id',$order_detail->order_id)->sum();
    //                         $remain_order = count($order->orderDetails) - count($count_order);
    //                         if($remain_order == 1){
    //                             $refund_amount = $order->grand_total - $refunded_order_amount;
    //                         }else{
    //                             $refund_amount = $order_detail->price;

    //                         }
                            
    //                     }else{
    //                         $refund_amount = $order_detail->price;

    //                     }

    //                 }

    //             }

    //             $data['refund_amount'] = $refund_amount;
    //             $shipping_address = json_decode($order->shipping_address); 
    //             $getSortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $shipping_address->postal_code . '"]\')')->first();
    //             $msg = "this is text for sorting hub";
    //             $number = "+917388991991";
    //             $shortingHubMsg = return_send_SMS($number,$msg);
    //             $requestReturn  = RefundRequest::create($data);
    //             if($requestReturn){
                   
    //                 return response()->json([
    //                     'status'=>true,
    //                     'message'=>'Refund Request has been sent successfully'
    //                 ]);
    //             }else{
    //                 return response()->json([
    //                     'status'=>false,
    //                     'message'=>'Something went wrong'
    //                 ]);

    //             }
    //     }else {
    //         return response()->json([
    //             'status'=>false,
    //             'message'=>'Something went wrong'
    //         ]);
    //     }

    // }
    public function refund_request_sends(Request $request){

        $id = $request->id;
        $order_detail = OrderDetail::where('id',$id)
                        ->whereNull('deleted_at')
                        ->where('delivery_status','!=','delivered')
                        ->where('delivery_status','!=','return')
                        ->where('delivery_status','!=','cancel')
                        ->first();
        if(!is_null($order_detail)){
            $order = Order::findorFail($order_detail->order_id);
            $refund_amount = $order_detail->price - $order_detail->peer_discount;
            // store
            $refundRequest = new RefundRequest;
            $refundRequest->user_id = Auth::user()->id;
            $refundRequest->order_id = $order->id;
            $refundRequest->order_detail_id = $id;
            $refundRequest->refund_amount = $refund_amount;
            if($refundRequest->save()){
                return response()->json([
                    'status'=>true,
                    'message'=>'Refund request send successfully.'
                ]);
            }
        }
        return response()->json([
            'status'=>false,
            'message'=>'Something went wrong!'
        ]);
    }

    // public function refund_request_send_back($id)
    // {
        
    //     $order_detail = OrderDetail::findOrFail($id);
    //     $order = Order::where('id',$order_detail->order_id)->first();
    //     $checkRequest = RefundRequest::where('order_detail_id',$id)->first();
    //     if (!empty($checkRequest)) {
            
    //         if($order->payment_type == "letzpay_payment"){
    //             $ob = new LetzpayController;
    //             $response = $ob->transactStatusLetzPay($id);
    //             if(!empty($response["RESPONSE_MESSAGE"])){
    //                 $hash = $ob->genrateResponseHash($response);

    //                 if($response["HASH"] == $hash){
    //                     if($response["RESPONSE_CODE"] == 0 && $response["STATUS"] ==  "Captured"){
    //                          //save data for generate refund request
    //                          $checkRequest->sorting_hub_approval = 1;
    //                          $checkRequest->admin_approval = 1;
    //                          $checkRequest->refund_status = 1;
    //                          $checkRequest->save();
                             
    //                         $data['delivery_status'] = 'refund';
    //                         $products = OrderDetail::where('id', $id)->update($data);
                            
    //                         flash("Refund Request has been sent successfully")->success();
    //                         return back();
    //                     }else{
    //                         flash($response["RESPONSE_MESSAGE"])->error();
    //                         return back();
    //                     }
                       
    //                 }else{
    //                     flash("response hash not match")->error();
    //                     return back();
    //                 }
    //             }else{
    //                 flash("Something went wrong")->error();
    //                 return back();
    //             }
    //         }else{
    //             $checkRequest->sorting_hub_approval = 1;
    //             $checkRequest->admin_approval = 1;
    //             $checkRequest->refund_status = 1;
            

    //         if($checkRequest->save()){
    //             flash("Refund Request has been sent successfully")->success();
    //                         return back();
    //         }else{
    //             flash("Something went wrong")->error();
    //             return back();
    //         }

    //         }
               
                
           
    //         // return view('refund_request.frontend.refund_request.create', compact('order_detail'));
    //     }
    //     else {
    //         flash("Something went wrong")->error();
    //         return back();
    //     }

    // }

    public function refund_request_send_back($id){
        
        DB::beginTransaction();
        $orderDetail = OrderDetail::where('id',$id)->first();
        $order = Order::where('id',$orderDetail->order_id)->first();
        $productInfo = Product::where('id',$orderDetail->product_id)->first();
        $checkRequest = RefundRequest::where('order_detail_id',$id)->first();
        // $total_price = $orderDetail->price*$orderDetail->quantity;
        $total_price = $orderDetail->price;
        $getUserWallet = User::where('id',$order->user_id)->first();
        $shipping_cost = $orderDetail->shipping_cost;
        $peer_discount = 0.00;
        $array = [];
        $array['view'] = 'emails.refund';
        $array['subject'] = 'Regarding Return Product';
        $array['from'] = env('MAIL_USERNAME');
        $email = json_decode($order->shipping_address,true)['email'];

        try{

            if($checkRequest->sorting_hub_approval == 0){
                    if(!empty($orderDetail->peer_discount)){
                    $peer_discount = $orderDetail->peer_discount;
                    $peerCommission = OrderReferalCommision::where('order_id',$order->id)->first();
                    $referalUsage = ReferalUsage::where('order_id',$order->id)->first();
                    $peerCommission->referal_commision_discount -= $orderDetail->sub_peer;
                    $peerCommission->master_discount  -= $orderDetail->master_peer;
                    $peerCommission->save();
                    $referalUsage->discount_amount -= $orderDetail->sub_peer;
                    $referalUsage->master_discount  -= $orderDetail->master_peer;
                    $referalUsage->save();
                }

                $refund_response = array();
                $refund_amount = $total_price - $orderDetail->peer_discount;
                if($order->payment_type == "cash_on_delivery"){
                    $order->grand_total -=  $refund_amount;
                    $array['content'] = translate('Hi. Your return request of '.$productInfo->name.'has been approved.');

                }elseif($order->payment_type == "razorpay" && empty($order->wallet_amount)){
                   
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

                        $array['content'] = translate('Hi. Your return request of '.$productInfo->name.'has been approved.And Amount of Rs.'.$refund_amount.'will be refunded in your account.');
                        $order->grand_total -=  $refund_amount;
                    }else{
                        flash(translate('Something went wrong.'))->success();
                        return redirect()->back();

                    }
                    

                }elseif($order->payment_type == "wallet"){
                    
                    $getUserWallet->balance += $refund_amount;
                    if($getUserWallet->save()){
                        $storeWalletHistory = Wallet::create([
                            'user_id'=>$order->user_id,
                            'amount'=>$refund_amount,
                            'payment_method'=> 'refund',
                            'order_id'=>$order->code
                        ]);
                        $order->wallet_amount -= $refund_amount;
                        $array['content'] = translate('Hi. Your return request of '.$productInfo->name.'has been approved.And Amount of Rs.'.$refund_amount.' refunded in your wallet.');

                    }else{
                        flash(translate('Something went wrong.'))->success();
                        return redirect()->back();
                    }

                }elseif($order->payment_type == "razorpay" && !empty($order->wallet_amount)){
                    $payment_response = json_decode($order->payment_details);
                    $pay_id = $payment_response->id;
                    $obj = new OrderController;
                    $resPay = $obj->getRemainAmountRazorpay($pay_id);
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
                                $array['content'] = translate('Hi. Your return request of '.$productInfo->name.' has been approved.And Amount of Rs.'.$refund_amount.' will be refunded in your account.');
                                $order->grand_total -=  $refund_amount;
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
                                    $order->grand_total -=  $cardAmount;
                                }else{
                                    flash('Something went wrong.')->error();
                                    return redirect()->back();
                
                                }

                            }
                            $getUserWallet->balance += $refund_amount_wallet;
                            if($getUserWallet->save()){
                                $storeWalletHistory = Wallet::create([
                                    'user_id'=>$order->user_id,
                                    'amount'=>$refund_amount_wallet,
                                    'payment_method'=> 'refund',
                                    'order_id'=>$order->code
                                ]);
                                $order->wallet_amount -= $refund_amount_wallet;

                                if($cardAmount != 0){
                                    $array['content'] = translate('Hi. Your return request of '.$productInfo->name.' has been approved.Amount of Rs.'. $cardAmount.' will be refunded in your account and'.$refund_amount_wallet.'in your wallet');
                                }else{
                                    $array['content'] = translate('Hi. Your return request of '.$productInfo->name.' has been approved.Amount of Rs.'.$refund_amount_wallet.' in your wallet.');
                                }
            
                            }else{
                                flash('Something went wrong.')->error();
                                return redirect()->back();
                            }

                        }

                    }else{
                        translate('Something went wrong.')->error();
                        return redirect()->back();
                    }
                }

                
                $order->referal_discount -= $peer_discount;
                
                if($order->save()){
                    if(empty($shipping_cost)){
                        if($order->grand_total<1500){
                            $shipping_cost = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
                        }

                        $orderDetail->delivery_status = 'return';
                        $orderDetail->save();
                        if(!empty($shipping_cost)){
                            $getOrderDetail = OrderDetail::where([['order_id','=',$order->id],['delivery_status','!=','return']])->get();
                            foreach($getOrderDetail as $k=>$v){
                                $getOrderDetail[$k]->shipping_cost += $shipping_cost/count($getOrderDetail);
                                $getOrderDetail[$k]->save();
        
                            }
                            
    
                        }
                        
                    }

                    $checkRequest->refund_status = 1;
                    $checkRequest->payment_response = json_encode($refund_response);
                    $checkRequest->refund_by = Auth::user()->id;
                    $checkRequest->admin_approval = 1;
                    $checkRequest->sorting_hub_approval = 1;
                    $checkRequest->save();
                }
                DB::commit();
                Mail::to($email)->queue(new EmailManager($array));
                flash("Refund  Approved Successfully.")->success();
                return back();
                

            }elseif($checkRequest->sorting_hub_approval == 1){
                    flash("Refund already Approved")->error();
                    return back();

            }
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function sorting_hub_refund_back($id)
    {
        
        $order_detail = OrderDetail::findOrFail($id);
        $order = Order::where('id',$order_detail->order_id)->first();
        $user = User::findOrFail($order->user_id);
        $shipping = OrderDetail::where('order_id',$order_detail->order_id)->sum('shipping_cost');
        // $countRemainingItem = OrderDetail::where('order_id',$order_detail->order_id)->where('delivery_status','=','pending')->count();

        $countRemainingItem = OrderDetail::where('order_id',$order_detail->order_id)->where('delivery_status','=','pending')->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();
        
        $refund_amount = $order_detail->price;
        if($countRemainingItem==1)
        {
            $refund_amount = $order_detail->price+$shipping;
        }
            if($order->payment_type == "letzpay_payment"){
                $ob = new LetzpayController;
                $response = $ob->transactStatusLetzPay($id);
                
                if(!empty($response["RESPONSE_MESSAGE"])){
                    $hash = $ob->genrateResponseHash($response);

                    if($response["HASH"] == $hash){
                        if($response["RESPONSE_CODE"] == 0 && $response["STATUS"] ==  "Captured"){
                             //save data for generate refund request

                            $data['delivery_status'] = 'refund';
                            $products = OrderDetail::where('id', $id)->update($data);
                            $refund = new RefundRequest;
                            $refund->shorting_hub_id = Auth::user()->id;
                            $refund->user_id = $order->user_id;
                            $refund->order_id = $order->id;
                            $refund->order_detail_id = $order_detail->id;
                            $refund->refund_amount = $refund_amount;
                            $refund->sorting_hub_approval = 1;
                            $refund->admin_approval = 1;
                            $refund->reason = "Product Out of Stock";
                            $refund->refund_status = 1;
                            $refund->payment_response  = json_encode($response);
                            $refund->refund_by = 'sorting_hub';
                            $refund->save();

                            $details = [
                                
                                'order_id' => $order->code,
                                'user'=>$user->name,
                                'refund_amount'=>$refund_amount
                            ];
                      
                            $user->notify(new PartialRefund($details));

                            flash("Refund Request has been sent successfully")->success();
                            return back();
                        }else{
                            flash($response["RESPONSE_MESSAGE"])->error();
                            return back();
                        }
                       
                    }else{
                        flash("response hash not match")->error();
                        return back();
                    }
                }else{
                    flash("Something went wrong")->error();
                    return back();
                }
            }else{
                flash("Something went wrong")->error();
                    return back();

            }
               
         

    }

    public function storeReturnRequest(Request $request){
        $id = $request->id;
        $order_detail = OrderDetail::where('id',$id)
                        ->whereNull('deleted_at')
                        ->where('delivery_status','!=','delivered')
                        ->where('delivery_status','!=','return')
                        ->where('delivery_status','!=','cancel')
                        ->first();
        if(!is_null($order_detail)){
            $order = Order::findorFail($order_detail->order_id);
            $refund_amount = $order_detail->price - $order_detail->peer_discount;
            // store
            $refundRequest = new RefundRequest;
            $refundRequest->user_id = Auth::user()->id;
            $refundRequest->order_id = $order->id;
            $refundRequest->order_detail_id = $id;
            $refundRequest->refund_amount = $refund_amount;
            if($refundRequest->save()){
                return response()->json([
                    'status'=>true,
                    'message'=>'Refund request send successfully'
                ]);
            }
        }
        return response()->json([
            'status'=>false,
            'message'=>'Something went wrong!'
        ]);
        

    }
}
