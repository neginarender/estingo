<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\ProductStock;
use App\Seller;
use App\User;
Use App\ShortingHub;
use DB;
use App\Order;
use App\PeerPartner;
use App\OrderReferalCommision;
use Excel;
use Illuminate\Support\Str;
use App\SkuDataExport;
use App\SaleDataExport;

class ReportController extends Controller
{
    public function stock_report(Request $request)
    {
        if($request->has('category_id') || $request->has('sorting_id')){
            // $products = Product::where('category_id', $request->category_id)->get();
            $sortinghub = $request->sorting_id;
            $categoryid = $request->category_id;
            $products = DB::table('products')
                    ->orderBy('mapping_product.qty', 'asc')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                    ->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
                    ->where('mapping_product.sorting_hub_id', $request->sorting_id)
                    ->where('products.category_id', $request->category_id)
                    ->select('products.name', 'product_stocks.sku', 'mapping_product.qty')
                    ->get();
        }
        else{
            // $products = Product::all();
            $sorting_hub = \App\ShortingHub::where('status', 1)->first();
            $category = \App\Category::first();

            $sortinghub = $sorting_hub->user_id;
            $categoryid = $category->id;
            $products = DB::table('products')
                    ->orderBy('mapping_product.qty', 'ASC')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                    ->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
                    ->where('mapping_product.sorting_hub_id', $sorting_hub->user_id)
                    ->where('products.category_id', $category->id)
                    ->select('products.name', 'product_stocks.sku', 'mapping_product.qty')
                    ->get();
        }
        return view('reports.stock_report', compact('products','sortinghub', 'categoryid'));
    }

    public function in_house_sale_report(Request $request)
    {
        if($request->has('category_id') || $request->has('sorting_id')){
			$sortinghub = $request->sorting_id;
            $categoryid = $request->category_id;
            //$products = Product::where('category_id', $request->category_id)->orderBy('num_of_sale', 'desc')->get();
	
            $products = DB::table('products')
                    ->orderBy('products.num_of_sale', 'desc')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
					->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
                    ->where('mapping_product.sorting_hub_id', $request->sorting_id)
                    ->where('products.category_id', $request->category_id)
                    ->select('products.name', 'product_stocks.sku', 'products.num_of_sale')
                    ->get();
        }
        else{
			$sorting_hub = \App\ShortingHub::where('status', 1)->first();
            $category = \App\Category::first();
			$sortinghub = $sorting_hub->user_id;
            $categoryid = $category->id;
            //$products = Product::orderBy('num_of_sale', 'desc')->where('products.category_id', $category->id)->get();
			$products = DB::table('products')
                    ->orderBy('products.num_of_sale', 'desc')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
					->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
					->where('mapping_product.sorting_hub_id', $sorting_hub->user_id)
                    ->where('products.category_id', $categoryid)
                    ->select('products.name', 'product_stocks.sku', 'products.num_of_sale')
                    ->get();
        }

        return view('reports.in_house_sale_report', compact('products','sortinghub','categoryid'));
    }

    public function seller_report(Request $request)
    {
        if($request->has('verification_status')){
            $sellers = Seller::where('verification_status', $request->verification_status)->get();
        }
        else{
            $sellers = Seller::all();
        }
        return view('reports.seller_report', compact('sellers'));
    }

    public function seller_sale_report(Request $request)
    {
        if($request->has('verification_status')){
            $sellers = Seller::where('verification_status', $request->verification_status)->get();
        }
        else{
            $sellers = Seller::all();
        }
        return view('reports.seller_sale_report', compact('sellers'));
    }

    public function wish_report(Request $request)
    {
        if($request->has('category_id')){
            $products = Product::where('category_id', $request->category_id)->get();
        }
        else{
            $products = Product::all();
        }
        return view('reports.wish_report', compact('products'));
    }
	
	//30-10-2021
	public function sku_data_report(Request $request){
			$sort_search = null; 
            if($request->all() != NULL){
                $sorting_hub_id = $request->sorting_id;
                if($sorting_hub_id!=''){
                    $sorting_hub_id = $request->sorting_id;
                }else{
                    $sortinghubids = ShortingHub::first();
                    $sorting_hub_id = $sortinghubids->user_id;
                }

                $products = DB::table('products')
                    ->orderBy('product_stocks.sku', 'ASC')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                    ->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->join('sub_categories', 'products.subcategory_id', '=', 'sub_categories.id')
                    ->join('sub_sub_categories', 'products.subsubcategory_id', '=', 'sub_sub_categories.id')
                     ->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
                    ->where('mapping_product.sorting_hub_id', $sorting_hub_id) 
                    ->select('products.id','products.name', 'product_stocks.sku','brands.name as brand_name','categories.name as category_name','sub_categories.name as subcategory_name','sub_sub_categories.name as subsubcategory_name','products.unit_price','products.purchase_price','products.unit','products.hsn_code','products.tax','products.tax_type','products.published','products.choice_options','products.attributes','product_stocks.price as stock_price','mapping_product.sorting_hub_id','mapping_product.selling_price','mapping_product.created_at','mapping_product.purchased_price');

            }else{

                $sorting_hub_id = $request->sorting_id;
                if($sorting_hub_id!=''){
                    $sorting_hub_id = $request->sorting_id;
                }else{
                    $sortinghubids = ShortingHub::first();
                    $sorting_hub_id = $sortinghubids->user_id;
                }
                $products = DB::table('products')
                    ->orderBy('product_stocks.sku', 'ASC')
                    ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                    ->join('brands', 'products.brand_id', '=', 'brands.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->join('sub_categories', 'products.subcategory_id', '=', 'sub_categories.id')
                    ->join('sub_sub_categories', 'products.subsubcategory_id', '=', 'sub_sub_categories.id')
                     ->join('mapping_product', 'products.id', '=', 'mapping_product.product_id')
                     ->where('mapping_product.sorting_hub_id', $sorting_hub_id)
                    ->select('products.id','products.name', 'product_stocks.sku','brands.name as brand_name','categories.name as category_name','sub_categories.name as subcategory_name','sub_sub_categories.name as subsubcategory_name','products.unit_price','products.purchase_price','products.unit','products.hsn_code','products.tax','products.tax_type','products.published','products.choice_options','products.attributes','product_stocks.price as stock_price','mapping_product.sorting_hub_id','mapping_product.selling_price','mapping_product.created_at','mapping_product.purchased_price');


            }
            if ($request->has('search')){
                $sort_search = $request->search;
                $products = $products->where('product_stocks.sku', 'like', '%'.$sort_search.'%');
            }
                $products = $products->paginate(20);
        if(isset($sorting_hub_id)) {
            return view('reports.sku_data', compact('products','sorting_hub_id', 'sort_search'));
        } else{
            return view('reports.sku_data', compact('products', 'sort_search'));
        }    
		
	} 

	public function sale_data_report(Request $request)
    {
        $global = DB::table('global_switch')->first(); 
        $sort_search = null;    
        $pay_status = "paid";
        if($request->all() != NULL){
            // dd('ok');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $from_date = $start_date;
            $to_date   = $end_date;

            if($from_date!=''){
                $from_date = $from_date;
            }else{
                $from_date = date('Y-m-d', strtotime('-7 days'));
            }

            if($to_date!=''){
                $to_date = $to_date;
            }else{
                $to_date   = date('Y-m-d');
            }
        
            $sorting_hub_id = $request->sorting_id;
            if($sorting_hub_id!=''){
                $sorting_hub_id = $request->sorting_id;
            }else{
                $sortinghubids = ShortingHub::first();
                $sorting_hub_id = $sortinghubids->user_id;
            }
            // echo $sorting_hub_id; die;

            $area_pincodes = ShortingHub::where('user_id',$sorting_hub_id)->first()->area_pincodes;
            
            $area_pincodes = explode('","', $area_pincodes);  
            $area_pincodes = str_replace('["','',$area_pincodes);
            $area_pincodes = str_replace('"]','',$area_pincodes);

            // dd($area_pincodes);
            // DB::enableQueryLog();
                $orders = DB::table('orders')->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))
                ->orderBy('orders.created_at', 'DESC')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->whereIn('orders.shipping_pin_code', $area_pincodes)
                ->where('orders.payment_status', $pay_status);
                // dd(DB::getQueryLog());
        }else{
            // dd('dd');
            $start_date = '';
            $end_date = '';
            $from_date = date('Y-m-d', strtotime('-7 days'));
            $to_date   = date('Y-m-d');

            $sortinghubids = ShortingHub::first();
            $sorting_hub_id = $sortinghubids->user_id;

            $area_pincodes = ShortingHub::where('user_id',$sorting_hub_id)->first()->area_pincodes;
            
            $area_pincodes = explode('","', $area_pincodes);  
            $area_pincodes = str_replace('["','',$area_pincodes);
            $area_pincodes = str_replace('"]','',$area_pincodes);
              
            // DB::enableQueryLog();
            $orders = DB::table('orders')->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))
                ->orderBy('orders.created_at', 'DESC')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->whereIn('orders.shipping_pin_code', $area_pincodes)
                ->where('orders.payment_status', $pay_status);
                 // dd(DB::getQueryLog());
        }

        if($global->access_switch == 0){
            $orders->where(['orders.dofo_status'=>0]);
        }
        $orders = $orders->where('log', 0)->select('orders.*','order_details.*');

        if ($request->has('search')){
            $sort_search = $request->search;
            $orders = $orders->where('product_stocks.sku', 'like', '%'.$sort_search.'%');
        }

        $orders = $orders->paginate(50);
        if(isset($sorting_hub_id)) {
            return view('reports.sale_data', compact('orders','sorting_hub_id', 'start_date', 'end_date', 'sort_search'));
        } else{
            // return view('reports.sale_data', compact('orders', 'sort_search'));
            return view('reports.sale_data', compact('orders','sorting_hub_id', 'start_date', 'end_date', 'sort_search'));
        } 
	} 
    public function peerpartner_data_report(Request $request){

        $sort_search = null;
        $approved = null;
        $peer_partner = PeerPartner::orderBy('created_at', 'desc')->where('peer_type', 'master');
        if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'partner')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%');
            })->pluck('id')->toArray();
            $peer_partner = $peer_partner->where(function($peer_partner) use ($user_ids){
                $peer_partner->whereIn('user_id', $user_ids);
            });
        }
        if ($request->approved_status != null) {
            $approved = $request->approved_status;
            $peer_partner = $peer_partner->where('verification_status', $approved);
        }
        $peer_partner = $peer_partner->paginate(10);


        return view('reports.peerpartners_data', compact('peer_partner','approved'));
    }

    public function show_masterpeer_commission($id)
    {

        $id = decrypt($id);        

        $start_date = '';
        $end_date = '';
        // $from_date = date('Y-m-d');
        $from_date = date('Y-m-d', strtotime('-7 days'));
        $to_date   = date('Y-m-d');

        $peer_refferal_code = PeerPartner::where('id', $id)->select('code','user_id')->first();
        $master_code = $peer_refferal_code->code;

        $peercodes = array();
        $subpeerlist = PeerPartner::where('parent', $id)->select('code')->where('code', '!=', null)->get();
        foreach($subpeerlist as $key => $peerlist){
            array_push($peercodes, $peerlist->code);
        }
        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->whereIn('refral_code', $peercodes)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount, refral_code, created_at, id, partner_id')->groupBy('refral_code')->get();         

        return view('reports.show_masterpeer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_code'));
    } 

    public function show_masterpeer_commission_by_date(Request $request, $id)
    { 
        $id = decrypt($id);
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;

        $peer_refferal_code = PeerPartner::where('id', $id)->select('code','user_id')->first();
        $master_code = $peer_refferal_code->code;

        $peercodes = array();
        $subpeerlist = PeerPartner::where('parent', $id)->select('code')->where('code', '!=', null)->get();
        foreach($subpeerlist as $key => $peerlist){
            array_push($peercodes, $peerlist->code);
        }
        $all_orders = OrderReferalCommision::whereBetween(DB::raw('DATE(created_at)'), array($from_date, $to_date))->where('wallet_status', 1)->whereIn('refral_code', $peercodes)->selectRaw('SUM(order_amount) as total_orderamount, SUM(referal_commision_discount) as total_refferaldiscount, SUM(master_discount) as total_masterdiscount, refral_code, created_at, id, partner_id')->groupBy('refral_code')->get(); 
             
        return view('reports.show_masterpeer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_code'));
    } 
    //peer by peer
    public function show_peers_commission($id)
    {

        

        $id = decrypt($id);
        $start_date = '';
        $end_date = '';
        // $from_date = date('Y-m-d');
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

        return view('reports.show_peer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function show_peers_commission_by_date(Request $request, $id)
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
             
        return view('reports.show_peer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 
    //customer wise peer
    public function show_customerpeer_commission($id)
    {
        $id = decrypt($id);
        $pieces = explode("_", $id);
        $customer_id = $pieces[0];
        $ids = $pieces[1];
        
        $start_date = '';
        $end_date = '';
        // $from_date = date('Y-m-d');
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

        return view('reports.show_customerwise_peer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function show_customerpeer_commission_by_date(Request $request, $id)
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
             
        return view('reports.show_customerwise_peer_commission', compact('all_orders', 'distributorids', 'id', 'start_date', 'end_date', 'master_name', 'master_email', 'master_code', 'peer_name', 'peer_email','all_total'));
    } 

    public function sku_data_export(Request $request){

        $sortinghubid = $request->sortinghubid;
        ini_set('max_execution_time', -1);
        return Excel::download(new SkuDataExport($sortinghubid), 'skudatareport.xlsx');
    }

    public function sale_data_export(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;
        $sortinghubid = $request->sortinghubid;
        ini_set('max_execution_time', -1);
        return Excel::download(new SaleDataExport($sortinghubid,$from_date,$to_date), 'saledatareport.xlsx');
    }

    public function invoice_data_report(Request $request)
    {
    
        $pay_status = "paid";
        $sort_search = null;    
        if($request->all() != NULL){
            //dd('ok');
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $from_date = $start_date;
            $to_date   = $end_date;

            if($from_date!=''){
                $from_date = $from_date;
            }else{
                $from_date = date('Y-m-d', strtotime('-7 days'));
            }

            if($to_date!=''){
                $to_date = $to_date;
            }else{
                $to_date   = date('Y-m-d');
            }
        
            $sorting_hub_id = $request->sorting_id;
            if($sorting_hub_id!=''){
                $sorting_hub_id = $request->sorting_id;
            }else{
                $sortinghubids = ShortingHub::first();
                $sorting_hub_id = $sortinghubids->user_id;
            }
            // echo $sorting_hub_id; die;

            $area_pincodes = ShortingHub::where('user_id',$sorting_hub_id)->first()->area_pincodes;
            
            $area_pincodes = explode('","', $area_pincodes);  
            $area_pincodes = str_replace('["','',$area_pincodes);
            $area_pincodes = str_replace('"]','',$area_pincodes);

            // dd($area_pincodes);
            $orders = DB::table('orders')->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))
                ->orderBy('orders.created_at', 'DESC')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->where('orders.sorting_hub_id', $sorting_hub_id) 
                //->whereIn('orders.shipping_pin_code', $area_pincodes) 
                ->where('log', 0)
                ->where('orders.payment_status', $pay_status)
                ->select('orders.*','order_details.*');
               
        }else{
            // dd('dd');
            $start_date = '';
            $end_date = '';
            $from_date = date('Y-m-d', strtotime('-7 days'));
            $to_date   = date('Y-m-d');
            $area_pincodes = [];
            $sorting_hub_id = auth()->user()->id;
            $sortinghubids = ShortingHub::where('status', 1)->where('user_id',$sorting_hub_id)->first();
            if(!is_null($sortinghubids)){
                $area_pincodes = $sortinghubids->area_pincodes;
                $area_pincodes = explode('","', $area_pincodes);  
                $area_pincodes = str_replace('["','',$area_pincodes);
                $area_pincodes = str_replace('"]','',$area_pincodes);
            }

           $orders = DB::table('orders')->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))
                ->orderBy('orders.created_at', 'DESC')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                //->whereIn('orders.shipping_pin_code', $area_pincodes) 
                ->where('orders.sorting_hub_id', $sorting_hub_id) 
                ->where('log', 0)
                ->where('orders.payment_status', $pay_status)
                ->select('orders.*','order_details.*');
               
        }

        $orders = $orders->paginate(20);
        return view('reports.invoice_report', compact('orders','sorting_hub_id', 'start_date', 'end_date'));
    }

    public function  generateReport(REQUEST $request){
        $start_date = date('Y-m-d',strtotime($request->start_date));
        $end_date = date('Y-m-d',strtotime($request->end_date));
        $order = Order::whereBetween('created_at', [$start_date, $end_date])->where('dofo_status',0)->select('id','code','date','created_at','shipping_address','shipping_pin_code','grand_total','referal_discount','payment_type','payment_status','dofo_status')->chunk(100,function($order){
            foreach($order as $key => $value){
                dd($value);
            }
        });

    }
}
