<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;
use App\PeerSetting;
use App\Product;
use App\ProductStock;
use App\MappingProduct;
use App\Distributor;
use App\Wishlist;
use DB;

class ProductDetailCollection extends ResourceCollection
{
    //protected static $peer_code = null;
    //protected static $sorting_hub_price = 0;
    //protected static $stock_price = 0;
    //protected static $discount = 0;
    //protected static $discount_percent = 0;
    protected static $stock_qty = 0;
    protected static $cart_id = 0;
    protected static $cart_qty = 0;
    protected static $isReview = false;
    //protected static $inWishlist = false;
    //protected static $variant = 0;
    //protected static $rating = 0;
    //protected static $yourRating = 0;

    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => (integer) $data->id,
                    //'peer_price' => @self::$peer_code,
                    //'sorting_hub_price'=>@self::$sorting_hub_price,
                    //'base_price'=>$data->unit_price,
                    //'stock_price'=>@self::$stock_price,
                    'name' => $data->name,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    //'quantity'=>@self::$stock_qty,
                    'quantity'=>$data->quantity,
                    'added_by' => $data->added_by,
                    'user' => [
                        'name' => is_null($data->user)?NULL:$data->user['name'],
                        'email' => is_null($data->user)?NULL:$data->user['email'],
                        'avatar' => is_null($data->user)?NULL:$data->user['avatar'],
                        'avatar_original' => is_null($data->user)?NULL:$data->user['avatar_original'],
                        'shop_name' => $data->added_by == 'admin' ? '' : $data->user->shop->name,
                        'shop_logo' => $data->added_by == 'admin' ? '' : $data->user->shop->logo,
                        'shop_link' => $data->added_by == 'admin' ? '' : route('shops.info', $data->user->shop->id)
                    ],
                    'category' => [
                        'name' => $data->category->name,
                        'banner' => $data->category->banner,
                        'icon' => $data->category->icon,
                        'links' => [
                            'products' => route('api.products.category', $data->category_id),
                            'sub_categories' => route('subCategories.index', $data->category_id)
                        ]
                    ],
                    'sub_category' => [
                        'name' => $data->subCategory != null ? $data->subCategory->name : null,
                        'links' => [
                            'products' => $data->subCategory != null ? route('products.subCategory', $data->subcategory_id) : null
                        ]
                    ],
                    'brand' => [
                        'name' => $data->brand != null ? $data->brand->name : null,
                        'logo' => $data->brand != null ? $data->brand->logo : null,
                        'links' => [
                            'products' => $data->brand != null ? route('api.products.brand', $data->brand_id) : null
                        ]
                    ],
                    'photos' => json_decode($data->photos),
                    'thumbnail_image' => $data->thumbnail_img,
                    'tags' => explode(',', $data->tags),
                    'price_lower' => (double) explode('-', homeDiscountedPrice($data->id))[0],
                    'price_higher' => (double) explode('-', homeDiscountedPrice($data->id))[1],
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => json_decode($data->colors),
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' => (integer) $data->featured,
                    'current_stock' => (integer) $data->current_stock,
                    'unit' => $data->unit,
                    //'discount' => (double)@self::$discount,
                    //'discount_percentage' => (double)substr(self::$discount_percent,1,-1),
                    //'discount_type' => $data->discount_type,
                    'tax' => (double) $data->tax,
                    'tax_type' => $data->tax_type,
                    'shipping_type' => $data->shipping_type,
                    'shipping_cost' => (double) $data->shipping_cost,
                    'number_of_sales' => (integer) $data->num_of_sale,
                    //'rating' => round(self::$rating,2),
                    // 'rating_count' => (integer) Review::where(['product_id' => $data->id])->count(),
                    'description' => $data->description,
                    //'cart_id'=>self::$cart_id,
                    //'cart_qty'=>self::$cart_qty,
                    'is_review'=>self::$isReview,
                    'reviewCount'=>(integer) Review::where(['product_id' => $data->id])->count(),
                    //'yourRating' => self::$yourRating,
                    //'in_wishlist'=>self::$inWishlist,
                    //'wishlist_id'=>(self::$inWishlist == True && isset($_SERVER['HTTP_USERID']))?((integer) Wishlist::where(['product_id' => $data->id, 'user_id' => $_SERVER['HTTP_USERID']])->pluck('id')->first()):0,
                    //'variant'=> self::$variant,
                    'variant'=>$data->variant,
                    'MRP' => $data->MRP,
                    'stock_price'=>(double)$data->MRP,
                    'base_price' => round($data->selling_price,2),
                    // 'base_price' => (double) $data->selling_price,
                    'discount' => $data->customer_off,
                    'discount_percentage' => json_decode($data->customer_discount),
                    'discount_type' => json_decode($data->discount_type),
                    'rating' => (double) $data->rating_avg,
                    'sales' => (integer) $data->num_of_sale,
                    'cart_qty'=>$data->cart_qty,
                    'cart_id'=>$data->cart_id,
                    'in_wishlist'=>$data->in_wishlist,
                    'wishlist_id'=>isset($data->wishlist_id)?$data->wishlist_id:0,
                    'links' => [
                        'reviews' => route('api.reviews.index', $data->id),
                        'related' => route('products.related', $data->id),
                        'product_link'=> env('APP_URL')."product/".$data->slug
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

    protected function convertToChoiceOptions($data){
        $result = array();
        foreach ($data as $key => $choice) {
            $item['name'] = $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->name;
            $item['options'] = $choice->values;
            array_push($result, $item);
        }
        return $result;
    }


    public static function peerCodePrice($peer_code="", $id=""){
        $result = array();
        $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        $product = Product::findOrFail($id);
        $price = $product->unit_price;
        $productstock = ProductStock::where('product_id', $id)->select('price')->first();
        $stock_price = $productstock->price; 

        if(!empty($peer_discount_check)){      
               $main_discount = $stock_price - $price;
               $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
               $last_price = ($main_discount * $discount_percent)/100; 
               $price = $stock_price - $last_price;
            }else{
               $price = $stock_price;
            }    

        self::$peer_code['peer_price'] = $price;
    }

    public static function peerCodeRate($peer="", $id="",$shortId=""){
       
        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
        // dd(DB::getQueryLog());
        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first(); 

        if(!empty($shortId)){
            $productM = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
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
                // $price = $productstock->price - $peer_discount_check->customer_off;
                $price = $stock_price - $peer_discount_check->customer_off;
                self::$peer_code = $price;
                return $price;
            }else{
                // $stock_price = $productstock->price;  
                $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                // $price = ($stock_price * $discount_percent)/100;
                ////$price = ($stock_price * $discount_percent)/100;
                $price = $stock_price;
                self::$peer_code = $price;
                return $price;
            }

        }else{
            // $price = $productstock->price;
            $price = $stock_price;
            self::$peer_code = $price;
            return $price;
        }
        
           // $price = $productstock->price;
           $price = $stock_price;
           self::$peer_code = $price;
           return $price;
    }

    public static function peerCodeRateOld($peer="", $id="",$shortId=""){

        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
    
        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first(); 

        if(!empty($shortId)){
            $productM = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
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
                
                $main_discount = $stock_price - $price;
    
                if(!empty($peer)){
                     // $discount_percent = Session::get('referal_discount');
                     $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                     $last_price = ($main_discount * $discount_percent)/100; 
                    }
    
                $price = $stock_price - $last_price;
                //echo convert_price($price);exit;
                self::$peer_code = $price;
                return $price;
            }else{
               
                 $price = $stock_price;
                 self::$peer_code = $price;
                 return $price;
            }    
    
           $price = $stock_price;
           self::$peer_code = $price;
            return $price;
    }
    

    public static function sorting_hub_price($pincode,$id)
    {

        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
        // $sorting_hub_price = MappingProduct::where('product_id1',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first()->selling_price;

        //13-11-2021 - handle NULL case
        $sorting_hub_price = MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
        if($sorting_hub_price != NULL){
            $sorting_hub_price = $sorting_hub_price->selling_price;
        }else{
            $sorting_hub_price = NULL;
        }

        self::$sorting_hub_price = $sorting_hub_price;
        return $sorting_hub_price;
           
    }

    public static function stock_price($id)
    {
        $stock_price = 0;
        // $s_price = ProductStock::where('product_id1',$id)->first()->price;
        $s_price = ProductStock::where('product_id',$id)->pluck('price')->first();
        self::$stock_price = $s_price;
        return $s_price;
    }

    public static function discount($product_id,$shortId){

        $customer_discount = 0;
        $discount_percentage = 0; 
        
        if(!empty($shortId)){

            $peer_discount_check = PeerSetting::where('product_id', '"'.$product_id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
                if(!is_null($peer_discount_check)){
                    $customer_discount = @$peer_discount_check->customer_off;
                    $discount_percentage = @$peer_discount_check->customer_discount;

                }
         }
         else{

             $peer_discount_check = PeerSetting::where('product_id', '"'.$product_id.'"')->latest('id')->first();

             if(!is_null($peer_discount_check)){
                 $customer_discount = @$peer_discount_check->customer_off;
                 $discount_percentage = @$peer_discount_check->customer_discount;
             }

         }

        self::$discount = round($customer_discount,2);
        self::$discount_percent = $discount_percentage;
        return round($customer_discount,2);
    }

    public static function stockQuantity($pincode,$id)
    {
        $quantity = 0;
        if(!empty($pincode)){
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');

            $sorting_hub_qty = MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
            if(!is_null($sorting_hub_qty))
            {
                $quantity = $sorting_hub_qty->qty;
            }
        }
        else{
            $stockQty = ProductStock::where('product_id',$id)->pluck('qty')->first();
            if(!is_null($stockQty)){
                // if(!is_null($stockQty->qty)){
                    // $quantity = $stockQty->qty;
                // }
                $quantity = $stockQty;
            }
        }
       
        self::$stock_qty = $quantity;
        return $quantity;
    }

    public static function cartData($id){
        $cart_idd = 0;
        $cart_qtyy = 0;
        if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
            $device_id = $_SERVER['HTTP_DEVICE'];
            $cart = \App\Models\Cart::where('device_id',$device_id)->where('product_id',$id)->select('quantity','id')->first();
            
            if(!is_null($cart)){
                $cart_qtyy = $cart->quantity;
                $cart_idd = $cart->id;
            }
        } 
        self::$cart_id = $cart_idd;
        self::$cart_qty = $cart_qtyy;
        return $cart_idd;  
    }

    public static function isReviewAble($product_id){
        $user = null;
        if(isset($_SERVER['HTTP_USERID']) && !empty($_SERVER['HTTP_USERID'])){
            $user_id = $_SERVER['HTTP_USERID'];
            $user = \App\User::find($user_id);
        }
        
        $product = \App\Product::find($product_id);
        $Review = false;
        if(!is_null($user) && !is_null($product)){
            foreach($product->orderDetails as $key => $orderDetail)
            {
                if($orderDetail->order != null && $orderDetail->order->user_id == $user->id  && \App\Review::where('user_id', $user->id)->where('product_id', $product->id)->first() == null)
                {
                    $Review = true;
                }  
            } 
                
        }
        self::$isReview = $Review;
        return $Review;
    }

    public static function checkProductInWishlist($product_id){
        $user = null;
        if(isset($_SERVER['HTTP_USERID']) && !empty($_SERVER['HTTP_USERID'])){
            $user_id = $_SERVER['HTTP_USERID'];
            $product = Wishlist::where(['product_id' => $product_id, 'user_id' => $user_id])->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();
        }else{
           $product = 0; 
        }
    

        if ($product > 0){
            $inWishlist = true;
        }else{
            $inWishlist = false;
        }

        self::$inWishlist = $inWishlist;
        return $inWishlist;
    }

    public static function variant($id){
        $variant = '';
        $productStock = ProductStock::where('product_id',$id)->select('variant')->first();
                if(!is_null($productStock)){
                    $variant = $productStock->variant;

                }
          self::$variant = $variant;
        return $variant;      
    }

    public static function calculateRating($id){
        $review = Review::where('product_id',$id)->get();
        if(!empty($review)){
            $count = count($review);
            $ratingSUM = Review::where('product_id',$id)->select(DB::raw('SUM(rating) as rating'))->first();
            if($count > 0){
                $rating = $ratingSUM->rating/$count;
            }else{
                $rating = $ratingSUM->rating;
            }
            
        }else{
            $rating = '0.0';
        }
        self::$rating = $rating;
        return $rating;
    }

    public static function yourReview($id){
        if(!empty($_SERVER['HTTP_USERID'])){
            $user_id = $_SERVER['HTTP_USERID'];
            $review = Review::where('product_id',$id)->where('user_id',$user_id)->pluck('rating')->first();  
            if(!is_null($review)){
                $yourRating = $review;
            }else{
                $yourRating = 0;
            }       
        }else{
            $yourRating = 0;
        }
        self::$yourRating = $yourRating;
        return $yourRating;
    }

}
