<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\ShortingHub;
use Illuminate\Http\Request;
use App\Customer;
use App\User;
use App\Order;
use Illuminate\Support\Str;
use DB;
use App\PeerPartner;
use App\Role;
use App\Models\Callcenter;
use Validator;
use Redirect;
use Session;
use Cookie;


/* --- 25-09-2021 --*/
use Excel;
use App\OrdersExport;
/* -----*/

use App\AssignOrder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use App\SubOrder;
use App\Events\OrderPlacedEmail;
use App\RefundRequest;

use App\Notifications\EmailVerificationNotification;

class CallCenterController extends Controller
{

    public function html_data_table(REQUEST $request){

        if (!Auth::check()) {
            return Redirect::route('login');
        }

        if (!(auth()->user()->user_type == 'operation')) {
           Auth::logout();
            return Redirect::route('login');

        }else{
                $sort_search = null;
                $start_date = null;
                $end_date = null;
                $user = Auth()->user();
                $orders = DB::table('final_orders');
                if($user->user_type == 'staff'){
                    $orders = $orders->where('sortinghub_id',$user->id);
                }
                if ($request->has('search') && !empty($request->search)){
                    
                    $sort_search = $request->search;
                    $orders = $orders->where(function($query) use($sort_search){
                        $query->where('order_code', 'like', '%'.$sort_search.'%')
                        ->orWhere('shipping_address->name','like', '%'.$sort_search.'%')
                        ->orWhere('shipping_address->phone', 'like', '%'.$sort_search.'%');
                    });
                    
                }

                if ($request->has('start_date') && $request->has('end_date')){
                    if(!empty($request->start_date) && !empty($request->end_date)){
                    $start_date = $request->start_date; 
                    $end_date = $request->end_date;
                    $orders = $orders->whereDate('order_date', '>=', $start_date)->whereDate('order_date', '<=', $end_date);
                    }
                }
                $orders = $orders->orderBy('order_id', 'desc')->paginate(25);

                // dd($orders);
                return view('orderopration',compact('orders','sort_search','start_date','end_date'));
            }
    }

    /*public function login($id)
    {

        // dd($id);
        $partner = PeerPartner::findOrFail(decrypt($id));
        $partner  = $partner->user;
        auth()->login($partner, true);
        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');

        setcookie('logged',encrypt(true),time()+60*60*24*30,'/');
        setcookie('auth',encrypt($user->id),time()+60*60*24*30,'/');
        session()->put('access_token',$tokenResult->accessToken);
        session()->put('user_id',$user->id);
        session()->put('user',json_encode($user));
        //location info
        //dd($user->partner);
        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$user->postal_code.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        setcookie('peer',$user->partner->code,time()+60*60*24*30,'/');
        setcookie('sid',encrypt($shortId['sorting_hub_id']),time()+60*60*24*30,'/');
        setcookie('pincode',$user->postal_code,time()+60*60*24*30,'/');
        setcookie('city_name',$user->city,time()+60*60*24*30,'/');
        // empty cart
       
        flash('Login successfully')->success();
        return redirect()->route('home');
    }*/

    public function login($id)
    {
        $partner = PeerPartner::findOrFail(decrypt($id));
        $code = $partner->code;
        $partner  = $partner->user;

        // auth()->login($partner, true);
        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');

        setcookie('logged',encrypt(true),time()+60*60*24*30,'/');
        setcookie('auth',encrypt($user->id),time()+60*60*24*30,'/');
        session()->put('access_token',$tokenResult->accessToken);
        session()->put('user_id',$partner->id);
        session()->put('user',json_encode($partner));
        session()->put('logged_url','call_center');
        //location info
        //dd($user->partner);
        $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$partner->postal_code.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        setcookie('peer',$code,time()+60*60*24*30,'/');
        setcookie('sid',encrypt($shortId['sorting_hub_id']),time()+60*60*24*30,'/');
        setcookie('pincode',$partner->postal_code,time()+60*60*24*30,'/');
        setcookie('city_name',$partner->city,time()+60*60*24*30,'/');
        // setcookie('logged_url','call_center',time()+60*60*24*30,'/');
        // empty cart
       
        flash('Login successfully')->success();
        return redirect()->route('home');
    }

    public function index(){

        $roles = Role::all();
        $sorting_hubs = ShortingHub::with('cluster')->paginate(10);


        return view('callcenter.index',compact('roles','sorting_hubs'));

    }
    public function Operations(){

        $roles = Role::all();
        $sorting_hubs = ShortingHub::with('cluster')->paginate(10);


        return view('callcenter.oprationuser',compact('roles','sorting_hubs'));

    }
    public function Callcetertbl(Request $request){

        if (!Auth::check()) {
            return Redirect::route('login');
        }

        if (!((auth()->user()->user_type == 'callcenter') && (auth()->user()->status == 1))) {
              Auth::logout();
            return Redirect::route('login');

        }else{
         $checkPeerStatus = DB::table('global_switch')->where('name','Peer')->first();
        $sort_search = null;
        $approved = null;
        $peer_partner = PeerPartner::orderBy('created_at', 'desc');
       
                if ($request->has('search')){
            $sort_search = $request->search;
            $user_ids = User::where('user_type', 'partner')->where(function($user) use ($sort_search){
                $user->where('name', 'like', '%'.$sort_search.'%')->orWhere('phone', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%');})->pluck('id')->toArray();
            $peer_partner = $peer_partner->where(function($peer_partner) use ($user_ids){
                $peer_partner->whereIn('user_id', $user_ids);
            });
        }
        if ($request->approved_status != null) {
            $approved = $request->approved_status;
            $peer_partner = $peer_partner->where('verification_status', $approved);
        }

        if($checkPeerStatus->access_switch == 0){
            $peer_partner->where('dofo_peer',0);
        }
        $peer_partner = $peer_partner->paginate(10);


        return view('callcenter',compact('peer_partner','approved'));}

    }

        public function Fieldofficer(Request $request){

if (!Auth::check()) {
    return Redirect::route('login');
}

if (!(auth()->user()->user_type == 'fieldofficer' && (auth()->user()->status == 1))) {
      Auth::logout();
    return Redirect::route('login');

}else{
         $checkPeerStatus = DB::table('global_switch')->where('name','Peer')->first();
        $sort_search = null;
        $approved = null;
        $peer_partner = PeerPartner::orderBy('created_at', 'desc');
       
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

        if($checkPeerStatus->access_switch == 0){
            $peer_partner->where('dofo_peer',0);
        }
        $peer_partner = $peer_partner->paginate(10);



        return view('callcenter.fieldofficer',compact('peer_partner','approved'));}
    }

    public function Logincc(Request $request){

        $req = $request->all();

         
                    $email = $request->input('email');
                       $password = $request->input('password');

                       $user = DB::table('callcenters')->where('email',$email)->first();

                        // dd($user->password);



                       if (Hash::check($password, $user->password)){
                           $apiToken = base64_encode(Str::random(40));
                             dd($apiToken);

                           DB::table('callcenters')->where('email',$email)->update([
                                'password' => $apiToken
                           ]);

                            dd('login');

                           // return response()->json([
                           //  'success' => true,
                           //  'message' => 'Login Success!',
                           //  'data' => [
                           //      'user' => $user,
                           //      'api_token' => $apiToken
                           //  ]
                           //  ], 201);
                       } else {
                        return response()->json([
                            'success' => false,
                            'message' => 'Login  fail!',
                        ], 400);
                       }

         
    }
    public function Callcenterall(Request $request){

        // $callcenterall = DB::table('callcenters')->paginate(10);

         // dd($callcenterall);

         // $callcenterall = $callcenterall->paginate(10);

        $sort_search =null;
        $brands = Callcenter::orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $brands = $brands->where('name', 'like', '%'.$sort_search.'%')->orWhere('email', 'like', '%'.$sort_search.'%')->orWhere('phone', 'like', '%'.$sort_search.'%');
        }
        $callcenterall = $brands->paginate(15);

         // dd($callcenterall);



        return view('callcenter.callcenterall',compact('callcenterall'));
    }
    public function Isactive(Request $request){

        $id = $request->all();
        
        $partner = Callcenter::where('customercare_id', $request->id)->firstOrFail();


        if($partner){
        $data = DB::table('callcenters')->where('customercare_id', $request->id)->update(['isactive' => $request->status]);
        if($data==1){

        $user = User::where('id', $request->id)->firstOrFail();

        if($user){

        $data = DB::table('users')->where('id', $request->id)->update(['status' => $request->status]);

        return 1;

        }

        }
       }
       return 0;

    }

        public function fieldofficerisactive(Request $request){

         if (!Auth::check()) {
                return Redirect::route('login');
            }

         

        $id = $request->all();
        // dd($id);

        $partner = PeerPartner::findOrFail($request->id);


        $name = auth()->user()->name;
        $user_id = auth()->user()->id;

        if($partner){
        $data = DB::table('peer_partners')->where('id', $request->id)->update(['fieldofficer_status_approve' => $request->status,'fieldofficer_id' => $user_id,'fieldofficer_name' => $name]);

         return 1;
       }
       return 0;

    }
    public function Addoperation(Request $request){
        
        $req = $request->all();
        if(User::where('email', $request->email)->first() == null){

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile;
        $user->password = bcrypt('admin@123$');
        $user->user_type = $request->role_id; 
        $user->sorting_hub_id = $request->sorting_hub;

        $user->status = '0';
        
        $user->save();
        if($user->save()){
            // echo "User's record id is ".$user->id; 

            // dd($user->id);

           

        $addcallcenters = new Callcenter;
        $addcallcenters->name = $request->name;
        $addcallcenters->email = $request->email;
        $addcallcenters->phone = $request->mobile;
        $addcallcenters->password = bcrypt('admin@123$');
        $addcallcenters->role = $request->role_id;
        $addcallcenters->customercare_id = $user->id;
        $addcallcenters->sorting_hub_id = $request->sorting_hub;
        $addcallcenters->isactive = '0';
        $addcallcenters->save();

        if($addcallcenters->save()){
                    flash(translate('User Create successfully'))->success();
                    // return redirect()->route('callcenter.index');
                    // return Redirect('callceter.callcenterall');
                   return redirect()->route('callceter.callcenterall');

        }
        }

    }
        flash(translate('Email already used'))->error();
        return back();


    }

    public function AddCustomer(Request $request){
        
        $req = $request->all();
        // dd($req);
        if(User::where('email', $request->email)->first() == null){

        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile;
        $user->password = bcrypt('admin@123$');
        $user->user_type = $request->role_id;
        $user->status = '0';
        
        $user->save();
        if($user->save()){
            // echo "User's record id is ".$user->id; 

            // dd($user->id);

        $addcallcenters = new Callcenter;
        $addcallcenters->name = $request->name;
        $addcallcenters->email = $request->email;
        $addcallcenters->phone = $request->mobile;
        $addcallcenters->password = bcrypt('admin@123$');
        $addcallcenters->role = $request->role_id;
        $addcallcenters->customercare_id = $user->id;
        $addcallcenters->isactive = '0';
        $addcallcenters->save();

        if($addcallcenters->save()){
                    flash(translate('User Create successfully'))->success();
                    // return redirect()->route('callcenter.index');
                   return redirect()->route('callceter.callcenterall');
        }
        }

    }
        flash(translate('Email already used'))->error();
        return back();


    }

     public function final_orders_export(){

        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];


        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $sorting_hub_id = $sorting_hub_id;
            $result = true;
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        // $orders = DB::connection('mysql2')->table('final_orders')
        $orders = DB::connection('mysql')->table('final_orders')
                        //    ->leftjoin('orders','orders.id','=','final_orders.order_id')
                        //   ->leftjoin('products','products.id','=','order_details.product_id')
                          ->leftjoin('users','users.id','=','final_orders.sortinghub_id');
        $from = date('Y-m-d',strtotime($from));
        $to = date('Y-m-d',strtotime($to.' +1 day'));

        if(isset($result)){
            $orders = $orders->where('sortinghub_id',$sorting_hub_id);
            if(isset($from)){
                // if($from == $to){
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to));
                // }else{
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to.' +1 day'));
                // }

                if($from != $to){
                    $orders = $orders->whereBetween('final_orders.order_date', [$from, $to]);
                }else{
                    $orders = $orders->whereDate('final_orders.order_date',$from);
                }
            }
            
        }else{

            if(isset($from)){
                $orders = $orders->whereBetween('final_orders.order_date', [$from, $to]);
                // dd(DB::getQueryLog());
            }
            
        }

        $orders = $orders->orderBy('final_orders.order_date','desc')
                          ->select('final_orders.order_code','final_orders.delivery_status','final_orders.order_id','final_orders.order_date','final_orders.order_date','final_orders.no_of_items','final_orders.shipping_address','final_orders.shipping_address','final_orders.pincode','users.name as shorting_hub','final_orders.payment_method','final_orders.payment_status','final_orders.grand_total','final_orders.grand_total','final_orders.referal_code')
                          ->get();

        // echo '<pre>';
        // print_r($orders);
        // die;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Order Code');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Num. of Products');
        $sheet->setCellValue('E1', 'Customer');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Pin Code');
        $sheet->setCellValue('H1', 'Phone Number');
        $sheet->setCellValue('I1', 'Sorting HUB');
        $sheet->setCellValue('J1', 'Payment Mode');
        $sheet->setCellValue('K1', 'Total Amount');
        $sheet->setCellValue('L1', 'Delivery Status');
        $sheet->setCellValue('O1', 'Payment Status');
        $sheet->setCellValue('P1', 'Email');
        $sheet->setCellValue('Q1', 'Peer Code');
        $sheet->setCellValue('R1', 'Delivery Boy');
        // $sheet->setCellValue('S1', 'Delivery Slot');
        // $sheet->setCellValue('T1', 'Delivery Time');

        $i = 0;

        foreach($orders as $key => $order)
        {

               $numProduct = OrderDetail::where('order_id', $order->order_id)->sum('quantity');
               $customer_detail = json_decode($order->shipping_address);
               
               $delivery_boy = AssignOrder::where('order_id',$order->order_id)->first();
               
               if(!empty($delivery_boy)){
                $delivery_boy_details = DeliveryBoy::where('id',$delivery_boy->delivery_boy_id)->first();
                    if(!empty($delivery_boy_details->user_id)){
                        $delivery_boy = User::where('id',$delivery_boy_details->user_id)->first('name');
                        $delivery_boy_name = $delivery_boy['name'];
                    }else{
                        $delivery_boy_name = 'NA';
                    }
                
               }else{
                $delivery_boy_name = 'NA';
               }
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $order->order_code);
                $sheet->setCellValue('C'.($i+2), $order->order_date);
                $sheet->setCellValue('D'.($i+2), $numProduct);
                $sheet->setCellValue('E'.($i+2), $customer_detail->name);
                $sheet->setCellValue('F'.($i+2), $customer_detail->address);
                $sheet->setCellValue('G'.($i+2), $order->pincode);
                $sheet->setCellValue('H'.($i+2), $customer_detail->phone); 
                $sheet->setCellValue('I'.($i+2), $order->shorting_hub);
                $sheet->setCellValue('J'.($i+2), $order->payment_method);
                $sheet->setCellValue('K'.($i+2), $order->grand_total);
                $sheet->setCellValue('L'.($i+2), $order->delivery_status);
                $sheet->setCellValue('O'.($i+2), $order->payment_status);
                $sheet->setCellValue('P'.($i+2), $customer_detail->email);
                $sheet->setCellValue('Q'.($i+2), $order->referal_code); 
                $sheet->setCellValue('R'.($i+2), $delivery_boy_name);
                // $sheet->setCellValue('S'.($i+2),  @$slot); 
                // $sheet->setCellValue('T'.($i+2),  @$d_slottime);               
                $i++;
       
        }

        $filename = "inhouseorders.xlsx";
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }

        public function orders_productexport_final(){
        $from = $_GET['date_from_export'];
        $to = $_GET['date_to_export'];
        $sorting_hub_id = $_GET['sorting_hub_id'];
        $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
        $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
        $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

        if(empty($from) ||empty($to)){
            flash(translate('Please select start date and end date.'))->error();
            return back(); 
        } 
        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $result = true;
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        // $orders = DB::connection('mysql2')->table('final_orders')
        $orders = DB::connection('mysql')->table('final_orders')
                          ->leftjoin('order_details','final_orders.order_id','=','order_details.order_id')
                          ->leftjoin('products','products.id','=','order_details.product_id')
                          ->leftjoin('users','users.id','=','final_orders.sortinghub_id');
                          $from = date('Y-m-d',strtotime($from));
                          $to = date('Y-m-d',strtotime($to.' +1 day'));

        if(isset($result)){
            $orders = $orders->where('sortinghub_id', $sorting_hub_id);
            
            if(isset($from)){
                // if($from == $to){
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to));
                // }else{
                //     $from = date('Y-m-d',strtotime($from));
                //     $to = date('Y-m-d',strtotime($to.' +1 day'));
                // }

                if($from != $to){
                    $orders = $orders->where('final_orders.order_date','>=', $from)->where('final_orders.order_date','<=', $to);
                }else{
                    $orders = $orders->whereDate('final_orders.order_date',$from);
                }
            }
            
        }else{

            if(isset($from)){

                if($from != $to){
                    $orders = $orders->where('final_orders.order_date','>=', $from)->where('final_orders.order_date','<=', $to);
                }else{
                    $orders = $orders->whereDate('final_orders.order_date',$from);
                }
            }
            
        }
        $orders = $orders->orderBy('final_orders.order_date','desc')
                          ->select('final_orders.order_code','final_orders.order_code','final_orders.order_date','final_orders.order_date','order_details.quantity as qty','final_orders.no_of_items','final_orders.shipping_address','final_orders.shipping_address','final_orders.pincode','products.hsn_code','products.name as product_name','users.name as shorting_hub','products.tax','order_details.peer_discount','order_details.price','order_details.shipping_cost','final_orders.payment_method','final_orders.payment_status','final_orders.grand_total','final_orders.grand_total','order_details.delivery_status','order_details.updated_at','final_orders.referal_code')
                          ->get();
        


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Order Code');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Num. of Products');
        $sheet->setCellValue('E1', 'Customer');
        $sheet->setCellValue('F1', 'Address');
        $sheet->setCellValue('G1', 'Pin Code');
        $sheet->setCellValue('H1', 'HSN Code');
        $sheet->setCellValue('I1', 'Sorting HUB');
        $sheet->setCellValue('J1', 'Product  Name');
        $sheet->setCellValue('K1', 'Qty');
        $sheet->setCellValue('L1', 'GST Rate');
        $sheet->setCellValue('N1', 'Discount Price');
        $sheet->setCellValue('M1', 'Price');
        $sheet->setCellValue('O1', 'Shipping Cost');
        $sheet->setCellValue('P1', 'Payment Mode');
        $sheet->setCellValue('Q1', 'Amount');
        $sheet->setCellValue('R1', 'Delivery Status');
        $sheet->setCellValue('S1', 'Delivery Date');
        $sheet->setCellValue('T1', 'Payment Method');
        $sheet->setCellValue('U1', 'Payment Status');
        $sheet->setCellValue('V1', 'Email');
        $sheet->setCellValue('W1', 'Peer Code');
        $sheet->setCellValue('X1', 'Phone Number');
        $i = 0;

            foreach($orders as $k=>$v)
            {
                $date =  $v->order_date;
                $customer_detail = json_decode($v->shipping_address);
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $v->order_code);
                $sheet->setCellValue('C'.($i+2), $date);
                $sheet->setCellValue('D'.($i+2), $v->no_of_items);
                $sheet->setCellValue('E'.($i+2), $customer_detail->name);
                $sheet->setCellValue('F'.($i+2), $customer_detail->address);
                $sheet->setCellValue('G'.($i+2), $v->pincode);
                $sheet->setCellValue('H'.($i+2), $v->hsn_code);
                $sheet->setCellValue('I'.($i+2), $v->shorting_hub);
                $sheet->setCellValue('J'.($i+2), $v->product_name);
                $sheet->setCellValue('K'.($i+2), $v->qty);
                $sheet->setCellValue('L'.($i+2), $v->tax);
                $sheet->setCellValue('M'.($i+2), $v->price);
                $sheet->setCellValue('N'.($i+2), $v->peer_discount);
                $sheet->setCellValue('O'.($i+2), $v->shipping_cost);
                $sheet->setCellValue('P'.($i+2), $v->payment_method);
                $sheet->setCellValue('Q'.($i+2), $v->grand_total);
                $sheet->setCellValue('R'.($i+2), $v->delivery_status);
                $sheet->setCellValue('S'.($i+2), $v->updated_at);
                $sheet->setCellValue('T'.($i+2), $v->payment_method);
                $sheet->setCellValue('U'.($i+2), $v->payment_status);
                $sheet->setCellValue('V'.($i+2), $customer_detail->email);
                $sheet->setCellValue('W'.($i+2), $v->referal_code); 
                $sheet->setCellValue('X'.($i+2), $customer_detail->phone); 
                $i++;

            }

        $filename = "inhouseorders.xlsx";
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
         
    }

     public function createPartnerByAdmin(){
        if (!Auth::check()) {
                return Redirect::route('login');
            }

        if (!((auth()->user()->user_type == 'callcenter') && (auth()->user()->status == 1))) {
                  Auth::logout();
                return Redirect::route('login');

            }else{
                    return view('callcenter.create_partner_callcenter');
                 }
        }
 
     public function storePartnerCreateByAdmin(Request $request){
         //dd($request->all());
         $request->validate([
             'name'=>'required',
             'phone'=>'required|digits_between:10,10|numeric',
             'address'=>'required',
             'city'=>'required',
             'pincode'=>'required',
             'state'=>'required',
             'block_id'=>'required'
         ]);
 
         $check = User::where('phone',$request->phone)->first();
         if(!is_null($check)){
             flash('Email or Phone already exist');
             return back();
         }
         try{
             DB::beginTransaction();
            
             $user = new \App\User;
             $user->name = $request->name;
             $user->phone = $request->phone;
             $user->user_type = 'partner';
             $user->peer_partner = 1;
             $user->password = Hash::make('Rozana@123');
             $user->city = $request->city;
             $user->postal_code = $request->pincode;
             $user->address = $request->address;
             $user->state = $request->state;
             $user->country = "India";
             $user->status = 1;
             $user->email_verification = 1;
 
             if($user->save()){
                 // Creating dummy email if user has no email address
                 $email = (empty($request->email)) ? $user->id."@xyz.com": $request->email;
                 \App\User::where('id',$user->id)->update(['email'=>$email]);
                 // create peer partner
                 $parent = (empty($request->parent_id)) ? 35 : $request->parent_id;
                 $partner = new \App\PeerPartner;
                 $partner->name = $request->name;
                 $partner->email = $email;
                 $partner->phone = $request->phone;
                 $partner->peer_type = 'sub';
                 $partner->user_id = $user->id;
                 $partner->address = $request->address;
                 $partner->parent = $parent;
                 $partner->zone = $request->zone;
                 $partner->save();
                 // create an address in address table
                 $address = new \App\Address;
                 $address->name = $request->name;
                 $address->user_id = $user->id;
                 $address->address = $request->address;
                 $address->country = "India";
                 $address->city = $request->city;
                 $address->state = $request->state;
                 $address->postal_code = $request->pincode;
                 $address->phone = $request->phone;
                 $address->set_default = 1;
                 $address->block_id = $request->block_id;
                 $address->village = $request->village;
                 $address->save();
 
             }
             DB::commit();
             flash('Peer Partner added successfully')->success();
             return back();
 
         }
         catch(\Exception $e){
             DB::rollabck();
             flash('Something went wrong');
             return back();
         }
         
 
 
     }



}

