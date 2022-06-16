<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WishlistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => (integer) $data->id,
                    'product' => [
                        'product_id' => $data->product_id,
                        'name' => trans($data->product->name),
                        'thumbnail_image' => $data->product->thumbnail_img,
                        'base_price' => (double) homeBasePrice($data->product->id),
                        'base_discounted_price' => (double) homeDiscountedBasePrice($data->product->id),
                        'stock_price'=>$this->stock_price($data->product->id),
                        'sorting_hub_price'=>$this->sorting_hub_price($data->product->id),
                        'peer_price'=>(!empty($_SERVER['HTTP_PEER']) && isset($_SERVER['HTTP_PEER'])) ? $this->peerCodeRate($data->product->id) : null,
                        'discount_percentage'=>(double)substr($this->discount_percentage($data->product->id),1,-1),
                        'unit' => $data->product->unit,
                        'rating' => (double) $data->product->rating,
                        'links' => [
                            // 'details' => route('products.show', $data->product->id),
                            // 'reviews' => route('api.reviews.index', $data->product->id),
                            // 'related' => route('products.related', $data->product->id),
                            // 'top_from_seller' => route('products.topFromSeller', $data->product->id)
                            'details' => "https://www.rozana.in/api/v1/products/".$data->product->id,
                            'reviews' => "https://www.rozana.in/api/v1/reviews/product/".$data->product->id,
                            'related' => "https://www.rozana.in/api/v1/products/related/".$data->product->id,
                            'top_from_seller' => "https://www.rozana.in/api/v1/products/top-from-seller/".$data->product->id
                        ]
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

    public function stock_price($id){
        $price = 0;
        $product = \App\ProductStock::where('product_id',$id)->first();
        if($product!=null)
        {
            $price = $product->price;
        }
        return $price;
    }

    public function sorting_hub_price($id)
    {
        $sorting_hub_price = 0;
        $shortId="";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
            $sorting_hub_product = \App\MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
        if(!is_null($sorting_hub_product))
        {
            $sorting_hub_price = $sorting_hub_product->selling_price;
        }

        }
        
        
        //self::$sorting_hub_price = $sorting_hub_price;
        return $sorting_hub_price;
           
    }

    public static function peerCodeRate($id){
        $shortId="";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
        }
        if(!empty($shortId)){
            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
    
        if(!empty($shortId)){
            $product = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
            $price = $product['purchased_price'];
            $stock_price = $product['selling_price'];
            if($price == 0 || $stock_price == 0){
                $product = \App\Product::findOrFail($id);
                $price = $product->unit_price;
                $productstock = \App\ProductStock::where('product_id', $id)->select('price')->first();
                $stock_price = $productstock->price;
    
            }  
    
        }else{
            $product = \App\Product::findOrFail($id);
            $price = $product->unit_price;
            $productstock = \App\ProductStock::where('product_id', $id)->select('price')->first();
            $stock_price = $productstock->price;  
    
        }
            if(!empty($peer_discount_check)){      
                
                $main_discount = $stock_price - $price;
    
                if(!empty($_SERVER['HTTP_PEER']) && isset($_SERVER['HTTP_PEER'])){
                     // $discount_percent = Session::get('referal_discount');
                     //$discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                     //$last_price = ($main_discount * $discount_percent)/100; 
                    $last_price = $peer_discount_check->customer_off;
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
    
public function discount_percentage($product_id){
    $shortId="";
    if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
        $pincode = $_SERVER['HTTP_PINCODE'];
        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
    }
    $discount_percentage = 0;
    if(!empty($shortId)){
        $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$product_id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
        $discount_percentage = @$peer_discount_check->customer_discount;

        
     }
     else{
         $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$product_id.'"')->latest('id')->first();
         $discount_percentage = @$peer_discount_check->customer_discount;
     }

     return $discount_percentage;
}

}
