<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\PublicSslCommerzPaymentController;
use App\Http\Controllers\InstamojoController;
use App\Http\Controllers\PaytmController;
use Auth;
use Session;
use DB;
use \Carbon\Carbon;
use App\Wallet;
use App\WalletPayout;
use App\Utility\PayhereUtility;
use App\Traits\WalletTrait;
use App\Traits\WhatsAppTrait;
use App\PeerPartner;
use App\OrderReferalCommision;

class WalletController extends Controller
{
    use WalletTrait,WhatsAppTrait;
    public function index()
    {
        //$wallets = Wallet::where('user_id', Auth::user()->id)->orderBy('id','desc')->paginate(10);
        $dateS = Carbon::now()->subDays(7);
        //$dateE = Carbon::now(); 
        $user_id = Auth::user()->id;
        $payout_balance = Wallet::where('user_id',$user_id)->where('payment_method','referral')->where('payment_method','refund')->where('tr_type','credit')->where('created_at','<',$dateS)->sum('amount');
        $paid_amount = Wallet::where('user_id',$user_id)->where('payment_method','razorpayx')->where('tr_type','debit')->where('created_at','>',$dateS)->sum('amount');
        $payout_balance = $payout_balance-$paid_amount;
        $gift_card_balance = Wallet::where('user_id',$user_id)->where('payment_method','gift_card')->sum('amount');
        $debited_amount = Wallet::where('user_id',$user_id)->where('tr_type','debit')->sum('amount');
        $partner = \App\PeerPartner::where('user_id',$user_id)->where('verification_status',1)->first();
        $total_requested_amount = \App\WalletPayout::where('user_id',$user_id)->where('status',0)->sum('amount');
        $transferable_amount_after_request = $payout_balance-$total_requested_amount;
        $is_request_available = ($payout_balance>$total_requested_amount) ? true:false;

       
        if (Auth::user()->id != null){
            $ids = Auth::user()->id;
            $peer_codes = \App\PeerPartner::where('user_id', $ids)->where('verification_status', 1)->where('peertype_approval', 1)->select('id','code')->first(); 
                if(!empty($peer_codes)){
                    $id = $peer_codes->id;  

                    $start_date = '';
                    $end_date = '';
                    $from_date = date('Y-m-d', strtotime('-7 days'));
                    $to_date   = date('Y-m-d');

                    $peer_refferal_code = PeerPartner::where('user_id', $ids)->select('code','user_id', 'id')->first();
                    $master_code = $peer_refferal_code['code'];


                    $peercodes = array();
                    $subpeerlist = PeerPartner::where('parent', $peer_refferal_code['id'])->select('code')->where('code', '!=', null)->get();
                    foreach($subpeerlist as $key => $peerlist){
                        array_push($peercodes, $peerlist->code);
                    }
                    // DB::enableQueryLog();
                    $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->whereIn('refral_code', $peercodes)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount, refral_code, created_at, id, partner_id')->groupBy('refral_code')->get();   
                    // dd(DB::getQueryLog());        
                } else{
                    $master_code = array();
                    $all_orders = array();
                }
        }     
        //peer

        $id = Auth::user()->id;
        $start_date = '';
        $end_date = '';
        $from_date = date('Y-m-d', strtotime('-7 days'));
        $to_date   = date('Y-m-d');

        $peer_refferal_code = PeerPartner::where('user_id', $id)->select('parent','name','email')->first();
        if(!empty($peer_codes)){
            $peer_name = $peer_refferal_code['name'];
            $peer_email = $peer_refferal_code['email'];
        }else{
            $peer_name = '';
            $peer_email = '';
        }    
        
        
        $wallets = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'created_at', 'id', 'partner_id')->get();   


        $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first();    
        if(!empty($all_total)){
            $all_total = $all_total;
        }else{
            $all_total = array();
        }   
        
        $wallet_recharge = Wallet::where('user_id', Auth::user()->id)->orderBy('id','desc')->paginate(10);

        return view('frontend.wallet', compact('wallets','payout_balance','gift_card_balance','debited_amount','partner','is_request_available','transferable_amount_after_request','all_orders', 'id', 'start_date', 'end_date', 'master_code','all_total','peer_name','peer_email','wallet_recharge'));
    }

    public function show_subpeers_by_date(Request $request, $id)
    { 

        $id = decrypt($id);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;

        $peer_refferal_code = PeerPartner::where('user_id', $id)->select('code','user_id', 'id')->first();
        $master_code = $peer_refferal_code->code;


        $peercodes = array();
        $subpeerlist = PeerPartner::where('parent', $peer_refferal_code->id)->select('code')->where('code', '!=', null)->get();
        foreach($subpeerlist as $key => $peerlist){
            array_push($peercodes, $peerlist->code);
        }
       // DB::enableQueryLog();
        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->whereIn('refral_code', $peercodes)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount, refral_code, created_at, id, partner_id')->groupBy('refral_code')->get(); 
        // dd(DB::getQueryLog());
       // dd($all_orders);      
        return view('frontend.peerwallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_code'));
    }

    //peer by peer
    public function show_wpeer_commission($id)
    {
        $id = decrypt($id);
        $start_date = '';
        $end_date = '';
        $from_date = date('Y-m-d', strtotime('-7 days'));
        $to_date   = date('Y-m-d');

        $peer_refferal_code = PeerPartner::where('user_id', $id)->select('parent','name','email')->first();
        $peer_name = $peer_refferal_code['name'];
        $peer_email = $peer_refferal_code['email'];
        $master_code = PeerPartner::where('id', $peer_refferal_code['parent'])->select('name','email','code')->first();
        $master_name = $master_code['name'];
        $master_email = $master_code['email'];
        $master_code = $master_code['code'];
        
        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'created_at', 'id', 'partner_id')->get();   

        $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first();       

        return view('frontend.peers_wallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function show_wpeer_commission_by_date(Request $request, $id)
    { 
        $id = decrypt($id);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;

        $peer_refferal_code = PeerPartner::where('user_id', $id)->select('parent','name','email')->first();
        $peer_name = $peer_refferal_code['name']; 
        $peer_email = $peer_refferal_code['email'];
        $master_code = PeerPartner::where('id', $peer_refferal_code['parent'])->select('name','email','code')->first();
        $master_name = $master_code['name'];
        $master_email = $master_code['email'];
        $master_code = $master_code['code'];
        
        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'created_at', 'id', 'partner_id')->get(); 

         $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first(); 
         // dd($all_total);
             
        return view('frontend.peers_wallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    //customer wise peer
    public function show_wallet_customerpeer_commission($id)
    {
        $id = decrypt($id);
        $pieces = explode("_", $id);
        $customer_id = $pieces[0];
        $ids = $pieces[1];
        
        $start_date = '';
        $end_date = '';
        $from_date = date('Y-m-d', strtotime('-7 days'));
        $to_date   = date('Y-m-d');

        $peer_refferal_code = PeerPartner::where('user_id', $ids)->select('parent','name','email')->first();
        $peer_name = $peer_refferal_code['name'];
        $peer_email = $peer_refferal_code['email'];
        $master_code = PeerPartner::where('id', $peer_refferal_code['parent'])->select('name','email','code')->first();
        $master_name = $master_code['name'];
        $master_email = $master_code['email'];
        $master_code = $master_code['code'];
        

        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'order_referal_commision.created_at', 'order_referal_commision.id', 'partner_id')->get();   

        $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first();       

        return view('frontend.peers_customer_wallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function show_wallet_customerpeer_commission_by_date(Request $request, $id)
    { 
        $id = decrypt($id);
        $pieces = explode("_", $id);
        $customer_id = $pieces[0];
        $ids = $pieces[1];

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;

        $peer_refferal_code = PeerPartner::where('user_id', $ids)->select('parent','name','email')->first();
        $peer_name = $peer_refferal_code['name']; 
        $peer_email = $peer_refferal_code['email'];
        $master_code = PeerPartner::where('id', $peer_refferal_code['parent'])->select('name','email','code')->first();
        $master_name = $master_code['name'];
        $master_email = $master_code['email'];
        $master_code = $master_code['code'];
        
       $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'order_referal_commision.created_at', 'order_referal_commision.id', 'partner_id')->get();   

         $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first(); 
         // dd($all_total);
             
        return view('frontend.peers_customer_wallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    //subpeer 1 dec 2021
    public function show_wsubpeer_commission_by_date(Request $request, $id)
    { 
        $id = decrypt($id);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;

        $peer_refferal_code = PeerPartner::where('user_id', $id)->select('parent','name','email')->first();
        $peer_name = $peer_refferal_code['name']; 
        $peer_email = $peer_refferal_code['email'];
        $master_code = PeerPartner::where('id', $peer_refferal_code['parent'])->select('name','email','code')->first();
        $master_name = $master_code['name'];
        $master_email = $master_code['email'];
        $master_code = $master_code['code'];
        
        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'created_at', 'id', 'partner_id')->get(); 

         $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first(); 
         // dd($all_total);
             
        return view('frontend.subpeers_wallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function show_wallet_customer_subpeer_commission($id)
    {
        $id = decrypt($id);
        $pieces = explode("_", $id);
        $customer_id = $pieces[0];
        $ids = $pieces[1];
        
        $start_date = '';
        $end_date = '';
        $from_date = date('Y-m-d', strtotime('-7 days'));
        $to_date   = date('Y-m-d');

        $peer_refferal_code = PeerPartner::where('user_id', $ids)->select('parent','name','email')->first();
        $peer_name = $peer_refferal_code['name'];
        $peer_email = $peer_refferal_code['email'];
        $master_code = PeerPartner::where('id', $peer_refferal_code['parent'])->select('name','email','code')->first();
        $master_name = $master_code['name'];
        $master_email = $master_code['email'];
        $master_code = $master_code['code'];
        

        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'order_referal_commision.created_at', 'order_referal_commision.id', 'partner_id')->get();   

        $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first();       

        return view('frontend.subpeers_customer_wallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function show_wallet_customer_subpeer_commission_by_date(Request $request, $id)
    { 
        $id = decrypt($id);
        $pieces = explode("_", $id);
        $customer_id = $pieces[0];
        $ids = $pieces[1];

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;

        $peer_refferal_code = PeerPartner::where('user_id', $ids)->select('parent','name','email')->first();
        $peer_name = $peer_refferal_code['name']; 
        $peer_email = $peer_refferal_code['email'];
        $master_code = PeerPartner::where('id', $peer_refferal_code['parent'])->select('name','email','code')->first();
        $master_name = $master_code['name'];
        $master_email = $master_code['email'];
        $master_code = $master_code['code'];
        
       $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->select('order_id', 'order_amount', 'referal_commision_discount','master_discount', 'refral_code', 'order_referal_commision.created_at', 'order_referal_commision.id', 'partner_id')->get();   

         $all_total = OrderReferalCommision::whereBetween(DB::raw('DATE(order_referal_commision.created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->where('partner_id', $ids)->join('orders', 'order_referal_commision.order_id', '=', 'orders.id')->where('orders.user_id', $customer_id)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount')->groupBy('refral_code')->first(); 
         // dd($all_total);
             
        return view('frontend.subpeers_customer_wallet', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function recharge(Request $request)
    {
        $data['amount'] = $request->amount;
        $data['payment_method'] = $request->payment_option;

        // dd($data);

        $request->session()->put('payment_type', 'wallet_payment');
        $request->session()->put('payment_data', $data);

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
            $voguepay = new VoguePayController;
            return $voguepay->customer_showForm();
        } elseif ($request->payment_option == 'payhere') {
            $order_id = rand(100000, 999999);
            $user_id = Auth::user()->id;
            $amount = $request->amount;
            $first_name = Auth::user()->name;
            $last_name = 'X';
            $phone = '123456789';
            $email = Auth::user()->email;
            $address = 'dummy address';
            $city = 'Colombo';

            return PayhereUtility::create_wallet_form($user_id, $order_id, $amount, $first_name, $last_name, $phone, $email, $address, $city);
        } elseif ($request->payment_option == 'ngenius') {
            $ngenius = new NgeniusController();
            return $ngenius->pay();
        }else if ($request->payment_option == 'mpesa') {
            $mpesa = new MpesaController();
            return $mpesa->pay();
        } else if ($request->payment_option == 'flutterwave') {
            $flutterwave = new FlutterwaveController();
            return $flutterwave->pay();
        } elseif ($request->payment_option == 'paytm') {
            $paytm = new PaytmController;
            return $paytm->index();
        }
    }

    public function wallet_payment_done($payment_data, $payment_details)
    {
        $user = Auth::user();
        $user->balance = $user->balance + $payment_data['amount'];
        $user->save();

        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $payment_data['amount'];
        $wallet->payment_method = $payment_data['payment_method'];
        $wallet->order_id = Session::get('ord_id');
        $wallet->payment_details = $payment_details;
        $wallet->save();

        Session::forget('payment_data');
        Session::forget('payment_type');
        Session::forget('ord_id');

        flash(translate('Payment completed'))->success();
        return redirect()->route('wallet.index');
    }

    public function offline_recharge(Request $request)
    {
        $wallet = new Wallet;
        $wallet->user_id = Auth::user()->id;
        $wallet->amount = $request->amount;
        $wallet->payment_method = $request->payment_option;
        $wallet->payment_details = $request->trx_id;
        $wallet->approval = 0;
        $wallet->offline_payment = 1;
        if ($request->hasFile('photo')) {
            $wallet->reciept = $request->file('photo')->store('uploads/wallet_recharge_reciept');
        }
        $wallet->save();
        flash(translate('Offline Recharge has been done. Please wait for response.'))->success();
        return redirect()->route('wallet.index');
    }

    public function offline_recharge_request()
    {
        $wallets = Wallet::where('offline_payment', 1)->paginate(10);
        return view('manual_payment_methods.wallet_request', compact('wallets'));
    }

    public function updateApproved(Request $request)
    {
        $wallet = Wallet::findOrFail($request->id);
        $wallet->approval = $request->status;
        if ($request->status == 1) {
            $user = $wallet->user;
            $user->balance = $user->balance + $wallet->amount;
            $user->save();
        } else {
            $user = $wallet->user;
            $user->balance = $user->balance - $wallet->amount;
            $user->save();
        }
        if ($wallet->save()) {
            return 1;
        }
        return 0;
    }

    function transferToBankAccount(Request $request){
        $requestedData = explode('|',$request->bank_account);
        //dd($requestedData);
        $payout = new WalletPayout;
        $payout->user_id = Auth::user()->id;
        $payout->holder_name = $requestedData[0];
        $payout->account_number = $requestedData[1];
        $payout->fund_account_number = $requestedData[2];
        $payout->ifsc = $requestedData[3];
        $payout->amount = $request->amount;
        $payout->tds = 0;//$request->tds;
        if($payout->save()){
            flash('Transfer requested successfully')->success();
            return back();
        }
        flash('Something went wrong!')->error();
        return back();

    }
    public function transferRequests(){
        $transfer_requests = WalletPayout::orderBy('id','desc')->paginate(10);
        return view('frontend.peer_partner.transfer_requests',compact('transfer_requests'));
    }

    public function updatePayoutRequestStatus(Request $request){
       
        $id = $request->id;
        $status = $request->status;
        $walletPayout = \App\WalletPayout::findOrFail($id);
        $walletPayout->status = $status;
        if(!empty($request->remarks)){
            $walletPayout->remarks = $request->remarks;
        }
        
        if($status==1)
        {
            // transfer amount to partner's bank account
            $res = $this->createPartnerPayout($id);
            $res = $res->getData();
            if($res->success){
                $walletPayout->save();

                // store in wallet table and also update in user balance
                $wallet = new Wallet;
                $wallet->user_id = $walletPayout->user_id;
                $wallet->amount = $walletPayout->amount;
                $wallet->payment_method = "razorpayx";
                $wallet->payout_request_id = $walletPayout->id;
                $wallet->payment_details = json_encode($res->response);
                $wallet->tr_type = "debit";
                $wallet->save();
                // update user balance (total credit- total debit)
                $credit = Wallet::where('user_id',$walletPayout->user_id)->where('tr_type','credit')->sum('amount');
                $debit =  Wallet::where('user_id',$walletPayout->user_id)->where('tr_type','debit')->sum('amount');
                $user = \App\User::find($walletPayout->user_id);
                $user->balance = $credit-$debit;
                $user->save();


                flash('Payout Request Approved')->success();
            }else{
                
                flash($res->response)->error();
            }
            return back();      
        }
        if($status==2){
            $walletPayout->save();
            flash("Payout Request Rejected")->success();
            return back();
        }
    flash("Something went wrong")->error();
    return back();

    }

    public function redeemGiftCardView(){
        return view('frontend.customer.redeem_gift_card');
    }

    public function redeemGiftCard(Request $request){
        $gift_card = \App\GiftCard::where('gift_code',$request->gift_code)->where('status',0)->first();
        $user_id = Auth::user()->id;
        $wallet_balance = Wallet::where('tr_type','credit')->where('user_id',$user_id)->sum('amount')-Wallet::where('tr_type','debit')->where('user_id',$user_id)->sum('amount');
        if(!is_null($gift_card)){
            // credit gift card amount to user wallet
            try{
                DB::beginTransaction();
                $walletGift = new Wallet;
                $walletGift->user_id = $user_id;
                $walletGift->amount = $gift_card->amount;
                $walletGift->gift_card_id = $gift_card->order_id;
                $walletGift->tr_type = 'credit';
                $walletGift->payment_method = 'gift_card';
                if($walletGift->save()){
                    //update gift card status
                    \App\GiftCard::where('id',$gift_card->id)->update(['status'=>1,'user_id'=>Auth::user()->id]);
                    //Update user balance
                    $wallet_balance+=$gift_card->amount;
                    \App\User::where('id',$user_id)->update(['balance'=>$wallet_balance]);
                }
                DB::commit();
                return $gift_card->amount;

            } catch(\Exception $e){
                info($e);
                return 2;
            }
            
            

        }

        return 0;

    }

    public function loadPayoutRequest(Request $request){
        $payoutId = $request->payout_id;
        $transfer_request = \App\WalletPayout::find($payoutId);
        return view('frontend.peer_partner.load_payout_request',compact('transfer_request'));
    }

    
}
