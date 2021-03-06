<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\PurchaseHistoryCollection;
use App\Models\Order;
use Carbon\Carbon;
use DB;

class PurchaseHistoryController extends Controller
{
    public function index($id)
    {
        return new PurchaseHistoryCollection(Order::where('user_id', $id)->latest()->get());
    }

    public function purchaseHistory($id){
        $dateS = Carbon::now()->startOfMonth()->subMonth(3);
        $dateE = Carbon::now()->startOfMonth(); 
        // return new PurchaseHistoryCollection(Order::where('user_id', $id)->latest()->get());
        // $order = new PurchaseHistoryCollection(Order::where('user_id',$id)->orderBy('date', 'ASC')->latest()->get());
       $order = DB::table('orders')
                ->LeftJoin('order_details','orders.id','=','order_details.order_id')
                ->RightJoin('products','order_details.product_id','=','products.id')
                ->where('orders.user_id','=',$id)  
                ->where('orders.created_at','>',$dateS)  
                ->orderBy('order_details.created_at', 'desc')
                // ->select('orders.code','order_details.variation','products.name','order_details.delivery_status','order_details.id as orderdetail_id')
                ->select('orders.code','order_details.shipping_cost','order_details.delivery_status as delivery_code','orders.payment_type','orders.shipping_address','order_details.variation','order_details.price','order_details.quantity','products.name','products.rating','products.unit_price','orders.coupon_discount','products.discount','products.tax','products.manage_by','products.id as product_id','products.thumbnail_img','order_details.id as orderdetail_id', 'orders.payment_status','orders.date', 'orders.grand_total','order_details.delivery_status')
                ->get();
        $orderdeatail = array();
        // foreach($order as $key=>$row){

        //     if($row->discount != '0'){
        //         $order[$key]->unit_price = $row->unit_price*(100-$row->discount)/100;
        //     }

        //     $order[$key]->price = $order[$key]->price/$row->quantity;
             
        //     // print_r($row->shipping_address);die;
        //     $order[$key]->date = date('d-m-Y', $row->date); 


        //         if($row->delivery_status == '6'){
        //             $cancelleddate = OrderDetail::where('id',$row->orderdetail_id)->first()->updated_at;
        //             $order[$key]->date = date("d-m-Y", strtotime($cancelleddate));

        //         }

        //         if($row->delivery_status == 'Failed'){
        //             $order[$key]->date = "";

        //         }
            
        // }
        if(isset($_SERVER['HTTP_X_LOCALIZATION']) && $_SERVER['HTTP_X_LOCALIZATION'] == "in"){
            // dd($order[0]->price);

            foreach($order as $key=>$value){
                $order[$key]->name = trans($value->name);
                $order[$key]->delivery_status = trans($value->delivery_status); 
                // echo $value->date;die;
                // echo date_format($value->date,"Y/m/d H:i:s");die;
            }
        }

        foreach($order as $k=>$v){
            $order[$k]->date = date("d-m-Y", $v->date);
        }
        
        $orderdeatail['data'] = $order;
        
        return $orderdeatail;
    }

    public function getOrders($id){
        $order = DB::table('orders')
        ->where('user_id',$id)
        ->where('log',0)
        ->select('orders.id','orders.code','orders.grand_total','orders.shipping_address','orders.payment_status','orders.payment_type','orders.date','orders.order_status')
        ->orderBy('created_at','desc')
        ->get();

        $orders = $order->map(function($item){
         $item->shipping_address = json_decode($item->shipping_address);
         $item->date = date('d-m-Y',$item->date);
         return $item;
        });

            return response()->json([
                'success'=>true,
                'data'=>$orders
            ]);
    }
}
