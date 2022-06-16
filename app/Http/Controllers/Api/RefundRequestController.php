<?php

namespace App\Http\Controllers\Api;

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

            //$refund_amount = ($order_detail->price-$order_detail->peer_discount)+$order_detail->shipping_cost;
            $refund_amount = ($order_detail->price-$order_detail->peer_discount);


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
