<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\PeerSetting;
use App\Product;
use App\ProductStock;
use App\MappingProduct;

class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'product_id'=>$data->id,
                    'category_id'=>$data->category_id,
                    'subcategory_id'=>$data->subcategory_id,
                    'name' => trans($data->name),
                    'photos' => json_decode($data->photos),
                    'thumbnail_image' => $data->thumbnail_img,
                    'quantity'=>$data->quantity,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    'variant'=>$data->variant,
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' =>(integer) $data->featured,
                    'choice_options' => $data->choice_options,
                    'unit' => $data->unit,
                    'rating' => round($data->rating_avg,1),
                    'sales' => (integer) $data->num_of_sale,
                    'cart_qty'=>$data->cart_qty,
                    'cart_id'=>$data->cart_id,
                    'in_wishlist'=>$data->in_wishlist,
                    'wishlist_id'=>isset($data->wishlist_id)?$data->wishlist_id:0,
                    'MRP' => $data->MRP,
                    'stock_price'=>(double)$data->MRP,
                    'base_price' => round($data->selling_price,2),
                    // 'base_price' => (double) $data->selling_price,
                    'discount' => $data->customer_off,
                    'discount_percentage' => json_decode($data->customer_discount),
                    'discount_type' => json_decode($data->discount_type),
                    'links' => [
                        'details' => route('products.show', $data->id),
                        'reviews' => route('api.reviews.index', $data->id),
                        'related' => route('products.related', $data->id),
                        'top_from_seller' => route('products.topFromSeller', $data->id)
                    ]
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
 
    public static function peerCodeRate($peer_code="", $id="",$shortId=""){
        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId. '"]\')')->latest('id')->first();            
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    

        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first(); 

        if(!empty($shortId)){
            $productM = MappingProduct::where(['sorting_hub_id'=>$shortId,'product_id'=>$id])->first();
            $price = $productM['purchased_price'];
            $stock_price = $productM['selling_price'];
            if($price == 0 || $stock_price == 0){
                $price = $product->unit_price;
                $stock_price = $productstock->price;
            }  
        }else{
            $price = $product->unit_price;
            $stock_price = $productstock->price;  
        }      

        if(!empty($peer_discount_check)){
            if(!empty($peer_discount_check->customer_off)){
                $price = $stock_price - $peer_discount_check->customer_off;
                return $price;
            }else{
                $price = $stock_price;
                return $price;
            }
        }else{
            $price = $stock_price;
            return $price;
        }
           $price = $stock_price;
           return $price;
    }
    
    public static function peerCodeRateOld($peer_code="", $id="",$shortId=""){

        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
    
        if(!empty($shortId)){
            $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
            $price = $product['purchased_price'];
            $stock_price = $product['selling_price'];
            if($price == 0 || $stock_price == 0){
                $product = Product::findOrFail($id);
                $price = $product->unit_price;
                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                $stock_price = $productstock->price;
    
            }  
    
        }else{
            $product = Product::findOrFail($id);
            $price = $product->unit_price;
            $productstock = ProductStock::where('product_id', $id)->select('price')->first();
            $stock_price = $productstock->price;  
    
        }
            if(!empty($peer_discount_check)){      
                
                $main_discount = $stock_price - $price;
    
                if(!empty($peer_code)){
                     // $discount_percent = Session::get('referal_discount');
                     $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                     $last_price = ($main_discount * $discount_percent)/100; 
                    }
    
                $price = $stock_price - $last_price;
                //echo convert_price($price);exit;
                return $price;
            }else{
               
                 $price = $stock_price;
                 return $price;
            }    
    
           $price = $stock_price;
            return $price;
    }
    
    
}
