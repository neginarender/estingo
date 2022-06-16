<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\WishlistCollection;
use App\Models\Wishlist;
use App\Models\Review;
use Illuminate\Http\Request;
use DB;
use App\MappingProduct;
use App\Product;

class WishlistController extends Controller
{

    // public function index($id)
    // { 
    //     $shortId = NULL;
    //     if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
    //         $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            // $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
            // $products = Wishlist::whereIn('product_id',$productIds)->where('user_id', $id)->latest()->get();
    //     }else{
    //         $products = Wishlist::where('user_id', $id)->latest()->get();
    //     }

    //     $arr = $this->products_with_peer_price($products,$shortId);
    //     return new WishlistCollection($arr);
    // }

    public function index($id)
    { 
        $shortId = NULL;
        // $allProducts = Wishlist::where('user_id', $id)->pluck('product_id')->all();

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
        //     $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
        //     $productID = Wishlist::whereIn('product_id',$productIds)->where('user_id', $id)->pluck('product_id')->all();
        }

        // $diff_product = array_diff($allProducts,$productID);
        $products = Wishlist::where('user_id', $id)->latest()->get();
        $arr = $this->products_with_peer_price($products,$shortId);

        return new WishlistCollection($arr);
    }

    public function store(Request $request)
    {
        Product::findOrFail($request->product_id);
        Wishlist::updateOrCreate(
            ['user_id' => $request->user_id, 'product_id' => $request->product_id]
        );
        return response()->json([
            'success' => true,
            'message' => 'Product is successfully added to your wishlist.'
        ], 201);
    }

    public function destroy($id)
    { 
        Wishlist::destroy($id);
        return response()->json([
            'success' => true,
            'message' => 'Product is successfully removed from your wishlist.'
        ], 200);
    }

    public function isProductInWishlist(Request $request)
    {      

        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' => $request->user_id])->count();
        // $product = Wishlist::where(['product_id' => $product_id, 'user_id' => $user_id])->select(DB::raw('COUNT(`id`) as id'))->pluck('id')->first();
        if ($product > 0)
            return response()->json([
                'message' => 'Product present in wishlist',
                'is_in_wishlist' => true,
                'product_id' => (integer) $request->product_id,
                'wishlist_id' => (integer) Wishlist::where(['product_id' => $request->product_id, 'user_id' => $request->user_id])->pluck('id')->first()
            ], 200);

        return response()->json([
            'message' => 'Product is not present in wishlist',
            'is_in_wishlist' => false,
            'product_id' => (integer) $request->product_id,
            'wishlist_id' => 0
        ], 200);
    }

    public function products_with_peer_price($products,$shortId = NULL){

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
        $rating = 0;
        
        if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
            $device_id = $_SERVER['HTTP_DEVICE'];
            $cart = Cart::where('device_id',$device_id)->where('product_id',$product->product_id)->select('quantity','id')->first();
            
            if(!is_null($cart)){
                $cart_qty = $cart->quantity;
                $cart_id = $cart->id;
            }
        }
        
        $qty = \App\ProductStock::where('product_id',$product->product_id)->pluck('qty')->first();
        if(!is_null($qty)){
            $quantity = $qty;
        }

        if(!is_null($shortId)){
            $mapped = \App\MappingProduct::where('sorting_hub_id',$shortId)->where('product_id',$product->product_id)->latest()->first();

            if(!empty($mapped)){
                $priceDetail = calculatePrice($product->product_id,$shortId);
                $priceDetail['is_available'] = true;
            }else{
                $priceDetail['MRP'] = 0;
                $priceDetail['selling_price'] = 0;
                $priceDetail['customer_off'] = 0;
                $priceDetail['customer_discount'] = null;
                $priceDetail['discount_type'] = null;
                $priceDetail['is_available'] = false;
            }
        }else{
            $priceDetail = calculatePrice($product->product_id,$shortId);
            $priceDetail['is_available'] = true;
        }

        $review = Review::where('product_id',$product->product_id)->get();
        if(!empty($review)){
            $count = count($review);
            $ratingSUM = Review::where('product_id',$product->product_id)->select(DB::raw('SUM(rating) as rating'))->first();
            if($count > 0){
                $rating = $ratingSUM->rating/$count;
            }else{
                $rating = $ratingSUM->rating;
            }
            
        }else{
            $rating = '0.0';
        }

        $variant = "";
        $pvariant = \App\ProductStock::where('product_id',$product->product_id)->first();
        if(!is_null($pvariant))
        {
            $variant = $pvariant->variant;
        }

        $productDetail = Product::where('id',$product->product_id)->first();

        $products[$key]['name'] = $productDetail->name;
        $products[$key]['thumbnail_img'] = $productDetail->thumbnail_img;
        $products[$key]['unit'] = $productDetail->unit;
        $products[$key]['variant'] = $variant;
        $products[$key]['quantity'] = $quantity;
        $products[$key]['rating'] = $rating;
        $products[$key]['MRP'] = $priceDetail['MRP'];
        $products[$key]['stock_price'] = $priceDetail['MRP'];
        $products[$key]['base_price'] = $priceDetail['selling_price'];
        $products[$key]['discount'] = $priceDetail['customer_off'];
        $products[$key]['discount_percentage'] = $priceDetail['customer_discount'];
        $products[$key]['discount_type'] = $priceDetail['discount_type'];
        $products[$key]['is_available'] = $priceDetail['is_available'];
        
        }
        return $products;
    }

}
