<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Category;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\InstamojoController;
use App\Http\Controllers\ClubPointController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PublicSslCommerzPaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\PaytmController;
use App\Http\Controllers\LetzpayController;
use App\Http\Controllers\DOFOController;
use Mail;
use PDF;
use App\Mail\InvoiceEmailManager;
use App\Order;
use App\BusinessSetting;
use App\Coupon;
use App\CouponUsage;
use App\User;
use App\Product;
use App\Address;
use Session;
use App\Utility\PayhereUtility;
use App\OrderReferalCommision;
use App\ReferalUsage;
use App\PeerSetting;
use App\ShortingHub;
use DB;
use Cookie;
use Carbon\Carbon;
use App\Events\OrderPlacedEmail;

class CheckoutController extends Controller
{

    public function __construct()
    {
        //
    }

    //check the selected payment gateway and redirect to that controller accordingly
    public function checkout(Request $request)
    {
        // echo '<pre>';
        // print_r($request->all());
        // die;
        //dd($request->all());
        
        if ($request->payment_option != null) {

            if(count(Session::get('cart'))==0){
                flash(translate('Your cart is empty'))->error();
                  
                return redirect()->route('home');
            }

            $orderController = new OrderController;
            $orderController->store($request);

           
            $request->session()->put('payment_type', 'cart_payment');

            if ($request->session()->get('order_id') != null) {
                if ($request->payment_option == 'paypal') {
                    $paypal = new PaypalController;
                    return $paypal->getCheckout();
                } elseif ($request->payment_option == 'stripe') {
                    $stripe = new StripePaymentController;
                    return $stripe->stripe();
                } elseif ($request->payment_option == 'sslcommerz') {
                    $sslcommerz = new PublicSslCommerzPaymentController;
                    return $sslcommerz->index($request);
                } elseif ($request->payment_option == 'instamojo') {
                    $instamojo = new InstamojoController;
                    return $instamojo->pay($request);
                } elseif ($request->payment_option == 'razorpay') {
                    $razorpay = new RazorpayController;
                    return $razorpay->payWithRazorpay($request);
                } elseif ($request->payment_option == 'paystack') {
                    $paystack = new PaystackController;
                    return $paystack->redirectToGateway($request);
                } elseif ($request->payment_option == 'voguepay') {
                    $voguePay = new VoguePayController;
                    return $voguePay->customer_showForm();
                } elseif ($request->payment_option == 'twocheckout') {
                    $twocheckout = new TwoCheckoutController;
                    return $twocheckout->index($request);
                } elseif($request->payment_option == 'letzpay_payment'){
                    $letzpay = new LetzpayController;
                    return $letzpay->payWithLetzpay($request);
                } elseif ($request->payment_option == 'payhere') {
                    $order = Order::findOrFail($request->session()->get('order_id'));

                    $order_id = $order->id;
                    $amount = $order->grand_total;
                    $first_name = json_decode($order->shipping_address)->name;
                    $last_name = 'X';
                    $phone = json_decode($order->shipping_address)->phone;
                    $email = json_decode($order->shipping_address)->email;
                    $address = json_decode($order->shipping_address)->address;
                    $city = json_decode($order->shipping_address)->city;
                    $state = json_decode($order->shipping_address)->state;

                    return PayhereUtility::create_checkout_form($order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
                } else if ($request->payment_option == 'ngenius') {
                    $ngenius = new NgeniusController();
                    return $ngenius->pay();
                } else if ($request->payment_option == 'flutterwave') {
                    $flutterwave = new FlutterwaveController();
                    return $flutterwave->pay();
                } else if ($request->payment_option == 'mpesa') {
                    $mpesa = new MpesaController();
                    return $mpesa->pay();
                } elseif ($request->payment_option == 'paytm') {
                    $paytm = new PaytmController;
                    return $paytm->index();
                } elseif ($request->payment_option == 'cash_on_delivery') {
                    //$orderController->store($request);
                    $order = Order::find($request->session()->get('order_id') );
                    if($order->dofo_status == 1){
                        $updateDofoComm = new DOFOController;
                        if($order->referal_discount !=null){
                            $updateDofoComm->updateCommission($order->id);
                        }
                        
            
                    }
                    $request->session()->put('cart', collect([]));
                    setcookie('cart', collect([]),time()+60*60*24*30,'/');
                    // $request->session()->forget('order_id');
                    $request->session()->forget('delivery_info');
                    $request->session()->forget('coupon_id');
                    $request->session()->forget('coupon_discount');

                    flash(translate("Your order has been placed successfully"))->success();
                    return redirect()->route('order_confirmed');
                } elseif ($request->payment_option == 'wallet') {
                    // $user = Auth::user();
                    // $user->balance -= Order::findOrFail($request->session()->get('order_id'))->wallet_amount;
                    // $user->save();
                    $order = Order::findOrFail($request->session()->get('order_id'));
                    // SMSonOrderPlaced($request->session()->get('order_id'));
                    SMSonOrderPlacedWithSlot($request->session()->get('order_id'));
                    //$this->sendOrderMail($order);
                    return $this->checkout_done($request->session()->get('order_id'), null);
                } else {
                    $order = Order::findOrFail($request->session()->get('order_id'));
                    $order->manual_payment = 1;
                    $order->save();

                    $request->session()->put('cart', collect([]));
                    // $request->session()->forget('order_id');
                    $request->session()->forget('delivery_info');
                    $request->session()->forget('coupon_id');
                    $request->session()->forget('coupon_discount');
                    $request->session()->forget('referal_discount');
                    $request->session()->forget('referal_code');

                    flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
                    return redirect()->route('order_confirmed');
                }
            }
            else{
                flash(translate('Unable to process your order.Try again later'))->error();
                return back();
            }
        } else {
            flash(translate('Select Payment Option.'))->warning();
            return back();
        }
    }

    //redirects to this method after a successfull checkout
    public function checkout_done($order_id, $payment)
    {   

        //dd("checkout done");

        $order = Order::findOrFail($order_id);
        
        $order->payment_status = 'paid';
        $order->payment_details = $payment;
        $order->log = 0;
        if($order->dofo_status == 1){
            $order->order_status = 'delivered';
            //$order->updated_at = $order->created_at;

        }
        
        $order->save();

        if($order->payment_status == 'paid'){
        $FAmount = 0;
        $unit_price = 0;
        $peer_percentage = 0;
        foreach ($order->orderDetails as $key => $value) {
            $id = $value->product_id;
            $product = Product::find($value->product_id);
            $productVarient = \App\ProductStock::where(['variant' => $value->variation, 'product_id' => $value->product_id])->first();
            $peer_discount_check = PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
            $FAmount += $value->price;
            $unit_price += $product->unit_price;  
            $peer_percentage += substr($peer_discount_check['peer_discount'], 1, -1);           
        }

         $ReferalUsage = ReferalUsage::where('order_id', $order->id)->first();
            if(!empty($ReferalUsage)){

                $margin_price = $FAmount - $unit_price;
                $partner_commision = ($margin_price * $peer_percentage ) / 100;

                //$partner_commision = ($FAmount * $ReferalUsage->commision_rate) / 100;

                $OrderReferalCommision = new OrderReferalCommision;
                $OrderReferalCommision->partner_id = $ReferalUsage->partner_id;
                $OrderReferalCommision->order_id = $order->id;
                $OrderReferalCommision->order_amount = $order->grand_total;
                $OrderReferalCommision->refral_code = $ReferalUsage->referal_code;
                $OrderReferalCommision->referal_code_commision = $ReferalUsage->commision_rate;
                $OrderReferalCommision->referal_commision_discount = $ReferalUsage->discount_amount;
                $OrderReferalCommision->master_commission = $ReferalUsage->master_percentage;
                $OrderReferalCommision->master_discount = $ReferalUsage->master_discount;
                $OrderReferalCommision->save();
            }
            
            if($order->wallet_amount>0){
                // 10june by n
                $user_wallet = User::where('id', Auth::user()->id)->first();
                $last_wallet = $user_wallet->balance - $order->wallet_amount;
                $referall_balance = User::where('id', Auth::user()->id)->update([
                                                'balance' => $last_wallet,
                                            ]);
                // Insert Record into wallet table
                $this->createHistoryInWallet($order);
    
                }
        }

        if (\App\Addon::where('unique_identifier', 'affiliate_system')->first() != null && \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated) {
            $affiliateController = new AffiliateController;
            $affiliateController->processAffiliatePoints($order);
        }

        if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated) {
            $clubpointController = new ClubPointController;
            $clubpointController->processClubPoints($order);
        }
        if (\App\Addon::where('unique_identifier', 'seller_subscription')->first() == null || !\App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated) {
            if (BusinessSetting::where('type', 'category_wise_commission')->first()->value != 1) {
                $commission_percentage = BusinessSetting::where('type', 'vendor_commission')->first()->value;
                foreach ($order->orderDetails as $key => $orderDetail) {
                    $orderDetail->payment_status = 'paid';
                    $orderDetail->save();

                    // if ($orderDetail->product->user->user_type == 'seller') {
                    //     $seller = $orderDetail->product->user->seller;
                    //     $seller->admin_to_pay = $seller->admin_to_pay + ($orderDetail->price * (100 - $commission_percentage)) / 100 + $orderDetail->tax + $orderDetail->shipping_cost;
                    //     $seller->save();
                    // }
                }
            } else {
                foreach ($order->orderDetails as $key => $orderDetail) {
                    $orderDetail->payment_status = 'paid';
                    $orderDetail->save();
                    if ($orderDetail->product->user->user_type == 'seller') {
                        $commission_percentage = $orderDetail->product->category->commision_rate;
                        $seller = $orderDetail->product->user->seller;
                        $seller->admin_to_pay = $seller->admin_to_pay + ($orderDetail->price * (100 - $commission_percentage)) / 100 + $orderDetail->tax + $orderDetail->shipping_cost;
                        $seller->save();
                    }
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                $orderDetail->payment_status = 'paid';
                $orderDetail->save();
                if ($orderDetail->product->user->user_type == 'seller') {
                    $seller = $orderDetail->product->user->seller;
                    $seller->admin_to_pay = $seller->admin_to_pay + $orderDetail->price + $orderDetail->tax + $orderDetail->shipping_cost;
                    $seller->save();
                }
            }
        }

        $order->commission_calculated = 1;
        $order->save();

       

        Session::put('cart', collect([]));
        // Session::forget('order_id');
        Session::forget('payment_type');
        Session::forget('delivery_info');
        Session::forget('coupon_id');
        Session::forget('coupon_discount');
        Session::forget('partner_id');
        Session::forget('referal_discount');
        Session::forget('referal_code');
        setcookie('cart', collect([]),time()+60*60*24*30,'/');
        flash(translate('Payment completed'))->success();

       
        
        //return view('frontend.order_confirmed', compact('order'));
        return redirect()->route('order_confirmed',compact('order'));
    }

    public function get_shipping_info(Request $request)
    {
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $request->request->add(['shipping_type_admin' => 'home_delivery']);
            $this->set_shipping($request);
            $categories = Category::all();
            return view('frontend.shipping_info', compact('categories'));
        }
        flash(translate('Your cart is empty'))->success();
        return back();
    }

    public function store_shipping_info(Request $request)
    {
        if (Auth::check()) {
            if ($request->address_id == null) {
                flash(translate("Please select delivery address"))->warning();
                return back();
            }

            $address = Address::findOrFail($request->address_id);

            $checkShortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $address->postal_code . '"]\')')->first();
                if(empty($checkShortingHub)){
                    flash(translate("Sorry, Our Services are not available at this Pincode."))->warning();
                    return back();

                }
            $data['name'] = (empty($address->name)) ? Auth::user()->name : $address->name;//Auth::user()->name;
            $data['email'] = (empty($address->email)) ? Auth::user()->email : Auth::user()->email;
            $data['address'] = $address->address;
            $data['country'] = $address->country;
            $data['city'] = $address->city;
            $data['postal_code'] = $address->postal_code;
            $data['phone'] = $address->phone;
            $data['state'] = $address->state;
            $data['checkout_type'] = $request->checkout_type;
        } else {
            $checkShortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $request->postal_code . '"]\')')->first();
            if(empty($checkShortingHub)){
                flash(translate("Sorry, Our Services are not available at this Pincode."))->warning();
                return back();

            }
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['address'] = $request->address;
            $data['country'] = $request->country;
            $data['city'] = $request->city;
            $data['postal_code'] = $request->postal_code;
            $data['phone'] = $request->phone;
            $data['state'] = $request->state;
            $data['checkout_type'] = $request->checkout_type;
        }

        $shipping_info = $data;
        $request->session()->put('shipping_info', $shipping_info);

        $subtotal = 0;
        $tax = 0;
        $shipping = 0;
        foreach (Session::get('cart') as $key => $cartItem) {
            $subtotal += $cartItem['price'] * $cartItem['quantity'];
            $tax += $cartItem['tax'] * $cartItem['quantity'];
            $shipping += $cartItem['shipping'] * $cartItem['quantity'];
        }

        $total = $subtotal + $tax + $shipping;

        if (Session::has('coupon_discount')) {
            $total -= Session::get('coupon_discount');
        }
        return redirect()->route('checkout.delivery_info');
        //return view('frontend.delivery_info');
        // return view('frontend.payment_select', compact('total'));
    }

    public function get_delivery_info_view(Request $request)
    {
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $request->request->add(['shipping_type_admin' => 'home_delivery']);
            $this->set_shipping($request);
            $categories = Category::all();
            return view('frontend.delivery_info'); 
        }
        flash(translate('Your cart is empty'))->success();
        return back();
        
    }

    public function store_delivery_info(Request $request)
    {
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $cart = $request->session()->get('cart', collect([]));
            $cart = $cart->map(function ($object, $key) use ($request) {
                if (\App\Product::find($object['id'])->added_by == 'admin') {
                    if ($request['shipping_type_admin'] == 'home_delivery' || $request['shipping_type_admin'] == 'Office_delivery') {
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

            $total = $subtotal + $tax + $shipping;

            if (Session::has('coupon_discount')) {
                $total -= Session::get('coupon_discount');
            }

            $deliveryDetail = [];

            $deliveryDetail['delivery_type'] = $request->delivery_type;
            $currentDateTime = Carbon::now();
            $is_fresh = ($request->fresh_incart==1) ? 1 : 0;
            $is_grocery = ($request->grocery_incart==1) ? 1: 0; 
            $items['fresh'] = $is_fresh;
            $items['grocery'] = $is_grocery;
            $deliveryDetail['items'] = $items;
                if($is_grocery){
                    if($request->delivery_type=="scheduled"){
                        $deliveryDetail['delivery_date_grocery'] = $request->delivery_date_grocery;
                        if(isset($request->delivery_slot_grocery)){
                            $deliveryDetail['delivery_slot_grocery'] = $request->delivery_slot_grocery;
                        }
                        else{
                            $deliveryDetail['delivery_slot_grocery'] = $request->delivery_slot_grocery_tom;
                        }
                        
                    }
                    else{
                        $deliveryDetail['delivery_date_grocery'] = $currentDateTime->addHour(24);
                        // $deliveryDetail['delivery_slot_grocery'] = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                        $deliveryDetail['delivery_slot_grocery'] = date("H:i:s",strtotime($currentDateTime));
                    }

                }

                if($is_fresh){
                    if($request->delivery_type=="scheduled"){
                        $deliveryDetail['delivery_date_fresh'] = $request->delivery_date_fresh;
                        if(isset($request->delivery_slot_fresh)){
                            $deliveryDetail['delivery_slot_fresh'] = $request->delivery_slot_fresh;
                        }
                        else{
                            $deliveryDetail['delivery_slot_fresh'] = $request->delivery_slot_fresh_tom;
                        }
                        
                    }
                    else{
                        $deliveryDetail['delivery_date_fresh'] = $currentDateTime->addHour(24);
                        // $deliveryDetail['delivery_slot_fresh'] = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                        $deliveryDetail['delivery_slot_fresh'] = date("H:i:s",strtotime($currentDateTime));
                    }
                    
                }
                
                
            $request->session()->put('delivery_schedule',json_encode($deliveryDetail));
            //info(session()->get('delivery_schedule'));
            return view('frontend.payment_select', compact('total'));
        } else {
            flash(translate('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }

    public function get_payment_info(Request $request)
    {
        $subtotal = 0;
        $tax = 0;
        $shipping = 0;

        foreach (Session::get('cart') as $key => $cartItem) {
            $subtotal += $cartItem['price'] * $cartItem['quantity'];
            $tax += $cartItem['tax'] * $cartItem['quantity'];
            $shipping += $cartItem['shipping'] * $cartItem['quantity'];
        }

        $total = $subtotal + $tax + $shipping;

        if (Session::has('coupon_discount')) {
            $total -= Session::get('coupon_discount');
        }  

        return view('frontend.payment_select', compact('total'));
    }

    public function apply_coupon_code(Request $request)
    {
        //dd($request->all());
        $coupon = Coupon::where('code', $request->code)->first();

        if ($coupon != null) {
            if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                if (CouponUsage::where('user_id', Auth::user()->id)->where('coupon_id', $coupon->id)->first() == null) {
                    $coupon_details = json_decode($coupon->details);

                    if ($coupon->type == 'cart_base') {
                        $subtotal = 0;
                        $tax = 0;
                        $shipping = 0;
                        foreach (Session::get('cart') as $key => $cartItem) {
                            $subtotal += $cartItem['price'] * $cartItem['quantity'];
                            $tax += $cartItem['tax'] * $cartItem['quantity'];
                            $shipping += $cartItem['shipping'] * $cartItem['quantity'];
                        }
                        $sum = $subtotal + $tax + $shipping;

                        if ($sum > $coupon_details->min_buy) {
                            if ($coupon->discount_type == 'percent') {
                                $coupon_discount = ($sum * $coupon->discount) / 100;
                                if ($coupon_discount > $coupon_details->max_discount) {
                                    $coupon_discount = $coupon_details->max_discount;
                                }
                            } elseif ($coupon->discount_type == 'amount') {
                                $coupon_discount = $coupon->discount;
                            }
                            $request->session()->put('coupon_id', $coupon->id);
                            $request->session()->put('coupon_discount', $coupon_discount);
                            flash(translate('Coupon has been applied'))->success();
                        }
                    } elseif ($coupon->type == 'product_base') {
                        $coupon_discount = 0;
                        foreach (Session::get('cart') as $key => $cartItem) {
                            foreach ($coupon_details as $key => $coupon_detail) {
                                if ($coupon_detail->product_id == $cartItem['id']) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount += $cartItem['price'] * $coupon->discount / 100;
                                    } elseif ($coupon->discount_type == 'amount') {
                                        $coupon_discount += $coupon->discount;
                                    }
                                }
                            }
                        }
                        $request->session()->put('coupon_id', $coupon->id);
                        $request->session()->put('coupon_discount', $coupon_discount);
                        flash(translate('Coupon has been applied'))->success();
                    }
                } else {
                    flash(translate('You already used this coupon!'))->warning();
                }
            } else {
                flash(translate('Coupon expired!'))->warning();
            }
        } else {
            flash(translate('Invalid coupon!'))->warning();
        }
        return back();
    }

    public function order_confirmed(Request $request){
    $order = Order::findOrFail(Session::get('order_id'));

    // 13-10-2021
    if(Session::has('referal_code')){
        $user = new user;
        $user->used_referral_code = $request->session()->get('referal_code');
        $user->id = $order->user_id;
        DB::table('users')
                ->where('id', $user->id)
                ->update(['used_referral_code' => $user->used_referral_code]);
    }

    if(Session::has('referal_discount')){
        $request->session()->forget('coupon_id');
        $request->session()->forget('coupon_discount');
    }
    return view('frontend.order_confirmed', compact('order'));
    }


    public function remove_coupon_code(Request $request)
    {
        $request->session()->forget('coupon_id');
        $request->session()->forget('coupon_discount');
        return back();
    }

    public function set_shipping(Request $request)
    {
        $min_order_amount = (int) env("MIN_ORDER_AMOUNT");
        $free_shipping_amount = (int) env("FREE_SHIPPING_AMOUNT");

        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $cart = $request->session()->get('cart', collect([]));
            $cart = $cart->map(function ($object, $key) use ($request) {
                if (\App\Product::find($object['id'])->added_by == 'admin') {
                    if ($request['shipping_type_admin'] == 'home_delivery' || $request['shipping_type_admin'] == 'Office_delivery') {
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
            if($subtotal>=$free_shipping_amount)
            {
                $shipping = 0;
            }
           
            $total = $subtotal + $tax + $shipping;

            if (Session::has('coupon_discount')) {
                $total -= Session::get('coupon_discount');
            }
        }
    }

    public function sendOrderMail($order){
        //stores the pdf for invoice
        $orderdetail = \App\OrderDetail::where('order_id',$order->id)->get();
        $orderproducts = $orderdetail->groupBy(function($item){
            return (string)$item->product['tax'];
        })->sortKeys();
        $schedules = $order->sub_orders;
             $pdf = PDF::setOptions([
                       'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                       'logOutputFile' => storage_path('logs/log.htm'),
                       'tempDir' => storage_path('logs/')
                    ])->loadView('invoices.new_customer_invoice', compact(['orderdetail','order','orderproducts','schedules']));

       $output = $pdf->output();
       file_put_contents('public/invoices/'.'Order#'.$order->code.'.pdf', $output);
       $array['view'] = 'emails.invoice';
       $array['subject'] = 'Order Placed - '.$order->code;
       $array['from'] = env('mail_from_address');
       $array['content'] = translate('Hi. A new order has been placed. Please check the attached invoice.');
       $array['file'] = 'public/invoices/Order#'.$order->code.'.pdf';
       $array['file_name'] = 'Order#'.$order->code.'.pdf';

        $postal_code = json_decode($order->shipping_address,true)['postal_code']; 
        $sorting_hub_id = ShortingHub::whereRaw(
                'JSON_CONTAINS(area_pincodes, \'["'.$postal_code.'"]\')'
            )->select('user_id')->first();
        $sh_id = $sorting_hub_id->user_id;
        $sh_name = User::where('id', $sh_id)->first()->email;

           if(env('MAIL_USERNAME') != null){
            Mail::to(json_decode($order->shipping_address,true)['email'])->queue(new InvoiceEmailManager($array));
            $array['subject'] = "New Order has been placed.";
            $array['content'] = translate('Dear Admin, A new order has been placed. You can check  order details in the invoice attached below.');
            Mail::to(env("ADMIN_MAIL"))->queue(new InvoiceEmailManager($array));
               try {
                   //Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
                   Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                   Mail::to(User::where('id', $sh_id)->first()->email)->queue(new InvoiceEmailManager($array));
               } catch (\Exception $e) {

               }
           }

       }

       function SMSonOrderPlaced($order_id){
        $order = Order::findOrFail($order_id);
        $currentTime = Carbon::now();
        if($currentTime->toTimeString()>=strtotime('18:00:00')){
            $addDate = Carbon::now()->addDay();
            $date = $addDate->toDateString();
        }else{
            $date = $currentTime->toDateString();
        }
        $to = json_decode($order->shipping_address,true)['phone'];
        $user_phone = "";
        if(!empty($order->user_id))
        {
            $user_phone = User::where('id',$order->user_id)->first()->phone;
        }
        
    
        $from = "RZANA";
        $tid  = "1707162444146910141"; 
        $msg = "Dear Customer, Your order ".$order->code." will be delivered on ".date('d-m-Y',strtotime($date))." between 08:00 AM and 08:00 PM. Please ensure that you keep a bag outside for a no contact & hassle free delivery. Thanks, Team Rozana";
        if($to==$user_phone || !empty($order->guest_id)){
            return mobilnxtSendSMS($to,$from,$msg,$tid);
        }else{
            mobilnxtSendSMS($to,$from,$msg,$tid);
            mobilnxtSendSMS($user_phone,$from,$msg,$tid);
        }
    }

    public function createHistoryInWallet($order){

        $wallet = new \App\Wallet;
        $wallet->user_id = $order->user_id;
        $wallet->amount = $order->wallet_amount;
        $wallet->order_id = $order->id;
        $wallet->tr_type = 'debit';
        $wallet->payment_method = 'wallet';
        if($wallet->save()){
            return true;
        }
        return false;


       
    
}

}
