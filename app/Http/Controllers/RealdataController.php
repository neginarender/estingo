<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\ProductStock;
use App\Seller;
use App\User;
Use App\ShortingHub;
use DB;
use App\Order;
use App\PeerPartner;
use App\OrderReferalCommision;
use Excel;
use Illuminate\Support\Str;
use App\SkuDataExport;
use App\SaleDataExport;
use DateTime;
use DateInterval;
use DatePeriod;

class RealdataController extends Controller
{
    public function return_cancellation_report(Request $request)
    {
        $orders = Order::where('order_status', 'cancel')->get();
        return view('real_data.return_cancellation',compact('orders'));
    }

    /*public function no_of_order_report(Request $request){
// dd($request->all());
        $pincodes = [];
        if(isset($request->zone)){
            $district = $request->zone;
        }
        if(!empty($request->district)){
            $pincodes = \App\Area::where('district_id',$request->district)->pluck('pincode')->toArray();
            $pincodes = array_unique($pincodes);
            $district = $request->district;
        }

        $date[] = date('m, Y');
        for ($i = 1; $i < 6; $i++) {
          $data = date('m, Y', strtotime("-$i month"));
          array_push($date,$data);
        }

        foreach($date as $key => $value){
            $val = explode(', ',$value );

            if(!empty($pincodes)){
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereIn('platform', ['ios', 'android'])->whereIn('shipping_pin_code',$pincodes)->get();
            }else{
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereIn('platform', ['ios', 'android'])->get();
            }
            $orderCount_app[] = count($order);
        }

        foreach($date as $key => $value){
            $val = explode(', ',$value );
            if(!empty($pincodes)){
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereNull('platform')->whereIn('shipping_pin_code',$pincodes)->get();
            }else{
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereNull('platform')->get();
            }
            $orderCount_web[] = count($order);
        }

        // $orders = Order::where('order_status', 'cancel')->get();
        return view('real_data.no_of_order',compact('orderCount_app','orderCount_web'));
    }*/


    public function no_of_order_report(Request $request)
    {
        ini_set('memory_limit','1024M');
        set_time_limit(0); //You can use 0 to remove limits
        // dd($request->all());
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        $six_months_back = date("F 1, Y", strtotime("-5 months"));
        $newdate = date('Y-m-d', strtotime($six_months_back));
        $from_dates = date('Y-m-d'); 
        if(empty($start_date))
        {
           $from_date = $newdate;
        }else{
           $from_date = $start_date;
        }

        $to_date = date('Y-m-d'); 
        if(empty($end_date))
        {
           $to_date = $from_dates;
        }else{
           $to_date = $end_date;
        }


        $pincode = array();
        if(isset($request->zone) ){
            $pincode = \App\Area::where('zone',$request->zone)->where('status',1)->select('pincode')->distinct()->get();
        }

        if(isset($request->zone) && isset($request->district)){
            $pincode = \App\Area::where('zone',$request->zone)->where('district_id',$request->district)->where('status',1)->select('pincode')->distinct()->get();
        }

        if(isset($request->zone) && isset($request->district) && isset($request->pincode)){
            $pincode[] = $request->pincode;
        }

        // $date[] = date('m, Y');
        // for ($i = 1; $i < 6; $i++) {
        //   $date_data = date('m, Y', strtotime("-$i month"));
        //   array_push($date,$date_data);
        // }


        $start = new DateTime($from_date);
        $end = new DateTime($to_date);

        $start->modify('first day of this month');
        $end->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);

        $date=array();
        foreach ($period as $dt) {
            $date_data = $dt->format("F Y");
            $date_data = date('m, Y', strtotime($date_data));
            array_push($date,$date_data);
       }

        // "03, 2022"
        // dd($date);
        foreach($date as $key => $value){
            $val = explode(', ',$value );

            if(!empty($pincode)){
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('platform', ['ios', 'android'])->whereIn('shipping_pin_code',$pincode)->get();
            }else{
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('platform', ['ios', 'android'])->get();
            }
            $orderCount_app[] = count($order);
        }
            // dd($orderCount_app);
        foreach($date as $key => $value){

            $val = explode(', ',$value );
            if(!empty($pincode)){
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereNull('platform')->whereIn('shipping_pin_code',$pincode)->get();
            }else{
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereNull('platform')->get();
            }
            $orderCount_web[] = count($order);
        }

        foreach($date as $key => $value){
            $val = explode(', ',$value );

            if(!empty($pincode)){
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('shipping_pin_code',$pincode)->distinct('user_id')->count('user_id');
            }else{
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->distinct('user_id')->count('user_id');
            }
            $transacting_user[] = $order;
        }

        foreach($date as $key => $value){
            $val = explode(', ',$value );

            $users = User::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->pluck('id')->toArray();
            // dd($users);
            if(!empty($pincode)){
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('shipping_pin_code',$pincode)->whereIn('user_id', $users)->distinct('user_id')->count('user_id');
            }else{
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('user_id', $users)->distinct('user_id')->count('user_id');
            }
            $total_user[] = $order;
        }

         foreach($date as $key => $value){
            $val = explode(', ',$value );

            $user_total = User::count('id');
            $users = User::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->pluck('id')->count('id');
            $total = ($users/$user_total)*100;
            $all_total = round($total,2);
           
            $transacting_user_percent[] = $all_total;
        }

        foreach($date as $key => $value){
            $val = explode(', ',$value );

            $users = User::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->pluck('id')->count('id');
            if(!empty($pincode)){
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('shipping_pin_code',$pincode)->distinct('user_id')->count('user_id');
            }else{
                $order = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->distinct('user_id')->count('user_id');
            }
            // dd($order);
            $total = ($order/$users)*100;
            $all_total = round($total,2);
           
            $first_user_percent[] = $all_total;
        }

        foreach($date as $key => $value){
            $val = explode(', ',$value );

            $users = User::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('user_type','partner')->pluck('id')->count('id');
            $num_of_peer[] = $users;
        }


         foreach($date as $key => $value){
            $val = explode(', ',$value );

            if(!empty($pincode)){
                $ordersum = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('shipping_pin_code',$pincode)->sum('grand_total');

                $ordertotal = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->whereIn('shipping_pin_code',$pincode)->count('id');
            }else{
                $ordersum = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->sum('grand_total');
                 $ordertotal = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->count('id');
            }

            // dd($ordersum);
            $total = (round($ordersum,2)/$ordertotal);
            $all_total = round($total,2);
           
            $average_order_val[] = $all_total;
        }

        foreach($date as $key => $value){
            $val = explode(', ',$value );

            if(!empty($pincode)){
                $ordersum = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->whereIn('shipping_pin_code',$pincode)->sum('grand_total');

                $peercount = DB::table('orders')
                        ->join('order_referal_commision', 'orders.id', '=', 'order_referal_commision.order_id')
                        ->whereMonth('order_referal_commision.created_at', '=', $val[0])
                        ->whereYear('order_referal_commision.created_at', '=', $val[1])
                        ->whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))
                        ->whereIn('orders.shipping_pin_code',$pincode)
                        ->distinct('order_referal_commision.refral_code')->count('order_referal_commision.id');
            }else{
                $ordersum = \App\Order::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))->sum('grand_total');
                 $peercount = \App\OrderReferalCommision::whereMonth('created_at', '=', $val[0])->whereYear('created_at', '=', $val[1])->whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->distinct('refral_code')->count('id');
            }

            // dd($ordersum);
            $total = (round($ordersum,2)/$peercount);
            $all_total = round($total,2);
           
            $average_order_peerwise[] = $all_total;
        }
        return view('real_data.no_of_order',compact('orderCount_app','orderCount_web','transacting_user','total_user','transacting_user_percent','first_user_percent','num_of_peer','average_order_val', 'average_order_peerwise', 'start_date', 'end_date'));
    }
}
