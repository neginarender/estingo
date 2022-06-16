<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\ProductCollection;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Traits\LocationTrait;

class FlashDealCollection extends ResourceCollection
{
    use LocationTrait;
    public function toArray($request)
    {
        $title = "";
        $enddate = "";
        $products = collect();
        $status = false;
        $location = $this->location();
        if(!is_null($this->collection->first())){
            $status = true;
            $flash_deal = FlashDeal::findOrFail($this->collection->first()->id);
            $title = $flash_deal->title;
            $enddate = $flash_deal->end_date;
            
            //dd($flash_deal->flashDealProducts);
            //$products = $this->products_with_peer_price(Product::whereIn('id',$flash_deal->flashDealProducts)->get(),$location->shortId);
            foreach ($flash_deal->flashDealProducts as $key => $flash_deal_product) {
            if(Product::find($flash_deal_product->product_id) != null){
                    $products->push(Product::find($flash_deal_product->product_id));
            }
        }
    }
        
        
        
        return [
            'flash_deal'=>$status,
            'title' => $title,
            'end_date' => $enddate,
            'products' => new ProductCollection($this->products_with_peer_price($products,$location->shortId))
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    public function products_with_peer_price($products,$shortId){
        $peerdetail = null;
        $sorting_hub_price = 0;
        
        $quantity = 0;
        
        
        foreach($products as $key => $product)
       {
        $cart_qty = 0;
        $cart_id = 0;
        $customer_discount = 0;
        $discount_percentage = 0;
        if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
            $device_id = $_SERVER['HTTP_DEVICE'];
            $cart = \App\Models\Cart::where('device_id',$device_id)->where('product_id',$product->id)->first();
            
            if(!is_null($cart)){
                $cart_qty = $cart->quantity;
                $cart_id = $cart->id;
            }
        }
        
        $qty = \App\ProductStock::where('product_id',$product->id)->first();
        if(!is_null($qty)){
            $quantity = $qty->qty;
        }

        $peer_code = "";
        if(!empty($_SERVER['HTTP_PEER'])){
            $peer_code = $_SERVER['HTTP_PEER'];
            $peerdetail = ProductCollection::peerCodeRate($peer_code, $product->id,$shortId);
        }
        if(!empty($shortId)){
           $sorting_hub_price = $this->sortingHubPrice($product->id,$shortId);
           $quantity = \App\MappingProduct::where('product_id',$product->id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first()->qty;

           $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$product->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
           $customer_discount = @$peer_discount_check->customer_off;
           $discount_percentage = @$peer_discount_check->customer_discount;

           
        }
        else{
            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$product->id.'"')->latest('id')->first();
            $customer_discount = @$peer_discount_check->customer_off;
            $discount_percentage = @$peer_discount_check->customer_discount;
        }
        $variant = "";
        $pvariant = $product->stocks->where('product_id',$product->id)->first();
        if(!is_null($pvariant))
        {
            $variant = $pvariant->variant;
        }
        $products[$key]['peer_price'] = $peerdetail;
        $products[$key]['sorting_hub_price'] = $sorting_hub_price;
        $products[$key]['stock_price'] = $this->stock_price($product->id);
        $products[$key]['variant'] = $variant;
        $products[$key]['customer_discount'] = $this->customer_discount($product->id,$shortId,$peer_code);
        $products[$key]['discount_percentage'] = $discount_percentage;
        $products[$key]['quantity'] = $quantity;
        $products[$key]['cart_qty'] = $cart_qty;
        $products[$key]['cart_id'] = $cart_id;
         }
        return $products;
    }

    public function sortingHubPrice($id,$shortId)
    {
        $price = 0;
        $product = \App\MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
        if($product !=null)
        {
            $price = $product->selling_price;
        }
        return $price;
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

    public function customer_discount($id,$shortId,$peercode){
       
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
        $last_price = 0;
            if(!empty($peer_discount_check)){      
                
                $main_discount = $stock_price - $price;

                if(!empty($peercode)){
                     // $discount_percent = Session::get('referal_discount');
                     $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                     $last_price = ($main_discount * $discount_percent)/100; 
                    }

                
                return $last_price;
            }  

          
            return $last_price;
    }


}
