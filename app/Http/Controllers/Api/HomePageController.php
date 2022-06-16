<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;

use App\Http\Resources\BannerCollection;
use App\Models\Banner;
use App\MasterBanner;
use App\Http\Resources\SliderCollection;
use App\Models\Slider;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductDetailCollection;
use App\Http\Resources\SearchProductCollection;
use App\Http\Resources\FlashDealCollection;
use App\Traits\LocationTrait;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Color;
use DB;
use App\Attribute;
use App\PeerPartner;
use App\PeerSetting;
use App\ProductStock;
use App\Models\Cart;
use App\Http\Resources\CategoryCollection;
use App\Models\BusinessSetting;

class HomePageController extends Controller
{
    public function index(){
        $banner = new BannerCollection(MasterBanner::where('published',1)->get());
        $slider = new SliderCollection(Slider::where('published',1)->get());

        $productIds = [];
        $shortId = "";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();
            $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
            if(is_null($shortId)){
                return response()->json([
                    'status'=>false,
                ]);
            }

            if(!empty($distributorId)){
                $productIds = \App\MappingProduct::whereIn('distributor_id',$distributorId)->where('published',1)->pluck('product_id')->all();

                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();

                $categories = new CategoryCollection(Category::whereIn('id',$categoryIds)->orderBy('sorting','asc')->where(['featured'=>1,'status'=>1])->get());
               
            }else{
                $categories = new CategoryCollection(Category::where(['featured'=>1,'status'=>1])->get());

            }

            //todays-deal
            $products = Product::whereIn('id',$productIds)->where('todays_deal', 1)->latest()->get();
            $sorting_hub_price = 0;
            foreach($products as $key => $product){
                $sorting_hub_price = $this->sortingHubPrice($product->id,$shortId);
                $products[$key]['sorting_hub_price'] = $sorting_hub_price;
            }
            $todayDeal = new ProductCollection($products);


        }else{
            $categories = new CategoryCollection(Category::where(['featured'=>1,'status'=>1])->get());

            $todayDeal = new ProductCollection(Product::where('todays_deal', 1)->latest()->get());
            }

            if(!empty($productIds)){
                $products_id = \App\Product::with('category')->where('published', '1')->whereIn('id',bestSellingProduct($shortId['sorting_hub_id']))->take(6)->get();

            }
            else{
                $products_id = \App\Product::with('category')->where('published', '1')->take(6)->orderBy('created_at','desc')->get();
            }
            
            $peerdetail = null;
            $sorting_hub_price = 0;
            
            $best_seller = new ProductCollection($this->products_with_peer_price($products_id,$shortId));



        return response()->json([
            'success'=>true,
            'banner'=> $banner,
            'slider'=> $slider,
            'best_seller' => $best_seller,
            'categories' => $categories,
            'todays_deal' => $todayDeal
        ]);
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
            $cart = Cart::where('device_id',$device_id)->where('product_id',$product->id)->first();
            
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

           $peer_discount_check = PeerSetting::where('product_id', '"'.$product->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
           $customer_discount = @$peer_discount_check->customer_off;
           $discount_percentage = @$peer_discount_check->customer_discount;

           
        }
        else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$product->id.'"')->latest('id')->first();
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
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
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

            $stock_price = $productstock['price'];  

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

    public function getDistributorSortingID(){
        $distributorId = array();
        $shortId = array();
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
           
            $pincode = $_SERVER['HTTP_PINCODE'];
            $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();

            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
  
            $shortId = $shortId['sorting_hub_id'];

            if(is_null($shortId)){
                return response()->json([
                    'status'=>false,
                ]);
            }
        }


        return response()->json([
            'success'=>true,
            'distributorids'=> implode(',',$distributorId),
            'sorting_hub_id'=> $shortId,
            'status' => 200
        ]);
    }

}
