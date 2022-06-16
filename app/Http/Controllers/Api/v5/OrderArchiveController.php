<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\v5\ArchiveCollection;
use Illuminate\Support\Facades\Response;
use DB;
use \Carbon\Carbon;
use App\ArchivedOrder;

class OrderArchiveController extends Controller
{
    //

    public function getOrderHistory(REQUEST $request){
        //dd($request->all());
        // $order = \DB::table('archive_orders')
        //          ->where('user_id',$request->user_id)
        //          ->select('id','shipping_address','shipping_pin_code','payment_type','payment_status','order_status','grand_total','referal_discount','wallet_amount','code','referal_code','referal_code','created_at');
                 
        //         if($request->search != null){
        //             $order = $order->where('code','like','%'.$request->search.'%');
        //         }
        //         $order = $order->paginate(20);  

        //         return Response::json($order, 200);

        $order = ArchivedOrder::where('user_id',$request->user_id)->select('id','shipping_address','shipping_pin_code','payment_type','payment_status','order_status','grand_total','referal_discount','wallet_amount','code','referal_code','referal_code','created_at as date')->orderBy('created_at','desc')->paginate(10);

        return new ArchiveCollection($order);
    }


    // public function getOrderDetailHistory(REQUEST $request){
    //     $order_detail = \DB::table('archive_order_details')
    //              ->leftjoin('archive_orders','archive_orders.id','=','archive_order_details.order_id')
    //              ->leftjoin('products','products.id','=','archive_order_details.product_id') 
    //              ->where('archive_order_details.order_id',$request->order_id)
    //              ->select('archive_order_details.product_id','products.name','products.photos','products.thumbnail_img','archive_order_details.price','archive_order_details.variation','archive_order_details.shipping_cost','archive_order_details.peer_discount','archive_order_details.quantity','archive_order_details.created_at');
                 
    //             if($request->search != null){
    //                 $order = $order->where('code','like','%'.$request->search.'%');
    //             }
    //             $order_detail = $order_detail->get();  

    //              return Response::json($order_detail, 200);
        

    // }


    public function getOrderDetailHistory(Request $request){
       
        $orderData = DB::table('archive_orders')
                ->where('archive_orders.id','=',$request->orderId) 
                ->orderBy('archive_orders.created_at', 'desc')
                ->select('archive_orders.id as order_id','archive_orders.code','archive_orders.user_id','archive_orders.order_type','archive_orders.order_for','archive_orders.referal_discount as discount','archive_orders.total_shipping_cost as shipping_cost','archive_orders.order_status','archive_orders.payment_type','archive_orders.shipping_address','archive_orders.wallet_amount','archive_orders.coupon_discount','archive_orders.payment_status','archive_orders.date', 'archive_orders.grand_total')->get();

        $order = DB::table('archive_orders')
                ->LeftJoin('archive_order_details','archive_orders.id','=','archive_order_details.order_id')
                ->RightJoin('products','archive_order_details.product_id','=','products.id')
                ->where('archive_orders.id','=',$request->orderId)
                // ->when($request->has('order_type'),function($query) use($request){
                //     return $query->where('archive_order_details.order_type',$request->order_type);
                // })
                ->whereNull('archive_order_details.deleted_at') 
                ->orderBy('archive_orders.created_at', 'desc')
                ->select('archive_order_details.delivery_status as delivery_code','archive_order_details.id as order_detail_id','archive_order_details.variation','archive_order_details.price as price','archive_order_details.quantity','archive_order_details.tax as ptax','products.name','products.rating','products.unit_price','archive_orders.coupon_discount','products.manage_by','products.id as product_id','products.thumbnail_img','archive_order_details.id as orderdetail_id','archive_order_details.delivery_status','archive_order_details.updated_at','archive_orders.user_id')->get();

                $schedule = DB::table('archive_sub_orders')
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
                
                $deliveryType = DB::table('archive_sub_orders')
                            ->where('order_id','=',$request->orderId)->where('status',1)->pluck('delivery_type')
                            ->first();
                            
                $tax = $order->sum('ptax');
                $total_price = $order->sum('price');

                $orders = $orderData->map(function($item) use($order,$tax,$total_price,$schedule,$deliveryType,$is_fresh,$is_grocery){
                    $item->date = date('d-m-Y',$item->date);
                    $item->grand_total = ($item->grand_total+$item->wallet_amount);
                    
                    $item->shipping_address = json_decode($item->shipping_address);

                    $item->details = $order->map(function($data) use($tax,$total_price){
                        $data->name = trans($data->name);
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
}
