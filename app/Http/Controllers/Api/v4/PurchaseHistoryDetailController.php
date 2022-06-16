<?php

namespace App\Http\Controllers\Api\v4;

use App\Http\Resources\v4\PurchaseHistoryDetailCollection;
use App\Models\OrderDetail;
use App\Models\Order;
use App\SubOrder;
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
        
        $orderData = DB::table('orders')
                ->where('orders.id','=',$request->orderId) 
                ->orderBy('orders.created_at', 'desc')
                ->select('orders.id as order_id','orders.code','orders.referal_discount as discount','orders.total_shipping_cost as shipping_cost','orders.order_status','orders.payment_type','orders.shipping_address','orders.wallet_amount','orders.coupon_discount','orders.payment_status','orders.date', 'orders.grand_total')->get();

        $order = DB::table('orders')
                ->LeftJoin('order_details','orders.id','=','order_details.order_id')
                ->RightJoin('products','order_details.product_id','=','products.id')
                ->where('orders.id','=',$request->orderId)
                ->when($request->has('sub_order_id'),function($query) use($request){
                    return $query->where('order_details.sub_order_id',$request->sub_order_id);
                })
                ->whereNull('order_details.deleted_at') 
                ->orderBy('orders.created_at', 'desc')
                ->select('order_details.delivery_status as delivery_code','order_details.id as order_detail_id','order_details.variation','order_details.price as price','order_details.quantity','order_details.tax as ptax','products.name','products.rating','products.unit_price','orders.coupon_discount','products.manage_by','products.id as product_id','products.thumbnail_img','order_details.id as orderdetail_id','order_details.delivery_status','order_details.updated_at','orders.user_id')->get();

                $schedule = DB::table('sub_orders')
                ->where('order_id','=',$request->orderId)->where('status',1)
                ->get();

                $is_fresh = 0;
                $is_grocery = 0;
                foreach($schedule as $data){
                    if($data->delivery_name == 'fresh'){
                        $is_fresh = 1;
                    }
                    if($data->delivery_name == 'grocery'){
                        $is_grocery = 1;
                    }
                }
                
                $deliveryType = DB::table('sub_orders')
                            ->where('order_id','=',$request->orderId)->where('status',1)->pluck('delivery_type')
                            ->first();
                            
                $tax = $order->sum('ptax');
                $total_price = $order->sum('price');

                $orders = $orderData->map(function($item) use($order,$tax,$total_price,$schedule,$deliveryType,$is_fresh,$is_grocery){
                    $item->date = date('d-m-Y H:i:s',$item->date);
                    $item->is_fresh = $is_fresh;
                    $item->is_grocery = $is_grocery;

                    $item->deliveryType = empty($deliveryType)?'':$deliveryType;
                    if($item->deliveryType != ''){


                        
                        $slot = array();
                        foreach($schedule as $value){
                            if($value->delivery_type=="scheduled"){
                                $slot[$value->delivery_name]['delivery_date'] = $value->delivery_date;
                                $slot[$value->delivery_name]['delivery_time'] = $value->delivery_time;
                                $slot[$value->delivery_name]['sub_order_code'] = $value->sub_order_code;
                            }
                            else{
                                $slot[$value->delivery_name]['delivery_date'] = "";
                                $slot[$value->delivery_name]['delivery_time'] = "";
                                $slot[$value->delivery_name]['sub_order_code'] = $value->sub_order_code;
                            }
                            
                        }
                    }else{
                        $slot = array();
                        $slot['fresh']['delivery_date'] = '';
                        $slot['fresh']['delivery_time'] = '';
                        $slot['grocery']['delivery_date'] = '';
                        $slot['grocery']['delivery_time'] = '';
                    }

                    $item->delivery_detail = $slot;

                    // $item->expectedDeliveryDate = date('d-m-Y H:i:s', strtotime($item->date . ' +1 day'));
                    $item->shipping_address = json_decode($item->shipping_address);
                    // if($item->payment_type =="wallet" || ($item->payment_type=="razorpay" && $item->wallet_amount!=0)){
                    //     $item->grand_total = ($item->grand_total+$item->wallet_amount);
                    // }

                    $item->details = $order->map(function($data) use($tax,$total_price){

                        $data->tax = $tax;
                        $check_return = \App\RefundRequest::where(['order_detail_id'=>$data->order_detail_id])->first();
                        $check_replacement = \App\ReplacementOrder::where(['order_detail_id'=>$data->order_detail_id])->first();
                        $replacement = "";
                        if(!is_null($check_replacement)){
                            if($check_replacement->approve==1){
                                 $replacement = "1";
                                 if($check_replacement->replaced==1){
                                    $replacement = "2";
                                }
                            }else{
                                $replacement = "0";
                            }
                        }
                        $return =0;
                        if(empty($check_return)){
                            // display request button
                            $return = 0; 
                        }elseif($check_return["sorting_hub_approval"] != 1 || $check_return["admin_approval"] != 1){
                            // Return already requested
                            $return = 1;
                        }elseif($check_return["sorting_hub_approval"] == 1 || $check_return["admin_approval"] == 1){
                            // Return request approved
                            $return = 2;
                        }
                        $data->return_status = $return;
                        $data->replacement_status = $replacement;
                        $ddate = Carbon::parse($data->updated_at);
                        $data->can_replace = true;
                        if($data->delivery_status=='delivered' && $ddate->diffInMinutes()>24*60)
                        {
                            $data->can_replace = false;
                        }

                        $data->reviewCount = \App\Review::where(['product_id'=>$data->product_id,'status'=>1])->get()->count();
                        $reviewDetail = \App\Review::where('user_id', $data->user_id)->where('product_id', $data->product_id)->first();
                        if($reviewDetail  == null){
                            $data->isReview = true;
                            $data->yourRating = 0;
                        }else{
                            $data->isReview = false;
                            $data->yourRating = $reviewDetail['rating'];
                        } 
                        return $data;
                        });
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
            'message'=>"Order not found"
        ]);
    }

    public function trackSubOrder(Request $request){
        $code = $request->sub_order_code;
        $subOrder = SubOrder::where('sub_order_code',$code)->first();
        if(!is_null($subOrder)){
            
            $request->request->add(['orderId' => $subOrder->order_id,'sub_order_id'=>$subOrder->id]);
            $order = $this->getOrderDetails($request);
            return $order;
        }

        return response()->json([
            'success'=>false,
            'data'=>[],
            'message'=>'Order not found'
        ]);

    }

    // public function getSubOrderDetails(){
    //     $orderId = $request->order_id;
    //     $order_type = $request->order_type;
    //     $order = SubOrder::LeftJoin('order_details','order_details.order_id','=','sub_orders.order_id')
    //         ->RightJoin('products','products.id','=','order_details.product_id')
    //         ->where('order_details.order_id',$orderId)
    //         ->where('order_details.order_type',$order_type)
    //         ->whereNull('order_details.deleted_at') 
    //         ->orderBy('orders.created_at', 'desc')
    //         ->select('sub_orders.delivery_status','order_details.id as order_detail_id','order_details.variation','order_details.price as price','order_details.quantity','order_details.tax as ptax','products.name','products.rating','products.unit_price','orders.coupon_discount','products.manage_by','products.id as product_id','products.thumbnail_img','order_details.id as orderdetail_id','order_details.updated_at','orders.user_id')->get();
    //         ->get();
    // }

}
