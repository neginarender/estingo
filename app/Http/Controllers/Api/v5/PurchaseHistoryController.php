<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\PurchaseHistoryCollection;
use App\Http\Resources\v5\OrderHistoryCollection;
use App\Models\Order;
use Carbon\Carbon;
use App\SubOrder;
use App\User;
use DB;



class PurchaseHistoryController extends Controller
{
    public function index($id)
    {
        return new PurchaseHistoryCollection(Order::where('user_id', $id)->where('log',0)->latest()->get());
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
                ->where('orders.log',0)
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

    /*public function getOrders($id){
        $user_type = User::where('id',$id)->pluck('user_type')->first();
        $order = DB::table('orders')
                 ->where('user_id',$id)
                 ->where('log',0);
                 
        if($user_type == 'partner'){
            $order = $order->where('order_type','self');
        }else{ 
            $order = $order->orWhere('order_for',$id);
        }
        
        $order = $order->select('orders.id','orders.code','orders.order_type','orders.grand_total','orders.shipping_address','orders.payment_status','orders.referal_discount as discount','orders.payment_type','orders.date','orders.order_status','orders.wallet_amount')
            ->orderBy('created_at','desc');
            return new OrderHistoryCollection($order->get()); 


        $orders = $order->map(function($item){
         $item->shipping_address = json_decode($item->shipping_address);
         $item->date = date('d-m-Y H:i:s',$item->date);
         $item->grand_total = ($item->grand_total+$item->wallet_amount);
        // $deliveryType = DB::table('sub_orders')
        //     ->where('order_id','=',$item->id)->where('status',1)->pluck('delivery_type')
        //     ->first();
        // $item->deliveryType = empty($deliveryType)?'':$deliveryType;

            $schedule = DB::table('sub_orders')
                ->where('order_id','=',$item->id)->where('status',1)
                ->get();

            $item->is_fresh = 0;
            $item->is_grocery = 0;
            foreach($schedule as $data){
                if($data->delivery_name == 'fresh'){
                    $item->is_fresh = 1;
                }
                if($data->delivery_name == 'grocery'){
                    $item->is_grocery = 1;
                }
            }

            // if($item->deliveryType != ''){
            //     $slot = array();
            //     foreach($schedule as $value){
            //         $slot[$value->delivery_name]['sub_order_code'] = $value->sub_order_code;
            //         $slot[$value->delivery_name]['delivery_date'] = $value->delivery_date;
            //         $slot[$value->delivery_name]['delivery_time'] = $value->delivery_time;
            //     }

            // }else{
            //     $slot = array();
            //     $slot['fresh']['delivery_date'] = '';
            //     $slot['fresh']['delivery_time'] = '';
            //     $slot['grocery']['delivery_date'] = '';
            //     $slot['grocery']['delivery_time'] = '';
            // }
            // $item->delivery_detail = $slot;
         return $item;
        });

            return response()->json([
                'success'=>true,
                'data'=>$orders
            ]);
    }*/

    public function getOrders($id){
        $user_type = User::where('id',$id)->pluck('user_type')->first();

        
        if($user_type == 'partner'){
            $order = DB::table('orders')
            ->where('user_id',$id)
            ->where('order_type','self')
            ->where('log',0)
            ->select('orders.id','orders.code','orders.order_type','orders.grand_total','orders.shipping_address','orders.payment_status','orders.referal_discount as discount','orders.payment_type','orders.date','orders.order_status','orders.wallet_amount')
            ->orderBy('created_at','desc')
            ->get();
        }else{ 
            $order = DB::table('orders')
            ->where('user_id',$id)
            ->where('log',0)
            ->orWhere('order_for',$id)
            ->select('orders.id','orders.code','orders.order_type','orders.grand_total','orders.shipping_address','orders.payment_status','orders.referal_discount as discount','orders.payment_type','orders.date','orders.order_status','orders.wallet_amount')
            ->orderBy('created_at','desc')
            ->get();
        }

        $orders = $order->map(function($item){
         $item->shipping_address = json_decode($item->shipping_address);
         $item->date = date('d-m-Y H:i:s',$item->date);
         $item->grand_total = ($item->grand_total+$item->wallet_amount);


            // $schedule = DB::table('sub_orders')
            //     ->where('order_id','=',$item->id)->where('status',1)
            //     ->get();

            // $item->is_fresh = 0;
            // $item->is_grocery = 0;
            // foreach($schedule as $data){
            //     if($data->delivery_name == 'fresh'){
            //         $item->is_fresh = 1;
            //     }
            //     if($data->delivery_name == 'grocery'){
            //         $item->is_grocery = 1;
            //     }
            // }

            
         return $item;
        });

            return response()->json([
                'success'=>true,
                'data'=>$orders
            ]);
    }

    public function getOrdersTest($id){
        $user_type = User::where('id',$id)->pluck('user_type')->first();

        $order = DB::table('orders')
            ->where('user_id',$id)
            ->where('log',0);
            
        if($user_type == 'partner'){
            $order = $order->where('order_type','self');
        }else{ 
            $order = $order->orWhere('order_for',$id);
        }

        $order = $order->select('orders.id','orders.code','orders.order_type','orders.grand_total','orders.shipping_address','orders.payment_status','orders.referal_discount as discount','orders.payment_type','orders.date','orders.order_status','orders.wallet_amount')
                       ->orderBy('created_at','desc')
                       ->get();

        return new OrderHistoryCollection($order);
        
        $orders = $order->map(function($item){
         $item->shipping_address = json_decode($item->shipping_address);
         $item->date = date('d-m-Y H:i:s',$item->date);
         $item->grand_total = ($item->grand_total+$item->wallet_amount);
        // $deliveryType = DB::table('sub_orders')
        //     ->where('order_id','=',$item->id)->where('status',1)->pluck('delivery_type')
        //     ->first();
        // $item->deliveryType = empty($deliveryType)?'':$deliveryType;

            $schedule = DB::table('sub_orders')
                ->where('order_id','=',$item->id)->where('status',1)
                ->get();

            $item->is_fresh = 0;
            $item->is_grocery = 0;
            foreach($schedule as $data){
                if($data->delivery_name == 'fresh'){
                    $item->is_fresh = 1;
                }
                if($data->delivery_name == 'grocery'){
                    $item->is_grocery = 1;
                }
            }

            // if($item->deliveryType != ''){
            //     $slot = array();
            //     foreach($schedule as $value){
            //         $slot[$value->delivery_name]['sub_order_code'] = $value->sub_order_code;
            //         $slot[$value->delivery_name]['delivery_date'] = $value->delivery_date;
            //         $slot[$value->delivery_name]['delivery_time'] = $value->delivery_time;
            //     }

            // }else{
            //     $slot = array();
            //     $slot['fresh']['delivery_date'] = '';
            //     $slot['fresh']['delivery_time'] = '';
            //     $slot['grocery']['delivery_date'] = '';
            //     $slot['grocery']['delivery_time'] = '';
            // }
            // $item->delivery_detail = $slot;
         return $item;
        });

            return response()->json([
                'success'=>true,
                'data'=>$orders
            ]);
    }

    public function getOrdersToCustomer($id){
        // $order = DB::table('orders')
        // ->LeftJoin('peer_partners','orders.user_id','=','peer_partners.user_id')
        // ->LeftJoin('order_referal_commision','orders.id','=','order_referal_commision.order_id')
        // ->where('orders.user_id',$id)
        // ->where('orders.order_type','other')
        // ->where('orders.log',0)
        // ->select('orders.id','orders.code','orders.order_type','orders.order_for','orders.grand_total','orders.wallet_amount','orders.shipping_address','orders.referal_discount as discount','orders.payment_status','orders.payment_type','orders.date','orders.order_status','peer_partners.code as peerCode','order_referal_commision.referal_commision_discount as commision')
        // ->orderBy('orders.created_at','desc')
        // ->groupBy('orders.id')
        // ->get();

        $order = DB::table('orders')
        ->LeftJoin('order_referal_commision','orders.id','=','order_referal_commision.order_id')
        ->where('orders.user_id',$id)
        ->where('orders.order_type','other')
        ->where('orders.log',0)
        ->select('orders.id','orders.code','orders.grand_total','orders.wallet_amount','orders.shipping_address','orders.referal_discount as discount','orders.date','order_referal_commision.referal_commision_discount as commision')
        ->orderBy('orders.created_at','desc')
        ->groupBy('orders.id')
        ->get();

        $orders = $order->map(function($item){
        $item->shipping_address = json_decode($item->shipping_address);
        $item->date = date('d-m-Y H:i',$item->date);
        $item->grand_total = ($item->grand_total+$item->wallet_amount);

         return $item;
        });

            return response()->json([
                'success'=>true,
                'data'=>$orders
            ]);
    }

    public function getOrdersByCustomer($id){
        $user = User::where('peer_user_id',$id)->where('banned',0)->where('status',1)->pluck('id');

        $order = DB::table('orders')
        ->LeftJoin('referral_usages','orders.id','=','referral_usages.order_id')
        ->LeftJoin('order_referal_commision','orders.id','=','order_referal_commision.order_id')
        ->where('orders.order_type','self')
        ->whereIn('orders.user_id',$user)
        ->where('orders.log',0)
        ->select('orders.id','orders.code','orders.user_id','orders.order_type','orders.order_for','orders.grand_total','orders.wallet_amount','orders.referal_discount as discount','orders.shipping_address','orders.payment_status','orders.payment_type','orders.date','orders.order_status','referral_usages.referal_code as peerCode','order_referal_commision.referal_commision_discount as commision')
        ->orderBy('orders.created_at','desc')
        ->groupBy('orders.id')
        ->get();
      
        $orders = $order->map(function($item){
        $item->shipping_address = json_decode($item->shipping_address);
        $item->date = date('d-m-Y H:i',$item->date);
        $item->grand_total = ($item->grand_total+$item->wallet_amount);
        // $schedule = DB::table('sub_orders')
        //         ->where('order_id','=',$item->id)->where('status',1)
        //         ->get();

        // $item->is_fresh = 0;
        // $item->is_grocery = 0;
        // foreach($schedule as $data){
        //     if($data->delivery_name == 'fresh'){
        //         $item->is_fresh = 1;
        //     }
        //     if($data->delivery_name == 'grocery'){
        //         $item->is_grocery = 1;
        //     }
        // }

        $customer = User::where('users.id',$item->user_id)
        ->LeftJoin('addresses','users.id','=','addresses.user_id')
        ->select('users.name','users.email','users.phone','addresses.id','addresses.address','addresses.state','addresses.city','addresses.postal_code','addresses.tag','addresses.set_default')
        ->first();
        $detail = array([
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'address_id' => $customer->id,
            'address' => $customer->address,
            'state' => $customer->state,
            'city' => $customer->city,
            'postal_code' => $customer->postal_code,
            'tag' => $customer->tag,
            'set_default' => $customer->set_default
        ]);
        $item->customer_detail = $detail;

        return $item;
        });

            return response()->json([
                'success'=>true,
                'data'=>$orders
            ]);
    }
}
