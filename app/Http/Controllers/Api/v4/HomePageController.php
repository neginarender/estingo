<?php

namespace App\Http\Controllers\Api\v4;
use Illuminate\Http\Request;

use App\Http\Resources\v4\BannerCollection;
use App\Models\Banner;
use App\MasterBanner;
use App\Http\Resources\v4\SliderCollection;
use App\Http\Resources\v4\RecurringCollection;
use App\Models\Slider;
use App\Http\Resources\v4\ProductCollection;
use App\Http\Resources\v4\ProductDetailCollection;
use App\Http\Resources\v4\SearchProductCollection;
use App\Http\Resources\v4\FlashDealCollection;
use App\Http\Resources\v4\HomepageCollection;
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
use App\Http\Resources\v4\CategoryCollection;
use App\Models\BusinessSetting;
use App\Wishlist;
use App\State;
use App\BestSellingProduct;
use App\Order;


class HomePageController extends Controller
{
    public function index(Request $request){
        $banner = new BannerCollection(MasterBanner::where('published',1)->get());

        $productIds = [];
        $shortId = "";
        $recurring = [];
        $refund = [];

        if(isset($_SERVER['HTTP_USERID']) && $_SERVER['HTTP_USERID'] != NULL){
            $user_id = $_SERVER['HTTP_USERID'];
        }else{
            $user_id = NULL;
        }
        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(is_null($shortId)){
                return response()->json([
                    'status'=>false,
                ]);
            }

            if(!empty($shortId)){

                // $productIds = \App\MappingProduct::whereIn('product_id',bestSellingProduct($shortId))->where('sorting_hub_id',$shortId)->where('published',1)->where('flash_deal',0)->pluck('product_id');
                // if($shortId == 165 ){
                //     $productIds = [4234,4233,11320,11322,4218,4219];
                // }

                $productIds = \App\BestSellingProduct::where('sorting_hub_id',$shortId)->first();
                $productIds = explode(',',$productIds->product_id);

                $sliders = \App\SortingHubSlider::where(['sorting_hub_id'=>$shortId,'published'=>1])->get();
                $recurring = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('recurring_status',1)->where('published',1)->get();
               
            }

            //todays-deal
            $products = Product::whereIn('id',$productIds)->where('todays_deal', 1)->latest()->get();
            $sorting_hub_price = 0;
            foreach($products as $key => $product){
                $sorting_hub_price = $this->sortingHubPrice($product->id,$shortId);
                $products[$key]['sorting_hub_price'] = $sorting_hub_price;
            }
            $todayDeal = new ProductCollection($products);
            $recurring_product = new RecurringCollection($this->recurring_products_with_peer_price($recurring,$shortId));

        }else{

            // $categories = new CategoryCollection(Category::where(['featured'=>1,'status'=>1])->get());

            $sliders = \App\SortingHubSlider::where('published',1)->get();
            $todayDeal = new ProductCollection(Product::where('todays_deal', 1)->latest()->get());
            $recurring_product = new RecurringCollection($recurring);
        }

        if(!empty($productIds)){
            // $products_id = \App\Product::with('category')->where('published', '1')->whereIn('id',bestSellingProduct($shortId))->take(6)->get();
            $products_id = \App\Product::with('category')->where('published', '1')->whereIn('id',$productIds)->take(6)->get();
        }
        else{
            $products_id = \App\Product::with('category')->where('published', '1')->take(6)->orderBy('created_at','desc')->get();
        }
           
            
        $best_seller = new HomepageCollection($this->products_with_peer_price($products_id,$shortId,$user_id));

        $arr = array();
        if(!empty($shortId)){
            $arr['sorting_hub_id'] = $shortId;
        }

        $slider = new SliderCollection($sliders);
        $categories = new CategoryCollection(featured_categories($arr));
        
        $data = Order::where('user_id',$user_id)->where('wallet_refund_status',1)->get();
        foreach($data as $key => $value){
                $refund[$key]['code'] = $value->code;
                $refund[$key]['amount'] = $value->wallet_amount;
        }


        return response()->json([
            'success'=>true,
            'banner'=> $banner,
            'slider'=> $slider,
            'best_seller' => $best_seller,
            'categories' => $categories,
            'todays_deal' => $todayDeal,
            'recurring_product' => $recurring_product,
            'wallet_refund' => $refund
        ]);
    }

    public function products_with_peer_price($products,$shortId=NULL,$user_id = NULL){
        $quantity = 0;
        
        foreach($products as $key => $product)
       {
        $cart_qty = 0;
        $cart_id = 0;
        $customer_discount = 0;
        $discount_percentage = 0;

        $priceDetail = calculatePrice($product->id,$shortId);

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

        $variant = "";
        $pvariant = $product->stocks->where('product_id',$product->id)->first();
        if(!is_null($pvariant))
        {
            $variant = $pvariant->variant;
        }

        if(!is_null($user_id)){
            $is_wishlist = $this->checkProductInWishlist($product->id,$user_id);
            $wishlist_id = (integer) Wishlist::where(['product_id' => $product->id, 'user_id' => $user_id])->pluck('id')->first();
        }else{
            $is_wishlist = false;
            $wishlist_id = 0;
        }


        $products[$key]['variant'] = $variant;
        $products[$key]['quantity'] = $quantity;
        $products[$key]['cart_qty'] = $cart_qty;
        $products[$key]['cart_id'] = $cart_id;
        $products[$key]['in_wishlist'] = $is_wishlist;
        $products[$key]['wishlist_id'] = $wishlist_id;
        $products[$key]['MRP'] = $priceDetail['MRP'];
        $products[$key]['selling_price'] = $priceDetail['selling_price'];
        $products[$key]['customer_off'] = $priceDetail['customer_off'];
        $products[$key]['customer_discount'] = $priceDetail['customer_discount'];
        $products[$key]['discount_type'] = $priceDetail['discount_type'];
         }
        return $products;
    }


    public function getDistributorSortingID(){

        //if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
        if(!empty($_SERVER['HTTP_PINCODE'])){   
            $pincode = $_SERVER['HTTP_PINCODE'];
            $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();

            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');

            if(is_null($shortId)){
                return response()->json([
                    'success'=> true,
                    'distributorids'=> "",
                    'sorting_hub_id'=> "",
                    'status' => 200
                ]);
            }

            return response()->json([
            'success'=>true,
            'distributorids'=> implode(',',$distributorId),
            'sorting_hub_id'=> (string) $shortId['sorting_hub_id'],
            'status' => 200
        ]);

        }else{
            return response()->json([
                    'success'=> true,
                    'distributorids'=> "",
                    'sorting_hub_id'=> "",
                    'status' => 200
                ]);
        }


        
    }

    public function checkProductInWishlist($product_id,$user_id){
        $product = Wishlist::where(['product_id' => $product_id, 'user_id' => $user_id])->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();
        if ($product > 0){
            return true;
        }else{
            return false;
        }
    }

    public function recurring_products_with_peer_price($products,$shortId){
        $quantity = 0;

        foreach($products as $key => $product){
        $customer_discount = 0;
        $discount_percentage = 0;

        $detail = \App\Product::where('id',$product->product_id)->first();

        $priceDetail = calculatePrice($product->product_id,$shortId);

        $variant = "";
        $pvariant = \App\ProductStock::where('product_id',$product->product_id)->first();
        if(!is_null($pvariant))
        {
            $variant = $pvariant->variant;
        }

        $products[$key]['quantity'] = $quantity;
        $products[$key]['variant'] = $variant;
        $products[$key]['name'] = trans($detail['name']);
        $products[$key]['category_id'] = $detail['category_id'];
        $products[$key]['subcategory_id'] = $detail['subcategory_id'];
        $products[$key]['subsubcategory_id'] = $detail['subsubcategory_id'];
        $products[$key]['photos'] = $detail['photos'];
        $products[$key]['thumbnail_img'] = $detail['thumbnail_img'];
        $products[$key]['unit'] = $detail['unit'];
        $products[$key]['tax'] = $detail['tax'];
        $products[$key]['max_purchase_qty'] = $detail['max_purchase_qty'];
        $products[$key]['description'] = $detail['description'];
        $products[$key]['slug'] = $detail['slug'];
        $products[$key]['MRP'] = $priceDetail['MRP'];
        $products[$key]['selling_price'] = $priceDetail['selling_price'];
        $products[$key]['customer_off'] = $priceDetail['customer_off'];
        $products[$key]['customer_discount'] = $priceDetail['customer_discount'];
        $products[$key]['discount_type'] = $priceDetail['discount_type'];
        }
        return $products;
    }


    

}
