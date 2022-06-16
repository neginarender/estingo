<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
{
    protected static $grand_total =0;
    public function toArray($request)
    {
        $min_order_amount = (int)env("MIN_ORDER_AMOUNT");
        $free_shipping_amount = (int)env("FREE_SHIPPING_AMOUNT");

        return [
            'data' => $this->collection->map(function($data) {
                self::$grand_total+=($data->price*$data->quantity)-($data->quantity - $data->discount);
                return [
                    'id' => $data->id,
                    'product_id'=>$data->product_id,
                    'product' => [
                        'name' => $data->product->name,
                        'image' => $data->product->thumbnail_img
                    ],
                    'variation' => $data->variation,
                    'price' => (double) ($data->price),
                    'peer_discount'=> (double) ($data->price-$data->discount),
                    'tax' => (double) ((($data->price-$data->discount)*$data->product->tax)/100)*$data->quantity,
                    'shipping_cost' => (double) $data->shipping_cost,
                    'quantity' => (integer) $data->quantity,
                    'discount'=>(double) $data->discount*$data->quantity,
                    'discount_percentage'=>$data->discount_percentage,
                    'date' => $data->created_at->diffForHumans(),
                    'max_purchase_qty'=>$data->product->max_purchase_qty,
                    'links'=>[
                        'details'=>env('APP_URL')."api/v1/products/".$data->product_id,
                        'related'=>env('APP_URL')."api/v1/products/related/".$data->product_id

                        ]
                ];
            }),
            'is_continue'=>(@self::$grand_total>=$min_order_amount) ? true : false,
            'min_amount'=>$min_order_amount,
            'free_shipping_amount'=>$free_shipping_amount,
            'delivery_charge'=>(@self::$grand_total>=$free_shipping_amount) ? 0 : 29
            
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    public function price($id){

        $product = \App\Product::findOrFail($id);
        $price = $product->unit_price;
        $productStock = \App\ProductStock::where('product_id',$id)->first();
        if(!is_null($productStock)){
            $price = $productStock->price;

        }
        if(isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_CITY']) && !empty($_SERVER['HTTP_CITY'])){

            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
            if(!is_null($shortId)){
                $mappedProduct = \App\MappingProduct::where(['product_id'=>$id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->first();
                if(!is_null($mappedProduct)){
                    if($mappedProduct->selling_price!=0){
                        $price = $mappedProduct->selling_price;
                    }
                }
            }
        }

        return $price;
    }

    
}
