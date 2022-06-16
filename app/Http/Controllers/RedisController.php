<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;
use DB;
use Cache;
use App\FinalOrder;
use Carbon\Carbon;

class RedisController extends Controller
{

    public function __construct(){
    }

    public function createFinalOrder(){
        // order table data
        // order detail table data
        // referal usage 
        if(Cache::has('viewed')){          
          if(Cache::get('viewed') == 1){
            Cache::put('viewed', 0);
          }else{
            Cache::put('viewed', 1);
          }
        }else{
          Cache::put('viewed', 1);
        }
        $last_order_id = FinalOrder::latest('order_id')->first('order_id');
        
        if($last_order_id == null){
          if(Cache::get('viewed') == 1){
            $orders = \App\Order::where('dofo_status',0)->where(['viewed'=>1,'edited'=>0])->take(1000);
          }else{
            $orders = \App\Order::where('dofo_status',0)->where(['viewed'=>1,'edited'=>1])->take(1000);
          }
          
        }else{

          if(Cache::get('viewed') == 1){
            $orders = \App\Order::where('dofo_status',0)->where('orders.id','>',$last_order_id['order_id']);
          }else{
            $orders = \App\Order::where('dofo_status',0)->where('orders.id','>',$last_order_id['order_id']);
          }
        }
        $orders = $orders
                  ->where(['dofo_status'=>0,'log'=>0]) 
                  ->orderBy('orders.id','desc')
                  ->get();
                  
                  
      foreach($orders as $key => $order){
          $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$order->shipping_pin_code.'"]\')')
          ->selectRaw('user_id as sorting_hub_id')
          ->first('sorting_hub_id');
        $shipping = json_decode($order->shipping_address);
        $finalOrder = [
            'order_id'=>$order->id,
            'order_code'=>$order->code,
            'no_of_items'=>$order->orderDetails->sum('quantity'),
            'user_id'=>$order->user_id,
            'guest_id'=>$order->guest_id,
            'shipping_address'=>$order->shipping_address,
            'pincode'=>$order->shipping_pin_code,
            'sortinghub_id'=>(!is_null($shortId)) ? $shortId['sorting_hub_id']:0,
            'grand_total'=>$order->grand_total+$order->wallet_amount,
            'delivery_status'=>$order->order_status,
            'order_status'=>$order->order_status,
            'payment_method'=>$order->payment_type,
            'payment_status'=>$order->payment_status,
            'total_discount'=>$order->referal_discount,
            'delivery_type'=>$order->delivery_type,
            'order_date'=>$order->created_at,
            //'referal_code'=>$order->referal_code,
            'phone'=>$shipping->phone,
            'customer_name'=>$shipping->name,
            'platform'=>$order->platform
        ];
        FinalOrder::updateOrCreate([
            'order_id'=>$order->id
        ],$finalOrder);
      }

      echo "Done";
        
    }


    public function sendDataToRedis(){
      $orders = FinalOrder::all();
     
      foreach($orders as $key =>$order){
        //Redis::zAdd('orders',time(),$order->order_id);
        $finalOrder[] = [
          'id'=>$order->id,
          'order_id'=>$order->id,
          'order_code'=>$order->order_code,
          'no_of_items'=>0,
          'user_id'=>$order->user_id,
          'guest_id'=>$order->guest_id,
          'shipping_address'=>$order->shipping_address,
          'pincode'=>$order->pincode,
          'sortinghub_id'=>$order->sortinghub_id,
          'grand_total'=>$order->grand_total,
          'delivery_status'=>$order->delivery_status,
          'order_status'=>$order->order_status,
          'payment_method'=>$order->payment_method,
          'payment_status'=>$order->payment_status,
          'total_discount'=>$order->total_discount,
          'delivery_type'=>$order->delivery_type,
          'order_date'=>$order->created_at,
          'referal_code'=>$order->referal_code,
          'phone'=>$order->phone,
          'customer_name'=>$order->customer_name,
          'platform'=>$order->platform
      ];
        dd(Redis::hMSet("order:{$order->order_id}",$finalOrder));
      }
      echo "Done";
    }

    public function getRedisOrders(){
      $redis = Redis::connection();
      if(Cache::has('sliders172')){
       $collect = Cache::get('sliders172');
       $collect->add(['id'=>74,'sorting_hub_id'=>172,
       'photo'=>'uploads/sliders/9bD9olGQI2AxLGMAaTKswXO7uHfX0RSFduwyjSlT.webp',
       'mobile_photo'=>'uploads/sliders/iS8aafQ1QwKRlQAhv9RiT0h1fX1YgFZLNOGUbUYb.webp',
      'published'=>1,
    'link'=>'https://www.rozana.in/search?category=Fruits',
  'link_type'=>'category',
'created_at'=>'2021-11-05 01:05:50',
'updated_at'=>'2021-11-06 09:54:28']);
dd($collect);
      }
      $data = Redis::hGetAll('order:150449');
      dd($data);
    }


    public function createFinalOrderCron(){
      // order table data
      // order detail table data
      // referal usage 
    //   $orders = \App\Order::
    //   //Join('order_details','order_details.order_id','=','orders.id')
    //  // ->Join('referral_usages','referral_usages.order_id','=','orders.id')
    //   //select('orders.*')
    //    where('created_at', '>', Carbon::now()->subHours(1)->toDateTimeString())
    //   ->orderBy('orders.id','desc')
    //   //->skip()
    //   //->take(100000)
    //   ->get();

    // order table data
        // order detail table data
        // referal usage 
        if(Cache::has('viewed')){          
          if(Cache::get('viewed') == 1){
            Cache::put('viewed', 0);
          }else{
            Cache::put('viewed', 1);
          }
        }else{
          Cache::put('viewed', 1);
        }
        $last_order_id = FinalOrder::latest('order_id')->first('order_id');
        
        if($last_order_id == null){
          if(Cache::get('viewed') == 1){
            $orders = \App\Order::where('dofo_status',0)->where(['viewed'=>1,'edited'=>0])->take(1000);
          }else{
            $orders = \App\Order::where('dofo_status',0)->where(['viewed'=>1,'edited'=>1])->take(1000);
          }
          
        }else{

          if(Cache::get('viewed') == 1){
            $orders = \App\Order::where('dofo_status',0)->where('orders.id','>',$last_order_id['order_id']);
          }else{
            $orders = \App\Order::where('dofo_status',0)->where('orders.id','>',$last_order_id['order_id']);
          }
        }
        
        $orders = $orders
                  ->where(['dofo_status'=>0,'log'=>0]) 
                  ->orderBy('orders.id','desc')
                  ->get();
      
    foreach($orders as $key => $order){
        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$order->shipping_pin_code.'"]\')')
        ->selectRaw('user_id as sorting_hub_id')
        ->first('sorting_hub_id');
      $shipping = json_decode($order->shipping_address);
      $finalOrder = [
          'order_id'=>$order->id,
          'order_code'=>$order->code,
          'no_of_items'=>$order->orderDetails->sum('quantity'),
          'user_id'=>$order->user_id,
          'guest_id'=>$order->guest_id,
          'shipping_address'=>$order->shipping_address,
          'pincode'=>$order->shipping_pin_code,
          'sortinghub_id'=>(!is_null($shortId)) ? $shortId['sorting_hub_id']:0,
          'grand_total'=>$order->grand_total+$order->wallet_amount,
          'delivery_status'=>$order->order_status,
          'order_status'=>$order->order_status,
          'payment_method'=>$order->payment_type,
          'payment_status'=>$order->payment_status,
          'total_discount'=>$order->referal_discount,
          'delivery_type'=>$order->delivery_type,
          'order_date'=>$order->created_at,
          'referal_code'=>$order->referal_code,
          'phone'=>$shipping->phone,
          'customer_name'=>$shipping->name,
          'platform'=>$order->platform
      ];
      FinalOrder::updateOrCreate([
          'order_id'=>$order->id
      ],$finalOrder);
    }

    echo "Done";
      
  }

  //07-05-2022
  public function updateOrderStatusCron(){
      
      $today_date = date('Y-m-d');
    // $today_date = '2022-05-05';

      $updateorders = \App\Order::where('dofo_status',0)->where('log',0)->where('orders.updated_at','>',$today_date)->orderBy('orders.id','desc')->get();

        foreach($updateorders as $key => $order){
          FinalOrder::where('order_id',$order->id)
                      ->update([
                        'no_of_items'=>$order->orderDetails->sum('quantity'),
                        'grand_total'=>$order->grand_total+$order->wallet_amount,
                        'delivery_status'=>$order->order_status,
                        'order_status'=>$order->order_status,
                        'payment_method'=>$order->payment_type,
                        'payment_status'=>$order->payment_status,
                        'total_discount'=>$order->referal_discount,
                        'delivery_type'=>$order->delivery_type
                      ]);
        }
  }

     
}
