<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\SaleTallyCollection;
use App\Order;
use App\OrderDetail;
use App\Product;
use App\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleTallyController extends Controller
{
    public function getOrderDetails(Request $request){
            $array = array();
            $totalAmount = 0;
            $discountOnSale = 0;
            $outputIGST = 0;

            $order = Order::where('code',$request->order_code)->first();
            if(is_null($order)){
                return response()->json([
                    'status'=>false,
                    'data' => $array,
                    'message' => 'Order Code not exist.'
                ]);
            }else{
            $userDetail = json_decode($order->shipping_address);
            $billingDetail = json_decode($order->billing_address);

            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$userDetail->postal_code.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            $shortId = $shortId['sorting_hub_id'];

            $array['order_code'] = $request->order_code;
            $array['date'] = date('d-m-Y',strtotime($order->created_at));

            $array['shipTo'] = [
                'name' => empty($userDetail->name)?NULL:$userDetail->name,
                'email' => empty($userDetail->email)?NULL:$userDetail->email,
                'address' => empty($userDetail->address)?NULL:$userDetail->address,
                'state' =>  empty($userDetail->state)?NULL:$userDetail->state,
                'city' => empty($userDetail->city)?NULL:$userDetail->city,
                'postal_code'=> empty($userDetail->postal_code)?NULL:$userDetail->postal_code,

            ];

            $array['billTo'] = [
                'name' => empty($billingDetail->name)?NULL:$billingDetail->name,
                'email' => empty($billingDetail->email)?NULL:$billingDetail->email,
                'address' => empty($billingDetail->address)?NULL:$billingDetail->address,
                'state' =>  empty($billingDetail->state)?NULL:$billingDetail->state,
                'city' => empty($billingDetail->city)?NULL:$billingDetail->city,
                'postal_code'=> empty($billingDetail->postal_code)?NULL:$billingDetail->postal_code,

            ];

            $array['payment_detail'] =[
                'payment_type' => $order->payment_type,
                'payment_status' => $order->payment_status,
            ];

            $orderDetail = OrderDetail::where('order_id',$order->id)->get();
            $i = 1;
            foreach($orderDetail as $detail){
                
                $productDetail = Product::where('id', $detail->product_id)->where('published',1)->first();

                $tax_type = $productDetail->tax_type;
                $percent = '';
                if($tax_type == 'percent'){
                    $percent = '%';
                }
                $tax_amount = $productDetail->tax;

                $productstock = ProductStock::where('product_id', $detail->product_id)->select('price')->first();
                if(!empty($shortId)){
                    $productM = \App\MappingProduct::where(['sorting_hub_id'=>$shortId,'product_id'=>$detail->product_id])->first();

                    $stock_price = $productM['selling_price'];
                    if($stock_price == 0){
                        $stock_price = $productstock['price'];
                    }  
                }else{
                    $stock_price = $productstock['price'];  
                }
                $taxableValue = 0;
                if($tax_amount != '0.00'){
                    if($tax_type == 'percent'){
                        $ratePrice = $stock_price-(($stock_price*$tax_amount)/100); 
                        $taxableValue = $this->calculateTaxableValue($ratePrice,$tax_amount);
                        $outputIGST += $taxableValue;                        
                    }else{
                        $ratePrice = $stock_price-$tax_amount;
                    }
                }else{
                    $ratePrice = $stock_price;
                }

                $amount  = $ratePrice*$detail->quantity;
                $totalAmount += $amount;
                $discountOnSale += $detail->peer_discount;

                $array['order_detail'][] = [
                    'sno.' => $i,
                    'description_of_goods' => $productDetail->name.' ('.$detail->variation.')',
                    'GST_rate' => $productDetail->tax.''.$percent,
                    'shipped_quantity'=> $detail->quantity.' '.$productDetail->unit,
                    'billed_quantity'=> $detail->quantity.' '.$productDetail->unit,
                    'rate_with_tax' => $stock_price,
                    'rate' => $ratePrice,
                    'per' => $productDetail->unit,
                    'amount' => $amount,
                    'taxablevalue' => $taxableValue
                ];
                $i++;
            }

            $grandTotal = $totalAmount - $discountOnSale;

            $arr = explode('.',$grandTotal);
            if($arr[1] > 50){
                $Total = ceil($grandTotal);
            }else{
                $Total = floor($grandTotal);
            }

            if($grandTotal > $Total){
                $rountOff = $grandTotal-$Total;
            }else{
                $rountOff = $Total-$grandTotal;
            }
            

            $array['TotalAmount'] = $totalAmount;
            $array['outputIGST'] = round($outputIGST,2);
            $array['discountOnSale'] = $discountOnSale;
            // $array['grandTotal'] = $grandTotal;
            $array['rountOff'] = round($rountOff,2);
            $array['Total'] = $Total;

        return response()->json([
            'status'=>true,
            'data' => $array,
            'message' => 'Order detail for tally.'
        ]);
        }
    }

    public function calculateTaxableValue($discountValue,$tax_amount){
        $taxableValue = ($discountValue*100)/(100+$tax_amount);
        $totalTaxValue = ($taxableValue * $tax_amount)/100;
        return $totalTaxValue;
    }

    

}
