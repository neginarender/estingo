<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\User;
use App\ShortingHub;
use App\Cluster;
use App\Order;
use App\DeliveryBoy;
use App\AssignOrder;
use App\Http\Controllers\Controller;

trait OrderTrait {

	public function orders($id){

		$obj = new Controller;

		$user = User::on('mysql')->where('id', $id)->first();
		// $user = User::on('mysql2')->where('id', $id)->first();
		$date = Carbon::now()->subDays(30);
		if($user->user_type == 'staff'){

			if(auth()->user()->staff->role->name == "Cluster Hub"){

				// $sorting_hub = ShortingHub::on('mysql2')->where('cluster_hub_id', $user->id)->get();
				$sorting_hub = ShortingHub::on('mysql')->where('cluster_hub_id', $user->id)->get();
				foreach ($sorting_hub as $key => $value) {
					$pin_code[] = json_decode($value->area_pincodes);
				}
				$result = array();
				foreach ($pin_code as $array) {
				    $result = array_merge($result, $array);
				}

				// $orders = Order::on('mysql2')->when($result, function ($query, $result) {	                    					
				$orders = Order::on('mysql')->when($result, function ($query, $result) {	                    					
							$query->where('log',0)->whereIn('shipping_pin_code', $result);						
								return $query;
						})->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
					
			}elseif(auth()->user()->staff->role->name == "Sorting Hub"){
				// $sorting_hub = ShortingHub::on('mysql2')->where('user_id', $user->id)->first();
				$sorting_hub = ShortingHub::on('mysql')->where('user_id', $user->id)->first();
				$result = json_decode($sorting_hub->area_pincodes);
				if($sorting_hub->access_switch == 0){
					// $orders = Order::on('mysql2')->when($result, function ($query, $result) {	                    					
					$orders = Order::on('mysql')->when($result, function ($query, $result) {	                    					
						$query->where('log',0)->whereIn('shipping_pin_code', $result);						
							return $query;
					})->where(function($q){
						$q->where('payment_status','paid')->orwhere('payment_type','=','cash_on_delivery');
					})->where('id','>','400000')->where('created_at', '>=', $date)->where('dofo_status','=',0)->orderBy('id','desc')->pluck('id')->take(10000);

				}else{
					// $orders = Order::on('mysql2')->when($result, function ($query, $result) {	                    					
					$orders = Order::on('mysql')->when($result, function ($query, $result) {	                    					
						$query->where('log',0)->whereIn('shipping_pin_code', $result);						
							return $query;
					})->where(function($q){
						$q->where('payment_status','paid')->orwhere('payment_type','=','cash_on_delivery');
					})->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
				}

				
			}elseif(auth()->user()->staff->role->name == "Sorting Hub Manager"){
				$sorting_hub_id = auth()->user()->sortinghubmanager->sorting_hub_id;
				// $sorting_hub = ShortingHub::on('mysql2')->where('user_id', $sorting_hub_id)->first();
				$sorting_hub = ShortingHub::on('mysql')->where('user_id', $sorting_hub_id)->first();
				$result = json_decode($sorting_hub->area_pincodes);

				// $orders = Order::on('mysql2')->when($result, function ($query, $result) {	                    					
				$orders = Order::on('mysql')->when($result, function ($query, $result) {	                    					
							$query->where('log',0)->whereIn('shipping_pin_code', $result);						
								return $query;
						})->where(function($q){
							$q->where('payment_status','paid')->orwhere('payment_type','=','cash_on_delivery');
						})->where('id','>','400000')->where('created_at', '>=', $date)->where('dofo_status','=',0)->orderBy('id','desc')->pluck('id')->take(10000);
			}elseif(auth()->user()->staff->role->name == "Delivery Boy"){
				$order_id = array();
				// $getAssignOrder = AssignOrder::on('mysql2')->where('delivery_boy_id',auth()->user()->delivery_boy->id)->get(['order_id']);
				$getAssignOrder = AssignOrder::on('mysql')->where('delivery_boy_id',auth()->user()->delivery_boy->id)->get(['order_id']);
				
				if(!empty($getAssignOrder)){
					foreach($getAssignOrder as $k=>$v){
						array_push($order_id,$v['order_id']);
					} 
				// $orders = Order::on('mysql2')->whereIn('id',$order_id)->where('log',0)->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
				$orders = Order::on('mysql')->whereIn('id',$order_id)->where('log',0)->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
				}else{
					$orders = [];
				}
			}
			else{
				//  dd('test');
				// $orders = Order::on('mysql2')->where('log',0)->where('id','>','400000')->orderBy('id','desc')->pluck('id')->take(10000);
				$orders = Order::on('mysql')->where('log',0)->where('id','>','400000')->orderBy('id','desc')->pluck('id')->take(10000);
			}
		}elseif(in_array($user->email,$obj->adminArray)){
			// $orders = Order::on('mysql2')->where('log',0)->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
			$orders = Order::on('mysql')->where('log',0)->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);

		}
		else{
		   
			// $global = DB::connection('mysql2')->table('global_switch')->first();
			$global = DB::connection('mysql')->table('global_switch')->first();
			if($global->access_switch == 0){
				// $orders = Order::on('mysql2')->where(['log'=>0,'dofo_status'=>0])->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
				$orders = Order::on('mysql')->where(['log'=>0,'dofo_status'=>0])->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);

			}else{
				// $orders = Order::on('mysql2')->where(['log'=>0])->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
				$orders = Order::on('mysql')->where(['log'=>0])->where('id','>','400000')->where('created_at', '>=', $date)->orderBy('id','desc')->pluck('id')->take(10000);
			}
			
		}
		return $orders;
	}

}