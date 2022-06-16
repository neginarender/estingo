<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\SearchCollection;
use App\Http\Resources\SearchHistory\v5\Collection;
use App\Http\Resources\v5\SearchTopCollection;
use App\Http\Resources\v5\ProductCollection;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchHistory;
use App\Models\ProductStock;
use App\PeerSetting;
use App\Models\Cart;
use Illuminate\Http\Request;
use DB;
use App\Wishlist;
use App\OrderDetail;
use Validator;

class SearchController extends Controller
{

      public function searchOrderHistory(Request $request)
    {


          $validator = Validator::make(request()->all(), [

             'search'  => 'required',
             'user_id'  => 'required'

         ]);


        if ($validator->fails()) {
                
                return response()->json([
                      'errors' => $validator->errors()],
                      401
                );
        }
          
        $search_history = new SearchHistory;
        $search_history->customer_id = isset($request->user_id)?$request->user_id:NULL;
        $search_history->device_id = isset($request->device_id)?$request->device_id:NULL;
        $search_history->category_id = isset($request->category_id)?$request->category_id:NULL;
        $search_history->search = $request->search;

        $user_id =$request->user_id;
        $count = SearchHistory::where('customer_id',$user_id)->count();
        $orderlist = SearchHistory::where('customer_id',$user_id)->get();


        $idd = $orderlist['0'];
        $id = $idd['id'];
        if($count>10)
        {
              $user = SearchHistory::find($id);
              $user->delete();
              $search_history->save();
        }

        $search_history->save();

        $orderid = $request->search;


        $orderlist = OrderDetail::where('order_id',$orderid)->first();
        $lastsearch = SearchHistory::where('customer_id',$request->user_id)->get();

         
         if($orderlist==null)
         {
        
                   return response()->json([
                            'success'=>"Record Not Found",
                            'status' => 404,
                            'lastsearch'=>$lastsearch
                        ]);
         }

        return response()->json([
            'status' => true,
            'data'=> $orderlist,
            'lastsearch'=>$lastsearch
        ]);


    

        
    }
    public function suggestion(Request $request){

        $query = $request->search;
        $keywords = array();
        $searchHistory = array();
        $products = array();

        if(!empty($query)){
        $products = Product::where(['published' => 1,'search_status'=>1]);

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('published',1)->pluck('product_id')->all();
                $products = $products->whereIn('id',$productIds);
            }
        }

        $products->whereRaw('json_contains(json_tags, \'["' . strtolower($query). '"]\')');

        $products->where(function($q) use($query){
            return $q->where('name','LIKE', '%' . $query . '%')
            ->orWhere('tags', 'LIKE', '%' . $query . '%');
        });

        $products->orderByRaw("IF(name = '{$query}',2,IF(name LIKE '{$query}%',1,0)) DESC");
        $products->groupBy('name');

        $products = filter_products($products)->get()->take(5);

        $suggestion =  new SearchCollection($products);
    }else{
        $suggestion =  new SearchCollection($products);
    }

        if(isset($request->user_id) && $request->user_id != NULL){
            $user_id = $request->user_id;

            $searchHistory = DB::table('search_history')
             ->select(DB::raw('DISTINCT search'),'category_id')
             ->where('customer_id',$request->user_id)
             ->whereNotNull('category_id')
             ->where('category_id','!=',0)
             ->orderBy('id', 'desc')
             ->get()->take(6);

             $history = new SearchHistoryCollection($searchHistory);

        }else{
            $history = new SearchHistoryCollection($searchHistory);
        }

        

        $topSearchData = DB::table('search_history')
             ->select(DB::raw('COUNT(search) as searchCount'),'search','category_id')
             ->whereNotNull('category_id')
             ->where('category_id','!=',0)
             ->groupBy('search')
             ->orderBy('searchCount', 'desc')
             ->take(6)->get();

        $topSearch = new SearchHistoryCollection($topSearchData);

        return response()->json([
            'success'=>true,
            'suggestion'=> $suggestion,
            'history'=> $history,
            'topSearch'=> $topSearch
        ]);
    }

    public function searchList(Request $request){
        $shortId = "";
        $search_history = new SearchHistory;
        $search_history->customer_id = isset($request->user_id)?$request->user_id:NULL;
        $search_history->device_id = isset($request->device_id)?$request->device_id:NULL;
        $search_history->category_id = isset($request->category_id)?$request->category_id:NULL;
        $search_history->search = $request->search;
        $search_history->save();

        $search = $request->search;
        $category_id = $request->category_id;
        // $base_url  = "https://www.rozana.in/rozana_uat/";
        $base_url  = "https://rural.rozana.in/";
        $dataArray = array();

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
             $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('published',1)->pluck('product_id')->all(); 
            }
            $dataArray  = DB::table('products') 
            ->where(['products.published' => 1,'products.search_status'=>1,'products.category_id'=>$category_id])
            ->whereRaw('json_contains(json_tags, \'["' . strtolower($request->search). '"]\')')
            ->orWhere('products.name','like', '%' . $request->search . '%')
            ->orWhere('products.tags', 'like', '%'.$request->search.'%')
            ->when(!empty($shortId), function ($query) use ($productIds) {
                $query->whereIn('id',$productIds);
            })
            ->select(['id',trans('name'),'thumbnail_img','max_purchase_qty'])
            ->addSelect(DB::raw("'links'"))
            ->inRandomOrder()
            ->orderBy('created_at','desc')
            ->get();

        }else{
            $dataArray  = DB::table('products') 
            ->where(['products.published' => 1,'products.search_status'=>1,'products.category_id'=>$category_id])
            ->whereRaw('json_contains(json_tags, \'["' . strtolower($request->search). '"]\')')
            ->orWhere('products.name','like', '%' . $request->search . '%')
            ->orWhere('products.tags', 'like', '%'.$request->search.'%')
            ->select(['id',trans('name'),'thumbnail_img','max_purchase_qty'])
            ->addSelect(DB::raw("'links'"))
            ->inRandomOrder()
            ->orderBy('created_at','desc')
            ->get();
        }

            foreach($dataArray as $k=>$r){
                $cart_qty = 0;
                $cart_id = 0;
                $in_wishlist = false;
                // $customer_discount = 0;
                // $discount_percentage = 0;
               
                $variant = "";
                $productStock = ProductStock::where('product_id',$r->id)->first();
                if(!is_null($productStock)){
                    $variant = $productStock->variant;
                }

                if(isset($request->device_id) && !empty($request->device_id)){
                    $device_id = $request->device_id;
                    $cart = Cart::where('device_id',$device_id)->where('product_id',$r->id)->first();
                    if(!is_null($cart)){
                        $cart_qty = $cart->quantity;
                        $cart_id = $cart->id;
                    }
                }

                if(isset($request->user_id) && $request->user_id != NULL){
                    $is_wishlist = $this->checkProductInWishlist($r->id,$request->user_id);
                    $wishlist_id = (integer) Wishlist::where(['product_id' => $r->id, 'user_id' => $request->user_id])->pluck('id')->first();
                }else{
                    $is_wishlist = false;
                    $wishlist_id = 0;
                }

                $priceDetail = calculatePrice($r->id,$shortId);
 
                $dataArray[$k]->name = trans($r->name);
                $dataArray[$k]->variant = $variant;
                $dataArray[$k]->max_purchase_qty = $r->max_purchase_qty;
                $dataArray[$k]->cart_qty = $cart_qty;
                $dataArray[$k]->cart_id = $cart_id;
                $dataArray[$k]->is_wishlist = $is_wishlist;
                $dataArray[$k]->wishlist_id = $wishlist_id;

                $dataArray[$k]->MRP = $priceDetail['MRP'];
                $dataArray[$k]->stock_price = $priceDetail['MRP'];
                $dataArray[$k]->base_price = round($priceDetail['selling_price'],2);
                $dataArray[$k]->discount = $priceDetail['customer_off'];
                $dataArray[$k]->discount_percentage = json_decode($priceDetail['customer_discount']);
                $dataArray[$k]->discount_type = json_decode($priceDetail['discount_type']);
                $r->links = ['details'=>$base_url."api/v5/products/".$r->id,
                      'related'=>$base_url."api/v5/products/related/".$r->id,
                      'reviews'=>$base_url."api/v5/reviews/product/".$r->id
                ];

            }

            return response()->json([
            'status' => true,
            'message' => 'All product search list.',
            'data' => $dataArray
        ]);
    }

    public function checkProductInWishlist($product_id,$user_id){
        $product = Wishlist::where(['product_id' => $product_id, 'user_id' => $user_id])->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();
        if ($product > 0){
            return true;
        }else{
            return false;
        }
    }

    public function searchInFinalProducts(Request $request){
        $sortinghubid = $request->sortinghubid;
        $search_key = $request->search;
        $self=$request->self;
        $prior_products = [];
        $by_products = [];
        $category = [];
        $tags = [];
        $products = \App\FinalProduct::where('sorting_hub_id',$sortinghubid)->where(function($query) use($search_key){
            return $query->whereRaw('json_contains(json_tags, \'["' . strtolower($search_key). '"]\')')->orwhere('name','like','%'.$search_key.'%')->orWhere('tags','like','%'.$search_key.'%');
        })->get();
        foreach($products as $key => $result){
            if(in_array($search_key,json_decode($result->json_tags))){
                if($search_key=="milk" || $search_key=="mil"){
                   if(in_array("amul milk",json_decode($result->json_tags)) || in_array('toned milk',json_decode($result->json_tags))){
                      $prior_products[] = $result;
                   }else{
                      $by_products[] =  $result;
                   }
                }
                else{
                   $prior_products[] =  $result;
                }
                
             }
             else{
                $by_products[] =  $result;
             }
        }

        $products = array_merge($prior_products,$by_products);
        $response = [];
        $prodcts = [];
        foreach($products as $key=>$prod){
            $priceDetail = calculatePrice($prod['product_id'],$sortinghubid,$self);
            $base_price = $priceDetail['selling_price'];
            $customer_off = $priceDetail['customer_off'];
            $prodcts[$key]['name'] = trans($prod['name']);
            $prodcts[$key]['thumbnail_image'] = $prod['thumbnail_image'];
            $prodcts[$key]['product_id'] = $prod['product_id'];
            $prodcts[$key]['stock_price'] = $prod['stock_price'];
            $prodcts[$key]['base_price'] = $base_price;
            $prodcts[$key]['variant'] = $prod['variant'];
            $prodcts[$key]['quantity'] = $prod['quantity'];
            $prodcts[$key]['max_purchase_qty'] = $prod['max_purchase_qty'];
            //$prodcts[$key]['discount_type'] = $prod['discount_type'];
            $prodcts[$key]['discount_percentage'] = $prod['discount_percentage'];
            $prodcts[$key]['customer_off'] = $customer_off;
            $prodcts[$key]['sorting_hub_id'] = $prod['sorting_hub_id'];
            $prodcts[$key]['flash_deal'] = $prod['flash_deal'];
            $prodcts[$key]['top_product'] = $prod['top_product'];
            $prodcts[$key]['choice_options'] = $prod['choice_options'];
            $prodcts[$key]['unit'] = $prod['unit'];
            $prodcts[$key]['rating'] = $prod['rating'];
            $prodcts[$key]['sales'] = $prod['sales'];
            //$prodcts[$key]['links'] = $prod['links'];
            $category[$key]['category_id'] = (int) $prod['category_id'];
            $cat = \App\Category::find($prod['category_id']);
            $category[$key]['search'] = $cat->name;
            $category[$key]['icon'] = $cat->banner;
            $tags[$key] = json_decode($prod['json_tags']);
         }
         $response['products'] = array_slice($prodcts,0,14);
         $response['categories'] = array_slice(array_unique($category,SORT_REGULAR),0,5);
         $response['tags'] = array_slice(array_unique(array_reduce($tags, 'array_merge', array())),0,7);

    return response()->json([
        'success'=>true,
        "data"=>$response
    ]);
    }
  
}
