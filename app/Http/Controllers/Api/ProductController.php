<?php

namespace App\Http\Controllers\Api;

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
use Illuminate\Http\Request;
use DB;
use App\Attribute;
use App\PeerPartner;
use App\PeerSetting;
use App\ProductStock;
use App\Models\Cart;
use App\Wishlist;

class ProductController extends Controller
{
    use LocationTrait;
    public function index()
    {
        return new ProductCollection(Product::latest()->paginate(10));
    }

    public function show($id)
    {
        //dd($_SERVER["HTTP_PEER"]);
        $sorting_hub_price = 0;
        $stock_price = ProductDetailCollection::stock_price($id);
        $shortId = "";
        $pincode = "";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            $sorting_hub_price = ProductDetailCollection::sorting_hub_price($pincode,$id);
        }
        if(!empty($_SERVER['HTTP_PEER'])){
            $peer_code = $_SERVER['HTTP_PEER'];
            $peer_price = ProductDetailCollection::peerCodeRate($peer_code, $id,$shortId);
               
        }
        ProductDetailCollection::discount($id,$shortId);
        ProductDetailCollection::stockQuantity($pincode,$id);
        ProductDetailCollection::cartData($id);
        ProductDetailCollection::isReviewAble($id);
        
        return new ProductDetailCollection(Product::where('id', $id)->where('published',1)->get());
       
    }

    public function admin()
    {
        return new ProductCollection(Product::where('added_by', 'admin')->latest()->paginate(10));
    }

    public function seller()
    {
        return new ProductCollection(Product::where('added_by', 'seller')->latest()->paginate(10));
    }

    public function category($id)
    {
        $productIds = [];
        $shortId = "";
        $id = request('id');
        $key = request('key');
        if(isset($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                // $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->get();
                
            }
        }
        $products  = Product::where('category_id', $id);
        
        if(!empty($productIds)){
            $products->whereIn('id',$productIds);
        }

        if(!empty($key)){
            $products->whereRaw('json_contains(json_tags, \'["' . strtolower($key). '"]\')');
        }

       $product = $products->latest()->paginate(10);
           
                $sorting_hub_price = 0;
                $peerdetail = null;
                
                
                $categories = new ProductCollection($this->products_with_peer_price($product,$shortId));

            
        return $categories;
    }

    public function subCategory($id)
    {
        return new ProductCollection(Product::where('subcategory_id', $id)->latest()->paginate(10));
    }

    public function subSubCategory($id)
    {
        return new ProductCollection(Product::where('subsubcategory_id', $id)->latest()->paginate(10));
    }

    public function brand($id)
    {
        return new ProductCollection(Product::where('brand_id', $id)->latest()->paginate(10));
    }

    public function todaysDeal()
    {
        $productIds = [];
        if(isset($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_CITY'])){

           

            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
            }
            $products = Product::whereIn('id',$productIds)->where('todays_deal', 1)->latest()->get();
            $sorting_hub_price = 0;
            foreach($products as $key => $product){
                $sorting_hub_price = $this->sortingHubPrice($product->id,$shortId);
                $products[$key]['sorting_hub_price'] = $sorting_hub_price;
            }
            return new ProductCollection($products);
        }
        else{
            return new ProductCollection(Product::where('todays_deal', 1)->latest()->get());
        }
        
    }

    public function flashDeal()
    {
        $shortId = "";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        }
        //$flash_deals = FlashDeal::where('sorting_hub_id',$shortId->sorting_hub_id)->get();
      
        $flash_deals = FlashDeal::where('sorting_hub_id',$shortId->sorting_hub_id)->where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
        return new FlashDealCollection($flash_deals);
    }

    public function featured()
    {
        $productIds = [];
        $sortId = "";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::whereIn('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                // $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->get();
            }
        }
               
                    if(!empty($productIds))
                    {
                        $products_id = Product::where('featured', 1)->whereIn('id',$productIds)->latest()->get();
                    }
                    else{
                        $products_id = Product::where('featured', 1)->latest()->get();
                    }
                    

                    $all_productid = array();
                    $peerdetail = null;
                    $sorting_hub_price = 0;
                    foreach($products_id as $key=>$row){
                        $id =  $row['id'];
                        if(!empty($_SERVER['HTTP_PEER'])){
                        $peer_code = $_SERVER['HTTP_PEER'];
                        $peerdetail = ProductCollection::peerCodeRate($peer_code, $id,$shortId);
                        // array_push($all_productid, $peerdetail);
                        }
                        if(!empty($shortId)){
                            $sorting_hub_price = \App\MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first()->selling_price;
                        
                        }
                        $products_id[$key]['stock_price'] = $this->stock_price($row['id']);
                        $products_id[$key]['sorting_hub_price'] = $sorting_hub_price;
                        $products_id[$key]['peer_price'] = $peerdetail;
                }
                
                return new ProductCollection($products_id);
           
    }

    public function bestSeller()
    {
        $productIds = [];
        $shortId = "";

        if(isset($_SERVER['HTTP_USERID']) && $_SERVER['HTTP_USERID'] != NULL){
            $user_id = $_SERVER['HTTP_USERID'];
        }else{
            $user_id = NULL;
        }

        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            if(is_null($shortId)){
                return response()->json([
                    'status'=>false,
                    //"message"=>"Service not available at this location"
                ]);
            }

            if(!empty($shortId)){
                $productIds = \App\MappingProduct::whereIn('product_id',bestSellingProduct($shortId['sorting_hub_id']))->where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->where('flash_deal',0)->pluck('product_id');
               
            }
        }

            if(!empty($productIds)){
                $products_id = \App\Product::with('category')->where('published', '1')->whereIn('id',$productIds)->take(10)->get();

            }
            else{
                $products_id = \App\Product::with('category')->where('published', '1')->take(10)->orderBy('created_at','desc')->get();
            }
            
            $peerdetail = null;
            $sorting_hub_price = 0;
            
            // dd($products_id);
            return new ProductCollection($this->products_with_peer_price($products_id,$shortId,$user_id));
        
        
    }

    public function related($id)
    {
        $product = Product::find($id);
        $productIds = [];
        $shortId = "";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
            }
        }
            if(!empty($productIds))
            {
                $products = \App\Product::whereIn('id',$productIds)->where('published',1)->where('subcategory_id', $product->subcategory_id)->where('id', '!=', $id)->limit(10)->get();
         
            }
            else{
                $products = Product::where('subcategory_id', $product->subcategory_id)->where('id', '!=', $id)->where('published',1)->limit(10)->get();
            }
               

            return new ProductCollection($this->products_with_peer_price($products,$shortId));

    }

    public function topFromSeller($id)
    {
        $product = Product::find($id);
        return new ProductCollection(Product::where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc')->limit(4)->get());
    }

    public function search()
    {
        $key = request('key');
        $scope = request('scope');
        $category_id = request('category_id');
        $shortId = "";
        $productIds = [];
        $conditions = [];
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        

            if(is_null($shortId)){
                return response()->json([
                    'success'=>false,
                    //"message"=>"Service not available at this location"
                ]);
            }
            if(!empty($shortId)){

                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                if(empty($key)){
                    $productIds = \App\MappingProduct::whereIn('product_id',bestSellingProduct($shortId['sorting_hub_id']))->where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->where('flash_deal',0)->pluck('product_id');

                }
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
            }
        }

        $conditions['published'] = 1;
        $conditions['search_status'] = 1;
        if(!empty($category_id))
        {
            $conditions['category_id'] = $category_id;
        }

        if(!empty($productIds))
        {
            $products = Product::where($conditions)->whereIn('id',$productIds);

        }
        else{
            $products = Product::where($conditions);
        }

        if(!empty($key))
        {
            $products->whereRaw('json_contains(json_tags, \'["' . strtolower($key). '"]\')');
        }
        switch ($scope) {

            case 'price_low_to_high':
                $collection = new SearchProductCollection($this->products_with_peer_price($products->orderBy('unit_price', 'asc')->paginate(10),$shortId));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                return $collection;

            case 'price_high_to_low':
                $collection = new SearchProductCollection($this->products_with_peer_price($products->orderBy('unit_price', 'desc')->paginate(10),$shortId));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                return $collection;

            case 'new_arrival':
                $collection = new SearchProductCollection($this->products_with_peer_price($products->orderBy('created_at', 'desc')->paginate(10),$shortId));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                return $collection;

            case 'popularity':
                $collection = new SearchProductCollection($this->products_with_peer_price($products->orderBy('num_of_sale', 'desc')->paginate(10),$shortId));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                return $collection;

            case 'top_rated':
                $collection = new SearchProductCollection($this->products_with_peer_price($products->orderBy('rating', 'desc')->paginate(10),$shortId));
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                return $collection;

            default:
                $collection = new SearchProductCollection($this->products_with_peer_price($products->orderBy('num_of_sale', 'desc')->paginate(10),$shortId));
                   
                $collection->appends(['key' =>  $key, 'scope' => $scope]);
                return $collection;
        }
    }

    public function variantPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $str = '';
        $tax = 0;

        if ($request->has('color')) {
            $data['color'] = $request['color'];
            $str = Color::where('code', $request['color'])->first()->name;
        }

        foreach (json_decode($request->choice) as $option) {
            $str .= $str != '' ?  '-'.str_replace(' ', '', $option->name) : str_replace(' ', '', $option->name);
        }

        if($str != null && $product->variant_product){
            $product_stock = $product->stocks->where('variant', $str)->first();
            $price = $product_stock->price;
            $stockQuantity = $product_stock->qty;
        }
        else{
            $price = $product->unit_price;
            $stockQuantity = $product->current_stock;
        }

        //discount calculation
        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $key => $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                if($flash_deal_product->discount_type == 'percent'){
                    $price -= ($price*$flash_deal_product->discount)/100;
                }
                elseif($flash_deal_product->discount_type == 'amount'){
                    $price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }
        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        if ($product->tax_type == 'percent') {
            $price += ($price*$product->tax) / 100;
        }
        elseif ($product->tax_type == 'amount') {
            $price += $product->tax;
        }

        return response()->json([
            'product_id' => $product->id,
            'variant' => $str,
            'price' => (double) $price,
            'in_stock' => $stockQuantity < 1 ? false : true
        ]);
    }

    public function home()
    {
        return new ProductCollection(Product::inRandomOrder()->take(50)->get());
    }


    public function ProductSorting(Request $request)
    {

        $category_id = $request->category_id;
        $subcategory_id = $request->subcategory_id;
        $page = $request->page;
        $seller_id = $request->seller_id;
        $minprice = $request->min_price;
        $maxprice = $request->max_price;
        $sort_by = $request->sort_by;
        $attributes = $request->attribute;
        $key = $request->key;

        $flash_deal_id = $request->flash_deal_id; //14-10-2021

        $dataArray = array();
        $shortId = "";
        $filterProducts = Product::where(['products.published'=>1,'search_status'=>1]);
        $filterProducts->select('products.*','mapping_product.selling_price','product_stocks.price');
        $filterProducts->join('product_stocks','product_stocks.product_id','=','products.id');
        $filterProducts->join('mapping_product','mapping_product.product_id','=','products.id');

        

        //14-10-2021
        if(!empty($flash_deal_id)){
            $filterProducts->join('flash_deal_products','flash_deal_products.product_id','=','products.id'); 
            $filterProducts->where('flash_deal_products.flash_deal_id',$flash_deal_id);
        }

        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            if(is_null($shortId)){
                return response()->json([
                    'status'=>false,
                    //"message"=>"Service not available at this location"
                ]);
            }
            if(!empty($shortId)){
                //$productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                $filterProducts->where('mapping_product.sorting_hub_id',$shortId['sorting_hub_id']);
                $filterProducts->where('mapping_product.published',1);
                //echo count($productIds);exit;
                //echo implode(',',$productIds);exit;
                //$categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                // $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->get();
                //$filterProducts->whereIn('products.id',$productIds)->where('sorting_hub_id',$shortId['sorting_hub_id']);

            }



        }


        

        if($category_id != null){
                    $filterProducts->where('products.category_id', $category_id);
            }

        if($subcategory_id != null){
            $filterProducts->where('subcategory_id', $subcategory_id);
        }

        if($request->has('max_price') && $maxprice != null){
            //if pincode selected sorting by hub price
                // if hub price ==0
                // sorting by stock price

            //else
             // sorting by stock price 
             if(!empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY']) && isset($_SERVER['HTTP_CITY'])){
                $filterProducts->whereBetween(DB::raw('IF(selling_price<=0,price,selling_price)'), [$minprice, $maxprice]);
            }
            else{
                $filterProducts->whereBetween('price', [$minprice, $maxprice]);
            }

            
        }
        
        if($seller_id != null){
          
            $filterProducts->whereIn('seller_id', $seller_id); 
        }

        if(!empty($key)){
            $filterProducts->whereRaw('json_contains(json_tags, \'["' . strtolower($key). '"]\')');
        }
       
      

        if($request->has('sort_by') && $sort_by != null){
           
            switch ($sort_by) {
                case '1':
                    $filterProducts->orderBy('products.created_at', 'desc');
                    break;
                case '2':
                    $filterProducts->orderBy('products.created_at', 'asc');
                    break;
                case '3':
                    if(!empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY']) && isset($_SERVER['HTTP_CITY'])){
             
                    $filterProducts->orderBy(DB::raw("IF(selling_price=0,price,selling_price)"), 'asc');
                    }
                    else{
                        $filterProducts->orderBy("price", 'asc');
                    }
                    break;
                case '4':
                  
                    if(!empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY']) && isset($_SERVER['HTTP_CITY'])){
             
                        $filterProducts->orderBy(DB::raw("IF(selling_price=0,price,selling_price)"), 'desc');
                        }
                        else{
                            $filterProducts->orderBy("price", 'desc');
                        }
                    break;
                default:
                    // code...
                    break;
            }
        }

        //echo $filterProducts->toSql();exit;

        $test = array();
        if($request->has('attribute')){
            //$products =$filterProducts->get();
            $dataArray = $filterProducts->get();//new ProductCollection($products);
        foreach($dataArray as $key=>$row){

            if(!empty($row['choice_options'])){
                $decodeAttr = json_decode($row['choice_options']);
                foreach($decodeAttr as $k=>$r){
                    if(in_array($r->attribute_id,$attributes['attribute_id'])){
                       foreach($r->values as $vk=>$v){
                        if(in_array(strtolower($v),$attributes['values'])){
                           array_push($test, $row['id']);
                        }
                        
                       } 
                    }
                    
                }
               

            }

        }
    }

      
        if($request->has('attribute') && !empty($attributes['attribute_id'])){
            
            return new ProductCollection($this->products_with_peer_price($filterProducts->whereIn('products.id',$test)->groupBy('products.id')->paginate(10),$shortId));

        }else{
           
           
            return new ProductCollection($this->products_with_peer_price($filterProducts->groupBy('products.id')->paginate(10),$shortId));
        }
       

    }
    public function getAllCategory()
    {
        $dataArray = array();
        $productIds = array();
        $sorting_hub_price = 0;
        $shortId = "";
        $base_url  = "https://www.rozana.in/";
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){           

            $pincode = $_SERVER['HTTP_PINCODE'];

            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
            if(is_null($shortId)){
                return response()->json([
                    'status'=>false,
                    //"message"=>"Service not available at this location"
                ]);
            }
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->where('published',1)->pluck('product_id')->all();
                //$categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                //$categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->get();
                    
            }else{
                //$categoryIds = array();
                $productIds = array();
                //$categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->get();
            }

        }else{
            
            //$categoryIds = array();
            $productIds = array();
            //$categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->get();
        }
        $categories = featured_categories($shortId);

        foreach($categories as $key=>$row){
            $to_remove = array('20','27','31','19');
            if(in_array($row->id,$to_remove))
            {
                continue;
            }
            
            // $dataArray[$key]['products']  = new AllCategoryResource(Product::where('category_id',$row->id)->limit(5)->get());

            $dataArray[$key]['category_name'] = trans($row->name);
            $dataArray[$key]['url'] = $base_url."api/v1/products/category/".$row->id;
            $dataArray[$key]['category_banner'] = $row->banner;
            $dataArray[$key]['products']  = DB::table('products') 
            ->where(['category_id'=>$row->id,'published'=>1,'search_status'=>1])
            ->when(!empty($productIds), function ($query) use ($productIds) {
                $query->whereIn('id',$productIds);
            })
            // ->orderby('ordering','asc')
            ->limit(6)
            // ->select('id','name','thumbnail_img','unit_price','purchase_price')
            ->select(['id',trans('name'),'thumbnail_img','unit_price','discount','max_purchase_qty'])
            ->addSelect(DB::raw("'links'"))
            ->inRandomOrder()
            ->orderBy('created_at','desc')
            ->get();

            // print_r($dataArray[$key]['products']);die;
            foreach($dataArray[$key]['products'] as $k=>$r){
                $cart_qty = 0;
                $cart_id = 0;
                $customer_discount = 0;
                $discount_percentage = 0;

                if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
                    $device_id = $_SERVER['HTTP_DEVICE'];
                    $cart = Cart::where('device_id',$device_id)->where('product_id',$r->id)->first();
                    
                    if(!is_null($cart)){
                        $cart_qty = $cart->quantity;
                        $cart_id = $cart->id;
                    }
                }
               
                if(!empty($shortId)){
                    $sorting_hub_price = \App\MappingProduct::where('product_id',$r->id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first()->selling_price;
                    $peer_discount_check = PeerSetting::where('product_id', '"'.$r->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
                    if(!is_null($peer_discount_check)){
                        $customer_discount = $peer_discount_check->customer_off;
                        $discount_percentage = $peer_discount_check->customer_discount;
                    }
                    
                }
                else{
                    $peer_discount_check = PeerSetting::where('product_id', '"'.$r->id.'"')->latest('id')->first();
                    if(!is_null($peer_discount_check)){
                        $customer_discount = $peer_discount_check->customer_off;
                        $discount_percentage = $peer_discount_check->customer_discount;
                    }
                }
                
            if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER'])){
                    $id = $r->id;
                    $peer_code = $_SERVER['HTTP_PEER'];
                    $price = ProductCollection::peerCodeRate($peer_code, $id,$shortId);

                        $dataArray[$key]['products'][$k]->peer_price = $price;
                }else{
                    $peer_code = "";
                     $dataArray[$key]['products'][$k]->peer_price = null;
                }

                $variant = "";
                $productStock = ProductStock::where('product_id',$r->id)->first();
                if(!is_null($productStock)){
                    $variant = $productStock->variant;

                }

                if(isset($_SERVER['HTTP_USERID']) && $_SERVER['HTTP_USERID'] != NULL){
                    $user_id = $_SERVER['HTTP_USERID'];
                    $is_wishlist = $this->checkProductInWishlist($r->id,$user_id);
                }else{
                    $is_wishlist = false;
                }

                $dataArray[$key]['products'][$k]->stock_price = $this->stock_price($r->id);
                $dataArray[$key]['products'][$k]->sorting_hub_price = $sorting_hub_price;  
                $dataArray[$key]['products'][$k]->discount = $this->customer_discount($r->id,$shortId,$peer_code);//$customer_discount;
                $dataArray[$key]['products'][$k]->discount_percentage = (double)substr($discount_percentage,1,-1);      
                $dataArray[$key]['products'][$k]->name = trans($r->name);
                $dataArray[$key]['products'][$k]->cart_qty = $cart_qty;
                $dataArray[$key]['products'][$k]->cart_id = $cart_id;
                $dataArray[$key]['products'][$k]->variant = $variant;
                $dataArray[$key]['products'][$k]->max_purchase_qty = $r->max_purchase_qty;

                $r->links = ['details'=>$base_url."api/v1/products/".$r->id,

                          'related'=>$base_url."api/v1/products/related/".$r->id,

                          'reviews'=>$base_url."api/v1/reviews/product/".$r->id

                ];
                $dataArray[$key]['products'][$k]->in_wishlist = $is_wishlist;

            }
        }
        // return $dataArray;

        return response()->json([

            'status' => true,

            'message' => 'All category list.',

            'data' => $dataArray

        ]);

    }

    
    public function getProductAttribute(){
        
        $category_id = request('category_id');
        $subcategory_id = request('subcategory_id');
        if(empty($subcategory_id)){
        $pricerange =   DB::table('products')
                        ->leftjoin('product_stocks','product_stocks.product_id','=','products.id')
                        ->where('products.category_id',$category_id)
                        ->select(DB::raw('MIN(unit_price) as min_price,MAX(unit_price) as max_price')) 
                        ->get();
        
        $products = Product::where('category_id',$category_id);
        }else{
        $pricerange =   DB::table('products')
                        ->leftjoin('product_stocks','product_stocks.product_id','=','products.id')
                        ->where('products.subcategory_id',$subcategory_id)
                        ->select(DB::raw('MIN(unit_price) as min_price,MAX(unit_price) as max_price')) 
                        ->get();
        $products = Product::where('subcategory_id',$subcategory_id);

        }
        
        $pricerange = $pricerange->map(function($item){
            $item->min_price = ceil($item->min_price);
            $item->max_price = ceil($item->max_price);
            return $item;
        });
       
        $non_paginate_products = filter_products($products)->get();
        //Attribute Filter
        $attributes = array();
        foreach ($non_paginate_products as $key => $product) {
            if($product->attributes != null && is_array(json_decode($product->attributes))){
                foreach (json_decode($product->attributes) as $key => $value) {
                    $flag = false;
                    $pos = 0;
                    foreach ($attributes as $key => $attribute) {
                       
                      
                        if($attribute['id'] == $value){
                            $flag = true;
                            $pos = $key;
                            break;
                        }
                    }
                    if(!$flag){
                        $item['id'] = $value;
                        $item['name'] = Attribute::where('id',$value)->first()->name;
                        $item['values'] = array();
                        foreach (json_decode($product->choice_options) as $key => $choice_option) {
                            if($choice_option->attribute_id == $value){
                                $item['values'] = $choice_option->values;
                                break;
                            }
                        }
                        array_push($attributes, $item);
                    }
                    else {
                        foreach (json_decode($product->choice_options) as $key => $choice_option) {
                            if($choice_option->attribute_id == $value){
                                foreach ($choice_option->values as $key => $value) {
                                    if(!in_array($value, $attributes[$pos]['values'])){
                                        array_push($attributes[$pos]['values'], $value);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } 

        foreach($attributes as $k=>$v){
            // sort($attributes[$k]['values']);
            
                $value = array();
                $arrangevalue = array();
                // dd($v['values']);
                foreach($v['values'] as $attrk=>$attrv){
                    
                        $qty = explode(" ",$attrv);
                        if(isset($qty[1])){
                            $value[$qty[0]] = $qty[1];  
                        }
                }

                ksort($value); 
                $attributes[$k]['values'] = array();
                $i = 0;
                foreach($value as $arrk=>$arrv){
                    // array_push($arrangevalue ,$arrk." ".$arrv);
                    $attributes[$k]['values'][$i] = $arrk." ".strtolower($arrv);
                    $i++;
                }
                // dd($arrangevalue);
               
                // array_push($attributes[$k]['values'],$arrangevalue);
            
        }


        if(!empty($attributes)){
        
        return response()->json([
            'status' => true,
            'message' => 'Attributes',
            'pricerange'=> $pricerange,
            'data' =>  $attributes 
        ]);
    }else{
        return response()->json([
            'status' => false,
            'message' => 'Attributes',
            'pricerange'=> '',
            'data' =>  '' 
        ]);
    }

    }

  public function applyPartner(Request $request)
    {
        
        $coupon = PeerPartner::where(['code' => $request->peercode, 'verification_status' => 1,'peertype_approval' => 0])->first();
        if(!empty($coupon)){
            if(isset($request->user_id) && !empty($request->user_id)){
                //update referral code in users table 
                \App\User::where('id',$request->user_id)->update(['used_referral_code'=>$request->peercode]);
            }
            
            return response()->json([
                'status' => true,
                'message' => 'Referral code has been applied',
                'peercode'=> $request->peercode
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid coupon!',
                'peercode'=> '' 
            ]);
        }
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

    public function products_with_peer_price($products,$shortId,$user_id = NULL){
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

        if(!is_null($user_id)){
            $is_wishlist = $this->checkProductInWishlist($product->id,$user_id);
        }else{
            $is_wishlist = false;
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
        $products[$key]['in_wishlist'] = $is_wishlist;
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

   public function checkProductInWishlist($product_id,$user_id){
        $product = Wishlist::where(['product_id' => $product_id, 'user_id' => $user_id])->count();
        if ($product > 0){
            return true;
        }else{
            return false;
        }
    }

    
}
