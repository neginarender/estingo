<?php

namespace App\Http\Controllers\Api\v5;
use Illuminate\Http\Request;

use App\Http\Resources\v5\BannerCollection;
// use App\Http\Resources\v5\RecurringCollection;
use App\Http\Resources\v5\StateCollection;
use App\Http\Resources\v5\CityCollection;
use App\Http\Resources\v5\AreaCollection;
use App\Http\Resources\v5\BlockCollection;
use App\Http\Resources\v5\PincodeCollection;
use App\Models\Banner;
use App\MasterBanner;
use App\Http\Resources\v5\SliderCollection;
use App\Models\Slider;
use App\Http\Resources\v5\ProductCollection;
use App\Http\Resources\v5\ProductDetailCollection;
use App\Http\Resources\v5\SearchProductCollection;
use App\Http\Resources\v5\FlashDealCollection;
use App\Http\Resources\v5\HomepageCollection;
use App\Http\Resources\v5\OrderCollection;
use App\Http\Resources\v5\UserCollection;
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
use App\Http\Resources\v5\CategoryCollection;
use App\Models\BusinessSetting;
use App\Wishlist;
use App\State;
use App\City;
use App\Area;
use App\Block;
use App\Order;
use App\User;
use App\OrderReferalCommision;
use Carbon\Carbon;
use App\BestSellingProduct;

class HomePageController extends Controller
{
    public function index(Request $request){
        $banner = new BannerCollection(MasterBanner::where('published',1)->get());

        $productIds = [];
        $shortId = "";
        // $recurring = [];
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
                $productIds = [];
                $bestproductIds = \App\BestSellingProduct::where('sorting_hub_id',$shortId)->first();
                if(!is_null($bestproductIds)){
                    $productIds = explode(',',$bestproductIds->product_id);
                }
                

                $sliders = \App\SortingHubSlider::where(['sorting_hub_id'=>$shortId,'published'=>1])->get();

                // $recurring = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('recurring_status',1)->where('published',1)->get();
            }

            //todays-deal
            $products = Product::whereIn('id',$productIds)->where('todays_deal', 1)->latest()->get();
            $sorting_hub_price = 0;
            foreach($products as $key => $product){
                $sorting_hub_price = $this->sortingHubPrice($product->id,$shortId);
                $products[$key]['sorting_hub_price'] = $sorting_hub_price;
            }
            $todayDeal = new ProductCollection($products);
            // $recurring_product = new RecurringCollection($this->recurring_products_with_peer_price($recurring,$shortId));


        }else{

            // $categories = new CategoryCollection(Category::where(['featured'=>1,'status'=>1])->get());

            $sliders = \App\SortingHubSlider::where('published',1)->get();
            $todayDeal = new ProductCollection(Product::where('todays_deal', 1)->latest()->get());
            // $recurring_product = new RecurringCollection($recurring);
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
            // 'recurring_product' => $recurring_product
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

        if(!empty($_SERVER['HTTP_PINCODE'])){   
            $pincode = $_SERVER['HTTP_PINCODE'];
            $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $pincode . '"]\')')->where('status',1)->pluck('id')->all();

            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');

            $district_id = \App\Area::where('pincode',$pincode)->pluck('district_id')->first();
            $city = \App\City::where('id',$district_id)->pluck('name')->first();

            if(is_null($shortId)){
                return response()->json([
                    'success'=> true,
                    'distributorids'=> "",
                    'sorting_hub_id'=> "",
                    'pincode'=> $pincode,
                    'city' => $city,
                    'status' => 200
                ]);
            }

            return response()->json([
                'success'=>true,
                'distributorids'=> implode(',',$distributorId),
                'sorting_hub_id'=> (string) $shortId['sorting_hub_id'],
                'pincode'=> $pincode,
                'city' => $city,
                'status' => 200
            ]);
        }else{
            return response()->json([
            'success'=>false,
            'distributorids'=> "",
            'sorting_hub_id'=> "",
            'pincode'=> "",
            'city' => "",
            'status' => 401,
            'message' => "Pincode required"
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

    // public function recurring_products_with_peer_price($products,$shortId){
    //     $quantity = 0;

    //     foreach($products as $key => $product){
    //     $customer_discount = 0;
    //     $discount_percentage = 0;

    //     $detail = \App\Product::where('id',$product->product_id)->first();

    //     $priceDetail = calculatePrice($product->product_id,$shortId);

    //     // $products[$key]['quantity'] = $quantity;
    //     $products[$key]['name'] = $detail['name'];
    //     $products[$key]['category_id'] = $detail['category_id'];
    //     $products[$key]['subcategory_id'] = $detail['subcategory_id'];
    //     $products[$key]['subsubcategory_id'] = $detail['subsubcategory_id'];
    //     $products[$key]['photos'] = $detail['photos'];
    //     $products[$key]['thumbnail_image'] = $detail['thumbnail_image'];
    //     $products[$key]['unit'] = $detail['unit'];
    //     $products[$key]['tax'] = $detail['tax'];
    //     $products[$key]['max_purchase_qty'] = $detail['max_purchase_qty'];
    //     $products[$key]['description'] = $detail['description'];
    //     $products[$key]['slug'] = $detail['slug'];
    //     $products[$key]['MRP'] = $priceDetail['MRP'];
    //     $products[$key]['selling_price'] = $priceDetail['selling_price'];
    //     $products[$key]['customer_off'] = $priceDetail['customer_off'];
    //     $products[$key]['customer_discount'] = $priceDetail['customer_discount'];
    //     $products[$key]['discount_type'] = $priceDetail['discount_type'];
    //     }
    //     return $products;
    // }

    public function stateList(){
        $clusters = \App\Cluster::where('status',1)->select('state_id')->get();
        $state_ids = [];

        foreach($clusters as $key => $cluster){
            foreach(json_decode($cluster->state_id) as $kk => $state){
                $state_ids[] =$state;
            }
            
        }
        
        $state = new StateCollection(State::where('status',1)->where('country_id',99)->whereIn('id',array_unique($state_ids))->get());
        return response()->json([
            'success'=>true,
            'status' => 200,
            'state'=> $state
            
        ]);
    }

    public function cityList(Request $request){
        $state_id = $request->state_id;
        $clusters = \App\Cluster::where('status',1)->select('cities')->get();
        $city_ids = [];
        foreach($clusters as $key => $cluster){
            foreach(json_decode($cluster->cities) as $kk => $city){
                $city_ids[] =$city;
            }
            
        }

        $city = new CityCollection(City::whereIn('id',array_unique($city_ids))->where('status',1)->where('state_id',$state_id)->get());
        return response()->json([
            'success'=>true,
            'status' => 200,
            'state'=> $city
            
        ]);
    }

    public function blockList(Request $request){
        $city_id = $request->city_id;
        $block = new BlockCollection(Block::where('district_id',$city_id)->get());
        return response()->json([
            'success'=>true,
            'status' => 200,
            'state'=> $block
            
        ]);
    }

    public function pincodeList(Request $request){
        $block_id = $request->block_id;
        //get district id 
        $district = \App\Block::where('id',$block_id)->first('district_id');
        //$block_pincodes= \App\Area::where('block_id',$block_id)->distinct()->select('pincode')->get();
        $block_pincodes= \App\Area::where('district_id',$district->district_id)->distinct()->select('pincode')->get();
        $sortinghubpincodes = \App\ShortingHub::where('status',1)->pluck('area_pincodes');
        $allpincodes = [];
        foreach($sortinghubpincodes as $key => $pincodes){
           
            foreach(json_decode($pincodes) as $kk=>$pincode){
                $allpincodes[]=$pincode;
            }
            
        }
        $filtered_pincodes = [];
        //dd($block_pincodes);
        foreach($block_pincodes as $key => $pincodes){
                if(in_array($pincodes->pincode,$allpincodes)){
                    $filtered_pincodes[]['pincode'] = $pincodes->pincode;
                }        
           
        }
        //dd($filtered_pincodes);
        $pincodes = new PincodeCollection(collect((object) $filtered_pincodes));
        return response()->json([
            'success'=>true,
            'status' => 200,
            'state'=> $pincodes
        ]);
    }

    public function dashboardRozana(Request $request){

        $detail = array();
        $saving = 0;
        $order = Order::where('user_id',$request->user_id)->where('order_type','self')->where('order_status','!=','cancel')->where('log',0)->get();
        foreach($order as $value){
            $saving = $saving + $value->referal_discount;
        }
        $detail['saving'] = (double)$saving;

        $detail['total_order'] = Order::where('user_id',$request->user_id)->where('order_type','self')->where('log',0)->get()->count();

        $detail['deliveredOrder'] = Order::where('user_id',$request->user_id)->where('order_type','self')->where('order_status','delivered')->where('log',0)->get()->count();

        $detail['inTransitOrder'] = Order::where('user_id',$request->user_id)->where('order_type','self')->where('order_status','in-transit')->where('log',0)->get()->count();

        $detail['cancelOrder'] = Order::where('user_id',$request->user_id)->where('order_type','self')->where('order_status','cancel')->where('log',0)->get()->count();

        $detail['pending'] = Order::where('user_id',$request->user_id)->where('order_type','self')->where('order_status','pending')->where('log',0)->get()->count();

        $order = Order::where('user_id',$request->user_id)->where('order_type','self')->where('log',0)->latest()->limit(5)->get();

        $detail['latest_order'] = new OrderCollection($order);

        return response()->json([
            'success'=>true,
            'status' => 200,
            'detail'=> $detail
        ]);
        
    }

    public function dashboardCustomer(Request $request){
        $detail = array();
        $orderId = array();

        $userCustomer = User::where('peer_user_id',$request->user_id)->pluck('id');
        
        $order = Order::where('user_id',$request->user_id)->where('order_type','other')->where('log',0)->get();

        $orderCustomer = Order::whereIn('user_id',$userCustomer)->where('order_type','self')->where('log',0)->get();
       
        foreach($order as $key => $value){
            array_push($orderId,$value->id);
        }

        foreach($orderCustomer as $key => $value){
            array_push($orderId,$value->id);
        }
       
        $commission = 0;
        if(!empty($orderId)){
            $commission = OrderReferalCommision::whereIn('order_id',$orderId)->where('wallet_status',1)->sum('referal_commision_discount');
        }
        
        
        $detail['earning'] = round($commission,2);

        $userCount = User::where('peer_user_id',$request->user_id)->where('user_type','customer')->get()->count();
        $detail['enrollCustomer'] = $userCount;

        $detail['deliveredOrder'] = Order::where('user_id',$request->user_id)->where('order_type','other')->where('order_status','delivered')->where('log',0)->get()->count();

        $detail['inTransitOrder'] = Order::where('user_id',$request->user_id)->where('order_type','other')->where('order_status','in-transit')->where('log',0)->get()->count();

        $detail['cancelOrder'] = Order::where('user_id',$request->user_id)->where('order_type','other')->where('order_status','cancel')->where('log',0)->get()->count();

        $detail['pending'] = Order::where('user_id',$request->user_id)->where('order_type','other')->where('order_status','pending')->where('log',0)->get()->count();

        $user = User::where('peer_user_id',$request->user_id)->where('user_type','customer')->latest()->limit(5)->get();
        $detail['customerDetail'] = new UserCollection($user);

        return response()->json([
            'success'=>true,
            'status' => 200,
            'detail'=> $detail
        ]);
    }

    public function earningDetail(Request $request){
        $userCustomer = User::where('peer_user_id',$request->user_id)->pluck('id');

        $order = DB::table('orders')
        ->LeftJoin('order_referal_commision','orders.id','=','order_referal_commision.order_id')
        // ->where('orders.order_type','other')  
        ->where('log',0)
        ->where('orders.user_id','=',$request->user_id)
        ->orWhereIn('user_id',$userCustomer)
        ->select('orders.code','orders.referal_discount as discount','orders.total_shipping_cost as shipping_cost','orders.payment_type','orders.shipping_address','orders.wallet_amount', 'orders.payment_status','orders.date', 'orders.grand_total','order_referal_commision.referal_commision_discount','order_referal_commision.wallet_status')
        ->groupBy('orders.id')
        ->get();
        
        $orders = $order->map(function($item) {
            $item->shipping_address = json_decode($item->shipping_address);
            $item->referal_commision_discount = is_null($item->referal_commision_discount)?0:$item->referal_commision_discount;
            $item->date = Carbon::createFromTimestamp($item->date)->format('d-m-Y');
            $item->grand_total = (double)($item->grand_total+$item->wallet_amount);
            return $item;
           });


        return response()->json([
            'success'=>true,
            'status' => 200,
            'Detail'=> $orders
        ]);


    }

    public function savingDetail(Request $request){


        $savingOrder = DB::table('orders')
            ->where('orders.user_id','=',$request->user_id)
            ->where('orders.order_type','self')
            ->where('log',0)
            ->whereNotIn('orders.order_status',['cancel'])
            ->select('orders.code','orders.referal_discount as discount','orders.total_shipping_cost as shipping_cost','orders.payment_type','orders.shipping_address','orders.wallet_amount', 'orders.payment_status','orders.date', 'orders.grand_total')
            ->groupBy('orders.id')
            ->get();

        $savingOrders = $savingOrder->map(function($item) {
            $item->shipping_address = json_decode($item->shipping_address);
            $item->discount = is_null($item->discount)?0:$item->discount;
            $item->date = Carbon::createFromTimestamp($item->date)->format('d-m-Y');
            $item->grand_total = (double)($item->grand_total+$item->wallet_amount);
            return $item;
           });
        return response()->json([
            'success'=>true,
            'status' => 200,
            'Detail' => $savingOrders
        ]);


    }
}   
