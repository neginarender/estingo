<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\ArchivedOrder;
use Carbon\Carbon;
use DB;

class OrderArchiveController extends Controller
{
    //

    public function __construct(){

    }

    public function archiveOrders(Request $request)
    {  
        $id = Auth()->user()->id; 
        $msc = microtime(true);
        $payment_status = null;
        $delivery_status = null;
        $pay_type = null;
        $sort_search = null;
        $edit_view = null;

        $global = DB::table('global_switch')->first();
        $orders = ArchivedOrder::when($global->access_switch == 0,function($query){
                       return $query->where('dofo_status','=',0);
                    })
                    ->orderBy('created_at', 'desc');
        if(Auth()->user()->user_type != 'admin'){
            $orders = $orders->where('sorting_hub_id',Auth()->user()->id);
        }
        if ($request->payment_type != null){
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }

        if ($request->edit_view != null){
            if($request->edit_view == 'viewed'){
                $orders = $orders->where(['viewed'=>1,'edited'=>0]);
            }elseif($request->edit_view == 'edited'){
                $orders = $orders->where(['viewed'=>1,'edited'=>1]);
            }
            
            $edit_view = $request->edit_view;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('order_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if($request->pay_type!=null){
            $orders = $orders->where('payment_type', $request->pay_type);
            $pay_type = $request->pay_type;
        }

        if ($request->has('search') && !empty($request->search)){
            $sort_search = $request->search;
            $orders = $orders->where(function($query) use($sort_search){
                $query->where('code', 'like', '%'.$sort_search.'%')
                ->orWhere('shipping_address->name','like', '%'.$sort_search.'%')
                ->orWhere('shipping_address->phone', 'like', '%'.$sort_search.'%');
            });
            
        }

        
        if($request->dateRangeStart != null && $request->dateRangeEnd != null && empty($request->search)){

            $newStartDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->dateRangeStart)->toDateTimeString(); 

            $start_time = date('Y-m-d', strtotime($newStartDate)); 

            $end_time = date('Y-m-d', strtotime($request->dateRangeEnd));

            $currentTime = time();

            $startTime = strtotime($start_time);

            $endTime = strtotime($end_time); 

            $days_between = ceil(abs($endTime - $startTime) / 86400);

            if($start_time == $end_time){

                $endDayFromCurrentDate = 0;

                $startDayFromCurrentDate = 0;  

            }         

            else{

                $startDayFromCurrentDate = ceil(abs($startTime - $currentTime) / 86400) -1;

                $endDayFromCurrentDate = null;

            }

            if($request->dateRangeEnd != date('d-m-Y')){

                $endDayFromCurrentDate = ceil(abs($endTime - $currentTime) / 86400) -1;

            }

           

             $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->dateRangeStart)))->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->dateRangeEnd)));

            

            //print_r($currentTime);

        }else{

            $startDayFromCurrentDate = null;

            $endDayFromCurrentDate = null;

            $days_between = 89;

            $dt = \Carbon\Carbon::now();

            $dt->toDateTimeString();  

            if(empty($request->search)){

                //$orders = $orders->whereDate('updated_at', '>', $dt->subDays($days_between)->format('Y-m-d'))->orwhereDate('created_at', '>', $dt->subDays($days_between)->format('Y-m-d'));

            }

            



        }

         $orders = $orders->latest()->paginate(25);
        if(Auth()->user()->user_type == "staff"){
            foreach($orders as $k=>$v){
                if($v->payment_type == "letzpay_payment" && $v->payment_status == "unpaid"){
                    // unset($orders[$k]);

                }
            }

        }
        
         return view('orders.archived_orders', compact('orders','payment_status','delivery_status', 'admin_user_id', 'pay_type','realdates', 'days_between', 'endDayFromCurrentDate', 'startDayFromCurrentDate','sort_search','edit_view'));
    }
}
