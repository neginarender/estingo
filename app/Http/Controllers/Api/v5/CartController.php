<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\CartCollection;
use App\Models\Cart;
use App\Models\Color;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ProductStock;
use App\MappingProduct;
use App\PeerSetting;
use App\BusinessSetting;
use Carbon\Carbon;

class CartController extends Controller
{
    public $min_order_amount = 0;
    public $free_shipping_amount = 0;
    public function __construct(){
        $this->min_order_amount =  (int) env("MIN_ORDER_AMOUNT");
        $this->free_shipping_amount = (int) env("FREE_SHIPPING_AMOUNT");
    }

    public function index($id)
    {
        $id = $_SERVER['HTTP_DEVICE'];
        $cart = Cart::where('device_id', $id)->latest()->get();
        return new CartCollection($cart);
    }

    public function add(Request $request)
    {
        // dd($request->all());
        $product = Product::findOrFail($request->id);
        $device_id = $_SERVER['HTTP_DEVICE'];
        $variant = ''.str_replace(' ', '', $request->variant);
        $color = $request->color;
        $tax = 0;
        $shortId = "";
        $productIds = [];
        $self  = $request->self;

        $checkCartItem = Cart::where('device_id',$device_id)->where('peer_status','!=',$self)->get();
        if(count($checkCartItem) > 0){
            $cartClean = Cart::where('device_id',$device_id)->delete();
        }
       
        if(isset($_SERVER['HTTP_SORTINGHUBID']) &&  !empty($_SERVER['HTTP_SORTINGHUBID'])){

            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
            }

        }

        $peer_code = "";
        if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER']))
        {
            $peer_code = $_SERVER['HTTP_PEER'];

        }

        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        
        $totalCartAmount = Cart::where('device_id',$device_id)->sum('price');

        if($this->CheckPurchaseLimit($device_id,$request->id)){

            $itemCount = Cart::where('device_id',$device_id)->select(DB::raw('SUM(`quantity`) as item'))->pluck('item')->first();

            if(is_null($itemCount)){
                $itemCount = '0';
            }

            $cartID = Cart::where('device_id',$device_id)->where('product_id',$request->id)->first();

            return response()->json([
                'status'=>false,
                'cart_id'=> $cartID->id,
                'item_in_cart' => $itemCount,
                'self' => $self,
                'message' => 'Maximum purchase limit has reached.'
            ]);

        }

        $priceDetail = calculatePrice($request->id,$shortId,$self);
        // dd($priceDetail);
        $discount = $priceDetail['customer_off'];
        $peer_commission = $priceDetail['peer_commission'];
        $master_commission = $priceDetail['master_commission'];
        $price = $priceDetail['MRP'];
        $selling_price = $priceDetail['selling_price'];
        $qty = 1;
        if($request->has('quantity')){
            $qty = $request->quantity;
        }
        $cart = Cart::updateOrCreate([
            'device_id'=> $device_id,
            'product_id' => $request->id
            
        ], [
            'user_id' => $request->user_id,
            'variation' => $variant,
            'discount' => $discount,
            'peer_commission'=>$peer_commission,
            'master_commission'=>$master_commission,
            'price' => $selling_price,
            'mrp'=>$price,
            'tax' => 0,
            'shipping_cost' => 0,
            'quantity' => DB::raw("quantity + $qty"),
            'peer_status' => $self
        ]);

        $this->applyShipping($device_id);
        $item = Cart::where('device_id',$device_id)->select(DB::raw('SUM(`quantity`) as item'))->pluck('item')->first();
        if(is_null($item)){
            $item = '0';
        }

        return response()->json([
            'status'=>true,
            'cart_id'=>$cart->id,
            'item_in_cart' => $item,
            'self' => $self,
            'message' => 'Product added to cart successfully.'
        ]);
    }

    public function changeQuantity(Request $request)
    {
        $cart = Cart::findOrFail($request->id);
        $device_id = $request->device_id;
        $maxLimit = Product::findOrFail($cart->product_id)->max_purchase_qty;
        if($this->CheckPurchaseLimit($device_id,$cart->product_id) && $request->quantity > $maxLimit){
            $itemCount = Cart::where('device_id',$request->device_id)->select(DB::raw('SUM(`quantity`) as item'))->pluck('item')->first();

            if(is_null($itemCount)){
                $itemCount = '0';
            }
            $res['item_in_cart'] = $itemCount;
            return response()->json([
                'message' => 'Maximum purchase limit has reached','data'=>$res], 
                200);
        }

        if($request->quantity == '0'){
            Cart::destroy($request->id);
        }else{
            $cart->update([
                'quantity' => $request->quantity
            ]);
        }
        $this->applyShipping($request->device_id);
        $data = $this->reCalculation($request->device_id);

        $item = Cart::where('device_id',$request->device_id)->select(DB::raw('SUM(`quantity`) as item'))->pluck('item')->first();

        if(is_null($item)){
            $item = '0';
        }
        $data['item_in_cart'] = $item;
        return response()->json([
            'message' => 'Cart updated','data'=>$data], 200);
    }

    public function applyShipping($device_id)
    {
        $cartData = Cart::where('device_id',$device_id)->get();
        $totalCartAmount = 0;
        $totalDiscount = 0;
        $cartIds = [];
        foreach($cartData as $key => $cart)
        {
            $totalCartAmount += $cart->price*$cart->quantity;
            $totalDiscount += $cart->quantity*$cart->discount;
            $cartIds[] = $cart->id;

        }
        $final_amount = $totalCartAmount - $totalDiscount;

        $shipping_cost = ($final_amount>=$this->free_shipping_amount) ? 0 : BusinessSetting::where('type','flat_rate_shipping_cost')->first()->value;
        $shipping_cost = (count($cartData)) ? $shipping_cost/count($cartData) : 0;
        if(count($cartData)){
            Cart::whereIn('id',$cartIds)->update(['shipping_cost'=>$shipping_cost]);
        }
    }

    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        Cart::destroy($id);
        $this->applyShipping($cart->device_id);
        $data = $this->reCalculation($cart->device_id);
        return response()->json(['message' => 'Product is successfully removed from your cart','data'=>$data], 200);
    }

      
    public function deleteCart($id)
    {
        $cart = Cart::findOrFail($id);
        Cart::destroy($id);
        $this->applyShipping($cart->device_id);
        $data = $this->reCalculation($cart->device_id);
        return response()->json(['message' => 'Product is successfully removed from your cart','data'=>$data], 200);

    }

    public function sortingHubPrice($id,$shortId)
    {
        $price = 0;
        $product = \App\MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId)->first();
        if($product !=null)
        {
            $price = $product->selling_price;
        }
        return $price;
    }

    public function peer_discounted_newbase_price($id,$shortId="",$peer_code="")
    {
        // DB::enableQueryLog();
        if(!empty($shortId)){
            // $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first(); 
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId. '"]\')')->latest('id')->first();           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
        // dd(DB::getQueryLog());
        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first(); 

        if(!empty($shortId)){
            // $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
            $product = MappingProduct::where(['sorting_hub_id'=>$shortId,'product_id'=>$id])->first();
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

        if(!empty($peer_code)){
            if(!empty($peer_discount_check->customer_off)){
                $price = $stock_price - $peer_discount_check->customer_off;
                
                return $price;
            }else{
                $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                $price = ($stock_price * $discount_percent)/100;
            
                return $price;
            }

        }else{
            $price = $stock_price;
            return $price;
        }
           
           $price = $stock_price;
           return $price;
    }

    public function peer_discounted_base_price($id,$shortId="",$peer_code=""){
        if(!empty($shortId)){
            // $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
    
        if(!empty($shortId)){
            // $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
            $product = MappingProduct::where(['sorting_hub_id'=>$shortId,'product_id'=>$id])->first();
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
            if(!empty($peer_discount_check)){      
                
                $main_discount = $stock_price - $price;
                $last_price = 0;
                if(!empty($peer_code)){
                     // $discount_percent = Session::get('referal_discount');
                     $discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                     $last_price = ($main_discount * $discount_percent)/100; 
                    }
    
                $price = $stock_price - $last_price;
                
                return $price;
            }else{
               
                 $price = $stock_price;
                 return $price;
            }    
    
           $price = $stock_price;
            return $price;
 
    }

    public function applyPeerDiscountCart(Request $request){
        $device_id = $request->device_id;
        $cartItems = Cart::where('device_id',$device_id)->get();

        $shortId = "";

        if(isset($_SERVER['HTTP_SORTINGHUBID']) &&  !empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
        }

        if(count($cartItems)>0){
            foreach($cartItems as $key => $cart){
                if(!empty($_SERVER['HTTP_PEER'])){
                    $peer_code = $_SERVER['HTTP_PEER'];
                    //$discounted_price = $this->peer_discounted_base_price($cart->product_id,$shortId,$peer_code);
                    Cart::where('id',$cart->id)->update(['discount'=>$this->customer_discount($cart->product_id,$shortId,$peer_code)]);
                }
                else{
                    
                    //$price = $this->peer_discounted_base_price($cart->product_id,$shortId);
                    Cart::where('id',$cart->id)->update(['discount'=>0]);
                }
                
            }
            return response()->json([
                'status'=>true,
                'message'=>"Cart updated"
            ]);
        }
        

    }

    public function checkInCart(Request $request)
    {
        $cartData = Cart::where(array('device_id'=>$request->device_id,'product_id'=>$request->product_id))->first();
        if(!is_null($cartData))
        {
            return response()->json([
                'status'=> true,
                'quantity'=> $cartData->quantity
            ]);
        }
        return response()->json([
            'status'=>false,
            'quantity' => 0
        ]);
    }

    public function checkAvailablity(Request $request)
    {
        $self = 0;
        $shortId = "";
        $productAvail = "";
        $shortinhHub = "";
        $buttonDisable = array();

        $self = $request->self;
        $shortinhHub = $request->sortinghubid;
        $device_id = $request->device_id;
        $checkShortingHub = \App\ShortingHub::where('user_id',$shortinhHub)->select('user_id','max_cod','min_cod')->first();
        $max_cod_amount = $checkShortingHub->max_cod;
        $min_cod_amount = $checkShortingHub->min_cod;
        $cartData = Cart::where('device_id',$device_id)->get();
        foreach($cartData as $key => $cart)
        {
            if(!empty($shortinhHub)){
                $productAvail = \App\MappingProduct::where(['sorting_hub_id'=>$shortinhHub,'product_id'=>$cart->product_id,'published'=>1])->first();

                if(empty($productAvail)){
                    array_push($buttonDisable,$cart->id);
                    $cartData[$key]['available'] = false;
                }

                $priceDetail = calculatePrice($cart->product_id,$shortinhHub,$self);
                $cartData[$key]['discount'] = $priceDetail['customer_off'];
                $cartData[$key]['peer_commission'] = $priceDetail['peer_commission'];
                $cartData[$key]['master_commission'] = $priceDetail['master_commission'];
                $cartData[$key]['MRP'] = $priceDetail['MRP'];
                $cartData[$key]['stock_price'] = $priceDetail['MRP'];
                $cartData[$key]['base_price'] = $priceDetail['selling_price'];
            }
        }
        $status = true;
        if(count($buttonDisable)>0){
            $status = false;
        }
        return response()->json([
            'status'=>$status,
            'max_cod_amount'=>$max_cod_amount,
            'min_cod_amount'=>$min_cod_amount,
            'data'=> $cartData->map(function($data){
                return [
                    'available'=>(is_null($data->available)) ? true :false,
                    'message'=>(is_null($data->available)) ? "Available at this location" :"Product not available at this location",
                    'id' => $data->id,
                    'product_id'=>$data->product_id,
                    'product' => [
                        'name' => trans($data->product->name),
                        'image' => $data->product->thumbnail_img
                    ],
                    'variation' => $data->variation,
                    'MRP' => (double) ($data->MRP),
                    'stock_price' => (double) ($data->stock_price),
                    'base_price' => round($data->base_price,2),
                    'peer_commission'=> (string) ($data->peer_commission),
                    'master_commission'=> (string) ($data->master_commission),
                    'master_commission'=> (string) ($data->master_commission),
                    'tax' => strval(((($data->price-$data->discount)*$data->product->tax)/100)*$data->quantity),
                    'shipping_cost' => (double) $data->shipping_cost,
                    'quantity' => (integer) $data->quantity,
                    'discount'=>(double)($data->discount*$data->quantity),
                    'date' => $data->created_at->diffForHumans(),
                    'max_purchase_qty'=>$data->product->max_purchase_qty,
                    'links'=>[
                        'details'=>env('APP_URL')."api/v5/products/".$data->product_id,
                        'related'=>env('APP_URL')."api/v5/products/related/".$data->product_id

                        ]
                ];
            })
        ]);


    }

    public function CheckPurchaseLimit($device_id,$productId){
    
        $cartQty = 0;
        $maxLimit = 100;
        $product = Product::findOrFail($productId);
        $cartData = Cart::where(['device_id'=>$device_id,'product_id'=>$productId])->first();

        if(!is_null($cartData)){
            $cartQty = $cartData->quantity;
        }
        if(!is_null($product)){
            $maxLimit = maxPurchaseQty($productId,$_SERVER['HTTP_SORTINGHUBID']);
        }
        
        if($cartQty >= $maxLimit){
            return true;
        }
        return false;
    }

    public function customer_discount($id,$shortId,$peercode){
       
        if(!empty($shortId)){
           $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId. '"]\')')->latest('id')->first();
        }else{
            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        } 

        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first();

        if(!empty($shortId)){

            $productM = \App\MappingProduct::where(['sorting_hub_id'=>$shortId,'product_id'=>$id])->first();

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
        $last_price = 0;

        if(!empty($peer_discount_check)){      
            $main_discount = $stock_price - $price;
            if(!empty($peercode)){
                 $last_price = $peer_discount_check->customer_off;
                }
            return $last_price;
        }  
        return $last_price;
    }

    public function price($id,$distributorId=null,$shortId=null){

        $product = \App\Product::findOrFail($id);
        $price = $product->unit_price;
        $productStock = \App\ProductStock::where('product_id',$id)->first();
        if(!is_null($productStock)){
            $price = $productStock->price;

        }

        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){

            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            if(!is_null($shortId) && !empty($shortId)){
                $mappedProduct = \App\MappingProduct::where(['product_id'=>$id,'sorting_hub_id'=>$shortId])->latest()->first();
                if(!is_null($mappedProduct)){
                    if($mappedProduct->selling_price!=0){
                        $price = $mappedProduct->selling_price;
                    }
                }
            }
        }

        return $price;
    }

    public function checkCartPrice(Request $request){
    	$shortId = "";

        if(isset($_SERVER['HTTP_SORTINGHUBID']) &&  !empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
        }

    $peer_code = "Rozana7";
    $self = $request->self;
    // if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER'])){
    // 	$peer_code = $_SERVER['HTTP_PEER'];

    // }
    	$device_id = $request->device_id;
    	$cartData = Cart::where('device_id',$device_id)->get();
    	if(count($cartData)>0){
    		foreach ($cartData as $key => $cart) {
    			$priceDetail = calculatePrice($cart->product_id,$shortId,$self);
                $available = $this->checkavailable($cart->product_id,$shortId);
                if($available == true){
                    $discount = $priceDetail['customer_off'];
                    $price = $priceDetail['selling_price'];
                    $mrp = $priceDetail['MRP'];
                    Cart::where('id',$cart->id)->update(['price'=>$price,'discount'=>$discount,'mrp'=>$mrp]);
                }
    			
    		}
    		return response()->json([
    			'status'=>true,
    			'message'=>"Price updated",
                'peer_code' => $peer_code
            ]);
    	}
    		return response()->json([
    			'status'=>false,
    			'message'=>'No data in cart',
                'peer_code' => $peer_code
            ]);
    }

    public function checkPriceAfterShippingPinCode(Request $request){

        $device_id = $request->device_id;
        $checkShortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $request->postal_code . '"]\')')->pluck('user_id')->first();
        $shortId = $checkShortingHub;

        $peer_code = "Rozana7";
        // if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER'])){
        //     $peer_code = $_SERVER['HTTP_PEER'];
        // }
    	$cartData = Cart::where('device_id',$device_id)->get();
    	if(count($cartData)>0){
    		foreach ($cartData as $key => $cart) {
    			$priceDetail = calculatePrice($cart->product_id,$shortId,$self);
                $discount = $priceDetail['customer_off'];
                $price = $priceDetail['selling_price'];
                $mrp = $priceDetail['MRP'];
                Cart::where('id',$cart->id)->update(['price'=>$price,'discount'=>$discount,'mrp'=>$mrp]);
    		}
    		return response()->json([
    			'status'=>true,
                'postal_code'=>$request->postal_code,
                'shipping_sortinghubid'=>$shortId,
    			'message'=>"Price updated"]);
    	}
    		return response()->json([
    			'status'=>false,
                'postal_code'=>$request->postal_code,
                'shipping_sortinghubid'=>$shortId,
    			'message'=>'No data in cart']);
    }

    public function applyPeerOnCheckout(Request $request){

        $coupon = \App\PeerPartner::where(['code' => $request->peercode, 'verification_status' => 1,'peertype_approval' => 0])->first();

        $peer_code = "";
        $msg = "Referral code has been removed";

        if(!empty($coupon)){
        // if($request->has('peercode')){
            if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER']) && !empty($request->peercode))
                {
                    if(isset($request->user_id) && !empty($request->user_id)){
                        //update referral code in users table 
                        \App\User::where('id',$request->user_id)->update(['used_referral_code'=>$request->peercode]);
                    }
                    
                    $peer_code = $request->peercode;
                    $msg = 'Referral code has been applied';
                }
            $this->checkCartPrice($request);
            $device_id = $request->device_id;
            $cartData = Cart::where('device_id', $device_id)->latest()->get();
            $mrp = 0;
            $total_tax = 0;
            $total_discount = 0;
            
            $final_amount = 0;
            foreach($cartData as $key => $item){
                // $mrp += $item->price*$item->quantity;
                $mrp += $item->mrp*$item->quantity;
                $total_discount +=$item->discount*$item->quantity;
                $total_tax += ($item->price-$item->discount)*$item->product->tax/100;

            }
            $final_amount = $mrp-$total_discount;
            $shipping_cost = ($final_amount>=$this->free_shipping_amount) ? 0: BusinessSetting::where('type','flat_rate_shipping_cost')->first()->value;
            $grand_total = round($final_amount+$shipping_cost,2);
            return response()->json([
                'status' => true,
                'message' => $msg,
                'peercode'=> $peer_code,
                'total_mrp'=>round($mrp,2),
                'total_tax'=>round($total_tax,2),
                'total_discount'=>round($total_discount,2),
                'shipping_cost' => (double)$shipping_cost,
                'final_amount'=>(double)$grand_total,
                'min_amount'=>(double) $this->minCodAmount(),
            ]);
        }elseif(empty($coupon) && $request->peercode == NULL){
            $this->checkCartPrice($request);
            $device_id = $request->device_id;
            $cartData = Cart::where('device_id', $device_id)->latest()->get();
            $mrp = 0;
            $total_tax = 0;
            $total_discount = 0;
            
            $final_amount = 0;
            foreach($cartData as $key => $item){
                $mrp += $item->price*$item->quantity;
                $total_discount +=$item->discount*$item->quantity;
                $total_tax += ($item->price-$item->discount)*$item->product->tax/100;

            }
            $final_amount = $mrp-$total_discount;
            $shipping_cost = ($final_amount>=$this->free_shipping_amount) ? 0: BusinessSetting::where('type','flat_rate_shipping_cost')->first()->value;
            $grand_total = round($final_amount+$shipping_cost,2);
            return response()->json([
                'status' => true,
                'message' => $msg,
                'peercode'=> $peer_code,
                'total_mrp'=>round($mrp,2),
                'total_tax'=>round($total_tax,2),
                'total_discount'=>round($total_discount,2),
                'shipping_cost' => (double)$shipping_cost,
                'final_amount'=>(double)$grand_total,
                'min_amount'=>(double) $this->minCodAmount(),
            ]);
        }else{
            $msg = 'Master coupon, not applied!';
            $_SERVER['HTTP_PEER'] = "";
            $this->checkCartPrice($request);
            $device_id = $request->device_id;
            $cartData = Cart::where('device_id', $device_id)->latest()->get();
            $mrp = 0;
            $total_tax = 0;
            $total_discount = 0;
            
            $final_amount = 0;
            foreach($cartData as $key => $item){
                $mrp += $item->price*$item->quantity;
                $total_discount +=$item->discount*$item->quantity;
                $total_tax += ($item->price-$item->discount)*$item->product->tax/100;

            }
            $final_amount = $mrp-$total_discount;
            $shipping_cost = ($final_amount >= $this->free_shipping_amount) ? 0: BusinessSetting::where('type','flat_rate_shipping_cost')->first()->value;
            $grand_total = round($final_amount+$shipping_cost,2);
            return response()->json([
                'status' => false,
                'message' => $msg,
                'peercode'=> $peer_code,
                'total_mrp'=>round($mrp,2),
                'total_tax'=>round($total_tax,2),
                'total_discount'=>round($total_discount,2),
                'shipping_cost' => (double)$shipping_cost,
                'final_amount'=>(double)$grand_total,
                'min_amount'=>(double) $this->minCodAmount(),
            ]);
        }
    }

    public function reCalculation($device_id){
        $cart = Cart::where('device_id',$device_id)->get();
        $total_mrp = 0;
        $total_tax = 0;
        $shipping_cost = 0;
        $total_discount = 0;
        $grand_total = 0;
        $total_items = 0;
        foreach($cart as $key => $cartItem){
            $total_items += $cartItem->quantity; 
            // $total_mrp+=(double) ($cartItem->price*$cartItem->quantity);
            $total_mrp+=(double) ($cartItem->mrp*$cartItem->quantity);
            $total_tax += (double) ((($cartItem->price-$cartItem->discount)*$cartItem->product->tax)/100)*$cartItem->quantity;
            $shipping_cost +=(double) $cartItem->shipping_cost;
            $total_discount += (double) $cartItem->discount*$cartItem->quantity;
            
        }
        $grand_total = ($total_mrp-$total_discount)+$shipping_cost; 

        // $min_order_amount = $this->min_order_amount;
        $min_order_amount = $this->minCodAmount();
        $free_shipping_amount = $this->free_shipping_amount;
        $is_continue = false;

        if($grand_total>=$min_order_amount){
            $is_continue = true;
        }

        return array(
            'total_quantity'=>$total_items,
            'total_mrp'=>round($total_mrp,2),
            'total_tax'=>round($total_tax,2),
            'shipping_cost'=>round($shipping_cost,0,2),
            'total_discount'=>round($total_discount,2),
            'grand_total'=>round($grand_total,2),
            'is_continue'=>$is_continue,
            'min_amount'=>$min_order_amount,
            'free_shipping_amount'=>$free_shipping_amount,
            'delivery_charge'=>($grand_total>=$free_shipping_amount) ? 0:0,
            'max_cod_amount' => $this->maxCodAmount()
        );
    }

    public function getCartItemCount(Request $request){
        $item = Cart::where('device_id',$request->device_id)->select(DB::raw('SUM(`quantity`) as item'))->pluck('item')->first();
        if(is_null($item)){
            $item = '0';
        }
        return response()->json([
                'status' => true,
                'item_in_cart'=> $item
            ]);
        
    }

    public function getdeliveryslot(Request $request){
        $cat_collection = array();
        $availSlot = array();

        $currentTime = Carbon::now();

        $deliverySlot = \App\DeliverySlot::where('status','1')->get();
        $item = Cart::where('device_id',$request->device_id)->select('product_id')->get();
        foreach($item as $value){
            $product_id = $value->product_id;
            $categoryid = Product::where('id',$product_id)->pluck('category_id')->first();
            array_push($cat_collection,$categoryid);
        }

        $cat_collection = array_unique($cat_collection);

        foreach($cat_collection as $key => $cat){
            foreach($deliverySlot as $key => $slot){
                $delSlot = explode(',',$slot->category_id);
                if(in_array($cat,$delSlot)){
                    array_push($availSlot,$slot->id);
                }else{
                    array_push($availSlot,2,3,4);
                }
            }
        }
        $availSlot = array_unique($availSlot);
        $availableSlot = array();
        $i = 1;
        foreach($availSlot as $key => $value){
            $slotData = \App\DeliverySlot::where('id',$value)->first();
            if($value == $slotData->id && strtotime($slotData->cut_off)> strtotime($currentTime)){
                $shift = 'TODAY';
            }else{
                $shift = 'TOMORROW';
            }
            $slot = $shift.' '.$slotData->delivery_time;
            if($value == 1){
                $text = 'Fresh Fruits, Dairy & Vegetables will be delivered separately between '.$slot;
                $availableSlot[$i] = $text;
            }else{
                $availableSlot[$i] = $slot;
            }
            $i++;



        }

        

        return response()->json([
                'status' => true,
                'available_slot'=> $availableSlot
            ]);
    }

    public function checkavailable($product_id,$shortId){
        $mappedProduct = \App\MappingProduct::where(['product_id'=>$product_id,'sorting_hub_id'=>$shortId])->latest()->first();
        if(is_null($mappedProduct)){
            return false;
        }else{
            return true;
        }
    }

    public function maxCodAmount(){
        if(isset($_SERVER['HTTP_SORTINGHUBID']) && !empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            $max_cod_amount = 0;
            if(!empty($shortId)){               
                $max_cod_amount = \App\ShortingHub::where('user_id',$shortId)->pluck('max_cod')->first();
            }
            return $max_cod_amount;
        }
    }

    public function minCodAmount(){
        $min_cod_amount = 0;
        if(isset($_SERVER['HTTP_SORTINGHUBID']) && !empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            $min_cod_amount = 0;
            if(!empty($shortId)){               
                $min_cod_amount = \App\ShortingHub::where('user_id',$shortId)->pluck('min_cod')->first();
            }
            return $min_cod_amount;
        }else{
            return $min_cod_amount;
        }
    }

}
