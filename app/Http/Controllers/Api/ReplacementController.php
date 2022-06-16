<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;

class ReplacementController extends Controller{

    public function order_replacement($id){
        $orderDetail = \App\OrderDetail::where('id',$id)->where('delivery_status','delivered')->first();
        if(!is_null($orderDetail)){
            $order = \App\Order::findOrFail($orderDetail->order_id);
            $order_detail = [
                'order_code'=>$order->code,
                'order_id'=>$orderDetail->order_id,
                'order_detail_id'=>$orderDetail->id,
                'product_name'=>$orderDetail->product->name,
                'qty'=>$orderDetail->quantity,
                'price'=>$orderDetail->price
            ];
            return response()->json([
                'success'=>true,
                'order_detail'=>$order_detail
            ]);
        }
        return response()->json([
            'success'=>false,
            'order_detail'=>[]
        ]);
    }

    public function storeReplacementRequest(Request $request){
        

        $order = \App\Order::findOrfail($request->order_id);
    	$distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $order->shipping_pin_code . '"]\')')->where('status',1)->pluck('id')->all();
        $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
        
        $replaceOrder = new \App\ReplacementOrder;
        $replaceOrder->order_id = $request->order_id;
        $replaceOrder->order_detail_id = $request->order_detail_id;
        $replaceOrder->sorting_hub_id = $shortId['sorting_hub_id'];
        $replaceOrder->message = $request->reason;
        //dd($request->photo);
        
        $replaceOrder->photos = $request->photos;

        if($replaceOrder->save()){

            return response()->json([
                'success'=>true,
                'message'=>'Replacement request sent successfully'
            ]);

        }

        return response()->json([
            'success'=>false,
            'message'=>'Something went wrong'
        ]);
    }

    public function uploadImages(Request $request){
        $imageName = "";
        if(!empty($request->photo)){
            foreach($request->photo as $key=>$value){
             $imageName = $value->store('frontend/images');
            }
            //echo $imageName;exit;
            return response()->json([
                'success'=>true,
                'message'=>"Image uploaded",
                'imageName'=>$imageName
            ]);
        }

        return response()->json([
            'success'=>false,
            'imageName'=>"",
            'message'=>"Image can not be blank"
        ]);

    }

    public function deliveryBoyReplacementOrders($id){
        $dboy = \App\DeliveryBoy::where('user_id',$id)->first()->id;
        $assign_orders = \App\ReplacementOrder::where('delivery_boy_id',$dboy)->where('approve',1)->pluck('order_id');
        $orders = DB::table('orders')
        ->join('replacement_orders','replacement_orders.order_id','=','orders.id')
        //->join('order_details','order_details.id','=','replacement_orders.order_detail_id')
        ->where('replacement_orders.delivery_boy_id',$dboy)
        ->select(DB::raw("count('replacement_orders.id') as no_of_item,orders.id,orders.shipping_address,orders.code,orders.payment_status,orders.order_status,orders.payment_type,orders.grand_total"))                  
        ->groupBy('orders.id')
        ->orderBy('replacement_orders.updated_at','desc')
        ->get();
        
        $orders = $orders->map(function($item){
            $item->shipping_address = json_decode($item->shipping_address);
            return $item;
        });
        return response()->json([
            'success'=>true,
            'orders'=>$orders
        ]);


    }

    public function replacementOrderDetail($id){
        $orders = \App\Order::where('id',$id)->select('id','shipping_address','billing_address','shipping_pin_code','payment_type','payment_status','order_status','grand_total','order_status','referal_discount','wallet_amount','total_shipping_cost','code','date','created_at')->get();
        $orders = $orders->map(function($item){
            unset($item->user_id);
            unset($item->billing_address);
            $item->shipping_address = json_decode($item->shipping_address);
            //$item->payment_details = json_decode($item->payment_details);
            $item->payment_method= ($item->payment_type=='razorpay') ? 'Razorpay':($item->payment_type=="wallet" ? 'Wallet':'Cash On Delivery');
            $item->pay_staus = ($item->payment_status=='paid') ? 'Paid' :'Unpaid';
            return $item; 
        });

        $replaceOrderDetail = \App\ReplacementOrder::where('order_id',$id)->pluck('order_detail_id');
        $order_detail = \App\OrderDetail::whereIn('id',$replaceOrderDetail)->get();
        $order_detail = $order_detail->map(function($item){
                unset($item->pickup_point_id);
                unset($item->product_referral_code);
                unset($item->deleted_at);
                unset($item->sub_peer);
                unset($item->master_peer);
                $product = \App\Product::where('id',$item->product_id)->first();
                $item['product_name'] = $product->name;
                $item['thumnail_img'] = $product->thumbnail_img;
                $item->price = doubleval($item->price);
                $item->shipping_cost = doubleval($item->shipping_cost);
                $item->delivery_status = str_replace('_',' ',$item->delivery_status);
                return $item;
            });

            return response()->json(
                ['success'=>true,
                'order'=>$orders,
                'order_detail'=>$order_detail
            ]);
    }

    public function update_replacement_status(Request $request){
        $update = \App\ReplacementOrder::where('order_id',$request->order_id)->update(['replaced'=>1]);
        if($update){
            return response()->json([
                'success'=>true,
                'message'=>'Replacement successfull'
            ]);
        }

        return response()->json([
            'success'=>false,
            'message'=>'Something went wrong'
        ]);
    }

}