<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\WishlistCollection;
use App\Traits\LocationTrait;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    use LocationTrait;

    public function index($id)
    {
        $productIds = [];
        if(isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_CITY']) &&!empty($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
                $products = Wishlist::whereIn('product_id',$productIds)->where('user_id', $id)->latest()->get();
            }
        }else{
            $products = Wishlist::where('user_id', $id)->latest()->get();
        }
        
        return new WishlistCollection($products);
    }

    public function store(Request $request)
    {
        Wishlist::updateOrCreate(
            ['user_id' => $request->user_id, 'product_id' => $request->product_id]
        );
        return response()->json(['message' => 'Product is successfully added to your wishlist'], 201);
    }

    public function destroy($id)
    {
        Wishlist::destroy($id);
        return response()->json(['message' => 'Product is successfully removed from your wishlist'], 200);
    }

    public function isProductInWishlist(Request $request)
    {
       

        $product = Wishlist::where(['product_id' => $request->product_id, 'user_id' => $request->user_id])->count();
        if ($product > 0)
            return response()->json([
                'message' => 'Product present in wishlist',
                'is_in_wishlist' => true,
                'product_id' => (integer) $request->product_id,
                'wishlist_id' => (integer) Wishlist::where(['product_id' => $request->product_id, 'user_id' => $request->user_id])->first()->id
            ], 200);

        return response()->json([
            'message' => 'Product is not present in wishlist',
            'is_in_wishlist' => false,
            'product_id' => (integer) $request->product_id,
            'wishlist_id' => 0
        ], 200);
    }


}
