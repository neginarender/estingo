<?php
namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\HttpService;
use Cookie;
use Session;
use Carbon\Carbon;
use App\User;
use DB;
class FrontEndController extends Controller
{
    use AuthenticatesUsers;
    protected $sortinghubid='';
    protected $peercode = '';
    protected $device = "";
    protected $client;
    protected $user_id=0;
    protected $base_url = "http://www.rozana.in/api/v5/";
    public function __construct(){
        if(Cookie::has('sid')){
            $this->sortinghubid=decrypt(Cookie::get('sid'));
        }
        if(Cookie::has('peer')){
            $this->peercode = Cookie::get('peer');
        }
        if(!Cookie::has('sessionID')){
            setcookie('sessionID',encrypt(uniqid()),time()+60*60*24*30,'/');
        }
        if(Cookie::has('sessionID')){
            $this->device = decrypt(Cookie::get('sessionID'));
        }
        if(Cookie::has('auth')){
            $this->user_id = decrypt(Cookie::get('auth'));
        }
        $this->client = new HttpService($this->base_url);
       
    }
    public function index(){

        $self = 1;
        $body = [
        'headers'=>[
            'sortinghubid'=>$this->sortinghubid,
            'device'=>$this->device,
            'userid'=>'132',
            'PEER'=>$this->peercode
        ]
    ];


     //dd($body);

    $response = $this->client->apiRequest('GET','homepage',$body);
    $result = $response->getData()->response;

    $categories = $result->categories->data;


   
    $banners = $result->banner->data;
    $sliders = $result->slider->data;
    //$best_sellers = $result->best_seller->data;
    $body['json'] = ['self'=>$self];
    $allcategories = $this->client->apiRequest('POST','homecategory',$body);
    //dd($body);
    $allcategories = $allcategories->getData()->response->data;
    // empty cart
    return view('frontend_new.index',compact('categories','banners','sliders','allcategories'));

    }



    public function purchase_history()
    {
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','order/history/'.$user_id,$body);
        $orders = $response->getData()->response;

        $orderslist = $orders->data;

        return view('frontend_new.purchase_history',compact('orderslist'));
    }

    public function purchaseorderdetail($id)
    {
        
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = ['orderId'=>$id];

        $response = $this->client->apiRequest('POST','order/history/detail',$body);
        $orderdetail = $response->getData()->response;

        

         // $orderslist = $orderdetail->data;
          // dd($orderdetail);

        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','order/history/'.$user_id,$body);
        $orders = $response->getData()->response;

        $orderslist = $orders->data;
               return view('frontend_new.purchase_orderdetail',compact('orderslist','orderdetail'));

    }

    public function getSubcategories(){
        
    }

    public function getPincodes(Request $request){
        $city_id = $request->city_id;
        $body['headers'] = ['Content-Type' => 'application/json'];
        $body['json'] = array(
          'city_id'=>$city_id
        );
        $response = $this->client->apiRequest('POST','city_pincode',$body);
        $pincodes = $response->getData()->response->data;
        return $pincodes;
    }

    public function setSortingHubId(Request $request){
        $body['headers'] = ['Content-Type' => 'application/json','pincode'=>$request->pincode];
        $response = $this->client->apiRequest('GET','getsortingid',$body);
        $sortinghubid = $response->getData()->response->sorting_hub_id;
       
        setcookie('sid',encrypt($sortinghubid),time()+60*60*24*30,'/');
        setcookie('pincode',$request->pincode,time()+60*60*24*30,'/');
        setcookie('city_name',$request->city_name,time()+60*60*24*30,'/');
        if(Cookie::has('sid')){
            return 1;
        }
        return 0;

    }

    public function addToCart(Request $request){
        $self = 1;
        $body['headers'] = ['Content-Type' => 'application/json','peer'=>$this->peercode,'device'=>$this->device,'sortinghubid'=>$this->sortinghubid];
        $body['json'] = ['user_id'=>'','id'=>$request->id,'variant'=>$request->variant,'self'=>$self];
        $response = $this->client->apiRequest('POST','carts/add',$body);
        return response()->json($response->getData()->response);

    }

    public function updateCart(Request $request){
        info("update".json_encode($request->all()));
        $body['headers'] = ['Content-Type' => 'application/json','peer'=>$this->peercode,'device'=>$this->device,'sortinghubid'=>$this->sortinghubid];
        $body['json'] = ["id"=>$request->cart_id,"device_id"=>$this->device,"quantity"=>$request->quantity];
        $response = $this->client->apiRequest('POST','carts/change-quantity',$body);
        return response()->json($response->getData()->response);
    }

    public function removeFromCart(Request $request){
        info("remove".json_encode($request->all()));
        $body['headers'] = [];
        $body['json'] = [];
        $response = $this->client->apiRequest('GET','carts/delete/'.$request->cart_id,$body);
        return response()->json($response->getData()->response);
    }

    public function applyPeerDiscount(Request $request){
        $body['headers'] = ['sortinghubid'=>$this->sortinghubid,'peer'=>$request->peercode];
        $body['json'] = ['peercode'=>$request->peercode,'user_id'=>$this->user_id,'device_id'=>$this->device];
        $endpoint = 'products/peer_discount';

        if(empty($request->peercode)){
            $endpoint = "products/remove_peercode";
        }

        if($request->cart>0){
            $endpoint = 'carts/apply_peer_on_checkout';
        }

        //dd($endpoint);
        $response = $this->client->apiRequest('POST',$endpoint,$body);
        
        if($response->getData()->response->status==true){
            setcookie('peer',$request->peercode,time()+60*60*24*30,'/');
            return 1;
        }
        if(Cookie::has('peer')){
            Cookie::queue(Cookie::forget('peer'));
        }
        
        
        return 0;
    }


    public function getCategoryElements(Request $request){
        $response  = $this->client->apiRequest('GET','sub-categories/'.$request->id,[]);
        $subcategories = $response->getData()->response->data;
        return view('frontend_new.partials.category_elements',compact('subcategories'));

    }

    public function productDetails($id){
        $id = decrypt($id);
        $self = 1;
        $body['headers'] = ['sortinghubid'=>$this->sortinghubid,'peer'=>$this->peercode,'self'=>$self];
        $response = $this->client->apiRequest('GET','products/'.$id,$body);
        //dd($response->getData()->response);
        $product = $response->getData()->response->data[0];
        
        $related = $this->client->apiRequest('GET','products/related/'.$id,$body);
        $related = $related->getData()->response->data;

        $review = $this->client->apiRequest('GET','reviews/product/'.$id,$body);
        $review = $review->getData()->response->data;

          if(session()->has('access_token')){
            
        

        $user_id = decrypt(Cookie::get('auth'));


        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];
        return view('frontend_new.product_details',compact('product','related','review','user'));

        }else{

            return view('frontend_new.product_details',compact('product','related','review'));

        }

         // dd($product);


    }

    public function categoryList(){
        $body['headers'] = ['sortinghubid'=>$this->sortinghubid];
        $response = $this->client->apiRequest('GET','categories',$body);
        //dd($response->getData()->response);
        $categories = $response->getData()->response->data;
        return view('frontend_new.partials.category_list',compact('categories'));
    }

    public function mappedCities(){
        $mapped_cities = $this->client->apiRequest('GET','mapped_cities',[]);
        $mapped_cities = $mapped_cities->getData()->response->data;
        return view('frontend_new.partials.mapped_cities_list',compact('mapped_cities'));
    }

    public function categoryProducts($id,$type){


        $self = 1;
        $category_id = $type=='category' ? decrypt($id) : null;
        $subcategory_id = $type=='subcategory' ? decrypt($id) : null;
        $subsubcategory_id = $type=='subsubcategory' ? decrypt($id) : null;
        //dd(decrypt($id));
        $body['headers'] = ["sortinghubid"=>$this->sortinghubid,'peer'=>$this->peercode,'device'=>$this->device,"userid"=>""];
        $body['json'] = [
            
            "category"=>$category_id,
            "attribute"=>[
                "values"=>[
                    // "1.5 kg",
                    // "500 gm"
                ],
            "attribute_id"=>[
                // 3
            ]
            ],
            "sort_by"=>"",
            "subcategory_id"=>$subcategory_id,
            "page"=>request('page'),
            "key"=>"",
            "min_price"=>"",
            "max_price"=>"",
            "subsubcategory_id"=>$subsubcategory_id,
            "self"=>$self
        ];
        //dd($body);
    $response = $this->client->apiRequest('POST','products/sorting/filter',$body);
    $products = $response->getData()->response->data;
    $meta = $response->getData()->response->meta;
    $links = $response->getData()->response->links;


    if ($type=='category') {
        $body['json']=['category_id'=>$category_id];
    }
    elseif($type=='subcategory') {
        $body['json']=['subcategory_id'=>$subcategory_id];
    }
    else {
        $body['json']=['subsubcategory_id'=>$subsubcategory_id];
    }
    
    $category =$this->client->apiRequest('POST','getproductattribute',$body);

    $getproductattribute =$category->getData()->response;
    
    $range = $getproductattribute->pricerange;
    // print_r($range); exit;
    $pricerange = $range['0'];
    $filterbyvolume = $getproductattribute->data;

    
    // $filterbyvolume = $data['0'];
    // $filterbyvolume = json_decode(json_encode($data),true);
    // $filterbyvolume = json_decode($data);
      // dd($data);

    return view('frontend_new.product_listing',compact('products','pricerange','filterbyvolume','links','meta','id','type'));

    }

    public function cartItems(Request $request){
        $user_id = 0;
        $body['headers'] = ["sortinghubid"=>$this->sortinghubid,'peer'=>$this->peercode,'device'=>$this->device,"userid"=>$user_id];
        $response = $this->client->apiRequest('GET','carts/'.$user_id,$body);
        $items = $response->getData()->response->data;
        $no_of_items = 0;
        foreach($items as $key=> $item){
            $no_of_items+=$item->quantity;
        }
        if($request->type=='refresh'){
            return response()->json([
                'success'=>true,
                'no_of_items'=>$no_of_items
            ]);
        }
        return view('frontend_new.partials.nav_cart',compact('items'));
    }

    public function cartDetails(){
        $user_id = 0;
        $body['headers'] = ["sortinghubid"=>$this->sortinghubid,'peer'=>$this->peercode,'device'=>$this->device,"userid"=>$user_id];
        $response = $this->client->apiRequest('GET','carts/'.$user_id,$body);
        $items = $response->getData()->response->data;
        $is_continue = $response->getData()->response->is_continue;
        return view('frontend_new.cart_details',compact('items','is_continue'));
    }

    public function cartSummary(){
        $user_id = 0;
        $body['headers'] = ["sortinghubid"=>$this->sortinghubid,'peer'=>$this->peercode,'device'=>$this->device,"userid"=>$user_id];
        $response = $this->client->apiRequest('GET','carts/'.$user_id,$body);
        $items = $response->getData()->response->data;
        return view('frontend_new.partials.cart_summary',compact('items'));
    }

    public function login($next){
        if(Auth::check()){
            return redirect()->route('home');
        }
         return view('frontend_new.user_login',compact('next'));
    }

    public function userlogin($next){

        if(Auth::check()){
            return redirect()->route('home');
        }

        return view('frontend_new.login',compact('next'));
    }

    public function loginuser(Request $request){


         $validate = $request->validate([


            'email' => 'required',
            'password' => 'required'
        ]);

       if($validate){

        $email = $request->email;
        $password = $request->password;
        $next = $request->next;
        $resp = $request->all();
        $body['json'] = ['email'=>$email,'password'=>$password];
        $response = $this->client->apiRequest('POST','auth/login',$body);
        $resp = $response->getData()->response;
        // dd($resp);
        if($resp->success){
            setcookie('logged',encrypt(true),time()+60*60*24*30,'/');
            setcookie('auth',encrypt($resp->user->id),time()+60*60*24*30,'/');
            session()->put('access_token',$resp->access_token);
            session()->put('user_id',$resp->user->id);
            session()->put('user',json_encode($resp->user));
            flash('Login successfully')->success();
            if($next=="checkout"){
                return redirect()->route('phoneapi.shipping_info');
            }
            return redirect()->route('new.home');
        }
        // flash('Email and Password Not Match')->error();
        // return back()->session()->flash('message', 'Email and Password Not Match');
            return redirect()->back()->with('message', 'Email and Password Not Match');

    }

    }

    public function resetpassword(){
        if(Auth::check()){
            return redirect()->route('home');
        }
    
        return view('frontend_new.reset_password');
    }

    public function resetpasswordemail(Request $request){


        $req = $request->email;

        $validate = $request->validate([
            'email' => 'required'
        ]);

       if($validate)
       {
         
         $email = $request->email;
       
         $body['json'] = ['email'=>$email];
         $response = $this->client->apiRequest('POST','auth/password/create',$body);
         $resp= $response->getData()->response;

        

         if($resp->success){

             $request->session()->put('email',$request->email);
             $request->session()->flash('message','Please check your email. We have e-mailed your password reset otp.'); 


           return view('frontend_new.emailotp');

         }else{

           return redirect()->back()->with('messagee', 'We can not find a user with that e-mail address');

         }
        }else{
             return redirect()->back()->with('messagee', 'We can not find a user with that e-mail address');

        }
    }
    public function verifyemailotp(Request $request){

        $req=$request->all();
       
        $body['json'] = ['email'=>$request->email,'otp'=>$request->otp];
        $response = $this->client->apiRequest('POST','auth/password/verifyotp',$body);
        $resp = $response->getData()->response;

        
      
        if($resp->status==true){

            $user_id = $resp->user_id;
            $request->session()->flash('message','OTP verified successfully. Create New Password'); 


            return view('frontend_new.forgotpassword',compact('user_id'));

        }

          $request->session()->flash('message','Invalid OTP'); 
          return view('frontend_new.emailotp');

    }

    public function forgoyPassword(Request $request){

        $req = $request->all();
        $next ="normal";
         $body['json'] = ['id'=>$request->id,'password'=>$request->password];

         $response = $this->client->apiRequest('POST','auth/forgot/password',$body);
         $respi = $response->getData()->response;

       

         if($respi->success){

            $request->session()->flash('message','Password change successfullyword'); 

            return redirect()->route('userapi.userlogin',compact('next'));
        }
            

        
       

    }

    public function sendOTP(Request $request){
       
        $phone = $request->phone;
        $next = $request->next;
        $body['json'] = ['mobile'=>$phone];
        $response = $this->client->apiRequest('POST','auth/loginwithOtp',$body);
        $resp = $response->getData()->response;
        if($resp->status){
            flash('OTP sent successfully')->success();
            return view('frontend_new.verify_otp',compact('resp','next','phone'));
        }
        flash('OTP sending fail')->error();
        return back();
    }

    public function verifyOTP(Request $request){
        $phone = $request->phone;
        $next = $request->next;
        $otp = $request->otp;
        $body['json'] = ['mobile'=>$phone,'otp'=>$otp];
        $response = $this->client->apiRequest('POST','auth/verifyOtp',$body);
        $resp = $response->getData()->response;
        // dd($resp);
        if($resp->success){
            setcookie('logged',encrypt(true),time()+60*60*24*30,'/');
            setcookie('auth',encrypt($resp->user->id),time()+60*60*24*30,'/');
            session()->put('access_token',$resp->access_token);
            session()->put('user_id',$resp->user->id);
            session()->put('user',json_encode($resp->user));
            Session::flash('success','Login successful. Hello ');


            if($next=="checkout"){
                return redirect()->route('phoneapi.shipping_info');
            }
            return redirect()->route('new.home');
        }
        flash('Invalid OTP')->error();
        return back();
    }

    public function register($next){

        return view('frontend_new.user_register',compact('next'));

    }

    public function userregister(Request $request)
    {
       

       $validate = $request->validate([


            'name' => 'required',
            'phone' => 'required|numeric|digits:10',
            'email' => 'required',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required'
        ]);

       if($validate)
       {
         $name = $request->name;
         $phone = $request->phone;
         $email = $request->email;
         $password = $request->password;
         $next = $request->next;
         $password_confirmation = $request->password_confirmation;
         $body['json'] = ['name'=>$name,'email'=>$email,'phone'=>$phone,'password'=>$password,'password_confirmation'=>$password_confirmation];
         $response = $this->client->apiRequest('POST','auth/signup',$body);
         $respi = $response->getData()->response;


        

            if (property_exists($respi, 'success')){

            $email = $request->email;
            $password = $request->password;
            $body['json'] = ['email'=>$email,'password'=>$password];
            $response = $this->client->apiRequest('POST','auth/login',$body);
            $resp = $response->getData()->response;

           if($resp->success){
            setcookie('logged',encrypt(true),time()+60*60*24*30,'/');
            setcookie('auth',encrypt($resp->user->id),time()+60*60*24*30,'/');
            session()->put('access_token',$resp->access_token);
            session()->put('user_id',$resp->user->id);
            session()->put('user',json_encode($resp->user));
            flash('Login successfully')->success();
            if($next=="checkout"){
                return redirect()->route('phoneapi.shipping_info');
            }
            return redirect()->route('new.home');
            }


          }else{

            
            return back()->with('status', 'Profile updated!');

          }

       }

       
    }

    public function logout(){
        
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $response = $this->client->apiRequest('GET','auth/logout',$body);
        Cookie::queue(Cookie::forget('logged'));
        Cookie::queue(Cookie::forget('auth'));
        Cookie::queue(Cookie::forget('sid'));
        Cookie::queue(Cookie::forget('pincode'));
        Cookie::queue(Cookie::forget('city'));
        Cookie::queue(Cookie::forget('peer'));
        session()->forget('access_token');
        session()->forget('user_id');
        session()->forget('user');
        \App\Models\Cart::where('device_id',$this->device)->delete();
        if(auth()->user() != null){
            $this->guard()->logout();
        }
        return redirect()->route('home');
    }

    public function shippingInfo(){
        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        // $user_id = decrypt(Cookie::get('auth'));
        $user_id = session()->get('user_id');

        $response = $this->client->apiRequest('GET','user/shipping/address/'.$user_id,$body);
        $resp = $response->getData()->response;
       $addresses = $resp->data;
        return view('frontend_new.shipping_info',compact('addresses'));
    }

    public function deliveryInfo(Request $request){
        $user = json_decode(session()->get('user'));
        $address = \App\Address::find($request->address_id);
        $self = 1;
        //check for guest user if user not logged in

        $sortinghub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $address->postal_code . '"]\')')->pluck('user_id')->first();
        $sortinghubid = "";
        if(!is_null($sortinghub)){
            $sortinghubid = $sortinghub;
        }
        $shipping_info = [
            'name'=>$address->name,
            'email'=>$user->email,
            'address'=>$address->address,
            'country'=>$address->country,
            'city'=>$address->city,
            'state'=>$address->state,
            'postal_code'=>$address->postal_code,
            'phone'=>$address->phone,
            'checkout_type'=>'logged'
        ];

        $request->session()->put('shipping_info',json_encode($shipping_info));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = ['device_id'=>$this->device,'sortinghubid'=>$sortinghubid,'self'=>$self];
        $response = $this->client->apiRequest('POST','carts/check_availability',$body);
        $carts = $response->getData()->response->data;
        $body['json']['postal_code'] = $address->postal_code;
        $slots = $this->client->apiRequest('POST','deliveryslot',$body);
        $availSlots= $slots->getData()->response->data;
        $todayDate = date('d-M, Y');
        $tommorowDate = date('d-M, Y', strtotime(date('Y-m-d'). ' + 1 day'));
        return view('frontend_new.delivery_info',compact('carts','availSlots','todayDate','tommorowDate'));
    }

    public function setDeliverySchedule($request){
        //dd($request->all());
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
                    $deliveryDetail['delivery_slot_grocery'] = date("H:i:s",strtotime($currentDateTime->addHour(24)));
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
                    $deliveryDetail['delivery_slot_fresh'] = date("H:i:s",strtotime($currentDateTime->addHour(24)));
                }
                
            }

        return $request->session()->put('delivery_schedule',json_encode($deliveryDetail));
        
    }

    public function paymentOption(Request $request){
        $this->setDeliverySchedule($request);
        return view('frontend_new.payment_select');
    }

    public function genereateOrder(Request $request){
        $user_id = decrypt(Cookie::get('auth'));
        $delivery_type = json_decode(session()->get('delivery_schedule'));
        $deliveryDetailFresh = [];
        $deliveryDetailGro = [];
        if($delivery_type->items->fresh){
            $deliveryDetailFresh =[
                'delivery_type'=>$delivery_type->delivery_type,
                'delivery_date'=>$delivery_type->delivery_date_fresh,
                'delivery_time'=>$delivery_type->delivery_slot_fresh
            ];
        }

        if($delivery_type->items->grocery){
            $deliveryDetailGro = [
                'delivery_type'=>$delivery_type->delivery_type,
                'delivery_date'=>$delivery_type->delivery_date_grocery,
                'delivery_time'=>$delivery_type->delivery_slot_grocery
            ];
        }

        $grand_total = session()->get('grand_total');
        $referal_code = $this->peercode;
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = [
            'device_id'=>$this->device,
            'payment_type'=>$request->payment_option,
            'payment_status'=>'unpaid',
            'payment_type'=>$request->payment_option,
            'user_id'=>$user_id,
            'test_grand_total'=>$grand_total,
            'coupon_discount'=>0,
            'coupon_code'=>"",
            'platform'=>"",
            'referal_code'=>$referal_code,
            'referal_discount'=>session()->get('total_saving'),
            'amount_by_wallet'=>$request->amount_by_wallet,
            'is_fresh'=>$delivery_type->items->fresh,
            'is_grocery'=>$delivery_type->items->grocery,
            'delivery_detail_fresh'=>$deliveryDetailFresh,
            'delivery_detail_grocery'=>$deliveryDetailGro,
            'sorting_hub_id'=>$this->sortinghubid,
            'order_type'=>'self'
        ];
        $payment_option = $request->payment_option;
        if($payment_option=="cash_on_delivery" || $payment_option=="wallet"){
            $endpoint = "payments/pay/cod";
            $body['json']['grand_total'] = $grand_total;
            $body['json']['shipping_address'] = session()->get('shipping_info');
        }else{
            $endpoint = "order/initiate";
            $body['json']['amount'] = $grand_total;
            $body['json']['shipping_address'] = json_decode(session()->get('shipping_info'));
        }

        $response = $this->client->apiRequest('POST',$endpoint,$body);
        if($response->getData()->response->success==true){
            if(isset($response->getData()->response->response->razorpayId)!=null){
                // sendToRazorpay dialogue

               
                return $this->sendToRazorpay($response->getData()->response->response,$referal_code);
            }else{
                $order = $response->getData()->response->order;
                session()->put('order',$order);
                return redirect()->route('new.phoneapi.confirm_order');
            }
            
        }
        flash('Something went wrong!')->error();
        return back();
    }

    public function sendToRazorpay($response,$referal_code){
        $response = (array) $response;
        //dd($response);
        return view('frontend_new.razor_wallet.razor-pay',compact('response','referal_code'));
    }

    public function paymentSuccess(Request $request){
       $code = $request->order_code;
       $referal_code = $request->referal_code;
       $payment_details = [
           'id'=>$request->rzp_paymentid,
           'order_id'=>$request->rzp_orderid
       ];
        $device_id = $this->device;
    
       $body['json'] = [
           'code'=>$code,
           'payment_detail'=>$payment_details,
           'device_id'=>$device_id,
           'referal_code'=>$referal_code
       ];

       $endpoint = "order/store";
       $response = $this->client->apiRequest('POST',$endpoint,$body);
       if($response->getData()->response->success){
        $order = $response->getData()->response->order;
        session()->put('order',$order);
        return redirect()->route('new.phoneapi.confirm_order');
       }
       flash('Something went wrong!')->error();
       return back();
    }

    public function confirmOrder(){
        $order = session()->get('order')[0];
        return view('frontend_new.confirm_order',compact('order'));
    }

    public function addadress()
    {
        return view('frontend_new.adduser_address');
    }

    public function addshippingaddress(Request $request)
    {

       // $user_id =  $this->user_id = decrypt(Cookie::get('auth'));
       $user_id =  $this->user_id = session()->get('user_id');

       $name = $request->name;
       $address = $request->address;
       $city = $request->city;
       $country = $request->country;
       $phone = $request->phone;
       $postal_code = $request->postal_code;
       $state = $request->state; 
       $tag = $request->tag; 

        
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];


        $body['json'] = ['name'=>$name,'address'=>$address,'city'=>$city,'country'=>$country,'phone'=>$phone,'postal_code'=>$postal_code,'state'=>$state,'user_id'=>$user_id,'tag'=>$tag];


         // $bodyy = json_encode($body['json']);

        // dd($encodedSku);

        $response = $this->client->apiRequest('POST','user/shipping/create',$body);


        $resp = $response->getData()->response;
       
         return redirect('new/shipping_info');

    }

    public function wishlists(){
        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $body['headers'] = [
            'Authorization'=>'Bearer '.session()->get('access_token'),
            'sortinghubid'=>$this->sortinghubid,
            'peer'=>$this->peercode
        ];

        // $user_id = decrypt(Cookie::get('auth'));
        $user_id = session()->get('user_id');

        $response = $this->client->apiRequest('GET','wishlists/'.$user_id,$body);
        $wishlists = $response->getData()->response->data;
        return view('frontend_new.wishlist',compact('wishlists'));
    }

    public function wallet(){
        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }

        // $user_id = decrypt(Cookie::get('auth'));
        $user_id = session()->get('user_id')
        ;
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','wallet/balance/'.$user_id,$body);
        $balance = $response->getData()->response;

        $response = $this->client->apiRequest('GET','wallet/history/'.$user_id,$body);
        $history = $response->getData()->response->data;

        return view('frontend_new.wallet',compact('balance','history'));


    }

    public function userinfo(){
        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        // $user_id = decrypt(Cookie::get('auth'));
        $user_id = session()->get('user_id');
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];
        return view('frontend_new.inc.customer_side_nav',compact('user'));
    }
    public function profile(){

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        // $user_id = decrypt(Cookie::get('auth'));
        $user_id = session()->get('user_id');


        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];

        $response = $this->client->apiRequest('GET','user/shipping/address/'.$user_id,$body);
        $addres = $response->getData()->response->data;

        // dd($addres);

        return view('frontend_new.profile',compact('user','addres'));
    }

    public function addressdelete(Request $request){

        $id = $request->id;

     

        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];


        $response = $this->client->apiRequest('GET','user/shipping/delete/'.$id,$body);

        $data = $response->getData()->response->data;

         return "1";

    }
    public function updateEmail(Request $request){


          if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }

          // $id = decrypt(Cookie::get('auth'));
          $id = session()->get('user_id');

           $validate = $request->validate([
            'email' => 'required|unique:users'
           ]);

       if($validate){

             $update =  DB::table('users')->where('id',$id)->update([
                    'email' => $request->email]);

             return redirect()->back()->with('message', 'Email Update');

                
          }
     
    }
    public function updateUserinfo(Request $request){

        $data =$request->all();
        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
                  // $user_id = decrypt(Cookie::get('auth'));
                  $user_id = session()->get('user_id');

         $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = ['name'=>$request->name,'phone'=>$request->phone,'user_id'=>$user_id];

        $response = $this->client->apiRequest('POST','user/info/update',$body);
        $orderdetail = $response->getData()->response;

     

         return redirect()->back()->with('messagee', ' Update Basic info');

      }
    public function dashboard(){

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        // $user_id = decrypt(Cookie::get('auth'));
        $user_id = session()->get('user_id');


        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];

          $response = $this->client->apiRequest('GET','user/shipping/address/'.$user_id,$body);
        $addre = $response->getData()->response->data;

        $addres = $addre['0'];

         // dd($user);

        return view('frontend_new.dashboard',compact('user','addres'));
    }

    public function add_review(Request $request){

        $data = $request->all();
        // $user_id = decrypt(Cookie::get('auth'));
        $user_id = session()->get('user_id');

        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = ['product_id'=>$request->product_id,'user_id'=>$user_id,'rating'=>$request->rating,'comment'=>$request->comment];

        $response = $this->client->apiRequest('POST','store-review',$body);
        $orderdetail = $response->getData()->response;

         return redirect()->back()->with('messagee', ' Add Your review ');
        

    }

    public function elasticSearch(){

        $base_url = "http://elastic.rozana.in/api/v1/";
        $this->client = new HttpService($base_url);
        $body['headers'] = ['sortinghubid'=>$this->sortinghubid];
        $body['json'] = ['search'=>request('q')];
        $response = $this->client->apiRequest('POST','elastic-search/search-product',$body);
        $products = $response->getData()->response->data->products;
        $pricerange = (object)['min_price'=>0,'max_price'=>200];
        $filterbyvolume = [];
        $links = "";
        //dd($products);
        return view('frontend_new.search_product_listing',compact('products','pricerange','filterbyvolume','links'));
    }

    public function elasticSearchSuggession(Request $request){

        $base_url = "http://elastic.rozana.in/api/v1/";
        $this->client = new HttpService($base_url);
        $body['headers'] = ['sortinghubid'=>$this->sortinghubid];
        $body['json'] = ['search'=>$request->search];
        $response = $this->client->apiRequest('POST','elastic-search/search-product',$body);
        $products = $response->getData()->response->data->products;
        $tags = $response->getData()->response->data->tags;
        $categories = $response->getData()->response->data->categories;
        // dd($response);
        return view('frontend_new.partials.search_content',compact('products','tags','categories'));
    }

    public function updateAddressByCallcenter(Request $request){

        $body['json'] = [
            'address'=>$request->address,
            'user_id'=>$request->user_id,
            'state'=>$request->state,
            'state_id'=>$request->state_id,
            'city'=>$request->city,
            'city_id'=>$request->city_id,
            'block'=>$request->block,
            'block_id'=>$request->block_id,
            'village'=>$request->village,
            'pincode'=>$request->pincode,
            'zone'=>$request->zone
        ];
        //dd(json_encode($body));
        $response = $this->client->apiRequest('POST','updateaddress',$body);
        if($response->getData()->response->success==true){
            $user = \App\User::find($request->user_id);
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$request->pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
            setcookie('peer',$user->partner->code,time()+60*60*24*30,'/');
            setcookie('sid',encrypt($shortId['sorting_hub_id']),time()+60*60*24*30,'/');
            setcookie('pincode',$request->pincode,time()+60*60*24*30,'/');
            setcookie('city_name',$request->city,time()+60*60*24*30,'/');
            flash('Information updated successfully')->success();
            return back();
        }
        flash('Something went wrong')->error();
        return back();
    }
    
}
