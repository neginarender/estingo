<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CartCollection;
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
        return new CartCollection(Cart::where('device_id', $id)->latest()->get());
    }

    public function add(Request $request)
    {
       
        $product = Product::findOrFail($request->id);
        $device_id = $_SERVER['HTTP_DEVICE'];
        $variant = ''.str_replace(' ', '', $request->variant);
        $color = $request->color;
        $tax = 0;
        $shortId = "";
        $productIds = [];
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
            if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
            }
        }
        $peer_code = "";
        if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER']))
        {
            $peer_code = $_SERVER['HTTP_PEER'];

        }
        $price = $this->price($request->id,$shortId);
        
        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        

        // if ($product->tax_type == 'percent') {
        //     $tax = ($price * $product->tax) / 100;
        // }
        // elseif ($product->tax_type == 'amount') {
        //     $tax = $product->tax;
        // }

        $totalCartAmount = Cart::where('device_id',$device_id)->sum('price');
        if($this->CheckPurchaseLimit($device_id,$request->id)){

            return response()->json([
                'status'=>false,
                'message' => 'Maximum purchase limit has reached'
            ]);

        }
        $discount_percentage = 0;
        if(isset($request->discount_percentage) && !empty($request->discount_percentage)){
            $discount_percentage = $request->discount_percentage;
        }
        $cart = Cart::updateOrCreate([
            'device_id'=> $device_id,
            'product_id' => $request->id
            
        ], [
            'user_id' => $request->user_id,
            'variation' => $variant,
            'discount' => $this->customer_discount($request->id,$shortId,$peer_code),
            'discount_percentage'=>$discount_percentage,
            'price' => $price,
            'tax' => 0,
            'shipping_cost' => 0,
            'quantity' => DB::raw('quantity + 1'),
        ]);

        $this->applyShipping($device_id);
        return response()->json([
            'status'=>true,
            'cart_id'=>$cart->id,
            'message' => 'Product added to cart successfully'
        ]);
    }

    public function changeQuantity(Request $request)
    {
        $cart = Cart::findOrFail($request->id);
        $device_id = $request->device_id;
        $maxLimit = Product::findOrFail($cart->product_id)->max_purchase_qty;
        if($this->CheckPurchaseLimit($device_id,$cart->product_id) && $request->quantity > $maxLimit){
            
            return response()->json(['message' => 'Maximum purchase limit has reached'], 200);
        }

        $cart->update([
            'quantity' => $request->quantity
        ]);
        $this->applyShipping($request->device_id);
        $data = $this->reCalculation($request->device_id);
        return response()->json(['message' => 'Cart updated','data'=>$data], 200);
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
        $product = \App\MappingProduct::where('product_id',$id)->where('sorting_hub_id',$shortId['sorting_hub_id'])->first();
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
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
        // dd(DB::getQueryLog());
        $product = Product::findOrFail($id);
        $productstock = ProductStock::where('product_id', $id)->select('price')->first(); 

        if(!empty($shortId)){
            $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
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
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
        }    
    
        if(!empty($shortId)){
            $product = MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$id])->first();
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
                //echo convert_price($price);exit;
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
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
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

        $productAvail = "";
        $shortinhHub = "";
        $buttonDisable = array();
        $pincode = $request->pincode;
        $device_id = $request->device_id;
        $shortId = "";
        if(!empty($pincode)){               
        $shortinhHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $pincode . '"]\')')->pluck('user_id')->first();
        $shortId = \App\MappingProduct::where('sorting_hub_id',$shortinhHub)->first('sorting_hub_id');
        }

        //$this->checkPriceAfterShippingPinCode($device_id,$shortId);
        $cartData = Cart::where('device_id',$device_id)->get();
        foreach($cartData as $key => $cart)
        {
            if(!empty($shortinhHub)){
                $productAvail = \App\MappingProduct::where(['sorting_hub_id'=>$shortinhHub,'product_id'=>$cart->product_id,'published'=>1])->first();

                if(empty($productAvail)){
                    array_push($buttonDisable,$cart->id);
                    $cartData[$key]['available'] = false;

                }
            }
        }

        $status = true;
        if(count($buttonDisable)>0){
            $status = false;
        }
        return response()->json([
            'status'=>$status,
            'data'=> $cartData->map(function($data){
                return [
                    'available'=>(is_null($data->available)) ? true :false,
                    'message'=>(is_null($data->available)) ? "" :"Product not available at this location",
                    'id' => $data->id,
                    'product_id'=>$data->product_id,
                    'product' => [
                        'name' => $data->product->name,
                        'image' => $data->product->thumbnail_img
                    ],
                    'variation' => $data->variation,
                    'price' => (double) $data->price,
                    'tax' => (double) $data->tax,
                    'shipping_cost' => (double) $data->shipping_cost,
                    'quantity' => (integer) $data->quantity,
                    'discount'=>(double) $data->discount,
                    'date' => $data->created_at->diffForHumans()
                ];
            })
        ]);

        //dd($buttonDisable);


    }


    public function CheckPurchaseLimit($device_id,$productId){
       
        $cartQty = 0;
        $maxLimit = 0;
        $product = Product::findOrFail($productId);
        $cartData = Cart::where(['device_id'=>$device_id,'product_id'=>$productId])->first();
        //dd($cartData);
        if(!is_null($cartData)){
            $cartQty = $cartData->quantity;
        }
        if(!is_null($product)){
            $maxLimit = $product->max_purchase_qty;
        }
        
        if($cartQty>=$maxLimit){
            
            return true;
        }
        return false;
    }

    public function customer_discount($id,$shortId,$peercode){
       
        if(!empty($shortId)){
            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first();
           
        }else{
            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
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
                     //$discount_percent = substr($peer_discount_check->customer_discount, 1, -1);
                     //$last_price = ($main_discount * $discount_percent)/100; 
                     $last_price = $peer_discount_check->customer_off;
                    }
                return $last_price;
            }  

          
            return $last_price;
    }

    public function price($id,$shortId){

        $product = \App\Product::findOrFail($id);
        $price = $product->unit_price;
        $productStock = \App\ProductStock::where('product_id',$id)->first();
        if(!is_null($productStock)){
            $price = $productStock->price;

        }
        //if(isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_CITY']) && !empty($_SERVER['HTTP_CITY'])){

            // $distributorId = \App\Distributor::whereRaw('json_contains(pincode, \'["' . $_SERVER['HTTP_PINCODE'] . '"]\')')->where('status',1)->pluck('id')->all();
            // $shortId = \App\MappingProduct::whereIn('distributor_id',$distributorId)->first('sorting_hub_id');
            if(!is_null($shortId)){
                $mappedProduct = \App\MappingProduct::where(['product_id'=>$id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->first();
                if(!is_null($mappedProduct)){
                    if($mappedProduct->selling_price!=0){
                        $price = $mappedProduct->selling_price;
                    }
                }
            }
        //}

        return $price;
    }

    public function checkCartPrice(Request $request){
    	$shortId = "";
    	if(isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_CITY']) && !empty($_SERVER['HTTP_CITY'])){

            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            
        }
    $peer_code = "";

    if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER'])){
    	$peer_code = $_SERVER['HTTP_PEER'];

    }
    	$device_id = $request->device_id;
    	$cartData = Cart::where('device_id',$device_id)->get();
    	if(count($cartData)>0){
    		foreach ($cartData as $key => $cart) {
    			$price = $this->price($cart->product_id,$shortId);
    			$discount = $this->customer_discount($cart->product_id,$shortId,$peer_code);
    			Cart::where('id',$cart->id)->update(['price'=>$price,'discount'=>$discount]);
    		}
    		return response()->json([
    			'status'=>true,
    			'message'=>"Price updated"]);
    	}
    		return response()->json([
    			'status'=>false,
    			'message'=>'No data in cart']);
    }

    public function checkPriceAfterShippingPinCode($device_id,$shortId){
        $peer_code = "";
        if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER'])){
            $peer_code = $_SERVER['HTTP_PEER'];
    
        }
    	$cartData = Cart::where('device_id',$device_id)->get();
    	if(count($cartData)>0){
    		foreach ($cartData as $key => $cart) {
    			$price = $this->price($cart->product_id,$shortId);
    			$discount = $this->customer_discount($cart->product_id,$shortId,$peer_code);
    			Cart::where('id',$cart->id)->update(['price'=>$price,'discount'=>$discount]);
    		}
    		return response()->json([
    			'status'=>true,
    			'message'=>"Price updated"]);
    	}
    		return response()->json([
    			'status'=>false,
    			'message'=>'No data in cart']);
    }

    public function applyPeerOnCheckout(Request $request){

        $coupon = \App\PeerPartner::where(['code' => $request->peercode, 'verification_status' => 1,'peertype_approval' => 0])->first();
        $peer_code = "";
        $msg = "Referral code has been removed";
        if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER']))
        {
            if(isset($request->user_id) && !empty($request->user_id) && $request->user_id!=0){
                //update referral code in users table 
                \App\User::where('id',$request->user_id)->update(['used_referral_code'=>$request->peercode]);
            }
            
            $peer_code = $request->peercode;
            $msg = 'Referral code has been applied';
        }

        $user_balance = 0;
        if(isset($request->user_id) && !empty($request->user_id) && $request->user_id!=0){
            //update referral code in users table 
            $user = \App\User::where('id',$request->user_id)->first();
            if(!is_null($user)){
                $user_balance = $user->balance;
            }
        }

        if(!empty($coupon)){
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
            $min_order_amount = $this->min_order_amount;
            $free_shipping_amount = $this->free_shipping_amount;
            $shipping_cost = ($final_amount>=$free_shipping_amount) ? 0: BusinessSetting::where('type','flat_rate_shipping_cost')->first()->value;
            $grand_total = round($final_amount+$shipping_cost,2);
            if(isset($request->is_wallet) && !empty($request->is_wallet) && $request->is_wallet==1){
                if($user_balance>=$grand_total)
                {
                    $grand_total = 0;
                }
                else{
                    $grand_total = round(($grand_total-$user_balance),2);
                }
                    
            }
            
            return response()->json([
                'status' => true,
                'message' => $msg,
                'peercode'=> $peer_code,
                'total_mrp'=>round($mrp,2),
                'total_tax'=>round($total_tax,2),
                'total_discount'=>round($total_discount,2),
                'final_amount'=>$grand_total
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

            if(isset($request->is_wallet) && !empty($request->is_wallet)){
                if($user_balance>=$grand_total)
                {
                    $grand_total = 0;
                }
                else{
                    $grand_total = round(($grand_total-$user_balance),2);
                }
                    
            }

            return response()->json([
                'status' => true,
                'message' => $msg,
                'peercode'=> $peer_code,
                'total_mrp'=>round($mrp,2),
                'total_tax'=>round($total_tax,2),
                'total_discount'=>round($total_discount,2),
                'shipping_cost' => $shipping_cost,
                'final_amount'=>$grand_total
            ]);
        }else{
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
            $shipping_cost = ($final_amount>=$this->free_shipping_amount) ? 0: BusinessSetting::where('type','flat_rate_shipping_cost')->first()->value;
            $grand_total = round($final_amount+$shipping_cost,2);

            if(isset($request->is_wallet) && !empty($request->is_wallet)){
                if($user_balance>=$grand_total)
                {
                    $grand_total = 0;
                }
                else{
                    $grand_total = round(($grand_total-$user_balance),2);
                }
                    
            }

            return response()->json([
                'status' => false,
                'message' => "Invalid code",
                'peercode'=> "",
                'total_mrp'=>round($mrp,2),
                'total_tax'=>round($total_tax,2),
                'total_discount'=>round($total_discount,2),
                'shipping_cost' => $shipping_cost,
                'final_amount'=>$grand_total
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
            $total_mrp+=(double) ($cartItem->price*$cartItem->quantity);
            $total_tax += (double) ((($cartItem->price-$cartItem->discount)*$cartItem->product->tax)/100)*$cartItem->quantity;
            $shipping_cost +=(double) $cartItem->shipping_cost;
            $total_discount += (double) $cartItem->discount*$cartItem->quantity;
            
        }
        $grand_total = ($total_mrp-$total_discount)+$shipping_cost;
        $min_order_amount = $this->min_order_amount;
        $free_shipping_amount = $this->free_shipping_amount;
        $is_continue = false;

        if($grand_total>=$min_order_amount){
            $is_continue = true;
        }

        return array(
            'total_quantity'=>$total_items,
            'total_mrp'=>round($total_mrp,0),
            'total_tax'=>round($total_tax,2),
            'shipping_cost'=>round($shipping_cost,0,2),
            'total_discount'=>round($total_discount,2),
            'grand_total'=>round($grand_total,2),
            'is_continue'=>$is_continue,
            'min_amount'=>$min_order_amount,
            'free_shipping_amount'=>$free_shipping_amount,
            'delivery_charge'=>($grand_total>=$free_shipping_amount) ? 0:'29'
        );
    }

}
