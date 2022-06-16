<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\SubSubCategory;
use App\Category;
use Session;
use App\Color;
use Cookie;
use App\PeerSetting;
use App\ProductStock;

class CartController extends Controller
{
    public function index(Request $request)
    {
        //dd($cart->all());
        $request->request->add(['shipping_type_admin' => 'home_delivery']);
            $this->set_shipping($request);
        $categories = Category::all();
        return view('frontend.view_cart', compact('categories'));
    }

    public function showCartModal(Request $request)
    {
        $product = Product::find($request->id);
        return view('frontend.partials.addToCart', compact('product'));
    }

    public function updateNavCart(Request $request)
    {
        return view('frontend.partials.cart');
    }

    public function addToCart(Request $request)
    {
        // echo '<pre>';
        // print_r($request->all());
        // die;
        $product = Product::find($request->id);
        $shortId = "";
        $data = array();
        $data['id'] = $product->id;
        $str = '';
        $tax = 0;

        if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
  
        }

        if($product->digital != 1 && $request->quantity < $product->min_qty) {
            return view('frontend.partials.minQtyNotSatisfied', [
                'min_qty' => $product->min_qty
            ]);
        }


        //check the color enabled or disabled for the product
        if($request->has('color')){
            $data['color'] = $request['color'];
            $str = Color::where('code', $request['color'])->first()->name;
        }

        if ($product->digital != 1) {
            //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
            foreach (json_decode(Product::find($request->id)->choice_options) as $key => $choice) {
                if($str != null){
                    $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
                else{
                   
                    $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                  
                }
            }
        }
       
        $data['variant'] = $str;
        if($str != null && $product->variant_product){
            $product_stock = $product->stocks->where('variant', $str)->first();
                if(!empty($shortId)){
                        $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first(); 
                        if($mappedProductPrice['selling_price'] !=0){
                            $price = $mappedProductPrice['selling_price'];
                        }else{
                            $price = $product_stock->price;
                        }
                        $quantity = mapped_product_stock($shortId->sorting_hub_id,$request->id);

                }else{
                    $price = $product_stock->price;
                    $quantity = $product_stock->qty;
                }
                //   $quantity = $product_stock->qty;
                

            if($quantity >= $request['quantity']){
                // $variations->$str->qty -= $request['quantity'];
                // $product->variations = json_encode($variations);
                // $product->save();
            }
            else{
                return view('frontend.partials.outOfStockCart');
            }
        }
        else{
            if(!empty($shortId)){
                $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first();
                if($mappedProductPrice['selling_price'] !=0){
                    $price = $mappedProductPrice['selling_price'];
                }else{
                    $price = $product_stock->unit_price;
                } 


            }else{
              $price = $product->unit_price;

            }
        }

        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        // $flash_deals = \App\FlashDeal::where('status', 1)->get();
        // $inFlashDeal = false;
        // foreach ($flash_deals as $flash_deal) {
        //     if ($flash_deal != null && $flash_deal->status == 1  && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && \App\FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
        //         $flash_deal_product = \App\FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
        //         if($flash_deal_product->discount_type == 'percent'){
        //             $price -= ($price*$flash_deal_product->discount)/100;
        //         }
        //         elseif($flash_deal_product->discount_type == 'amount'){
        //             $price -= $flash_deal_product->discount;
        //         }
        //         $inFlashDeal = true;
        //         break;
        //     }
        // }
        // if (!$inFlashDeal) {
        //     if($product->discount_type == 'percent'){
        //         $price -= ($price*$product->discount)/100;
        //     }
        //     elseif($product->discount_type == 'amount'){
        //         $price -= $product->discount;
        //     }
        // }

        if($product->tax_type == 'percent'){
            $tax = ($price*$product->tax)/100;
        }
        elseif($product->tax_type == 'amount'){
            $tax = $product->tax;
        }

        $data['quantity'] = $request['quantity'];
        $data['price'] = $price;
        $data['tax'] = $tax;
        $data['shipping'] = 0;
        $data['product_referral_code'] = null;
        $data['digital'] = $product->digital;

        if ($request['quantity'] == null){
            $data['quantity'] = 1;
        }

        if(Cookie::has('referred_product_id') && Cookie::get('referred_product_id') == $product->id) {
            $data['product_referral_code'] = Cookie::get('product_referral_code');
        }

        if($request->session()->has('cart')){
            $foundInCart = false;
            $cart = collect();

            foreach ($request->session()->get('cart') as $key => $cartItem){
                if($cartItem['id'] == $request->id){
                    if($cartItem['variant'] == $str){
                        $foundInCart = true;
                        $cartItem['quantity'] += $request['quantity'];
                    }
                }
                $cart->push($cartItem);
            }

            if (!$foundInCart) {
                $cart->push($data);
            }
            //print_r($cart);exit;
            $request->session()->put('cart', $cart);
        }
        else{
            $cart = collect([$data]);
            $request->session()->put('cart', $cart);
        }
        setcookie('cart', $cart,time()+60*60*24*30,'/');

        return view('frontend.partials.addedToCart', compact('product', 'data'));
    }

    //removes from Cart
    public function removeFromCart(Request $request)
    {
        if($request->session()->has('cart')){
            $cart = $request->session()->get('cart', collect([]));
            $cart->forget($request->key);
            $request->session()->put('cart', $cart);
        }
        setcookie('cart', $cart,time()+60*60*24*30,'/');
        if(isset($request->delivery_page)){
            return view('frontend.partials.cart_summary');
        }
        
        return view('frontend.partials.cart_details');
    }

    function updateCartQ(Request $request)
    {
        $product = Product::find($request->id);

        $data = array();
        $data['id'] = $product->id;
        $str = '';
        $tax = 0;
        $shortId = "";

        if($product->digital != 1 && $request->quantity < $product->min_qty) {
            return view('frontend.partials.minQtyNotSatisfied', [
                'min_qty' => $product->min_qty
            ]);
        }

        if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
  
        }

        //check the color enabled or disabled for the product
        if($request->has('color')){
            $data['color'] = $request['color'];
            $str = Color::where('code', $request['color'])->first()->name;
        }

        if ($product->digital != 1) {
            //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
            foreach (json_decode(Product::find($request->id)->choice_options) as $key => $choice) {
                if($str != null){
                    $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
                else{
                    $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                }
            }
        }

        $data['variant'] = $str;

        if($str != null && $product->variant_product){
            $product_stock = $product->stocks->where('variant', $str)->first();
            if(!empty($shortId)){
                $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first(); 
                if($mappedProductPrice['selling_price'] !=0){
                    $price = $mappedProductPrice['selling_price'];
                }else{
                    $price = $product_stock->price;
                }
                $quantity = mapped_product_stock($shortId->sorting_hub_id,$request->id);

        }else{
            $price = $product_stock->price;
            $quantity = $product_stock->qty;
        }

            if($quantity >= $request['quantity']){
                // $variations->$str->qty -= $request['quantity'];
                // $product->variations = json_encode($variations);
                // $product->save();
            }
            else{
                return view('frontend.partials.outOfStockCart');
            }
        }
        else{
            if(!empty($shortId)){
                $mappedProductPrice = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$product->id])->first();
                if($mappedProductPrice['purchased_price'] !=0){
                    $price = $mappedProductPrice['purchased_price'];
                }else{
                    $price = $product_stock->unit_price;
                } 


            }else{
              $price = $product->unit_price;

            }
        }

        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        $flash_deals = \App\FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1  && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && \App\FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = \App\FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
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

        if($product->tax_type == 'percent'){
            $tax = ($price*$product->tax)/100;
        }
        elseif($product->tax_type == 'amount'){
            $tax = $product->tax;
        }

        $data['quantity'] = $request['quantity'];
        $data['price'] = $price;
        $data['tax'] = $tax;
        $data['shipping'] = 0;
        $data['product_referral_code'] = null;
        $data['digital'] = $product->digital;

        if ($request['quantity'] == null){
            $data['quantity'] = 1;
        }

        if(Cookie::has('referred_product_id') && Cookie::get('referred_product_id') == $product->id) {
            $data['product_referral_code'] = Cookie::get('product_referral_code');
        }

        if($request->session()->has('cart')){
            $foundInCart = false;
            $cart = collect();

            foreach ($request->session()->get('cart') as $key => $cartItem){
                if($cartItem['id'] == $request->id){
                    if($cartItem['variant'] == $str){
                        $foundInCart = true;
                        
                        $cartItem['quantity'] -= $request['quantity'];
                        $request->request->add(['key' => $key]);
                        
                        
                    }
                    if($cartItem['quantity']<='0')
                        {
                            $this->removeFromCart($request); 
                        }
                }
                if($cartItem['quantity']>0){
                   $cart->push($cartItem); 
                }
                
            }

            if (!$foundInCart) {
                $cart->push($data);
            }
            $request->session()->put('cart', $cart);
        }
        else{
            $cart = collect([$data]);
            $request->session()->put('cart', $cart);
        }
        setcookie('cart', $cart,time()+60*60*24*30,'/');
        return view('frontend.partials.addedToCart', compact('product', 'data'));
    }
    
    

    //updated the quantity for a cart item
    public function updateQuantity(Request $request)
    {
        $shortId = "";
        if(!empty(Cookie::get('pincode'))){ 
            $pincode = Cookie::get('pincode');
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
  
        }

        $cart = $request->session()->get('cart', collect([]));
        $cart = $cart->map(function ($object, $key) use ($request,$shortId) {
            if($key == $request->key){
                $product = \App\Product::find($object['id']);
                if($object['variant'] != null && $product->variant_product){
                    $product_stock = $product->stocks->where('variant', $object['variant'])->first();
                    
                    if(!empty($shortId)){
                        $mappedProduct = \App\MappingProduct::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'product_id'=>$object['id']])->first(); 
                        $quantity = $mappedProduct->qty;
                    }
                    else{
                        $quantity = $product_stock->qty;
                    } 
                    
                    if($quantity >= $request->quantity){
                        if($request->quantity >= $product->min_qty){
                            $object['quantity'] = $request->quantity;
                        }
                    }
                }
                elseif($request->quantity >= $product->min_qty){
                    $object['quantity'] = $request->quantity;
                }
            }
            return $object;
        });
        $request->session()->put('cart', $cart);
        if(isset($request->delivery_page)){
            return view('frontend.partials.cart_summary');
        }
        return view('frontend.partials.cart_details');
    }

    public function set_shipping(Request $request)
    {
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $cart = $request->session()->get('cart', collect([]));
            $cart = $cart->map(function ($object, $key) use ($request) {
                if (\App\Product::find($object['id'])->added_by == 'admin') {
                    if ($request['shipping_type_admin'] == 'home_delivery') {
                        $object['shipping_type'] = 'home_delivery';
                    } else {
                        $object['shipping_type'] = 'pickup_point';
                        $object['pickup_point'] = $request->pickup_point_id_admin;
                    }
                } else {
                    if ($request['shipping_type_' . \App\Product::find($object['id'])->user_id] == 'home_delivery') {
                        $object['shipping_type'] = 'home_delivery';
                    } else {
                        $object['shipping_type'] = 'pickup_point';
                        $object['pickup_point'] = $request['pickup_point_id_' . \App\Product::find($object['id'])->user_id];
                    }
                }
                return $object;
            });

            $request->session()->put('cart', $cart);

            $cart = $cart->map(function ($object, $key) use ($request) {
                $object['shipping'] = getShippingCost($key);
                return $object;
            });

            $request->session()->put('cart', $cart);
           
            $subtotal = 0;
            $tax = 0;
            $shipping = 0;
            foreach (Session::get('cart') as $key => $cartItem) {
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                $tax += $cartItem['tax'] * $cartItem['quantity'];
                $shipping += $cartItem['shipping'] * $cartItem['quantity'];
            }

            if($subtotal>=1500)
            {
                $shipping = 0;
            }
            
            $total = $subtotal + $tax + $shipping;

            if (Session::has('coupon_discount')) {
                $total -= Session::get('coupon_discount');
            }
        }
    }

    public function cart_qty(Request $request)
    {
        $cartQty=0;
        if(Session::has('cart'))
        {
            $cartQty = Session::get('cart')->where('id',$request->pid)->first()['quantity'];
            if(is_null($cartQty)){
                $cartQty = 0;
            }
        }
        
        return $cartQty;
    }

    public function totalCartItem(Request $request)
    {
        $totalItems = 0;
        if(Session::has('cart'))
        {
            $totalItems = count(Session::get('cart'));
        }

        return $totalItems;
    }

    public function removecartproducts(Request $request){
       
        if(count($request->p_id)>0){
            foreach($request->p_id as $key => $pid) {  
                // echo $pid;
                 if($request->session()->has('cart')){
                    $cart = $request->session()->get('cart', collect([]));
                    $cart->forget($pid);                    
                }
            }
            return 1;
        }
        return 0;
       

    }
}
