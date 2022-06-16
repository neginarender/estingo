<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\User;
use App\ShortingHub;
use App\Order;
use App\OrderDetail;
use App\Category;
use App\ProductChangeLog;
use App\Product;
use App\Http\Controllers\Controller;

trait AddToOrderTrait {

    public function addToOrder($id){
        $order = Order::findOrFail($id);
        if($order->payment_type == "wallet"){
            flash('You cannot add product in wallet payment.')->error();
            return back();
        }
        $categories = Category::all();
        $shorting_hub = ShortingHub::where('user_id',auth()->user()->id)->get();
        return view('orders.add_to_order',compact('order','shorting_hub','categories'));
    }

      public function addtoOrderoperations($id){
        $order = Order::findOrFail($id);
        $categories = Category::all();
        // $shorting_hub = ShortingHub::where('id',auth()->user()->shorting_hub_id)->get();
                $shorting_hub = ShortingHub::with('cluster')->paginate(10);

        return view('callcenter.add_to_order',compact('order','shorting_hub','categories'));
    }

    public function get_product_by_hub_category(Request $request){

        $id = $request->hub_id;
        $sortinghub_id = $request->hub_id;
        $added_products = \App\MappingProduct::where('sorting_hub_id', $id)->pluck('product_id')->toArray();
        $products = \App\Product::where('subcategory_id', $request->subcategory_id)->whereIn('id', $added_products)->get();
        
        return $products;
      }

    public function load_products_added_to_order(Request $request){
        //dd($request->quantity);
        $quantity = [];
        if(isset($request->quantity) && !is_null($request->quantity)){
            $quantity = $request->quantity;
        }
        
        $order = Order::findOrFail($request->order_id);
        $product_ids = $request->product_ids;
        $flash_deal_id = $request->flash_deal_id;
        $self = $request->self;
        $shortId = ['sorting_hub_id'=>$request->sorting_hub_id];
        return view('orders.load_add_to_order_products', compact('product_ids', 'flash_deal_id','shortId','order','quantity','self'));
    }

    // public function storeAddToOrder(Request $request){
    //     //dd($request->all());
    //     $order = Order::findOrFail($request->order_id);
    //     $total_item = OrderDetail::where('order_id',$request->order_id)->count()+count($request->products);
    //     $grand_total = $order->grand_total;
    //     $final_grand_total = $grand_total+$request->total_amount;
    //     $total_discount = $order->referal_discount+$request->total_discount;
    //     $shipping = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
    //     $shipping_cost = $shipping/$total_item;
        
    //     if($final_grand_total>=1500){
    //         $shipping_cost = 0;
    //     }
        
    //     DB::beginTransaction();

    //     try {
             
    //         foreach($request->products as $key=> $id){
                
    //             $orderDetail = new OrderDetail;
    //             $orderDetail->order_id = $request->order_id;
    //             $orderDetail->product_id = $id;
    //             $orderDetail->variation = $request->variant[$key];
    //             $orderDetail->price = $request->price[$key]*$request->qty[$key];
    //             $orderDetail->tax = $request->tax[$key];
    //             $orderDetail->peer_discount = $request->discount[$key];
    //             $orderDetail->sub_peer = $request->peer_commission[$key];
    //             $orderDetail->master_peer = $request->master_commission[$key];
    //             $orderDetail->quantity = $request->qty[$key];
    //             $orderDetail->shipping_type = 'home_delivery'; 
    //             $orderDetail->add_by_admin = 1;
    //             $orderDetail->save();
    //         }
    //         //update shipping cost
    //         OrderDetail::where('order_id',$request->order_id)->update(['shipping_cost'=>$shipping_cost]);
    //         //update grand total
    //         $order->grand_total = $final_grand_total;
    //         $order->referal_discount = $total_discount;
    //         $order->save();

    //         $referal_usage = \App\ReferalUsage::where('order_id',$request->order_id)->first();
    //         if(!is_null($referal_usage))
    //         {
    //             //update referal usage and OrderReferalCommission
    //             $total_customer_discount_percent = $referal_usage->discount_rate+$request->total_customer_discount_percent;
    //             $total_peer_discount = $referal_usage->discount_amount+$request->total_peer_commission;
    //             $total_peer_percent = $referal_usage->commission_rate+$request->total_peer_percent;
    //             $total_master_commission = $referal_usage->master_discount+$request->total_master_commission;
    //             $total_master_percent = $referal_usage->master_percentage+$request->total_master_percent;

    //             $dataUsage = [
    //                 'discount_rate'=>$total_customer_discount_percent,
    //                 'discount_amount'=>$total_peer_discount,
    //                 'commision_rate'=> $total_peer_percent,
    //                 'master_discount'=> $total_master_commission,
    //                 'master_percentage'=> $total_master_percent
    //             ];
                
    //             \App\ReferalUsage::where('order_id',$request->order_id)->update($dataUsage);

    //             $ReferalUsage = \App\ReferalUsage::where('order_id', $request->order_id)->first();
    //             if(!empty($ReferalUsage)){
    //                 $dataCommission = [
    //                     'order_amount'=>$order->grand_total,
    //                     'referal_code_commision'=>$ReferalUsage->commision_rate,
    //                     'referal_commision_discount'=>$ReferalUsage->discount_amount,
    //                     'master_commission'=>$ReferalUsage->master_percentage,
    //                     'master_discount'=> $ReferalUsage->master_discount
    //                 ];

    //                 \App\OrderReferalCommision::where('order_id',$request->order_id)->update($dataCommission);
                    
    //             }
    //         }
            
    //         DB::commit();
    //         // all good
    //         flash('Order updated successfully')->success();
    //         return back();

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         dd($e);
    //         // something went wrong
    //         flash('Something went wrong')->error();
    //         return back();
    //     }

    // }

    public function storeAddToOrder(Request $request){
        // dd($request->all());
        $order = Order::findOrFail($request->order_id);
        $order->edited = 1;
        $order->save();
        $OrdDetail = OrderDetail::where('order_id',$request->order_id);
        $total_item = $OrdDetail->count()+count($request->products);
        $grand_total = $OrdDetail->sum('price')-$OrdDetail->sum('peer_discount');
        $final_grand_total = $grand_total+$request->total_amount;
        $shipping = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
        $shipping_cost = $shipping/$total_item;
        
        $free_shipping_amount = env("FREE_SHIPPING_AMOUNT");
        if($final_grand_total>=$free_shipping_amount){
            $shipping_cost = 0;
        }
        
        //product_change_log -start
        $product_id = $request->products[0];
        $product_name = Product::where('id',$product_id)->pluck('name')->first();
        $product_change_log = new ProductChangeLog;
        $product_change_log->updated_by = Auth()->user()->id;
        $product_change_log->order_id = $request->order_id;
        $product_change_log->product_id = $product_id;
        $product_change_log->product_name = $product_name;
        $product_change_log->type = "added";
        $product_change_log->created_at = Carbon::now();
        $product_change_log->updated_at = Carbon::now();
        $product_change_log->save();
        //product_change_log -end
        
        DB::beginTransaction();

        try {
             
            foreach($request->products as $key=> $id){
                //Generate sub order if not exist
                $product = \App\Product::find($id);
                if(!is_null($product)){
                    if(isFreshInCategories($product->category_id) || isFreshInSubCategories($product->subcategory_id)){
                        $type = "fresh";  
                    }else{
                        $type = "grocery";
                    }
                    
                    $subOrder = \App\SubOrder::where(['order_id'=>$request->order_id,'delivery_name'=>$type])->first();
                    if(is_null($subOrder)){

                        $currentDateTime = Carbon::now();
                        if($request->delivery_type=="scheduled"){
                            $deliverydate = $request->delivery_date;
                            if(isset($request->delivery_slot_today)){
                                $delivery_slot = $request->delivery_slot_today;
                            }
                            else{
                                $delivery_slot = $request->delivery_slot_tom;
                            }
                            
                        }
                        else{
                            $deliverydate = $currentDateTime->addHour(24);
                            $delivery_slot = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                        }

                        $createSubOrder = new \App\SubOrder;
                        $createSubOrder->order_id = $request->order_id;
                        $createSubOrder->delivery_type = $request->delivery_type;
                        $createSubOrder->delivery_name = $type;
                        $createSubOrder->delivery_date = $deliverydate;
                        $createSubOrder->delivery_time = $delivery_slot;
                        $createSubOrder->status = 1;
                        $createSubOrder->save();
                        $sub_order_id = $createSubOrder->id;
                    }else{
                        $sub_order_id = $subOrder->id;
                    }
                }
                //sub order end 

                $orderDetail = new OrderDetail;
                $orderDetail->order_id = $request->order_id;
                $orderDetail->order_type = $type;
                $orderDetail->sub_order_id = $sub_order_id;
                $orderDetail->product_id = $id;
                $orderDetail->variation = $request->variant[$key];
                $orderDetail->price = $request->price[$key]*$request->qty[$key];
                $orderDetail->tax = $request->tax[$key];
                $orderDetail->peer_discount = $request->discount[$key];
                $orderDetail->sub_peer = $request->peer_commission[$key];
                $orderDetail->master_peer = $request->master_commission[$key];
                $orderDetail->quantity = $request->qty[$key];
                $orderDetail->shipping_type = 'home_delivery'; 
                $orderDetail->add_by_admin = 1;
                $orderDetail->save();
            }
            //update shipping cost
            OrderDetail::where('order_id',$request->order_id)->update(['shipping_cost'=>$shipping_cost]);
            //update grand total
            $total_discount = $OrdDetail->sum('peer_discount');
            $final_grand_total = ($OrdDetail->sum('price')+$OrdDetail->sum('shipping_cost'))-$OrdDetail->sum('peer_discount');
            $order->grand_total = $final_grand_total;
            $order->referal_discount = $total_discount;
            $order->save();

            $referal_usage = \App\ReferalUsage::where('order_id',$request->order_id)->first();
            if(!is_null($referal_usage))
            {
                //update referal usage and OrderReferalCommission
                $total_customer_discount_percent = $referal_usage->discount_rate+$request->total_customer_discount_percent;
                $total_peer_discount = $referal_usage->discount_amount+$request->total_peer_commission;
                $total_peer_percent = $referal_usage->commission_rate+$request->total_peer_percent;
                $total_master_commission = $referal_usage->master_discount+$request->total_master_commission;
                $total_master_percent = $referal_usage->master_percentage+$request->total_master_percent;

                $dataUsage = [
                    'discount_rate'=>$total_customer_discount_percent,
                    'discount_amount'=>$total_peer_discount,
                    'commision_rate'=> $total_peer_percent,
                    'master_discount'=> $total_master_commission,
                    'master_percentage'=> $total_master_percent
                ];
                
                \App\ReferalUsage::where('order_id',$request->order_id)->update($dataUsage);

                $ReferalUsage = \App\ReferalUsage::where('order_id', $request->order_id)->first();
                if(!empty($ReferalUsage)){
                    $dataCommission = [
                        'order_amount'=>$order->grand_total,
                        'referal_code_commision'=>$ReferalUsage->commision_rate,
                        'referal_commision_discount'=>$ReferalUsage->discount_amount,
                        'master_commission'=>$ReferalUsage->master_percentage,
                        'master_discount'=> $ReferalUsage->master_discount
                    ];

                    \App\OrderReferalCommision::where('order_id',$request->order_id)->update($dataCommission);
                    
                }
            }
            updateFinalOrder($request->order_id,$OrdDetail->sum('quantity'),$final_grand_total,$total_discount);
            $no_of_items_of_sub_order = $OrdDetail->where('order_type',$type)->sum('quantity');
            $total_payable_amount_of_sub_order = $OrdDetail->where('order_type',$type)->sum('price')-($OrdDetail->where('order_type',$type)->sum('shipping_cost')+$OrdDetail->where('order_type',$type)->sum('peer_discount'));
            $total_discount_of_sub_order = $OrdDetail->where('order_type',$type)->sum('peer_discount');
            updateSubOrder($no_of_items_of_sub_order,$type,$request->order_id,$total_payable_amount_of_sub_order,$total_discount_of_sub_order);

            DB::commit();
            // all good
            if(auth()->user()->user_type == "operations"){

               $id = encrypt($request->order_id);
               

                flash('Order updated successfully')->success();

                return redirect(route('callceter.show', ['id' => $id]));



            }else{

                flash('Order updated successfully')->success();
            return back();

            }

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            // something went wrong
            flash('Something went wrong')->error();
            return back();
        }

    }

    public function load_slots(Request $request){
        $type = $request->type;
        $shortingHubId = \App\ShortingHub::where('user_id',auth()->user()->id)->pluck('id')->first();
        $currentTime = Carbon::now();
        // $currentTime = '00:15:00';
        $currentTime = date('H:i:s',strtotime($currentTime));
        $todayDate = date('d-M, Y');
        $tommorowDate = date('d-M, Y', strtotime(date('Y-m-d'). ' + 1 day'));
        $todaySlot = \App\DeliverySlot::where('status','1')->where('shorting_hub_id',$shortingHubId)->where('cut_off','>',date('H:i:s',strtotime($currentTime)))->where('type',$type)->orderBy('delivery_time', 'ASC')->get();
        $availSlotTom = \App\DeliverySlot::where('status','1')->where('type',$type)->where('shorting_hub_id',$shortingHubId)->orderBy('delivery_time', 'ASC')->get();                      
        return view('orders.order_slots',compact('currentTime','todayDate','tommorowDate','shortingHubId','todaySlot','availSlotTom'));
    }
}