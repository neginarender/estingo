<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SearchCollection;
use App\Http\Resources\SearchHistoryCollection;
use App\Http\Resources\ProductCollection;
use App\Models\Category;
use App\Models\Product;
use App\Models\SearchHistory;
use App\Models\ProductStock;
use App\PeerSetting;
use App\Models\Cart;
use Illuminate\Http\Request;
use DB;
use App\Wishlist;

class SearchController extends Controller
{
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

        // $products->when(!empty($shortId), function ($q) use ($productIds) {
        //     return $q->whereIn('id',$productIds);
        // });

        // $products->orWhere('name','like', '%' . $query . '%');
        // $products->orWhere('tags', 'like', '%' . $query . '%');

        // $products->orWhere([
        //     ['name','like', '%' . $query . '%'],
        //     ['tags', 'like', '%' . $query . '%']
        // ]);

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

        

        $topSearch = DB::table('search_history')
             ->select(DB::raw('COUNT(search) as searchCount'),'search','category_id')
             ->whereNotNull('category_id')
             ->where('category_id','!=',0)
             ->groupBy('search')
             ->orderBy('searchCount', 'desc')
             ->get()->take(6);

        $topSearch = new SearchHistoryCollection($topSearch);

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
        $base_url  = "https://www.rozana.in/";
        $dataArray = array();

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
             $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('published',1)->pluck('product_id')->all(); 
            }
            $dataArray  = DB::table('products') 
            ->where(['products.published' => 1,'products.search_status'=>1,'products.category_id'=>$category_id])
            
            ->where(function($q) use($request){
                return $q->whereRaw('json_contains(json_tags, \'["' . strtolower($request->search). '"]\')')
                ->orWhere('name','like', '%' . $request->search . '%')
                ->orWhere('tags', 'like', '%' . $request->search . '%');
            })

            // ->whereRaw('json_contains(json_tags, \'["' . strtolower($request->search). '"]\')')
            // ->orWhere('products.name','like', '%' . $request->search . '%')
            // ->orWhere('products.tags', 'like', '%'.$request->search.'%')
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
            
            ->where(function($q) use($request){
                return $q->whereRaw('json_contains(json_tags, \'["' . strtolower($request->search). '"]\')')
                ->orWhere('name','like', '%' . $request->search . '%')
                ->orWhere('tags', 'like', '%' . $request->search . '%');
            })
            
            // ->whereRaw('json_contains(json_tags, \'["' . strtolower($request->search). '"]\')')
            // ->orWhere('products.name','like', '%' . $request->search . '%')
            // ->orWhere('products.tags', 'like', '%'.$request->search.'%')
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
                $r->links = ['details'=>$base_url."api/v1/products/".$r->id,
                      'related'=>$base_url."api/v1/products/related/".$r->id,
                      'reviews'=>$base_url."api/v1/reviews/product/".$r->id
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
  
}
