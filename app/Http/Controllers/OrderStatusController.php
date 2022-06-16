<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\OrderStatus;
use App\OrderDetail;
use App\SubOrder;
use DB;
use Cache;
use Auth;
use App\ShortingHub;
use App\AssignSuborder;

class OrderStatusController extends Controller
{
    //

    public function __construct(){

    }

    public function newOrders(REQUEST $request,$order_status = null,$order_status_id = null){
        // dd(decrypt($order_status_id));
        $order_status_key = array((decrypt($order_status_id)+1));
        $order_status = OrderStatus::whereIn('rank',$order_status_key)->first();
        $current_order_status = decrypt($order_status_id);
        $start_date = "";
        $end_date = "";
        $pincode = "";
        $sort_search = "";
        $new_orders = DB::table('sub_orders')
                    ->leftjoin('orders','orders.id','=','sub_orders.order_id')
                    ->leftjoin('order_status','order_status.id','=','sub_orders.order_status')
                    ->leftjoin('order_assign_suborder','order_assign_suborder.suborder_id','=','sub_orders.id')
                    ->where('orders.dofo_status',0);

                    if(decrypt($order_status_id) >1){
                        $new_orders = $new_orders->where('order_assign_suborder.order_status',decrypt($order_status_id));
                    }else{
                        $new_orders = $new_orders->where('sub_orders.order_status',decrypt($order_status_id));
                    }
                    

                    $new_orders = $new_orders->orderBy('orders.created_at','DESC')
                    ->when(!empty($request->order_type),function($query) use($request){
                        $query->where('delivery_name',$request->order_type);
                    });

                    if(Auth::user()->user_type != "admin"){
                       if(Auth::user()->staff->role_id == 3){
                        $pincode = ShortingHub::where('user_id',Auth::user()->id)->first('area_pincodes');
                        $new_orders->whereIn('orders.shipping_pin_code',json_decode($pincode['area_pincodes']));
                       }

                    }
                    

                    if ($request->has('search') && !empty($request->search)){
                        $sort_search = $request->search;
                        $new_orders = $new_orders->where(function($query) use($sort_search){
                            $query->where('orders.code', '=', $sort_search)
                            ->orWhere('orders.shipping_address->name','like', '%'.$sort_search.'%')
                            ->orWhere('orders.shipping_address->phone', 'like', '%'.$sort_search.'%');
                        });
            
                    }
                    if($request->has("dateRangeStart") && $request->has("dateRangeEnd")){
                        if(!empty($request->dateRangeStart)){
                            $date_time = explode("-",$request->daterange);
                            //dd($request->dateRangeStart);
                            $start_date = date('Y-m-d H:i:s',strtotime($request->dateRangeStart));
                            $end_date = date('Y-m-d H:i:s',strtotime($request->dateRangeEnd));
                            // $new_orders->whereBetween('sub_orders.created_at',array($start_date,$end_date));
                        }
                        
                    }
        //$this->downloadOrderExcel($new_orders);
        $order_type = $request->order_type;
        $new_orders = $new_orders->select('orders.code','order_assign_suborder.assign_to','sub_orders.order_id','sub_orders.id AS sub_order_id','sub_orders.delivery_name','sub_orders.order_status','order_status.name','order_status.id AS order_status_id','sub_orders.payable_amount','sub_orders.no_of_items','sub_orders.payment_status','sub_orders.payment_mode','orders.shipping_address','sub_orders.delivery_status','sub_orders.delivery_type',DB::raw('CONCAT(sub_orders.delivery_date,\' \',sub_orders.delivery_time) AS expected_delivery'),'orders.shipping_pin_code','sub_orders.created_at')
                      ->paginate(20);
        
        return view('orders.new_order',compact('new_orders','start_date','end_date','order_status','current_order_status','order_type','sort_search'));
    }


    public function changeOrderStatus(REQUEST $request){
        $sub_order_id = $request->sub_order_id;
        $order_status_id = $request->order_status_id;
        // DB::beginTransaction();
        $response = array();
        try{
            // $update_suborder = SubOrder::where('id',$sub_order_id)->update(['order_status'=>$order_status_id]);
            // $update_orderdetail = OrderDetail::where('sub_order_id',$sub_order_id)->get();
            // dd($update_orderdetail);
            //$update_orderdetail = OrderDetail::where('sub_order_id',$sub_order_id)->update(['order_status'=>$order_status_id]);
            $get_assign_suborder = AssignSuborder::where('suborder_id',$sub_order_id)->first();
            if(!empty($get_assign_suborder)){
                $get_assign_suborder->order_status = $order_status_id;
            }else{
                $get_assign_suborder = new AssignSuborder;
                $get_assign_suborder->suborder_id = $sub_order_id;
                $get_assign_suborder->order_status = $order_status_id;
            }


            
            if($get_assign_suborder->save()){
                // DB::commit();
                $response['message'] = "order status has been changed.";
                $response['status'] = true;    
            }

        }catch(\Exception $e){
            // DB::rollback();
            $response['message'] = $e->getMessage();
            $response['status'] = false;
        }

        return $response;

    }

    public function assignOrder(REQUEST $request){
        $sub_order_id = $request->sub_order_id;
        $delivery_boy_id = $request->delivery_boy_id;
        $dboy = \App\DeliveryBoy::find($delivery_boy_id);
        // $subOrder = SubOrder::where('id',$sub_order_id)->first();
        $subOrder = AssignSuborder::where('suborder_id',$sub_order_id)->first();
        DB::beginTransaction();
        $response = array();
        try{
            $update_assign_suborder = AssignSuborder::where('suborder_id',$sub_order_id)->update(['assign_to'=>$delivery_boy_id,'order_status'=>4]);
            if($update_assign_suborder){
                DB::commit();
                generateNotification($subOrder->sub_order_code,$subOrder->order_id,$dboy->user_id,$subOrder->delivery_status);
                $response['message'] = "order  has been assigned.";
                $response['status'] = true;    
            }

        }catch(\Exception $e){
            DB::rollback();
            $response['message'] = $e->getMessage();
            $response['status'] = false;
        }

        return $response;


    }


    public function downloadOrderExcel($new_orders){
        $name =  public_path(). "/orders_report/report2.csv";
        $headers = [
            'Content-Disposition' => 'attachment; filename='. $name,
        ];
        $columns = [
            'Order Code',
            'Assign To',
            'SubOrder Id',
            'Delivery Name',
            'Order Status',
            'Order Status Name',
            'Order Status Id',
            'Payable Amount',
            'Items',
            'Payment Status',
            'Payment Mode',
            'Shipping Address',
            'Delivery Status',
            'Delivery Type',
            'Expected Delivery',
            'Shipping Pincode',
            'created_at'
        ];
        $file = fopen($name, 'w');
        fputcsv($file, $columns);

        $new_orders->orderBy('orders.code')->select('orders.code','sub_orders.assign_to','sub_orders.id AS sub_order_id','sub_orders.delivery_name','sub_orders.order_status','order_status.name','order_status.id AS order_status_id','sub_orders.payable_amount','sub_orders.no_of_items','sub_orders.payment_status','sub_orders.payment_mode','orders.shipping_address','sub_orders.delivery_status','sub_orders.delivery_type',DB::raw('CONCAT(sub_orders.delivery_date,\' \',sub_orders.delivery_time) AS expected_delivery'),'orders.shipping_pin_code','sub_orders.created_at')
                    ->chunk(100000, function ($new_orders) use ($file){
            foreach ($new_orders->toArray() as $d) {
                dd($d);
            $new_orders = $d;

            //unset($new_orders['id']);
            fputcsv($file, $new_orders);
            }
        }); 

        fclose($file);
        

    }
}
