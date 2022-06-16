<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\HttpService;
use Cookie;
use Session;
use Carbon\Carbon;
class NewOrderController extends Controller
{
    protected $sortinghubid='';
    protected $peercode = '';
    protected $device = "";
    protected $client;
    protected $user_id=0;
    protected $base_url = "https://prelive.rozana.in/api/v5/";
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

    public function track_order(){

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];

       

       return view('frontend_new.track_order',compact('user'));
    }

    public function showtrack_order(Request $request){
        // $req= $request->all();

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }

         $validate = $request->validate([
            'order_code' => 'required'
        ]);

       if($validate){
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];


        $order_code= $request->input('order_code');

        $body['json'] = ['order_code'=>$order_code];

        $response = $this->client->apiRequest('POST','order/track',$body);
        $orderdetail = $response->getData()->response;

         if($orderdetail->success){

        $orderd = $orderdetail->data;

         $orderdetails  = $orderd['0'];
         $shippingaddress  = $orderd['0']->shipping_address;
         $details  = $orderd['0']->details;

         // dd($orderdetails,$shippingaddress,$details);
        return view('frontend_new.showtrack_order',compact('user','orderdetails','shippingaddress','details'));
    }else{

               return view('frontend_new.track_order',compact('user'))->with('success','Order Not Found,Please Entern Valide Order Code *');;
    }
    }

    }


    public function purchase_history(){

          if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];



        $response = $this->client->apiRequest('GET','order/history/'.$user_id,$body);
        $orders = $response->getData()->response;

        $orderslist = $orders->data;

        return view('frontend_new.purchase_history',compact('orderslist','user'));
    }

    public function purchaseorderdetail($id){
           if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];

       
        
        $body['json'] = ['orderId'=>$id];

        $response = $this->client->apiRequest('POST','order/history/detail',$body);
        $orderdetail = $response->getData()->response;

        

          $orderd = $orderdetail->data;


          $orderdetails  = $orderd['0'];
          $shippingaddress  = $orderd['0']->shipping_address;
          $details  = $orderd['0']->details;

            // dd($details);

        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','order/history/'.$user_id,$body);
        $orders = $response->getData()->response;

        $orderslist = $orders->data;
         return view('frontend_new.purchase_orderdetail',compact('orderslist','orderdetails','shippingaddress','details','user'));


    }
    public function wishlists(){

          if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }

          $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];
           // dd($user);

        $body['headers'] = [
            'Authorization'=>'Bearer '.session()->get('access_token'),
            'sortinghubid'=>$this->sortinghubid,
            'peer'=>$this->peercode
        ];

        $user_id = decrypt(Cookie::get('auth'));

        $response = $this->client->apiRequest('GET','wishlists/'.$user_id,$body);
        $wishlists = $response->getData()->response->data;
        return view('frontend_new.wishlist',compact('wishlists','user'));
     
    }


    public function addwishlists(Request $request){


        $product_id = $request->id;
      

        $user_id = decrypt(Cookie::get('auth'));



        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = ['product_id'=>$product_id,'user_id'=>$user_id];

        $response = $this->client->apiRequest('POST','wishlists',$body);
        $orderdetail = $response->getData()->response;


    }

    public function wishlistsremove(Request $request){

        $id = $request->id;


        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('DELETE','wishlists/'.$id,$body);

        $res = $response->getData()->response;

    }

    public function myWallet(){

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];

        $responses = $this->client->apiRequest('GET','wallet/balance/'.$user_id,$body);

        $wallet =$responses->getData()->response;


        $response = $this->client->apiRequest('GET','wallet/history/'.$user_id,$body);

        $responses = $response->getData()->response;

        $res = $responses->data;

        $response = $this->client->apiRequest('GET','wallet/history/'.$user_id,$body);
        $history = $response->getData()->response->data;

        //dd($history);

       return view('frontend_new.newwallet',compact('user','wallet','history'));
    }

    public function walletRecharge(Request $request){

        $req = $request->payment_type;
       


        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }

        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = [

            'user_id'=>$user_id,
            'amount'=>$request->amount,
            'payment_method'=>$request->payment_option,
            'payment_type'=>$request->payment_type
        ];


        $response = $this->client->apiRequest('POST','wallet/recharge',$body);
        $walletpay = $response->getData()->response->response;
        $order_detail = $response->getData()->response->order_detail;

        $data = json_decode(json_encode($order_detail),true);
        $data2 = json_decode(json_encode($walletpay),true);
        $data3 = array(     'amount'=>$request->amount,
            'payment_method'=>$request->payment_option,
            'payment_type'=>$request->payment_type); 

        $referal_code="";

       

       
         // dd($walletpay);

          if($response->getData()->response->success==true){
            if(isset($response->getData()->response->response->razorpayId)!=null){
                // sendToRazorpay dialogue

                 // dd("helo");
                 $response = array_merge( $data, $data2,$data3 );
                  // dd($response);
                 return $this->sendToRazorpay($response,$referal_code);
            }
            
        }

        flash('Something went wrong!')->error();
        return back();




        //  $jsonobj = '"Peter":$walletpay->razorpayId,"order_id":$walletpay->orderId}';

        //  $age = array("id"=>"$walletpay->razorpayId", "order_id"=>"$walletpay->orderId");

        //  $pd = json_encode( $age );



        //     $body['json'] = [
              
        //                     "payment_method"=>$request->payment_type,
        //                     "payment_status"=>"success",
        //                     "user_id"=>$user_id,
        //                     "amount"=>$request->amount,
        //                     "receiptId"=>$walletpay->receiptId,
        //                     "payment_detail"=>$pd,
        //                     "order_detail"=> $order_detail

        // ];

        // $response = $this->client->apiRequest('POST','wallet/rechargestore',$body);
        // $walletpay = $response->getData()->response;



        // dd($walletpay);

    }

      public function sendToRazorpay($response,$referal_code){
        $response = (array) $response;
        //dd($response);
        return view('frontend_new.razor_wallet.wallet-recharge',compact('response','referal_code'));
    }


    public function walletrechargeSuccess(Request $request){

        $req = $request->all();
                $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

         $user_id = decrypt(Cookie::get('auth'));

        
       $payment_details = [
           'id'=>$request->rzp_paymentid,
           'order_id'=>$request->rzp_orderid
       ];

       $order_detail = [

                             'id'=>$request->rzp_paymentid,
                              'entity'=>$request->entity,
                              'amount'=>$request->amount,
                              'currency'=>$request->currency,
                              'receipt'=>$request->receipt,
                              'status'=>$request->status,
                              'attempts'=>$request->attempts
       ];
       
    
               $body['json'] = [
              
                            "payment_method"=>$request->payment_type,
                            "payment_status"=>"success",
                            "user_id"=>$user_id,
                            "amount"=>$request->amount,
                            "receiptId"=>$request->receiptId,
                            "payment_detail"=>$payment_details,
                            "order_detail"=> $order_detail

        ];

        $response = $this->client->apiRequest('POST','wallet/rechargestore',$body);
        $walletpay = $response->getData()->response;

       if($response->getData()->response->success){
  
        return redirect()->route('phoneapi.wallet')->withErrors(['error' => 'Wallet Recharge uccessfully']);
       }
       flash('Something went wrong!')->error();
       return back();
    }


    public function supportTicket(){

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];

        return view('frontend_new.support_ticket',compact('user'));
    }

    public function futureOrder(){

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];


        return view('frontend_new.future_order',compact('user'));

    }

    public function cancelOrder($id){
   
        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];


        $body['json'] = ['orderId'=>$id];

        $response = $this->client->apiRequest('POST','order/history/detail',$body);
        $orderdetail = $response->getData()->response;
        $orderd = $orderdetail->data[0];

        // dd($orderd);

        return view('frontend_new.cancel_order',compact('user','orderd'));
    }

    public function cancelOrderid(Request $request){


          $validate = $request->validate([
            'reason' => 'required'
        ]);

       if($validate){

        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];
        $body['json'] = ['order_id'=>$request->order_id,'reason'=>$request->reason];

        $response = $this->client->apiRequest('POST','order/cancel',$body);

        $ordercancel = $response->getData()->response;

         return redirect('new/purchase_history')->with('message', 'Order Cancelled successfully.');

       }else{

          return redirect()->back()->with('message', 'Orderd not Cancelled');

       }

    }

    public function help($id){

        if(!session()->has('access_token')){
            return redirect()->route('userapi.login',['checkout']);
        }
        $user_id = decrypt(Cookie::get('auth'));
        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];

        $response = $this->client->apiRequest('GET','user/info/'.$user_id,$body);
        $user = $response->getData()->response->data[0];
        $body['json'] = ['orderId'=>$id];

        $response = $this->client->apiRequest('POST','order/history/detail',$body);
        $orderdetail = $response->getData()->response;
        $orderd = $orderdetail->data[0];

        // dd($orderd);

        return view('frontend_new.help',compact('user','orderd'));

    }

    public function emailverify(Request $request){

        $email = $request->id;

        $body['headers'] = ['Authorization'=>'Bearer '.session()->get('access_token')];      
        $body['json'] = ['orderId'=>$email];
        $response = $this->client->apiRequest('POST','auth/verifymail',$body);
        $emailverify = $response->getData()->response;
    
    

    }


}
