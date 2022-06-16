<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CartCollection extends ResourceCollection
{
    protected static $grand_total =0;
    protected static $final_amount = 0;
    protected static $mrp = 0;
    protected static $total_discount = 0;
    protected static $total_tax = 0;
    public function toArray($request)
    {
        $min_order_amount = (int)env("MIN_ORDER_AMOUNT");
        $free_shipping_amount = (int)env("FREE_SHIPPING_AMOUNT");

        return [
            'data' => $this->collection->map(function($data) {
                self::$grand_total+=($data->price*$data->quantity)-($data->quantity - $data->discount);
                self::$mrp += $data->price*$data->quantity;
                self::$total_discount +=$data->discount*$data->quantity;
                self::$total_tax += ($data->price-$data->discount)*$data->product->tax/100;
                self::$final_amount = @self::$mrp-@self::$total_discount;
                return [
                    'id' => $data->id,
                    'product_id'=>$data->product_id,
                    'product' => [
                        'name' => trans($data->product->name),
                        'image' => $data->product->thumbnail_img
                    ],
                    'variation' => $data->variation,
                    'price' => (double) ($data->mrp),
                    'peer_discount'=> (string) ($data->price),
                    'tax' => strval(((($data->price-$data->discount)*$data->product->tax)/100)*$data->quantity),
                    'shipping_cost' => (double) $data->shipping_cost,
                    'quantity' => (integer) $data->quantity,
                    'discount'=>strval($data->discount*$data->quantity),
                    // 'date' => $data->created_at->diffForHumans(),
                    'date' => date('d-m-Y',strtotime($data->created_at)),
                    'max_purchase_qty'=>maxPurchaseQty($data->product_id,$_SERVER['HTTP_SORTINGHUBID']),
                    'links'=>[
                        'details'=>env('APP_URL')."/api/v5/products/".$data->product_id,
                        'related'=>env('APP_URL')."/api/v5/products/related/".$data->product_id

                        ],
                    'in_stock' => empty($this->checkInStock($data->product_id)) ? false : true,
                    'is_available' => empty($this->checkavailable($data->product_id)) ? false : true,
                    'self' => $data->peer_status
                ];
            }),
            // 'mrp'=> @self::$mrp,
            // 'final_amount' => @self::$final_amount,
            'is_continue'=>(@self::$grand_total >= $min_order_amount) ? true : false,
            'min_amount'=>(double) $min_order_amount,
            'free_shipping_amount'=>(double)$free_shipping_amount,
            'delivery_charge'=>(@self::$final_amount >= $free_shipping_amount) ? 0 : 0

            
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    public function checkInStock($id){
        $avail = "";
        if(isset($_SERVER['HTTP_SORTINGHUBID']) && !empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            $mappedProduct = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('product_id',$id)->where('published',1)->first();
            if($mappedProduct){
                if(is_null($mappedProduct->qty) || $mappedProduct->qty == 0){
                    $avail = "";
                }else{
                    $avail = 1;
                }
            }else{
                $avail = "";
            }
            
        }else{
            $productStock = \App\ProductStock::where('product_id',$id)->first();
            if(is_null($productStock->qty) || $productStock->qty == 0){
                $avail = "";
            }else{
                $avail = 1;
            }
        }
        
        return $avail;
    }

    public function checkavailable($product_id){
        if(isset($_SERVER['HTTP_SORTINGHUBID']) && !empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
        $mappedProduct = \App\MappingProduct::where(['product_id'=>$product_id,'sorting_hub_id'=>$shortId])->latest()->first();
        if(is_null($mappedProduct)){
            return false;
        }else{
            return true;
        }
    }
    }

    

    
}
