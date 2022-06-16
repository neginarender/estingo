<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PurchaseHistoryDetailCollection;
use App\Models\OrderDetail;
use App\Models\Order;
use Illuminate\Http\Request;
use DB;
use \Carbon\Carbon;

class PurchaseHistoryDetailController extends Controller
{
    public function index($id)
    {
        return new PurchaseHistoryDetailCollection(OrderDetail::where('order_id', $id)->get());
    }

    public function orderHistoryDetails(REQUEST $request){

        // $trackOrder = array();

        // $dataArray = array();

        // $deliverymsg = array();

        // $deliverytime = array();

        $orderDetailId = $request['order_detail_id'];

        



        $orderdetail = DB::table('order_details')

        ->LeftJoin('orders','orders.id','=','order_details.order_id')

        ->RightJoin('products','order_details.product_id','=','products.id')

        ->where('order_details.id','=',$orderDetailId)  

        ->select('orders.code','orders.payment_type','orders.date','orders.shipping_address','orders.id as orderid','products.manage_by','orders.coupon_discount','order_details.variation','order_details.price','order_details.quantity','products.name','products.id as product_id','products.thumbnail_img','order_details.id as orderdetail_id', 'orders.payment_status','orders.grand_total','order_details.delivery_status','order_details.updated_at')

        ->first();

        return response()->json([

            'success' => true,

            'data' => $orderdetail,



        ]);

    }

    public function getOrderDetails(Request $request){
        
        $order = DB::table('orders')
                ->LeftJoin('order_details','orders.id','=','order_details.order_id')
                ->RightJoin('products','order_details.product_id','=','products.id')
                ->where('orders.id','=',$request->orderId) 
                ->whereNull('order_details.deleted_at') 
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.id as order_id','orders.code','orders.referal_discount as discount','orders.total_shipping_cost as shipping_cost','orders.order_status','order_details.delivery_status as delivery_code','orders.payment_type','orders.shipping_address','orders.wallet_amount','order_details.id as order_detail_id','order_details.variation','order_details.price as price','order_details.quantity','order_details.tax as ptax','products.name','products.rating','products.unit_price','orders.coupon_discount','products.manage_by','products.id as product_id','products.thumbnail_img','order_details.id as orderdetail_id', 'orders.payment_status','orders.date', 'orders.grand_total','order_details.delivery_status','order_details.updated_at')
                ->get();
                $tax = $order->sum('ptax');
                $total_price = $order->sum('price');
                $orders = $order->map(function($item) use($tax,$total_price){
                    $item->totalOrderPrice = round($total_price,2);
                    $item->shipping_address = json_decode($item->shipping_address);
                     
                    $item->date = date('d-m-Y',$item->date);
                    $check_return = \App\RefundRequest::where(['order_detail_id'=>$item->order_detail_id])->first();
                    $check_replacement = \App\ReplacementOrder::where(['order_detail_id'=>$item->order_detail_id])->first();

                    $replacement = "";
                    if(!is_null($check_replacement)){
                        if($check_replacement->approve==1){
                             $replacement = "1";
                             if($check_replacement->replaced==1){
                                $replacement = "2";
                            }
                        }
                        else{
                            $replacement = "0";
                        }
                    }
                    $return =0;
                    if(empty($check_return)){
                        // display request button
                        $return = 0; 
                    }
                    elseif($check_return["sorting_hub_approval"] != 1 || $check_return["admin_approval"] != 1){
                        // Return already requested
                        $return = 1;
                    }
                    elseif($check_return["sorting_hub_approval"] == 1 || $check_return["admin_approval"] == 1){
                        // Return request approved
                        $return = 2;
                    }
                    $item->tax = $tax;
                    $item->return_status = $return;
                    $item->replacement_status = $replacement;
                    if($item->payment_type=="wallet" || ($item->payment_type=="razorpay" && $item->wallet_amount!=0)){
                        $item->grand_total = ($item->grand_total+$item->wallet_amount);
                    }

                    $ddate = Carbon::parse($item->updated_at);
                    $item->can_replace = true;
                    if($item->delivery_status=='delivered' && $ddate->diffInMinutes()>24*60)
                    {
                        $item->can_replace = false;
                    }

                    return $item;
                   });

        return response()->json([

            'success' => true,

            'data' => $orders,
            'message'=>"Order Found"


        ]);


    }

    public function trackOrder(Request $request)
    {
        $code = $request->order_code;
        if(!str_contains($code,'ORD')){
            $code = "ORD".$code;
        }

        $orderId = Order::where('code',$code)->first();
        if(!is_null($orderId))
        {
            $request->request->add(['orderId' => $orderId->id]);
            $order = $this->getOrderDetails($request);
            return $order;
        }
        return response()->json([
            'success'=>false,
            'data'=>[],
            'message'=>trans("Order not found")
        ]);
    }

}
