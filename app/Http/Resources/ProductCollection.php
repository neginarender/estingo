<?php

namespace App\Http\Resources;

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
                    'name' => trans($data->name),
                    'photos' => json_decode($data->photos),
                    'thumbnail_image' => $data->thumbnail_img,
                    'base_price' => (double) homeBasePrice($data->id),
                    'stock_price'=>$data->stock_price,
                    'quantity'=>$data->quantity,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    'variant'=>$data->variant,
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' =>(integer) $data->featured,
                    'choice_options' => $data->choice_options,
                    'unit' => $data->unit,
                    'discount' => (double) $data->customer_discount,
                    'discount_percentage' => (double)substr($data->discount_percentage,1,-1),
                    'discount_type' => $data->discount_type,
                    'rating' => (double) $data->rating,
                    'sales' => (integer) $data->num_of_sale,
                    'peer_price' => $data->peer_price,
                    'sorting_hub_price' => $data->sorting_hub_price,
                    'cart_qty'=>$data->cart_qty,
                    'cart_id'=>$data->cart_id,
                    'in_wishlist'=>$data->in_wishlist,
                    'links' => [
                        'details' => "https://www.rozana.in/api/v1/products/".$data->id,
                        'reviews' => "https://www.rozana.in/api/v1/reviews/product/".$data->id,
                        'related' => "https://www.rozana.in/api/v1/products/related/".$data->id,
                        // 'top_from_seller' => route('products.topFromSeller', $data->id)
                        'top_from_seller' => "https://www.rozana.in/api/v1/products/top-from-seller/".$data->id
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
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
        // dd(DB::getQueryLog());
        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first(); 

        if(!empty($shortId)){
            $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
            $price = $product['purchased_price'];
            $stock_price = $product['selling_price'];
            if($price == 0 || $stock_price == 0){
                $product = Product::findOrFail($id);
                $price = $product->unit_price;
                $productstock = ProductStock::where('product_id', $id)->select('price')->first();
                if(!is_null($productstock)){
                    $stock_price = $productstock->price;
                }
                

            }  

        }else{
            $product = Product::findOrFail($id);
            $price = $product->unit_price;
            $productstock = ProductStock::where('product_id', $id)->select('price')->first();
            $stock_price = $product->unit_price;
            if(!is_null($productstock)){
                $stock_price = $productstock->price;  
            }
            

        }      

        if(!empty($peer_discount_check)){
            if(!empty($peer_discount_check->customer_off)){
                // $price = $productstock->price - $peer_discount_check->customer_off;
                $price = $stock_price - $peer_discount_check->customer_off;
                return $price;
            }else{
                // $stock_price = $productstock->price;  
                //$discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                // $price = ($stock_price * $discount_percent)/100;
                //$price = ($stock_price * $discount_percent)/100;
                $price = $stock_price;
                return $price;
            }

        }else{
            // $price = $productstock->price;
            $price = $stock_price;
            return $price;
        }
        
           // $price = $productstock->price;
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
