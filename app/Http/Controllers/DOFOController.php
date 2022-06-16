<?php




namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Staff;
use App\DOFO;
use App\Role;
use App\DeliveryBoy;
use App\AssignOrder;
use Auth;
use App\Traits\OrderTrait;
use CoreComponentRepository;
use App\User;
use App\Order;
use App\OrderDetail;
use DB;
use App\ImportDOFO;
use App\ImportDofoOrders;
use App\DofoOrderExport;
use Excel;
use App\Category;
use App\ShortingHub;
use Session;
use App\MappingProduct;
use App\Product;
use App\ProductStock;
use App\PeerPartner;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use App\OrderReferalCommision;
use App\ReferalUsage;
use App\Wallet;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use DateTime;
use App\Cluster;
use App\SubOrder;
use Carbon\Carbon;
use App\CsvOrders;
use App\ImportCSVOrders;
use App\Jobs\CSVOrder;
use App\PeerSetting;


class DOFOController extends Controller
{
    //

    use OrderTrait;

    public function __construct(){
       
       
    }

    public function index(REQUEST $request){
        // $dofo = DOFO::all();
        $dofo = DOFO::where('status',1);
        $search = "";
        if($request->has('search')){
            $search = $request->search;
            
            $dofo->where('email','like','%'.$request->search.'%')->orWhere(function($query) use($search){
                return $query->where('phone',$search);
                       
            });

        }
        $dofo = $dofo->paginate(50);
        
        return view('DOFO.index', compact('dofo','search'));
    }


    public function create()
    {
        $roles = Role::all();
        return view('DOFO.create', compact('roles'));
    }


    public function store(Request $request)
    {
        if(DOFO::where('email', $request->email)->first() == null){
            $dofo = new DOFO;
            $dofo->name = $request->name;
            $dofo->email = $request->email;
            $dofo->phone = $request->mobile;
            $dofo->pincode = $request->pin;
            $dofo->address = $request->address;
            if($dofo->save()){
                    flash(translate('DOFO has been inserted successfully'))->success();
                    return redirect()->route('DOFO.index');
            }
        }

        flash(translate('Email already used'))->error();
        return back();
    }


    public function destroy($id)
    {
        if(DOFO::destroy($id)){
            flash(translate('DOFO has been deleted successfully'))->success();
            return redirect()->route('DOFO.index');
        }

        flash(translate('Something went wrong'))->error();
        return back();
    }


    public function __call($method,$args){
        if(!empty($args)){
            $email = $args[0]['email'];
            $phone = $args[0]['phone'];
            $getDOFO = DOFO::where('email',$email)->where('status','=',1)->first();
            $response = '';
            if(!empty($getDOFO)){
                $response = 1;
            }else{
                $getDOFO = DOFO::where('phone',$phone)->where('status','=',1)->first();
                if(!empty($getDOFO)){
                    $response = 1;
                }else{
                    $response = 0;

                }
                
            }

        }else{
            $response = 0;
        }
        return $response;

    }


    public function updateDeliveryBoy($shortid,$orderid){
        $deliveryBoy = DeliveryBoy::where('sorting_hub_id',$shortid)->pluck('id')->toArray();
        $order = Order::where('id',$orderid)->first();
        if(!empty($deliveryBoy)){
            $random_keys = array_rand($deliveryBoy,1);
            $assignOrder = AssignOrder::create([
                'delivery_boy_id' => $deliveryBoy[$random_keys],
                'order_id' => $orderid,
                'status' => 1,
                'is_view' => 1,
                'created_at' => $order['created_at'],
                'updated_at' => $order['updated_at'],
            ]);

            if($assignOrder){
                return true;
            }else{
                return false;
            }
       }

    }


    public function updateStatus(Request $request)
    {
        $dofo = DOFO::findOrFail($request->id);
        $dofo->status = $request->status;
        if($dofo->save()){
            return 1;

        }else{
            return 0;

        }
        
    }

    public function getDateTime($request){
        $datetime = "";
        $order_date = $request->order_date;
        $order_time = $request->order_time;
        
        if($order_date != null && $order_time != null){
        $datetime = $order_date.' '.date("H:i:s", strtotime($order_time));
        }elseif($order_date != null && $order_time == null){
            $datetime = $order_date.' '. date('H:i:s');

        }elseif($order_time != null && $order_date == null){
            $datetime = date('Y-m-d').' '.date("H:i:s", strtotime($order_time));
        }

        $request->offsetUnset('order_date');

        return $datetime;

    }

    public function dofoOrder(Request $request){
        $id = Auth()->user()->id;        
        // $orderID = $this->orders($id);
        $payment_status = null;
        $delivery_status = null;
        $pay_type = null;
        $sort_search = null;
        $admin_user_id = User::where('user_type', 'admin')->first()->id;


        $orders = Order::where("dofo_status",1)
                  ->where('log',0)  
                  ->orderBy('created_at', 'desc')
                  ->distinct('code');       
                    

        if ($request->payment_type != null){
            $orders = $orders->where('payment_status', $request->payment_type);
            $payment_status = $request->payment_type;
        }
        if ($request->delivery_status != null) {
            $orders = $orders->where('order_status', $request->delivery_status);
            // $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.delivery_status', $request->delivery_status);
            $delivery_status = $request->delivery_status;
        }
        if($request->pay_type!=null){
            $orders = $orders->where('payment_type', $request->pay_type);
            $pay_type = $request->pay_type;
        }
        if(!empty($request->dateRangeStart) && !empty($request->dateRangeEnd)){
            $orders = $orders->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->dateRangeStart)))->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->dateRangeEnd)));

        }
        $orders = $orders->select('id','code','grand_total','shipping_address','shipping_pin_code','date','guest_id','payment_type','payment_status')->paginate(50);
        return view('DOFO.orderList', compact('orders','payment_status','delivery_status', 'admin_user_id', 'pay_type'));

    }

    public function UploadDOFOUsers(REQUEST $request){
        //info($request->all());
        if($request->hasFile('bulk_file')){
            // dd($request->bulk_file);
            Excel::import(new ImportDOFO, request()->file('bulk_file'));
        }
        flash(translate('DOFO exported successfully'))->success();
        return back();

    }

    public function createDofoOrders(){

        $categories = Category::all();
        $shorting_hub = ShortingHub::all();
        return view('DOFO.create_dofo_orders', compact('categories','shorting_hub'));
    }


    public function getUserDetail(int $email){
        $dofo = DOFO::where('id',$email)->first();

        if(!empty($dofo)){
            return $dofo;
        }else{
            return 400;
        }


    }

    public function addToCartDofoOrders(REQUEST $request){

        $product = Product::find($request->product_id);
        if(!is_null($product)){
        $str = '';
        $data = array();
        $data['id'] = $request->product_id;
        $data['price'] = $request->price;
        $data['dicounted_price'] = 0;
        $data['shipping'] = 0;
        $data['quantity'] = $request->quantity;
        $data['shipping_type'] = "home_delivery";
        if ($product->digital != 1) {
            //Gets all the choice values of customer choice option and generate a string like Black-S-Cotton
            if(!is_null($product)){
                foreach (json_decode($product->choice_options) as $key => $choice) {
                    if($str != null){
                        $str .= '-'.str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                    }
                    else{
                    
                        $str .= str_replace(' ', '', $request['attribute_id_'.$choice->attribute_id]);
                    
                    }
                }
            }
        }

        if($product->tax_type == 'percent'){
            $tax = ($request->price*$product->tax)/100;
        }
        elseif($product->tax_type == 'amount'){
            $tax = $product->tax;
        }
        $data['tax'] = $tax;
        $data['variant'] = $str;
        $data['product_referral_code'] = "";
        // $request->session()->pull('dofoOrder', []);
        // dd( $request->session()->get('dofoOrder'));
        if($request->session()->has('cart') && count($request->session()->get('cart')) !=null){
            $foundInCart = false;
            $dofocart = collect();

            foreach ($request->session()->get('cart') as $key => $cartItem){
                if($cartItem['id'] == $request->product_id){
                        $foundInCart = true;
                        $cartItem['quantity'] += $request->quantity;
                }
                $dofocart->push($cartItem);
            }

            if (!$foundInCart) {
                $dofocart->push($data);
            }
            $request->session()->put('cart', $dofocart);

          

        }else{

            $dofocart = collect([$data]);
            $request->session()->put('cart', $dofocart);
           

        }

        if($request->has('xls_upload')){
            return 1;
        }


        return view('DOFO.dofoAddToCart');
    }

    }


    public function storeDofoOrders(REQUEST $request){
        if(!empty($request->order_date)){
            $request->order_date = date('Y-m-d', strtotime($request->order_date));

        }

        $id = $request->email;
        $dofoDetails = DOFO::where('id',$id)->first();
        $district_id = \App\Area::where('pincode',$request->pincode)->first('district_id');
        $city = \App\City::where('id',$district_id['district_id'])->first();
        $state = \App\State::where('id',@$city->state_id)->first();
        $data['name'] = $dofoDetails->name;
        $data['email'] = $dofoDetails->email;
        $data['address'] = $request->address;
        $data['country'] = 'India';
        $data['city'] = @$city->name;
        $data['postal_code'] = $request->pincode;
        $data['phone'] = $dofoDetails->phone;
        $data['state'] = @$state->name;
        $data['checkout_type'] = "";
        @setcookie('pincode',$request->pincode,time()+60*60*24*30,'/');
        $request->session()->put('shipping_info',$data);
        if(!empty($request->peercode)){
            $coupon = PeerPartner::where(['code' => strtoupper($request->peercode), 'verification_status' => 1, 'peertype_approval' => 0])->first();
            if( $coupon != null){
                $request->session()->put('partner_id', $coupon->user_id);
                $request->session()->put('referal_discount', $coupon->discount);
                $request->session()->put('referal_code', $coupon->code);
            }

        }

        $checkout = new CheckoutController;
        $checkout->checkout($request);
        $request->session()->forget('partner_id');
        $request->session()->forget('referal_discount');
        $request->session()->forget('referal_code');
        \Cookie::forget('pincode');
        if($request->has('xls_upload')){
            return 1;
        }
        return redirect()->back();


    }


    public function getProductBySortingHub(Request $request){
        $peer_code = $request->peer_code;
        $id = $request->hub_id;
        $sortinghub_id = $request->hub_id;
        $peerPartner = PeerPartner::where('code','like', '%' . $peer_code . '%')->first();
        if(empty($peerPartner)){
            $peer_code = null;

        }

        $added_products = MappingProduct::where(['sorting_hub_id'=>$id,'published'=>1])->pluck('product_id')->toArray();
        $products = Product::where('subcategory_id', $request->subcategory_id)->whereIn('id', $added_products)->get();
        
        return view('DOFO.dofoproduct', compact('products', 'sortinghub_id','peer_code'));
      }

      public function exportDofoOrders() 
      {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setCellValue('A1', 'Sr No.');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Order No.');
        $sheet->setCellValue('D1', 'Party  Name');
        $sheet->setCellValue('E1', 'Address');
        $sheet->setCellValue('F1', 'Pincode');
        $sheet->setCellValue('G1', 'Phone');
        $sheet->setCellValue('H1', 'Email ID');
        $sheet->setCellValue('I1', 'Sorting HUB');
        $sheet->setCellValue('J1', 'Product  Name');
        $sheet->setCellValue('K1', 'Qty');
        $sheet->setCellValue('L1', 'GST Rate');
        $sheet->setCellValue('M1', 'Price');
        $sheet->setCellValue('N1', 'Discount Price');
        $sheet->setCellValue('O1', 'GST AMT');
        $sheet->setCellValue('P1', 'Shipping Cost');
        $sheet->setCellValue('Q1', 'Payment Mode');
        $orders = Order::where('dofo_status',1)->orderBy('created_at','DESC')->get();
        $i = 0;
        foreach($orders as $key => $search)
        {
            
            $shorting_hub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $search->shipping_pin_code . '"]\')')->first();
            $address = !empty($search->shipping_address)?json_decode($search->shipping_address):[];
            foreach($search->orderDetails as $k=>$v)
            {
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), date('m/d/Y H:i:s', $search->date));
                $sheet->setCellValue('C'.($i+2), $search->code);
                $sheet->setCellValue('D'.($i+2), @$address->name);
                $sheet->setCellValue('E'.($i+2), @$address->address);
                $sheet->setCellValue('F'.($i+2), $search->shipping_pin_code);
                $sheet->setCellValue('G'.($i+2), @$address->phone);
                $sheet->setCellValue('H'.($i+2), @$address->email);
                $sheet->setCellValue('I'.($i+2), @$shorting_hub->user['name']);
                $sheet->setCellValue('J'.($i+2), @$v->product['name']);
                $sheet->setCellValue('K'.($i+2), $v->quantity);
                $sheet->setCellValue('L'.($i+2), 0);
                $sheet->setCellValue('M'.($i+2), $v->price);
                $sheet->setCellValue('N'.($i+2), ($v->price-$v->peer_discount));
                $sheet->setCellValue('O'.($i+2), 0);
                $sheet->setCellValue('P'.($i+2), $v->shipping_cost);
                $sheet->setCellValue('Q'.($i+2), $search->payment_type);
                $i++;

            }
       
        }
        

        // $writer = new Xlsx($spreadsheet);
        // $writer->save('dofo_orders.xlsx');
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        $writer->save('dofo_orders.xlsx');
        $filename = "dofo_orders.xlsx";
        return response()->download(base_path()."/dofo_orders.xlsx", $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
      }

      public function accessSwitch(){
        $sorting_hubs = ShortingHub::with('cluster')->get();
        $global = DB::table('global_switch')->where('name','Global')->first();
        $peer = DB::table('global_switch')->where('name','Peer')->first();
        $delivery_boy = DB::table('global_switch')->where('name','Delivery Boy')->first();
        return view("DOFO/access_switch",compact("sorting_hubs","global","peer","delivery_boy"));


      }

      public function changeAccessSwitch(REQUEST $request){
          $shorting_hub_id = $request->id;
          $switch_status = $request->status;
          $change_status = ShortingHub::where('id',$shorting_hub_id)->update(["access_switch"=>$switch_status]);
          
          if($change_status){
              $status = 1;
          }else{
            $status = 0;

          }
          return $status;



      }

      public function changeGlobalAccessSwitch(REQUEST $request){
        $global_id = $request->id;
        $global_status = $request->status;
        $change_status = DB::table('global_switch')->where('id',$global_id)->update(["access_switch"=>$global_status]);
        
        if($change_status){
            $status = 1;
        }else{
          $status = 0;

        }
        return $status;

      }


      public function updateCommission($order_id){
        $order = Order::findOrFail($order_id);
        $status = 'delivered';
        foreach($order->orderDetails as $key => $orderDetail){
            $orderDetail->delivery_status = 'delivered';
            // if($status == 'delivered' && $order->payment_status == 'paid'){
            if(($status == 'delivered' && $order->payment_status == 'paid') || ($status == 'delivered' && $order->payment_type == "cash_on_delivery")){
                // echo 'pp'; die;
                    $OrderReferalCommision = OrderReferalCommision::where('order_id', $order->id)->first();
                    $partner = PeerPartner::where('user_id', $OrderReferalCommision->partner_id)->first();

                    if(!empty($OrderReferalCommision) && $OrderReferalCommision->wallet_status == 0){
                        $partner = PeerPartner::where('user_id', $OrderReferalCommision->partner_id)->first();

                         if(!empty($partner) && $partner->verification_status == 1 && $partner->parent != 0){
                            $select_partner = PeerPartner::where('id', $partner->parent)->first();
                            $master_partner = User::find($select_partner->user_id);
                            $mastertotal_balance = $master_partner->balance+$OrderReferalCommision->master_discount;
                            $master_partner->balance = $mastertotal_balance;
                            $master_partner->save();

                            // $to = $select_partner->phone; 
                            // $from = "RZANA";
                            // $tid  = "1707163117111494696"; 

                            // $msg = "Hello Rozana Master Peer, an order has been delivered to your customer using ".$partner->code." Peer Code. You have received ".$OrderReferalCommision->master_discount." points in your Rozana wallet. To review your points please log into your Rozana dashboard. Thank you for helping make Rozana a part of everyone’s daily lives. Feel free to reach out to us for any concerns or queries on +91 9667018020. Team Rozana";
                            //     mobilnxtSendSMS($to,$from,$msg,$tid);
                         }   

                        if(!empty($partner) && $partner->verification_status == 1){
                            $peer_partner = User::find($partner->user_id);
                            $total_balance = $peer_partner->balance+$OrderReferalCommision->referal_commision_discount;
                            $peer_partner->balance = $total_balance;
                            
                            if($peer_partner->save()){

                                $wallet = new Wallet;
                                $wallet->user_id = $partner->user_id;
                                $wallet->amount = $OrderReferalCommision->referal_commision_discount;
                                $wallet->payment_method = 'referral';
                                $wallet->payment_details = null;
                                $wallet->save();

                                $OrderReferalCommision->wallet_status = 1;
                                $OrderReferalCommision->save();
                            }
                        }
                }else{
                    return 0;
                }
            }

            $orderDetail->save();
        }
        return 1;

      }

      public function updateOldCommission(REQUEST $request){
        $order = Order::where('code',$request->code)->first();
        if(!empty($order)){
            $date = $order->created_at->timestamp;
            $OrderReferalCommision = OrderReferalCommision::where('order_id', $order->id)->first();
            $ReferralUsage = ReferalUsage::where('order_id', $order->id)->first();
            $OrderReferalCommision->created_at = $date;
            $ReferralUsage->created_at = $date;
            $OrderReferalCommision->save();
            $ReferralUsage->save();
            foreach($order->orderDetails as $key => $orderDetail){
                $orderDetail->created_at = $date;
                $orderDetail->save();
            }
            $update = $this->updateCommission($order->id);
            if($update == 0){
                flash(translate('Commission has already been updated.'))->success();

            }else{
                flash(translate('Commission has  been updated.'))->success();
            }
            
            

        }else{
            flash(translate('Wrong Order Code.'))->error();
            
        }
        return back();

      }


      public function uploadDofoOrders(REQUEST $request){
        $randDate = new DateTime();
        if($request->hasFile('dofo_orders')){
           $reader = new Xlsx();
            $arr_file = explode('.', $_FILES['dofo_orders']['name']);
            $extension = end($arr_file);
            $file = $request->file('dofo_orders');
            $imagePath = $file->getPathName();
            $spreadsheet = $reader->load($_FILES['dofo_orders']['tmp_name']);
            $sheetData = array_filter($spreadsheet->getActiveSheet()->toArray());
            $i=1;
            $loopRun = count($sheetData) - $i;
            
            $request->request->set('xls_upload',1);
            
            for($i;$i<=$loopRun;$i++){
                    if($sheetData[$i][0] != null){
                        $email = $sheetData[$i][0];
                        $dofoDetails = DOFO::where('email',$email)->first();
                        if(!empty($dofoDetails)){
                            $addToCart = array();
                            $product_ids = explode(',',$sheetData[$i][1]);
                            $product_qty = explode(',',$sheetData[$i][2]);
                            $shortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $dofoDetails->pincode . '"]\')')->first();
                            if($sheetData[$i][5] == null){
                                $randDate->setTime(mt_rand(7, 23), mt_rand(0, 59),mt_rand(0, 59));
                                $random_time = $randDate->format('h:i:s a');
                                $request->request->set('order_time',$random_time);
                            }else{
                                $request->request->set('order_time',$sheetData[$i][5]);
                            }

                            if($sheetData[$i][6] != null){
                                $request->request->set('order_code',$sheetData[$i][6]);

                            }
                            $request->request->set('order_date',$sheetData[$i][4]);
                            $request->request->set('email',$dofoDetails->id);
                            $request->request->set('pincode',$dofoDetails->pincode);
                            $request->request->set('address',$dofoDetails->address);
                            $request->request->set('payment_option','cash_on_delivery');
                            if($sheetData[$i][3] != null){
                                $request->request->set('peercode',$sheetData[$i][3]);
                            }
                            foreach($product_ids as $k=>$v){
                                $product_price = MappingProduct::where(['sorting_hub_id'=>@$shortingHub->user_id,'product_id'=>$v])->first();
                                $request->request->set('product_id',$v);
                                $request->request->set('quantity',$product_qty[$k]);
                                if(@$product_price->selling_price == 0){
                                    $product_stock = ProductStock::where('product_id',$v)->first();
                                    $request->request->set('price',@$product_stock->price);
                                }else{
                                    $request->request->set('price',$product_price->selling_price);
                                }
                            $this->addToCartDofoOrders($request);
                            }
                            
                        }
                        $this->storeDofoOrders($request);
                        
                }

            }
            return back();
            
        }

      }


      public function testExcelDofoOrders(REQUEST $request){
            $reader = new Xlsx();
            $arr_file = explode('.', $_FILES['dofo_orders_test']['name']);
            $extension = end($arr_file);
            $file = $request->file('dofo_orders_test');
            $imagePath = $file->getPathName();
            $spreadsheet = $reader->load($_FILES['dofo_orders_test']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet()->toArray();
            dd($sheetData );
        

      }


      public function uploadPeerPartner(REQUEST $request){
        $reader = new Xlsx();
        $arr_file = explode('.', $_FILES['peer_upload']['name']);
        $extension = end($arr_file);
        $file = $request->file('peer_upload');
        $imagePath = $file->getPathName();
        $spreadsheet = $reader->load($_FILES['peer_upload']['tmp_name']);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        $i=1;
        $loopRun = count($sheetData) - $i;
        for($i;$i<=$loopRun;$i++){
            $checkPeer = $sheetData[$i][5];
            if($checkPeer == 1){
                $checkUser = User::where(['email'=>$sheetData[$i][1],'peer_partner'=>1])->first();
                if($checkUser == null){
                    $createUser = User::where(['email'=>$sheetData[$i][1]])->first();
                    $peer_type = 'sub';
                    if($createUser == null){
                        $createUser = new User;
                        $createUser->user_type = 'partner';
                        $createUser->name = $sheetData[$i][0];
                        $createUser->email = $sheetData[$i][1];
                        $createUser->password = Hash::make('test@123');
                        $createUser->phone = $sheetData[$i][2];
                        $createUser->address = $sheetData[$i][4];
                        $createUser->peer_partner = 1;
                    }else{

                        $createUser->user_type = 'partner';
                        $createUser->peer_partner = 1;

                    }
                    
                    
                    if($createUser->save()){
                        $defaultmaster = 'defaultpeer@rozana.in';
                        $defaultid = PeerPartner::where('email', $defaultmaster)->first('id'); 
                        $p_id = $defaultid->id;
                        $peer_partner = new PeerPartner;
                        $peer_partner->user_id = $createUser->id;
                        $peer_partner->name = $createUser->name;
                        $peer_partner->peer_type = $peer_type;
                        $peer_partner->parent = $p_id;
                        $peer_partner->email = $createUser->email;
                        $peer_partner->phone = $createUser->phone;
                        $peer_partner->address = $createUser->address;
                        $peer_partner->verification_status = 0;
                        $peer_partner->dofo_peer = 1;
                        $peer_partner->save();
                    }
                }
                

            }
        }

        flash(translate('Peer has been stored.'))->success();
        return back();

      }


      public function changeStatusPurpose(REQUEST $request){
          $status = 0;
          $data = array();

        if($request->purpose == "delivery_boy"){

            $deleteDeliveryBoy = AssignOrder::where('order_id',$request->order_id)->delete();

            if($deleteDeliveryBoy){
                $status = 1;

            }else{
                $status = 0;
            }
        }else{
            $updateOrder = Order::where('id',$request->order_id)->update([
                'order_status'=>$request->delivery_status
            ]);
            if($updateOrder){
                $updateDelivery = OrderDetail::where('order_id',$request->order_id)->update([
                    'delivery_status'=> $request->delivery_status
                 ]);

            }

            if($updateDelivery){
                $status = 1;

            }else{
                $status = 0;
            }
        }

        $data['status'] = $status;
        $data['purpose'] = $request->purpose;

        return $data;

      }


      public function getDofoDeliveryBoy(){
         $dofoDeliveryBoy = DeliveryBoy::where('dofo_status',1)->get();

          return view('DOFO/deliveryBoyList',compact('dofoDeliveryBoy'));

      }

      public function createDofoDeliveryBoy(){
          $clusterHub = Cluster::get();
          return view('DOFO/createDofoDeliveryBoy',compact('clusterHub'));

      }

      public function getSortingHub(REQUEST $request){
        $cluster_hub_id = $request->cluster_hub_id;
        // $sortingHub = ShortingHub::where('cluster_hub_id',$cluster_hub_id)->get();
        $sortingHub = DB::table('shorting_hubs')
                      ->leftjoin('users','users.id','=','shorting_hubs.user_id')
                      ->where('shorting_hubs.cluster_hub_id',$cluster_hub_id)
                      ->select('users.name as name','shorting_hubs.user_id as id','shorting_hubs.area_pincodes as pincodes','shorting_hubs.user_id as user_id')
                      ->get(); 
        $status = 0;
        $data = array();
        if(!empty($sortingHub)){
            $status = 1;
            $data['sorting_hub'] = $sortingHub;
        }else{
            $status = 0;
            $data['sorting_hub'] = [];

        }
        $data['status'] = $status;
        return $data;
      }


      public function storeDofoDeliveryBoy(REQUEST $request){
        DB::beginTransaction();
        if(User::where('email', $request->email)->first() != null){
            flash(translate('Email already exists!'))->error();
            return back();
        }

        try{
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->user_type = 'staff';
            $user->email_verified_at = date("Y-m-d h:i:sa");
            $user->password = Hash::make('123456');

            if($user->save()){
                $deliveryboy = new DeliveryBoy;
                $deliveryboy->user_id = $user->id;
                $deliveryboy->cluster_hub_id = $request->cluster_hub_id;
                $deliveryboy->sorting_hub_id = $request->sorting_hub_id;
                $deliveryboy->phone = $request->phone;
                $deliveryboy->dofo_status = 1;
                // $deliveryboy->area_id = $request->area_id;

                if($deliveryboy->save()){
                        $staff = new Staff;
                        $staff->user_id = $user->id;
                        $staff->role_id = 6;

                        if($staff->save()){
                        DB::commit();
                            flash(translate('Delivery Boy has been inserted successfully'))->success();
                            return redirect()->route('DOFO.delivery-boy');
                        }else{
                            flash(translate('Somthing went wrong'))->error();
                            return redirect()->back();
                        }

                    }
            }

        }catch(\Throwable $e){
            DB::rollback();
            dd($e->getMessage());
        }

      }


      public function uploadDeliveryBoy(REQUEST $request){
        dd($request->all());

      }


      public function deleteDofoOrders(REQUEST $request){
          $status = 0;
        foreach($request->order_id as $key=>$value){
            $deleteOrder = DB::table('orders')
                    ->leftjoin('order_details','order_details.order_id','=','orders.id')
                    ->where('orders.id','=',$value)
                    ->delete();
        }

        if($deleteOrder){
            $status = 1;
        }

        return  $status;

      }



      public function cronDownloadOrders() 
      {
        
        // $spreadsheet = new Spreadsheet();
        // $sheet = $spreadsheet->getActiveSheet();
        
        // $sheet->setCellValue('A1', 'Sr No.');
        // $sheet->setCellValue('B1', 'Date');
        // $sheet->setCellValue('C1', 'Order No.');
        // $sheet->setCellValue('D1', 'Party  Name');
        // $sheet->setCellValue('E1', 'Address');
        // $sheet->setCellValue('F1', 'Pincode');
        // $sheet->setCellValue('G1', 'Phone');
        // $sheet->setCellValue('H1', 'Email ID');
        // $sheet->setCellValue('I1', 'Sorting HUB');
        // $sheet->setCellValue('J1', 'Product  Name');
        // $sheet->setCellValue('K1', 'Qty');
        // $sheet->setCellValue('L1', 'GST Rate');
        // $sheet->setCellValue('M1', 'Price');
        // $sheet->setCellValue('N1', 'Discount Price');
        // $sheet->setCellValue('O1', 'GST AMT');
        // $sheet->setCellValue('P1', 'Shipping Cost');
        // $sheet->setCellValue('Q1', 'Payment Mode');
        // $from = date('Y-m-d',strtotime("01-08-2021"));
        // $to = date('Y-m-d',strtotime("05-08-2021"));
        // $orders = Order::whereBetween('created_at', [$from, $to])->orderBy('created_at','DESC')->get();
        // $i = 0;
        // foreach($orders as $key => $search)
        // {
           
            
        //     $shorting_hub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $search->shipping_pin_code . '"]\')')->first();
        //     $address = !empty($search->shipping_address)?json_decode($search->shipping_address):[];
        //     foreach($search->orderDetails as $k=>$v)
        //     {
        //         $sheet->setCellValue('A'.($i+2), $i+1);
        //         $sheet->setCellValue('B'.($i+2), date('m/d/Y H:i:s', $search->date));
        //         $sheet->setCellValue('C'.($i+2), $search->code);
        //         $sheet->setCellValue('D'.($i+2), @$address->name);
        //         $sheet->setCellValue('E'.($i+2), @$address->address);
        //         $sheet->setCellValue('F'.($i+2), $search->shipping_pin_code);
        //         $sheet->setCellValue('G'.($i+2), @$address->phone);
        //         $sheet->setCellValue('H'.($i+2), @$address->email);
        //         $sheet->setCellValue('I'.($i+2), @$shorting_hub->user['name']);
        //         $sheet->setCellValue('J'.($i+2), @$v->product['name']);
        //         $sheet->setCellValue('K'.($i+2), $v->quantity);
        //         $sheet->setCellValue('L'.($i+2), @$v->product->tax);
        //         $sheet->setCellValue('M'.($i+2), $v->price);
        //         $sheet->setCellValue('N'.($i+2), ($v->price-$v->peer_discount));
        //         $sheet->setCellValue('O'.($i+2), 0);
        //         $sheet->setCellValue('P'.($i+2), $v->shipping_cost);
        //         $sheet->setCellValue('Q'.($i+2), $search->payment_type);
        //         $i++;

        //     }
       
        // }
        

        // // $writer = new Xlsx($spreadsheet);
        // // $writer->save('dofo_orders.xlsx');
        // $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        // $saveExcel = $writer->save('dofo_orders.xlsx');
        // $status = 0;
        // if($saveExcel){
        //     $status = 1;
            
        // }else{
        //     $status = 0;
        // }
        // return $status;
        //$filename = "dofo_orders.xlsx";
        // return response()->download(base_path()."/dofo_orders.xlsx", $filename, [
        //     'Content-Type' => 'application/vnd.ms-excel',
        //     'Content-Disposition' => 'inline; filename="' . $filename . '"'
        // ]);


        $from = date('Y-m-d',strtotime("11-12-2021"));
         $to = date('Y-m-d',strtotime("20-12-2021"));
        $sorting_hub_id = '';
        $deliveryStatus = '';
        $payStatus = '';
        $paymentStatus = '';

        // ini_set('max_execution_time', -1);
        // return Excel::download(new OrdersExport($sorting_hub_id,$from,$to,$deliveryStatus,$payStatus,$paymentStatus), 'inhouseorders.xlsx');

        
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
            $sorting_hub_id = $sorting_hub_id;
            $sorting_hub = ShortingHub::where('user_id', $sorting_hub_id)->first();
            $result = json_decode($sorting_hub['area_pincodes']);
        }else{
            $sorting_hub_id = $sorting_hub_id;
        }

        if(isset($result)){
            $orders = Order::whereIn('shipping_pin_code', $result);
            if(isset($from)){
                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                }

                if($from != $to){
                    $orders = $orders->whereBetween('created_at', [$from, $to]);
                }else{
                    $orders = $orders->whereDate('created_at',$from);
                }
            }
            if(isset($deliveryStatus) && $deliveryStatus != NULL){
                $orders = $orders->where('order_status', $deliveryStatus);
            }

            if(isset($payStatus) && $payStatus != NULL){
                $orders = $orders->where('payment_type', $payStatus);
            }

            if(isset($paymentStatus) && $paymentStatus != NULL){
                $orders = $orders->where('payment_status', $paymentStatus);
            }

            $orders = $orders->get();
            
        }else{

            if(isset($from)){

                if($from == $to){
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to));
                }else{
                    $from = date('Y-m-d',strtotime($from));
                    $to = date('Y-m-d',strtotime($to.' +1 day'));
                }
                
                if($from != $to){
                    $orders = Order::whereBetween('created_at', [$from, $to]);
                }else{
                    $orders = Order::whereDate('created_at',$from);
                }

                if(isset($deliveryStatus) && $deliveryStatus != NULL){
                    $orders = $orders->where('order_status', $deliveryStatus);
                }

                if(isset($payStatus) && $payStatus != NULL){
                    $orders = $orders->where('payment_type', $payStatus);
                }

                if(isset($paymentStatus) && $paymentStatus != NULL){
                    $orders = $orders->where('payment_status', $paymentStatus);
                }
                $orders = $orders->get();
            }else{
                $orders = Order::all();
            }
            
        }


        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        ini_set('max_execution_time', -1);
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
        $sheet->setCellValue('Y1', 'Category Name');
        $sheet->setCellValue('Z1', 'Unit');
        $sheet->setCellValue('AA1', 'GST Amount');
        $sheet->setCellValue('AB1', 'Customer Type');
        $sheet->setCellValue('AC1', 'Unit Price');

        $i = 0;

      
        foreach($orders as $key => $order)
        {
            
        $date = date("d/m/Y h:i:s A", $order->date);

        $numProduct = $order->orderDetails->where('order_id', $order->id)->sum('quantity');
        
        $delivery_peercode = ReferalUsage::where('order_id',$order->id)->first('referal_code');
        
        if(!empty($delivery_peercode)){
            $peercode = $delivery_peercode->referal_code;
        }else{
            $peercode = 'NA';
        }
        

        $address = json_decode($order->shipping_address);
        $phone = "";
        $usertype = "";

        if($order->user != null){
            $usertype = 'Customer';
            $customer = $order->user->name.' '.$address->phone;
            $phone = $address->phone;
        }else{
            $usertype = 'Guest';
            if(!empty($address->name) && !empty($address->phone)){
                $customer = $address->name;
                $phone = $address->phone;
            }else{
                $customer = 'Guest';
                $phone = '';
            }
        }

        $customer_detail = $customer;
        
        $getAssignedBoy = AssignOrder::where('order_id',$order->id)->first('delivery_boy_id');

        if($getAssignedBoy != NULL){
            $deliveryBoy = DeliveryBoy::where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
            $deliveryBoyName = User::where('id',$deliveryBoy['user_id'])->first('name');
            $deliveryBoyName = $deliveryBoyName['name'];
        }else{
            $deliveryBoyName = ' ';
        }
        
        $sortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
            if(!empty($sortingHub)){
              $sortingHub = $sortingHub->user->name;

            }else{
                $sortingHub = "Not Available";
            }
         
        if($order->wallet_amount == 0){
            $total_amount = $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('price') + $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('shipping_cost') - $order->orderDetails->where('delivery_status','!=','return')->where('order_id', $order->id)->sum('peer_discount');
        }else{
            $total_amount = $order->orderDetails->where('order_id', $order->id)->sum('price') + $order->orderDetails->where('order_id', $order->id)->sum('shipping_cost'); 
        }
 
        if($order->referal_discount > 0){
              $referral = $order->referal_discount;
        }

        if($order->wallet_amount > 0){
            $wallet = $order->wallet_amount;
            $total_amount = $total_amount - $wallet;
        }
         
        $amount = single_price($total_amount);
        
        $deliveryStatus = ucfirst(str_replace('_', ' ', $order->order_status));

        if($deliveryStatus == 'pending'){
            $deliveryDate = '';
        }else{
            $deliveryDate = date('d/m/Y H:i:s', strtotime($order->updated_at));
        }
        
        $paymentType = ucfirst(str_replace('_', ' ', $order->payment_type));

        if(!empty($address->address)){
            $user_address = $address->address;
        }else{
            $user_address = "";
        }
            foreach($order->orderDetails as $k=>$v)
            {
                $sheet->setCellValue('A'.($i+2), $i+1);
                $sheet->setCellValue('B'.($i+2), $order->code);
                $sheet->setCellValue('C'.($i+2), $date);
                $sheet->setCellValue('D'.($i+2), $numProduct);
                $sheet->setCellValue('E'.($i+2), $customer_detail);
                $sheet->setCellValue('F'.($i+2), $user_address);
                $sheet->setCellValue('G'.($i+2), $order->shipping_pin_code);
                $sheet->setCellValue('H'.($i+2), @$v->product->hsn_code);
                $sheet->setCellValue('I'.($i+2), $sortingHub);
                $sheet->setCellValue('J'.($i+2), @$v->product['name']);
                $sheet->setCellValue('K'.($i+2), $v->quantity);
                $sheet->setCellValue('L'.($i+2), $v->product['tax']);
                $sheet->setCellValue('M'.($i+2), $v->price);
                $sheet->setCellValue('N'.($i+2), ($v->price-$v->peer_discount));
                $sheet->setCellValue('O'.($i+2), $v->shipping_cost);
                $sheet->setCellValue('P'.($i+2), $order->payment_type);
                $sheet->setCellValue('Q'.($i+2), $amount);
                $sheet->setCellValue('R'.($i+2), $deliveryStatus);
                $sheet->setCellValue('S'.($i+2), $deliveryDate);
                $sheet->setCellValue('T'.($i+2), $paymentType);
                $sheet->setCellValue('U'.($i+2), $order->payment_status);
                $sheet->setCellValue('V'.($i+2), $address->email);
                $sheet->setCellValue('W'.($i+2),  $peercode); 
                $sheet->setCellValue('X'.($i+2),  $phone); 
                $sheet->setCellValue('Y'.($i+2),  @$v->product->category['name']);
                $sheet->setCellValue('Z'.($i+2),  @$v->product->stocks[0]->variant);
                $sheet->setCellValue('AA'.($i+2),  $v->tax);
                $sheet->setCellValue('AB'.($i+2),  $usertype);
                $sheet->setCellValue('AC'.($i+2),  ($v->price-$v->peer_discount)/$v->quantity);
                $i++;

            }
       
        }

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
        
        //$filename = "inhouseorders.xlsx";
        $status = 0;
        if($writer->save(base_path().'/public/orders_report/inhouseorders11-20DECEMBER-2021.xlsx')){
            $status = 1;

        }else{
            $status = 0;
        }
        return $status;
        // return response()->download(base_path()."/inhouseorders.xlsx", $filename, [
        //     'Content-Type' => 'application/vnd.ms-excel',
        //     'Content-Disposition' => 'inline; filename="' . $filename . '"'
        // ]);
      }


      public function deliverySchedule($orderId,$cart){ 
         $is_fresh = 0;
         $is_grocery = 0; 
         
         foreach($cart as $key=>$value){
            $product = Product::where('id',$value['id'])->first('category_id');
            if($product['category_id'] == '18' || $product['category_id']=='26' || $product['category_id']=='34' || $product['subcategory_id'] == '129' || $product['subcategory_id']==67 || $product['category_id']=='33' || $product['category_id']=='38' || $product['category_id']=='39' || $product['category_id']=='40'){
                $is_fresh = 1;      
            }else{
                $is_grocery = 1; 
            }
            if($is_fresh == 1 && $is_grocery == 1){
                break;
            }

         }
        // dd($orderId);
        $items['fresh'] = $is_fresh;
        $items['grocery'] = $is_grocery;
        $deliveryDetail['items'] = $items;
        $currentDateTime = new Carbon($orderId['created_at']);
        $deliveryDetail['delivery_date'] =  date("Y-m-d",strtotime($currentDateTime->addHour(24)));
        $deliveryDetail['delivery_slot'] = date("H:i:s",strtotime($currentDateTime->addHour(24)));
        
            foreach($items as $key => $item){
                
                if($item){
                    $orderSchedule = new SubOrder;
                    $orderSchedule->order_id = $orderId['id'];
                    $orderSchedule->delivery_name = $key;
                    $orderSchedule->delivery_type = 'normal';
                    $orderSchedule->delivery_date = $deliveryDetail['delivery_date'];
                    $orderSchedule->delivery_time = $deliveryDetail['delivery_slot'];
                    $orderSchedule->status = 1;
                    $orderSchedule->payment_status = 'paid';
                    $orderSchedule->created_at = $orderId['created_at'];
                    $orderSchedule->updated_at = $orderId['updated_at'];
                    $orderSchedule->save();
                }
                
            }
   // return true;
    
}


public function uploadCSVorders(REQUEST $request){
    if($request->hasFile('csv_dofo_orders')){
        // dd($request->bulk_file);
       Excel::import(new ImportCSVOrders, request()->file('csv_dofo_orders'));
    }
    $csvorders = CsvOrders::limit(1)->get();
    // CSVOrder::dispatch($csvorders)->delay(Carbon::now()->addSeconds(90));
    flash(translate('DOFO exported successfully'))->success();
    return back();
}

public function showCSVorders(){
    $csvorders = CsvOrders::orderBy('created_at','asc')->paginate(100);
    return view('DOFO.show_csv_orders',compact('csvorders'));
}

    public function csvJobsOrders(){
        $csvorders = CsvOrders::limit(200)->get();
        $randDate = new DateTime();
        $order_code = null;
        $peer_code = null;
        $cart_item = array();
        $str = "";
            if(!empty($csvorders)){
                
                foreach($csvorders as $key => $value){
                        if($value->email != null){
                            $email = $value->email;
                            $dofoDetails = DOFO::where('email',$email)->first();
                            
                            if(!empty($dofoDetails)){
                                $district_id = \App\Area::where('pincode',$dofoDetails->pincode)->first('district_id');
                                $city = \App\City::where('id',$district_id['district_id'])->first();
                                $state = \App\State::where('id',@$city->state_id)->first();
                                $product_ids = json_decode($value->product_ids);
                                $product_qty = json_decode($value->product_qty);
                                $shortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $dofoDetails->pincode . '"]\')')->first();
                                $cart_item['shorting_hub'] = $shortingHub;
                                //add shipping info start
                                    $cart_item['shipping_info']['name'] = $dofoDetails->name;
                                    $cart_item['shipping_info']['email'] = $dofoDetails->email;
                                    $cart_item['shipping_info']['address'] = $dofoDetails->address;
                                    $cart_item['shipping_info']['country'] = 'India';
                                    $cart_item['shipping_info']['city'] = @$city->name;
                                    $cart_item['shipping_info']['postal_code'] = $dofoDetails->pincode;
                                    $cart_item['shipping_info']['phone'] = $dofoDetails->phone;
                                    $cart_item['shipping_info']['state'] =  @$state->name;
                                    $cart_item['shipping_info']['checkout_type'] = '';
                                //add shipping info end

                                if($value->created_time == null){
                                    $randDate->setTime(mt_rand(7, 23), mt_rand(0, 59),mt_rand(0, 59));
                                    $cart_item['created_time'] = $randDate->format('h:i:s a');
                                }else{
                                    $cart_item['created_time'] = $value->created_time->format('h:i:s a');
                                }
                                

                                if($value->order_code != null){
                                    $cart_item['order_code'] = $value->order_code;
                                }else{
                                    $cart_item['order_code'] = "";
                                }

                                $cart_item['order_date'] = date('Y-m-d',strtotime($value->created_date));
                                $cart_item['payment_option'] = 'cash_on_delivery';
                                if($value->peer_code != null){
                                    $coupon = PeerPartner::where(['code' => strtoupper($value->peer_code), 'verification_status' => 1, 'peertype_approval' => 0])->first();
                                    if( $coupon != null){
                                        $cart_item['coupon']['partner_id'] = $coupon->user_id;
                                        $cart_item['coupon']['referal_discount'] = $coupon->discount;
                                        $cart_item['coupon']['referal_code'] = $coupon->discount;
                                        $cart_item['coupon']['code'] = $value->peer_code;
                                    }
                                }else{
                                    $cart_item['coupon']['partner_id'] = '';
                                    $cart_item['coupon']['referal_discount'] = '';
                                    $cart_item['coupon']['referal_code'] = '';
                                    $cart_item['coupon']['code'] = '';
                                }



                                $cart_item['total_price'] = 0;
                                //add product detail start 
                                foreach($product_ids as $k=>$v){
                                    $product = Product::find($v);
                                    if(!empty( $product)){
                                        $product_price = MappingProduct::where(['sorting_hub_id'=>@$shortingHub->user_id,'product_id'=>$v])->first();
                                        $cart_item['products'][$k]['id'] = $v;
                                        $cart_item['products'][$k]['quantity'] = empty($product_qty[$k])?1:$product_qty[$k];
                                        $product_stock = ProductStock::where('product_id',$v)->first();
                                        $cart_item['products'][$k]['price'] = @$product_stock->price;
                                        $cart_item['products'][$k]['purchase_price'] = @$product->unit_price;
                                        if(!empty($product_price)){
                                            if($product_price->selling_price != 0){
                                                $cart_item['products'][$k]['price'] = $product_price->selling_price;
                                                $cart_item['products'][$k]['purchase_price'] = $product_price->purchased_price;
                                            }
                                        }
                                        $cart_item['products'][$k]['dicounted_price'] = 0;
                                        $cart_item['products'][$k]['shipping'] = 0;
                                        $cart_item['products'][$k]['shipping_type'] = "home_delivery";

                                        if(@$product->tax_type == 'percent'){
                                            $cart_item['products'][$k]['tax'] = ($product_stock->price*$product->tax)/100;
                                        }
                                        elseif(@$product->tax_type == 'amount'){
                                            $cart_item['products'][$k]['tax'] = $product->tax;
                                        }else{
                                            $cart_item['products'][$k]['tax'] = 0.0;
                                        }

                                        $cart_item['products'][$k]['variant'] = @$product_stock->variant;
                                        $cart_item['products'][$k]['product_referral_code'] = "";
                                        $cart_item['total_price'] += $cart_item['products'][$k]['quantity']*$cart_item['products'][$k]['price'];
                                    }

                                }
                                //add product detail end
                                $order_status =  $this->storeOrders($cart_item,$value->id);
                            }
                            unset($cart_item);                            
                    }

                }
                return 1;
                
            }
    }

    public function storeOrders($cart_item,$id){
        $subtotal = 0;
        $tax = 0;
        $shipping = 0;
        $ref_dis = 0;
        $peer_percentage = 0;
        $total_peer_percent = 0;
        $total_discount_percent = 0;
        $total_master_percent = 0;
        $master_percentage = 0;
        DB::beginTransaction();
        try{
            $min_order_amount = (int)env("MIN_ORDER_AMOUNT");
            $free_shipping_amount = (int)env("FREE_SHIPPING_AMOUNT");
            $order = new Order;
            $order->guest_id = mt_rand(100000, 999999);
            $lastorderID = Order::orderBy('id', 'desc')->first();
            if(!empty($lastorderID)){
                $orderId = $lastorderID->id;
            }else{
                $orderId = 1;
            }
            $datetime = $this->getDateTimeCSV($cart_item);
            if(!empty($datetime)){
               
                $order->created_at = $datetime;
                $timestamp = strtotime($datetime);
                $date = date('d-m-Y', $timestamp);
                $time = date('H:i:s', $timestamp);

                if(strtotime($time) <= strtotime('20:00:00')){
                    $arr_time = explode(':',$time);
                    $ran_time = rand($arr_time[0]+1,19).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT);
                    $updatedatetime = date('Y-m-d H:i:s', strtotime("$date $ran_time"));
                }else{
                    
                    $ran_time = rand(7,20).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT).":".str_pad(rand(0,59), 2, "0", STR_PAD_LEFT);
                   
                    $new_date = date("d-m-Y", strtotime("$date +1 day"));
                    
                    $updatedatetime = date('Y-m-d H:i:s', strtotime("$new_date $ran_time"));
                }
                

                $order->updated_at = $updatedatetime;
               //dd( $order);
            }
            $order->shipping_address = json_encode($cart_item['shipping_info']);
            $order->shipping_pin_code = $cart_item['shipping_info']['postal_code'];
            $order->payment_type = $cart_item['payment_option'];
            $order->delivery_viewed = '0';
            $order->payment_status_viewed = '0';
            $order->payment_status = 'paid';
            $order->code = empty($cart_item['order_code'])?('ORD'.mt_rand(10000000,99999999).$orderId):$cart_item['order_code'];
            $order->date = strtotime($datetime);
            $order->dofo_status = 1;
            $order->order_status = 'delivered';
                if($cart_item['total_price']>=$free_shipping_amount)
                {
                    $order->total_shipping_cost = 0;
                }else{
                    $order->total_shipping_cost = \App\BusinessSetting::where('type', 'shipping_cost_admin')->first()->value;
                }
            $order->used_referral_code = $cart_item['coupon']['code'];
            if($order->save()){
                
                $this->deliveryScheduleCSV($order,$cart_item['products']);

                //Order Details Storing Start
                foreach ($cart_item['products'] as $key => $cartItem){
                    $product = Product::find($cartItem['id']);
                    $subtotal += $cartItem['price']*$cartItem['quantity'];
                    $tax += $cartItem['tax']*$cartItem['quantity'];
                    $product_variation = $cartItem['variant'];
                    $order_detail = new OrderDetail;
                    $order_detail->order_id  = $order->id;
                    $order_detail->seller_id = $product->user_id;
                    $order_detail->product_id = $product->id;
                    $order_detail->variation = $product_variation;
                    $order_detail->price = $cartItem['price'] * $cartItem['quantity'];
                    // $order_detail->tax = $cartItem['tax'] * $cartItem['quantity'];
                   
                    $order_detail->shipping_type = $cartItem['shipping_type'];
                    $order_detail->product_referral_code = $cart_item['coupon']['code'];
                    $order_detail->created_at = $datetime;
                    $order_detail->updated_at = $updatedatetime;
                    $order_detail->shipping_cost = $order->total_shipping_cost/count($cart_item['products']);
                    $profit_margin = $cartItem['price'] - $cartItem['purchase_price'];
                    if(isFreshInCategories($product->category_id) || isFreshInSubCategories($product->subcategory_id)){
                        $type="fresh";
                    }else{
                        $type = "grocery";
                    }
                    $sub_order_id = \App\SubOrder::where('order_id',$order->id)->where('delivery_name',$type)->first();
                    $order_detail->order_type = $type;
                    if(!is_null($sub_order_id)){
                        $order_detail->sub_order_id = $sub_order_id->id;
                    }
                    // $order_detail->sub_order_id = $sub_order_id->id;
                    
                    if(!empty($cart_item['coupon']['code'])){
                        $peer_discount_check = PeerSetting::where('product_id', '"'.$product->id.'"')->whereRaw('json_contains(sorting_hub_id, \'["' . $cart_item['shorting_hub']['user_id']. '"]\')')->latest('id')->first();
                        $customer_off_price = $peer_discount_check['customer_off'];
                        $total_discount_percent += substr($peer_discount_check['customer_discount'], 1, -1);
                        $peer_commission = $peer_discount_check['peer_commission'];
                        $peer_percentage += $peer_commission*$cartItem['quantity'];
                        $total_peer_percent += substr($peer_discount_check['peer_discount'], 1, -1);
                        $master_commission = $peer_discount_check['master_commission'];
                        $master_percent = substr($peer_discount_check['company_margin'], 1, -1);
                        $master_last_price = ($profit_margin * $master_percent)/100; 
                        $master_percentage += $master_last_price*$cartItem['quantity'];
                        $total_master_percent += substr($peer_discount_check['company_margin'], 1, -1);
                        $rozana_margin = $peer_discount_check['rozana_margin'];
                        $margin = $peer_discount_check['margin'];
                        $customer_purchase_price = $cartItem['price'] - $customer_off_price;
                        $ref_dis += $customer_off_price*$cartItem['quantity'];
                        $order_detail->peer_discount = $customer_off_price*$cartItem['quantity'];
                        $order_detail->sub_peer = $peer_commission*$cartItem['quantity'];
                        $order_detail->master_peer = $master_commission*$cartItem['quantity'];
                        $order_detail->orderrozana_margin = $rozana_margin*$cartItem['quantity'];
                        $order_detail->order_margin = $margin*$cartItem['quantity'];
                        $order_detail->quantity = $cartItem['quantity'];
                        $taxp = ($customer_purchase_price*100)/(100+$product->tax);
                        $tax = (($taxp*$product->tax)/100)*$cartItem['quantity'];
                    }else{
                        $peer_discount_check = PeerSetting::where('product_id', '"'.$product->id.'"')->latest('id')->first();
                        $rozana_margin = $peer_discount_check['rozana_margin'];
                        $margin = $peer_discount_check['margin'];
                        $order_detail->peer_discount = 0;
                        $order_detail->sub_peer = 0;
                        $order_detail->master_peer = 0;
                        $order_detail->orderrozana_margin = $rozana_margin*$cartItem['quantity'];
                        $order_detail->order_margin = $margin*$cartItem['quantity'];
                        $taxp = ($cartItem['price']*100)/(100+$product->tax);
                        $tax = (($taxp*$product->tax)/100)*$cartItem['quantity'];
                        $order_detail->quantity = $cartItem['quantity'];
                    }
                    
                    $order_detail->tax = $tax;
                    $order_detail->payment_status = 'paid';
                    $order_detail->delivery_status = 'delivered';
                    $order_detail->save();

                }
                //Order Details Storing End

                $order->grand_total = $subtotal + 0 - $ref_dis;
                $order->test_grand_total = $subtotal + 0  - $ref_dis;
                $order->wallet_amount = 0;
                $order->referal_discount =  $ref_dis;
                $this->updateDeliveryBoy($cart_item['shorting_hub']['user_id'],$order->id);
                if(!empty($cart_item['coupon']['code'])){
                    //order referal usage start 
                    $referal_usage = new ReferalUsage;
                    $referal_usage->user_id = '';
                    $referal_usage->partner_id = $cart_item['coupon']['partner_id'];
                    $referal_usage->order_id = $order->id;
                    $referal_usage->referal_code = $cart_item['coupon']['code'];
                    $referal_usage->discount_rate = $total_discount_percent;
                    $referal_usage->discount_amount = $peer_percentage;
                    $referal_usage->commision_rate = $total_peer_percent;
                    $referal_usage->master_discount = $master_percentage;
                    $referal_usage->master_percentage = $total_master_percent;
                    $referal_usage->created_at = $datetime;
                    $referal_usage->updated_at = $updatedatetime;
                    $referal_usage->save();
                    //order referal usage end

                    //order refferal commission start
                        $OrderReferalCommision = new OrderReferalCommision;
                        $OrderReferalCommision->partner_id = $referal_usage->partner_id;
                        $OrderReferalCommision->order_id = $order->id;
                        $OrderReferalCommision->order_amount = $order->grand_total;
                        $OrderReferalCommision->refral_code = $referal_usage->referal_code;
                        $OrderReferalCommision->referal_code_commision = $referal_usage->commision_rate;
                        $OrderReferalCommision->referal_commision_discount = $referal_usage->discount_amount;
                        $OrderReferalCommision->master_commission = $referal_usage->master_percentage;
                        $OrderReferalCommision->master_discount = $referal_usage->master_discount;
                        $OrderReferalCommision->created_at = $datetime;
                        $OrderReferalCommision->updated_at = $updatedatetime;
                        $OrderReferalCommision->save();
                    //order refferal commission end
                }
                

                if($order->save()){
                    CsvOrders::destroy($id);
                    
                    DB::commit();
                    return 1;
                }else{
                    return 0;
                }
            }
            
        }catch(\Exception $e){
            DB::rollback();
            dd($e->getMessage());

        }

    }


    public function getDateTimeCSV($cart_item){
        $datetime = "";
        $order_date = $cart_item['order_date'];
        $order_time = $cart_item['created_time'];
        
        if($order_date != null && $order_time != null){
        $datetime = $order_date.' '.date("H:i:s", strtotime($order_time));
        }elseif($order_date != null && $order_time == null){
            $datetime = $order_date.' '. date('H:i:s');

        }elseif($order_time != null && $order_date == null){
            $datetime = date('Y-m-d').' '.date("H:i:s", strtotime($order_time));
        }

        // $request->offsetUnset('order_date');

        return $datetime;

    }


    public function deliveryScheduleCSV($orderId,$cart){ 
        $is_fresh = 0;
        $is_grocery = 0; 
        foreach($cart as $key=>$value){
           $product = Product::where('id',$value['id'])->first('category_id');
           if($product['category_id'] == '18' || $product['category_id']=='26' || $product['category_id']=='34' || $product['subcategory_id'] == '129' || $product['subcategory_id']==67 || $product['category_id']=='33' || $product['category_id']=='38' || $product['category_id']=='39' || $product['category_id']=='40'){
               $is_fresh = 1;      
           }else{
               $is_grocery = 1; 
           }
           if($is_fresh == 1 && $is_grocery == 1){
               break;
           }

        }
       // dd($orderId);
       $items['fresh'] = $is_fresh;
       $items['grocery'] = $is_grocery;
       $deliveryDetail['items'] = $items;
       $currentDateTime = new Carbon($orderId['created_at']);
       $deliveryDetail['delivery_date'] =  date("Y-m-d",strtotime($currentDateTime->addHour(24)));
       $deliveryDetail['delivery_slot'] = date("H:i:s",strtotime($currentDateTime->addHour(24)));
       
           foreach($items as $key => $item){
               
               if($item){
                   $orderSchedule = new SubOrder;
                   $orderSchedule->order_id = $orderId['id'];
                   $orderSchedule->delivery_name = $key;
                   $orderSchedule->delivery_type = 'normal';
                   $orderSchedule->delivery_date = $deliveryDetail['delivery_date'];
                   $orderSchedule->delivery_time = $deliveryDetail['delivery_slot'];
                   $orderSchedule->status = 1;
                   $orderSchedule->payment_status = 'paid';
                   $orderSchedule->created_at = $orderId['created_at'];
                   $orderSchedule->updated_at = $orderId['updated_at'];
                   $orderSchedule->save();
               }
               
           }
  // return true;
   
}



      
      
}
