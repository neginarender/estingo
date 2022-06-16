<?php

namespace App\Observers;

use App\Models\OrderDetail;
use App\SubOrder;
use DB;
class ApiOrderDetailObserver
{
    /**
     * Handle the order detail "created" event.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return void
     */
    public function created(OrderDetail $orderDetail)
    {
        $type = "grocery";
        if($orderDetail->product['category_id'] == '18' || $orderDetail->product['category_id']=='26' || $orderDetail->product['category_id']=='34' || $orderDetail->product['subcategory_id'] == '129' || $orderDetail->product['subcategory_id']==67 || $orderDetail->product['category_id']=='33' || $orderDetail->product['category_id']=='38' || $orderDetail->product['category_id']=='39' || $orderDetail->product['category_id']=='40'){
            $type = "fresh";      
        }
        $discount = $orderDetail->peer_discount;
        if(is_null($discount)){
            $discount = 0;
        }
        //Get Sub Order Id
        $sOrder = SubOrder::where(['order_id'=>$orderDetail->order_id,'delivery_name'=>$type])->first();
        //dd(['order_id'=>$orderDetail->order_id,'delivery_name'=>$type]);
        $sub_order_id = 0;
        if(!is_null($sOrder)){
            $sub_order_id = $sOrder->id;
        }

        $amount = round(($orderDetail->price-$orderDetail->peer_discount)+$orderDetail->shipping_cost,2);
        $update = SubOrder::where(['order_id'=>$orderDetail->order_id,'delivery_name'=>$type])
        ->update([
            'payable_amount'=>DB::raw("payable_amount+$amount"),
            'customer_discount'=>DB::raw("customer_discount+$discount"),
            'no_of_items'=>DB::raw("no_of_items+$orderDetail->quantity")
        ]);

        $orderDetail->sub_order_id = $sub_order_id;
        $orderDetail->order_type = $type;
        $orderDetail->save();
    }


    /**
     * Handle the order detail "updated" event.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return void
     */
    public function updated(OrderDetail $orderDetail)
    {
        // dd($orderDetail);
        // SubOrder::where(['order_id'=>$orderDetail->order_id,'delivery_name'=>$orderDetail->order_type])->update(['customer_discount'=>DB::raw("customer_discount"+$orderDetail->peer_discount)]);
    }

    /**
     * Handle the order detail "deleted" event.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return void
     */
    public function deleted(OrderDetail $orderDetail)
    {
        //
    }

    /**
     * Handle the order detail "restored" event.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return void
     */
    public function restored(OrderDetail $orderDetail)
    {
        //
    }

    /**
     * Handle the order detail "force deleted" event.
     *
     * @param  \App\OrderDetail  $orderDetail
     * @return void
     */
    public function forceDeleted(OrderDetail $orderDetail)
    {
        //
    }
}
