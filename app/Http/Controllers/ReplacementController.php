<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Order;
use App\OrderDetail;
use App\ReplacementOrder;

class ReplacementController extends Controller
{

	public function replacement_requests(){
		$sorting_hub_id = auth()->user()->id;

		$replacement_requests = ReplacementOrder::where('sorting_hub_id',$sorting_hub_id)->orderBy('created_at','desc')->paginate(50);
		return view('orders.replace_product',compact('replacement_requests'));
	}

	public function approve_replacement_request(REQUEST $request){
		$update = ReplacementOrder::where('id',$request->id)->update(['approve'=>$request->approve]);
		if($update){
			return "1";
		}

		return "0";
	}

	public function order_replacement($id)
    {
        $order = Order::findOrfail(decrypt($id));
        return view('frontend.replacement.order_replacement',compact('order'));
    }

    public function getOrderReplaceDetail(REQUEST $request){
        $orderDeatilId = $request->product_detail_id;

        $orderDetail = OrderDetail::where('id',$orderDeatilId)->first();
        return $orderDetail;

    }


    public function storeOrderReplace(REQUEST $request){
        $request->validate([
            'order_details'=>'required',
            'qty'=>'required',
            'price'=>'required',
            'reason'=>'required'
        ],
        [
            'order_details.required'=>"Choose product",
            'qty.required'=>'Quantity can not be empty',
            'price.required'=>'Price is can not be empty',
            'reason.required'=>'Reason can not be empty'
        ]);

    	$order = Order::findOrfail($request->order_id);
    	$distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $order->shipping_pin_code . '"]\')')->where('status',1)->pluck('id')->all();
        $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');

       $replaceOrder = new ReplacementOrder;
       $imageName = [];
       $replaceOrder->order_id = $request->order_id;
       $replaceOrder->order_detail_id = $request->order_details;
       $replaceOrder->sorting_hub_id = $shortId['sorting_hub_id'];
       $replaceOrder->message = $request->reason;
       if(!empty($request->photo)){
           foreach($request->photo as $key=>$value){
            $imageName[$key] = $value->store('frontend/images');
           }
       }
       $replaceOrder->photos = json_encode($imageName);
       if($replaceOrder->save()){
        flash(translate('Message has been sent successfully'))->success();
        

       }else{
        flash(translate('Something went wrong'))->error();
       }
       return back();


        
    }

    public function assign_order(REQUEST $request){
        $id = $request->id;
        $update = ReplacementOrder::where('id',$id)->update(['delivery_boy_id'=>$request->delivery_boy]);
        if($update==1){
            return "1";
        }
        return "0";
    }




}