<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\ProductCollection;
use App\Http\Resources\v2\ProductDetailCollection;
use App\Http\Resources\v2\SearchProductCollection;
use App\Http\Resources\v2\FlashDealCollection;
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
use App\Review;

class ProductController extends Controller
{
    use LocationTrait;
    public function index()
    {
        return new ProductCollection(Product::latest()->paginate(10));
    }

    public function show($id)
    {
        
        $shortId = '';
        $product = Product::where('id', $id)->where('published',1)->get();

        ProductDetailCollection::isReviewAble($id);
        ProductDetailCollection::yourReview($id);
        
        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
        }
        if(count($product) > 0){
            return new ProductDetailCollection($this->products_with_peer_price($product,$shortId));
        }else{
            return [
                'success' => false,
                'message' => 'Unpublished Product.'
            ];
        }
        
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

        $products  = Product::where('category_id', $id)->select('id','name','photos','thumbnail_img','choice_options','todays_deal','featured','unit','num_of_sale','rating','max_purchase_qty','category_id','subcategory_id');

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
                if(!empty($productIds)){
                    $products->whereIn('id',$productIds);
                }
            }
        }
                       

        if(!empty($key)){
            $products->whereRaw('json_contains(json_tags, \'["' . strtolower($key). '"]\')');
        }

        $product = $products->latest()->paginate(10);
        $categories = new ProductCollection($this->products_with_peer_price($product,$shortId));
        return $categories;
    }

    public function subCategory($id)
    {
        return new ProductCollection(Product::where('subcategory_id', $id)->latest()->paginate(10));
    }

    public function subSubCategory($id)
    {
        $shortId = "";
        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
                if(!empty($productIds)){
                    $product = Product::whereIn('id',$productIds)->where('subsubcategory_id', $id)->latest()->paginate(10);
                }
            }
        }else{
            $product = Product::where('subsubcategory_id', $id)->latest()->paginate(10);
        }
        return new ProductCollection($this->products_with_peer_price($product,$shortId));
    }

    public function brand($id)
    {
        return new ProductCollection(Product::where('brand_id', $id)->latest()->paginate(10));
    }

    public function todaysDeal()
    {
    
        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            $products = Product::whereIn('id', function($query) use ($shortId){
                                        $query->select('product_id')
                                        ->from('mapping_product')->where('sorting_hub_id',$shortId);
                                    })->select('id','name','photos','thumbnail_img','choice_options','todays_deal','featured','unit','num_of_sale','rating','max_purchase_qty','category_id','subcategory_id')->where('todays_deal', 1)->latest()->get();
        }else{
            $products = Product::where('todays_deal', 1)->latest()->get();
        }
        return new ProductCollection($this->products_with_peer_price($products,$shortId));
        
    }

    public function flashDeal()
    {

        $shortId = $_SERVER['HTTP_SORTINGHUBID'];
        $flash_deals = FlashDeal::where('sorting_hub_id',$shortId)->where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();

        return new FlashDealCollection($flash_deals);
    }

    public function featured(Request $request)
    {
        $shortId = "";
        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
            }
        }
               
        if(!empty($productIds))
        {
            $products_id = Product::where('featured', 1)->whereIn('id',$productIds)->latest()->get();
        }else{
            $products_id = Product::where('featured', 1)->latest()->get();
        }
    
    return new ProductCollection($this->products_with_peer_price($products_id,$shortId));
           
    }

    public function bestSeller()
    {
        $productIds = [];
        $shortId = "";

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){

            $shortId = $_SERVER['HTTP_SORTINGHUBID'];

            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('published',1)->pluck('product_id')->all();
            }

        }

            if(!empty($productIds)){
                $products_id = \App\Product::with('category')->where('published', '1')->whereIn('id',$productIds)->take(10)->get();
            }
            else{
                $products_id = \App\Product::with('category')->where('published', '1')->take(10)->orderBy('created_at','desc')->get();
            }
            
            return new ProductCollection($this->products_with_peer_price($products_id,$shortId));
        
    }

    public function related(Request $request,$id)
    {

        $product = Product::find($id);
        $productIds = [];
        $shortId = "";

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){

            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
                // $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('published',1)->pluck('product_id')->all();
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
        $shortId = '';
        $product = Product::find($id);
        $products = Product::where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc')->limit(4)->get();
        return new ProductCollection($this->products_with_peer_price($products,$shortId));
    }

    public function search(Request $request)
    {
        $key = request('key');
        $scope = request('scope');
        $category_id = request('category_id');
        $shortId = "";
        $productIds = [];
        $conditions = [];

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];

            if(is_null($shortId)){
                return response()->json([
                    'success'=>false,
                ]);
            }

            if(!empty($shortId)){
               $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
                if(empty($key)){
                    $productIds = bestSellingProduct($shortId);
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

    public function variantPrice(Request $request){
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
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->select('discount_type','discount')->first();

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

        $category_id = $request->category;
        $subcategory_id = $request->subcategory_id;
        $page = $request->page;
        $seller_id = $request->seller_id;
        $minprice = $request->min_price;
        $maxprice = $request->max_price;
        $sort_by = $request->sort_by;
        $attributes = $request->attribute;
        $key = $request->key;

        $flash_deal_id = $request->flash_deal_id;

        $dataArray = array();
        $shortId = "";
        $filterProducts = Product::where(['products.published'=>1,'search_status'=>1]);
        $filterProducts->select('products.*','mapping_product.selling_price','product_stocks.price');
        $filterProducts->join('product_stocks','product_stocks.product_id','=','products.id');
        $filterProducts->join('mapping_product','mapping_product.product_id','=','products.id');

        if(!empty($flash_deal_id)){
            $filterProducts->join('flash_deal_products','flash_deal_products.product_id','=','products.id'); 
            $filterProducts->where('flash_deal_products.flash_deal_id',$flash_deal_id);
        }

        // if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
        //     $pincode = $_SERVER['HTTP_PINCODE'];
        //     $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
        //     if(is_null($shortId)){
        //         return response()->json([
        //             'status'=>false,
        //             //"message"=>"Service not available at this location"
        //         ]);
        //     }
        //     if(!empty($shortId)){
        //         $filterProducts->where('mapping_product.sorting_hub_id',$shortId['sorting_hub_id']);
        //         $filterProducts->where('mapping_product.published',1);
        //     }
        // }

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
                $filterProducts->where('mapping_product.sorting_hub_id',$shortId);
                $filterProducts->where('mapping_product.published',1);
            }
        }
    

        if($category_id != null){
            $filterProducts->where('products.category_id', $category_id);
        }

        if($subcategory_id != null){
            $filterProducts->where('products.subcategory_id', $subcategory_id);
        }

        if($request->has('max_price') && $maxprice != null){
            //if pincode selected sorting by hub price
                // if hub price ==0
                // sorting by stock price

            //else
             // sorting by stock price 
             if(!empty($shortId)){
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
                    // if(!empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY']) && isset($_SERVER['HTTP_CITY'])){
             if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
                    $filterProducts->orderBy(DB::raw("IF(selling_price=0,price,selling_price)"), 'asc');
                    }
                    else{
                        $filterProducts->orderBy("price", 'asc');
                    }
                    break;
                case '4':
                  
                    // if(!empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY']) && isset($_SERVER['HTTP_CITY'])){
             if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
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
            //$products = $filterProducts->get();
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

    public function getAllCategory(Request $request)
    {

        $dataArray = array();
        $categoryIds = array();
        $productIds = array();
        $shortId = "";
        // $base_url  = "https://www.rozana.in/";
        $base_url  = "https://prelive.rozana.in/";

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];

            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('published',1)->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->select('id','name','banner')->get();
                    
            }else{
                $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->select('id','name','banner')->get();
            }
        }else{
            $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')
            ->select('id','name','banner')->get();
        }


        $dataArray = $categories->map(function($item) use($base_url,$productIds,$shortId){
            $item->category_name = $item->name;
            $item->url = $base_url."api/v2/products/category/".$item->id;
            $item->category_banner = $base_url."api/v2/products/category/".$item->banner;
            $product = DB::table('products') 
            ->where(['category_id'=>$item->id,'published'=>1,'search_status'=>1])
            ->when(!empty($productIds), function ($query) use ($productIds) {
                $query->whereIn('id',$productIds);
            })
            ->limit(6)
            ->select(['id',trans('name'),'thumbnail_img','max_purchase_qty'])
            ->addSelect(DB::raw("'links'"))
            ->inRandomOrder()
            ->orderBy('created_at','desc')
            ->get();

            $cart_qty = 0;
            $cart_id = 0;
            $in_wishlist = false;

            $item->products = $product->map(function($data) use($base_url,$shortId,$cart_qty,$cart_id,$in_wishlist){
                
                if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
                    $device_id = $_SERVER['HTTP_DEVICE'];
                    $cart = Cart::where('device_id',$device_id)->where('product_id',$data->id)->select('quantity','id')->first();
                    
                    if(!is_null($cart)){
                        $cart_qty = $cart->quantity;
                        $cart_id = $cart->id;
                    }
                }
                $data->cart_qty = $cart_qty;
                $data->cart_id = $cart_id;

                $priceDetail = calculatePrice($data->id,$shortId);

                $variant = "";
                $productStock = ProductStock::where('product_id',$data->id)->select('variant')->first();
                if(!is_null($productStock)){
                    $variant = $productStock->variant;

                }

                if(isset($_SERVER['HTTP_USERID']) && $_SERVER['HTTP_USERID'] != NULL){
                    $user_id = $_SERVER['HTTP_USERID'];
                    $is_wishlist = $this->checkProductInWishlist($data->id,$user_id);
                    $wishlist_id = (integer) Wishlist::where(['product_id' => $data->id, 'user_id' => $user_id])->pluck('id')->first();
                }else{
                    $is_wishlist = false;
                    $wishlist_id = 0;
                }

                $review = Review::where('product_id',$data->id)->get();
                if(!empty($review)){
                    $count = count($review);
                    $ratingSUM = Review::where('product_id',$data->id)->select(DB::raw('SUM(rating) as rating'))->first();
                    if($count > 0){
                        $rating = $ratingSUM->rating/$count;
                    }else{
                        $rating = $ratingSUM->rating;
                    }
                    
                }else{
                    $rating = '0.0';
                }

                // $data->stock_price = $this->stock_price($data->id);
                // $data->sorting_hub_price = $sorting_hub_price;
                // $data->discount = $this->customer_discount($data->id,$shortId,$peer_code);
                // $data->discount_percentage = (double)substr($discount_percentage,1,-1);
                $data->variant = $variant;
                $data->links = ['details'=>$base_url."api/v2/products/".$data->id,
                          'related'=>$base_url."api/v2/products/related/".$data->id,
                          'reviews'=>$base_url."api/v2/reviews/product/".$data->id
                ];
                $data->in_wishlist = $is_wishlist;
                $data->wishlist_id = $wishlist_id;
                $data->rating = (!is_null($rating))?$rating:'0.0';
                $data->MRP = $priceDetail['MRP'];
                $data->stock_price = $priceDetail['MRP'];
                $data->base_price = $priceDetail['selling_price'];
                $data->discount = $priceDetail['customer_off'];
                $data->discount_percentage = json_decode($priceDetail['customer_discount']);
                $data->discount_type = json_decode($priceDetail['discount_type']);
                return $data;
            });

            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => 'All category list.',
            'data' => $dataArray
        ]);

    }
    
   /*public function getAllCategory(Request $request)
    {

        $dataArray = array();
        $productIds = array();
        $sorting_hub_price = 0;
        $shortId = "";
        $base_url  = "https://www.rozana.in/";

        
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){           
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            if(is_null($shortId)){
                return response()->json([
                    'status'=>false,
                ]);
            }
            $shortId = $shortId['sorting_hub_id'];

            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('published',1)->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->whereIn('id',$categoryIds)->get();
                    
            }else{
                $categoryIds = array();
                $productIds = array();
                $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->get();
            }

        }else{
            
            $categoryIds = array();
            $productIds = array();
            $categories = \App\Category::where(['status'=> 1,'featured'=>1])->orderBy('sorting','asc')->get();
        }

        foreach($categories as $key=>$row){
            
            // $dataArray[$key]['products']  = new AllCategoryResource(Product::where('category_id',$row->id)->limit(5)->get());

            $dataArray[$key]['category_name'] = trans($row->name);
            $dataArray[$key]['url'] = $base_url."api/v1/products/category/".$row->id;
            $dataArray[$key]['category_banner'] = $base_url."api/v1/products/category/".$row->banner;
            $dataArray[$key]['products']  = DB::table('products') 
            ->where(['category_id'=>$row->id,'published'=>1,'search_status'=>1])
            ->when(!empty($productIds), function ($query) use ($productIds) {
                $query->whereIn('id',$productIds);
            })
            ->limit(6)
            ->select(['id',trans('name'),'thumbnail_img','unit_price','discount','max_purchase_qty'])
            ->addSelect(DB::raw("'links'"))
            ->inRandomOrder()
            ->orderBy('created_at','desc')
            ->get();


            foreach($dataArray[$key]['products'] as $k=>$r){
                $cart_qty = 0;
                $cart_id = 0;
                $customer_discount = 0;
                $discount_percentage = 0;
                $in_wishlist = false;

                if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
                    $device_id = $_SERVER['HTTP_DEVICE'];
                    $cart = Cart::where('device_id',$device_id)->where('product_id',$r->id)->first();
                    
                    if(!is_null($cart)){
                        $cart_qty = $cart->quantity;
                        $cart_id = $cart->id;
                    }
                }
               
                if(!empty($shortId)){
                    $sorting_hub_price = \App\MappingProduct::where('product_id',$r->id)->where('sorting_hub_id',$shortId)->first()->selling_price;
                    $peer_discount_check = PeerSetting::where('product_id', '"'.$r->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId. '"]\')')->latest('id')->first();           
                    if(!is_null($peer_discount_check)){
                        $customer_discount = $peer_discount_check->customer_off;
                        $discount_percentage = $peer_discount_check->customer_discount;
                    }
                    
                }else{
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

        return response()->json([
            'status' => true,
            'message' => 'All category list.',
            'data' => $dataArray
        ]);

    }*/

    
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
        // $product = \App\MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();

        $product = \App\MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId)->first();
        if($product !=null)
        {
            $price = $product->selling_price;
        }
        return $price;
    }

    public function products_with_peer_price($products,$shortId){
        $quantity = 0;

        if(isset($_SERVER['HTTP_USERID']) && $_SERVER['HTTP_USERID'] != NULL){
            $user_id = $_SERVER['HTTP_USERID'];
        }else{
            $user_id = NULL;
        }
                
        foreach($products as $key => $product){
        $cart_qty = 0;
        $cart_id = 0;
        $customer_discount = 0;
        $discount_percentage = 0;
        if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
            $device_id = $_SERVER['HTTP_DEVICE'];
            $cart = Cart::where('device_id',$device_id)->where('product_id',$product->id)->select('quantity','id')->first();
            
            if(!is_null($cart)){
                $cart_qty = $cart->quantity;
                $cart_id = $cart->id;
            }
        }
        
        $qty = \App\ProductStock::where('product_id',$product->id)->pluck('qty')->first();
        if(!is_null($qty)){
            $quantity = $qty;
        }

        $priceDetail = calculatePrice($product->id,$shortId);

        $variant = "";
        $pvariant = $product->stocks->where('product_id',$product->id)->first();
        if(!is_null($pvariant))
        {
            $variant = $pvariant->variant;
        }

        $review = Review::where('product_id',$product->id)->get();
        if(!empty($review)){
            $count = count($review);
            $ratingSUM = Review::where('product_id',$product->id)->select(DB::raw('SUM(rating) as rating'))->first();
            if($count > 0){
                $rating = $ratingSUM->rating/$count;
            }else{
                $rating = $ratingSUM->rating;
            }
            
        }else{
            $rating = '0.0';
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
        $products[$key]['rating_avg'] = $rating;
        $products[$key]['MRP'] = $priceDetail['MRP'];
        $products[$key]['selling_price'] = $priceDetail['selling_price'];
        $products[$key]['customer_off'] = $priceDetail['customer_off'];
        $products[$key]['customer_discount'] = $priceDetail['customer_discount'];
        $products[$key]['discount_type'] = $priceDetail['discount_type'];
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

        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first();
        if(!empty($shortId)){
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId. '"]\')')->latest('id')->first();
        }
        else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
       
        // if(!empty($shortId)){
        //     $productM = \App\MappingProduct::where(['sorting_hub_id'=>$shortId,'product_id'=>$id])->first();

        //     $price = $productM['purchased_price'];
        //     $stock_price = $productM['selling_price'];
        //     if($price == 0 || $stock_price == 0){
        //         $price = $product->unit_price;
        //         $stock_price = $productstock['price'];
        //     }  
        // }else{
        //     $price = $product->unit_price;
        //     $stock_price = $productstock['price'];  
        // }

        // $last_price = 0;
        // if(!empty($peer_discount_check)){      
        //     // $main_discount = $stock_price - $price;
        //     if(!empty($peercode)){
        //          // $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
        //          // $last_price = ($main_discount * $discount_percent)/100; 
        //     }
        //     return $last_price;
        // } 

        $last_price = 0;
        if(!empty($peer_discount_check)){      
            if(!empty($peercode)){
                 $last_price = $peer_discount_check->customer_off;
            }
            return $last_price;
        }
        return $last_price;
    }

    public function checkProductInWishlist($product_id,$user_id){
        $product = Wishlist::where(['product_id' => $product_id, 'user_id' => $user_id])->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();

        if ($product > 0){
            return true;
        }else{
            return false;
        }
    }

    public function removePeercode(){
        if(isset($_SERVER['HTTP_USERID']) && !empty($_SERVER['HTTP_USERID'])){
            $user_id = $_SERVER['HTTP_USERID'];
    
            \App\User::where('id',$user_id)->update(['used_referral_code'=> NULL]);
                return response()->json([
                    'status' => true,
                    'message' => 'Referral code removed.'
                ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User id required.'
            ]);
        }

    }

    
}
