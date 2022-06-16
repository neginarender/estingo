<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ShortingHub;
use App\Cluster;
use App\AssignOrder;
use DB;
use App\User;
use App\Staff;
use App\Banner;
use App\SortingHubSlider;
use App\SortingHubNews;
use App\DeliveryBoy;
use App\Order;
use App\OrderDetail;
use Hash;
use Auth;
use Carbon\Carbon;

//01-10-2021
use App\PeerSetting;
use App\Product;
use App\PeerSettingNew;
use Illuminate\Support\Facades\Cache;

class SortingHubController extends Controller
{
    public function index()
    {
        if(Auth::user()->user_type == 'admin'){
            $sorting_hubs = ShortingHub::with('cluster')->paginate(10);
        }else{
            $sorting_hubs = ShortingHub::with('cluster')->where('cluster_hub_id', auth()->user()->id)->paginate(10);
        }
        
        return view('sorting_hub.index', compact('sorting_hubs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $getCluster = Cluster::get();
        
        $cluster = \App\Cluster::where('user_id', auth()->user()->id)->first();
        $data['cluster'] = \App\Cluster::where('user_id', auth()->user()->id)->first();
        return view('sorting_hub.create', compact(['data','getCluster','cluster']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        if(User::where('email', $request->email)->first() != null){
            flash(translate('Email already exists!'))->error();
            return back();
        }
        DB::beginTransaction();
        try{
            $user = new User;
            $user->name = $request->sorting_name;
            $user->email = $request->email;
            $user->user_type = 'staff';
            $user->password = Hash::make('123456');
            $user->phone = $request->phone;
            
            if($user->save()){
                $getClusterUserId = Cluster::where('id',$request->cluster_hub)->first('user_id');
             	$sortinghub = new ShortingHub;
                $sortinghub->user_id = $user->id;
                $sortinghub->cluster_hub_id = $getClusterUserId['user_id'];
                $sortinghub->area_pincodes = json_encode(explode(',',$request->pincodes));
                $sortinghub->base_state = $request->state_id;
                //$sortinghub->area_pincodes = json_encode($request->area_ids);
                $maptype = str_replace('_', ' ', $request->mapping_type);
                if($sortinghub->save()){
                    $staff = new Staff;
                    $staff->user_id = $user->id;
                    $staff->role_id = 3;

                    if($staff->save()) {

                        $array['subject'] = $maptype.' Account Regsitration';
                        $array['user_id'] = $user->email;
                        $array['password'] = '123456';
                        $array['account_type'] = $maptype;

                        //Mail::to($request->email)->queue(new MappingMailManager($array));

                        DB::commit();
                        flash(translate($maptype.' has been created successfully'))->success();
                        return redirect()->route('sorthinghub.index');
                    }
                }
            }
        }catch(Exception $e) {
            DB::rollBack();
            flash(translate('Something went wrong'))->error();
            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    { 
        $ShortingHub = ShortingHub::findOrFail(decrypt($id));
        return view('sorting_hub.edit', compact('ShortingHub'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {  
        $ShortingHub = ShortingHub::findOrFail(decrypt($id));
        DB::beginTransaction();
        try{
            $ShortingHub->cluster_hub_id = $request->cluster_hub;
            //$ShortingHub->area_pincodes = json_encode($request->area_ids);
            $ShortingHub->area_pincodes = json_encode(explode(',',$request->pincodes));
            User::where('email',$request->email)->update(array('phone'=>$request->phone,'name'=>$request->sorting_name));
            $ShortingHub->save();

            DB::commit();
            flash(translate('updated successfully'))->success();
            return redirect()->back();
        }catch(Exception $e) {
            DB::rollBack();
            flash(translate('Something went wrong'))->error();
            return redirect()->back();
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ShortingHub = ShortingHub::findOrFail($id);
        Staff::where('user_id', $ShortingHub->user_id)->delete();
        User::destroy($ShortingHub->user_id);
        if(ShortingHub::destroy($id)){
            flash(translate('Shorting hub has been deleted successfully'))->success();
            return redirect()->route('sorthinghub.index');
        }
        else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }

    public function approve_sorting(Request $request)
    {
        $shortingHub = ShortingHub::findOrFail($request->id);
        $shortingHub->status = $request->status;
        if($shortingHub->save()){
            return 1;
        }
        return 0;
    }


    

     public function login($id)
    {
        $ShortingHub = ShortingHub::findOrFail(decrypt($id));

        $user  = $ShortingHub->user;

        auth()->login($user, true);

        return redirect()->route('admin.dashboard');
    }


    public function assignOrder(REQUEST $request){
        $order_id = $request->order_id;
        $delivery_boy_id = $request->delivery_boy_id;

        $checkAssignedOrder = AssignOrder::where(['order_id'=>$order_id])->first();
        if(empty($checkAssignedOrder)){
            $assign_order = new AssignOrder;
            $assign_order->delivery_boy_id = $delivery_boy_id;
            $assign_order->order_id = $order_id;
            $assign_order->created_at = Carbon::now();
            $assign_order->updated_at = Carbon::now();
            if($assign_order->save()){
                $this->send_push_notification($delivery_boy_id,$order_id);
                return 1;
            }else{
                return 0;
            }

        }else{
            $checkAssignedOrder->delivery_boy_id = $delivery_boy_id;
            $checkAssignedOrder->order_id = $order_id;
            $checkAssignedOrder->created_at = Carbon::now();
            $checkAssignedOrder->updated_at = Carbon::now();
            if($checkAssignedOrder->save()){
                $this->send_push_notification($delivery_boy_id,$order_id);
                $msg = "Assign Order has been updated.";
                return $msg;
            }else{
                return 0;
            }
        }
        


    }


    public function sortingHubBanner()
    {
        $banners = SortingHubSlider::where('sorting_hub_id',Auth::user()->id)->get();
        return view('sorting_hub.show_banner', compact('banners'));
    }

    public function createBanner(Request $request)
    {
        
        return view('sliders.create');
    }


    public function storeBanner(Request $request)
    {
        $sorting_hub_id = Auth::user()->id;
        $slider = new SortingHubSlider;
        if(Cache::has('sliders'.$sorting_hub_id)){
            Cache::forget('sliders'.$sorting_hub_id);
          }
        if($request->hasFile('mobile_photos')){
                $slider->mobile_photo = $request->mobile_photos->store('uploads/sliders');
           
        }
        
        if($request->hasFile('photos')){
            foreach ($request->photos as $key => $photo) {
                $slider->link = $request->url;
                $slider->sorting_hub_id = $sorting_hub_id;
                $slider->photo = $photo->store('uploads/sliders');
                $slider->link_type = $request->link_type;
                $slider->save();
            }
            flash(translate('Slider has been inserted successfully'))->success();
        }
        return redirect()->back();
    }


    public function showNews(){
        $news = SortingHubNews::where('sorting_hub_id',Auth::user()->id)->get();
        return view('sorting_hub.show_news',compact('news'));
    }


    public function createNews(){
        $getNews = SortingHubNews::where('sorting_hub_id',Auth::user()->id)->first();
        return view('sorting_hub.create_news',compact('getNews'));
    }


    public function storeNews(REQUEST $request){
        $getNews = SortingHubNews::where('sorting_hub_id',Auth::user()->id)->first();
        $sorting_hub_id = Auth::user()->id;
        if(empty($getNews)){
            if(Cache::has('getnews'.$sorting_hub_id)){
                Cache::forget('getnews'.$sorting_hub_id);
              }
        $news = new SortingHubNews;
        $news->news = $request->news;
        $news->sorting_hub_id = $sorting_hub_id;
        if($news->save()){
            flash(translate('News has been inserted successfully'))->success();
            return redirect()->back();
        }

        }else{
        $getNews->news = $request->news;
        if($getNews->update()){
            flash(translate('News has been Updated successfully'))->success();
            return redirect()->back();
        }

        }
        

        
    }

    public function destroyNews($id)
    {
        if(SortingHubNews::destroy($id)){
            flash(translate('News has been deleted successfully'))->success();
            return redirect()->route('sorthinghub.news');
        }
        else {
            flash(translate('Something went wrong'))->error();
            return back();
        }
    }


    public function send_push_notification($delivery_boy_id,$order_id)
    {
        $user_id = DeliveryBoy::where('id',$delivery_boy_id)->first()->user_id;
        $device_id = User::where('id',$user_id)->first()->device_id;
        $order = Order::find($order_id);
        $delivery_address = json_decode($order->shipping_address);
        $notification = [
           
            'title'=>'New Order Recieved',
            'body'=>$order->code.', '.$delivery_address->name.', '.$delivery_address->email.', '.$delivery_address->address,
            
            ];
            return $this->notify($device_id,$notification);
    }

    public function notify($to,$data){

        $api_key=env('FCM_KEY');
        $url="https://fcm.googleapis.com/fcm/send";
        $fields=json_encode(array('to'=>$to,'notification'=>$data));
    
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();
    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ($fields));
    
        $headers = array();
        $headers[] = 'Authorization: key ='.$api_key;
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    
    //01-10-2021
    public function sortingDiscountList(Request $request){

        // $setting_data = PeerSetting::select(DB::raw('data.*'))
        //     ->from(DB::raw('(SELECT * FROM peer_settings ORDER BY created_at DESC) data'))
        //     ->groupBy('data.product_id')
        //     ->groupBy('data.sorting_hub_id')
        //     ->orderBy('data.created_at','DESC');


        $setting_data = PeerSetting::where('status','1')
            ->groupBy('product_id')
            ->groupBy('sorting_hub_id')
            ->orderBy('created_at','DESC');

                
        if ($request->sorting_hub != NULL && $request->search == NULL){
            $sortinghubID = '["'.$request->sorting_hub.'"]';
            $setting_data  = PeerSetting::where('sorting_hub_id', $sortinghubID)->where('status','1')->groupBy('product_id')->orderBy('id', 'DESC');
        }

        // 04-10-2021
        if ($request->search != null && $request->sorting_hub == NULL){
            $sort_search = $request->search;

            $product = Product::where('name', 'like', '%'.$sort_search.'%')->pluck('id');

            $arr = array();
            foreach($product as $key => $value){
                array_push($arr, $value);
            }

            $product_id = implode('","', $arr);
            $product_id = '"'.$product_id.'"';
            
            $productID = explode(',',$product_id);

            $setting_data  = PeerSetting::where('status','1')->whereIn('product_id', $productID)->groupBy(['product_id','sorting_hub_id'])->orderBy('id', 'DESC');
        }

        if($request->search != NULL && $request->sorting_hub != NULL){
            $sort_search = $request->search;

            $product = Product::where('name', 'like', '%'.$sort_search.'%')->pluck('id');

            $arr = array();
            foreach($product as $key => $value){
                array_push($arr, $value);
            }

            $product_id = implode('","', $arr);
            $product_id = '"'.$product_id.'"';
            
            $productID = explode(',',$product_id);

            $sortinghubID = '["'.$request->sorting_hub.'"]';

            $setting_data  = PeerSetting::where('status','1')->where('sorting_hub_id', $sortinghubID)->whereIn('product_id', $productID)->groupBy(['product_id','sorting_hub_id'])->orderBy('id', 'DESC');
        }

        $setting_data = $setting_data->paginate(15);

        $sortinghub  = DB::table('peer_settings')->distinct()->get(['sorting_hub_id']);

        return view('sorting_hub.sorting_hub_discount_list', compact('setting_data','sortinghub'));
    }


    public function peersettingupdate(){
        $setting_data = DB::table('peer_settings')
        ->selectRaw('max(id) as max_id')
        ->groupBy('product_id')
        ->groupBy('sorting_hub_id')
        ->havingRaw('max(id)')
        ->orderBy('created_at','DESC')
        ->get();

        $arr = array();

        foreach($setting_data as $data){
            array_push($arr, $data->max_id);
        }

        $count = 0;

        foreach($arr as $value){
            $update_setting = DB::statement("UPDATE peer_settings SET status = 1 WHERE id = $value");
            $count++;
        }

    }

    public function sortingHubPurchaseReport(Request $request){

        $from_date = (isset($request->start_date)) ? $request->start_date : date("Y-m-d");
        $to_date   = (isset($request->end_date)) ? $request->end_date : date('Y-m-d');

        $start_time = (isset($request->start_time)) ? $request->start_time: "00:00";
        $end_time = (isset($request->end_time)) ? $request->end_time : "23:59"; 

        $sortId = (isset( $request->sorting_hub_id)) ?  $request->sorting_hub_id : Auth::user()->id;
        //get All Pincodes
        $sortingHub = \App\ShortingHub::where('user_id',$sortId)->first();
        $pincodes = [];
        if(!is_null($sortingHub)){
            $pincodes = json_decode($sortingHub->area_pincodes);
        }
        
        // get orders of these pincodes

        $orders = Order::whereIn('shipping_pin_code',$pincodes)
        ->where('order_status','!=','delivered')
        ->where('order_status','!=','cancel')
        ->where('log','!=',1)
        ->where('dofo_status',0)
        ->whereBetween(DB::raw('orders.created_at'), array($from_date." ". $start_time." ",$to_date." ".$end_time." "))
        ->pluck('id');
        
        $orderDetails = OrderDetail::whereIn('order_id',$orders)
        ->where('delivery_status','!=','delivered')
        ->where('delivery_status','!=','on_delivery')
        ->where('delivery_status','!=','refund')
        ->where('delivery_status','!=','return')
        ->where('delivery_status','!=','cancel')
        ->selectRaw('GROUP_CONCAT(order_id) as orderIds,SUM(quantity) as total_quantity, product_id, quantity, price, order_details.created_at, order_details.order_id,variation')
        ->groupBy('product_id')
        ->orderBy('order_details.created_at', 'DESC')
        ->get();
        $productIds = [];
        foreach($orderDetails as $orderDetail){
            $productIds[] = $orderDetail->product_id;
        }
        $all_products = $orderDetails;
        return view('sorting_hub.sortinghub_order_report', compact('all_products', 'sortId','from_date', 'to_date','start_time', 'end_time'));
   
    }

    public function generateFinalProduct(){
        //get sortinghubid 
        $shortId['sorting_hub_id'] = 1839;
        $products = \App\Product::leftJoin('mapping_product','products.id','=','mapping_product.product_id')
        ->leftJoin('product_stocks','products.id','=','product_stocks.product_id')
        ->leftJoin('mapped_categories','mapped_categories.category_id','=','mapping_product.category_id')
                            ->where('products.published', 1)
                            ->where('products.search_status', 1)
                            ->where('mapping_product.sorting_hub_id', $shortId['sorting_hub_id'])
                            ->where('mapping_product.published',1)
                            ->where('mapped_categories.status',1)
                            ->where('mapped_categories.sorting_hub_id',$shortId['sorting_hub_id'])
                            ->select('products.*','mapping_product.qty','mapping_product.purchased_price','mapping_product.selling_price','mapping_product.flash_deal','mapping_product.top_product','mapping_product.published as spublished','product_stocks.price','product_stocks.variant')
                            ->orderBy('mapping_product.top_product','DESC')
                            ->get();
        
        foreach($products as $key => $data){
            $peer_discount = \App\PeerSetting::where('product_id', '"'.$data->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $shortId['sorting_hub_id']. '"]\')')->latest('id')->first(); 
            $discount_type = "percent";
            $discount_percentage = 0;
            $customer_off = 0;
            if(!is_null($peer_discount)){
                $discount_type = substr($peer_discount->discount,1,-1);
                $discount_percentage = substr($peer_discount->customer_discount,1,-1);
                $customer_off = $peer_discount->customer_off;
            }
            $check = \App\FinalProduct::where(['product_id'=>$data->id,'sorting_hub_id'=>$shortId['sorting_hub_id']])->first();
            if(is_null($check)){
                $final = new \App\FinalProduct;
                $final->name  =$data->name;
                $final->product_id  =$data->id;
                $final->category_id  =$data->category_id;
                $final->subcategory_id  =$data->subcategory_id;
                $final->subsubcategory_id  =$data->subsubcategory_id;
                $final->stock_price  =(double)price($data->id,$shortId);
                $final->base_price  =round(peer_discounted_newbase_price($data->id,$shortId),2);
                $final->variant  =$data->variant;
                $final->tags  =$data->tags;
                $final->json_tags  =$data->json_tags;
                $final->quantity  =$data->qty;
                $final->max_purchase_qty  =$data->max_purchase_qty;
                $final->discount_type  =$discount_type;
                $final->discount_percentage  =$discount_percentage;
                $final->customer_off  =$customer_off;
                $final->thumbnail_image  =$data->thumbnail_img;
                $final->photos  =$data->photos;
                $final->sorting_hub_id  =$shortId['sorting_hub_id'];
                $final->flash_deal  =$data->flash_deal;
                $final->top_product  =$data->top_product;
                $final->published  =$data->spublished;
                $final->choice_options  =$data->choice_options;
                $final->unit  =$data->unit;
                $final->rating  =$data->rating;
                $final->sales  =$data->num_of_sale;
                $final->links   = json_encode([
                    'details'   => route('products.show', $data->id),
                    'reviews'   => route('api.reviews.index', $data->id),
                    'related'   => route('products.related', $data->id)
                ]);
                $final->save();
            }
            
            

            // \App\FinalProduct::updateOrCreate(
            //     [
            //        'product_id'   => $data->id,
            //        'sorting_hub_id'=>$shortId['sorting_hub_id']
            //     ],
            //     ['name'=>$data->name, 
            //     'product_id'=>$data->id, 
            //     'category_id'=>$data->category_id,
            //     'subcategory_id'=>$data->subcategory_id,
            //     'subsubcategory_id'=>$data->subsubcategory_id,
            //     'stock_price'=>(double)price($data->id,$shortId),
            //     'base_price'=>round(peer_discounted_newbase_price($data->id,$shortId),2),
            //     'variant'=>$data->variant,
            //     'tags'=>$data->tags,
            //     'json_tags'=>$data->json_tags,
            //     'quantity'=>$data->qty,
            //     'max_purchase_qty'=>$data->max_purchase_qty,
            //     'discount_type'=>$discount_type,
            //     'discount_percentage'=>$discount_percentage,
            //     'customer_off'=>$customer_off,
            //     'thumbnail_image'=>$data->thumbnail_img,
            //     'photos'=>$data->photos,
            //     'sorting_hub_id'=>$shortId['sorting_hub_id'],
            //     'flash_deal'=>$data->flash_deal,
            //     'top_product'=>$data->top_product,
            //     'published'=>$data->spublished,
            //     'choice_options'=>$data->choice_options,
            //     'unit'=>$data->unit,
            //     'rating'=>$data->rating,
            //     'sales'=>$data->num_of_sale,
            //     'links' => json_encode([
            //         'details' => route('products.show', $data->id),
            //         'reviews' => route('api.reviews.index', $data->id),
            //         'related' => route('products.related', $data->id)
            //     ])
            //     ]
            // );
        }

        echo "Done Final Product";
        
    }

}