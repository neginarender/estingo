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
use App\ArchivedOrder;
use Hash;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class TestController extends Controller
{
    //

    public function index(){
        return view('test/index');
    }

    public function showWebp()
    {
        $banners = SortingHubSlider::where('sorting_hub_id',Auth::user()->sortinghub['id'])->get();
        return view('WEBP.show_banner', compact('banners'));
    }


    public function uploadImage(REQUEST $request){
           $dir = 'public/uploads/webp/';
          
            $newName = strtok($request->photo->getClientOriginalName(), '.').'.webp';
            if($request->photo->getClientOriginalExtension() == "png"){
                $img = imagecreatefrompng($request->photo);

            }else{
                $img = imagecreatefromjpeg($request->photo);
            }
                // // Create and save
           
            imagepalettetotruecolor($img);
            imagealphablending($img, true);
            imagesavealpha($img, true);
            if(imagewebp($img, $dir .'/'. $newName, 40)){
                flash(translate('image has been converted to webp'))->success();
                return back();
            }
    }

    public function generateOrderCommission($order_id){
        
        $order = \App\Order::findOrFail($order_id);
        $order->payment_status = 'paid';
        //$order->payment_details = $payment;
        $order->log = 0;
        if($order->dofo_status == 1){
            $order->order_status = 'delivered';
            $order->updated_at = $order->created_at;

        }
        
        $order->save();

        if($order->payment_status == 'paid'){
        $FAmount = 0;
        $unit_price = 0;
        $peer_percentage = 0;
        foreach ($order->orderDetails as $key => $value) {
            $id = $value->product_id;
            $product = \App\Product::find($value->product_id);
            $productVarient = \App\ProductStock::where(['variant' => $value->variation, 'product_id' => $value->product_id])->first();
            $peer_discount_check = \App\PeerSetting::where('product_id', '"'.$id.'"')->latest('id')->first();
            $FAmount += $value->price;
            $unit_price += $product->unit_price;  
            $peer_percentage += substr($peer_discount_check['peer_discount'], 1, -1);           
        }

         $ReferalUsage = \App\ReferalUsage::where('order_id', $order->id)->first();
            if(!empty($ReferalUsage)){

                $margin_price = $FAmount - $unit_price;
                $partner_commision = ($margin_price * $peer_percentage ) / 100;

                //$partner_commision = ($FAmount * $ReferalUsage->commision_rate) / 100;

                $OrderReferalCommision = new \App\OrderReferalCommision;
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
                $user_wallet = \App\User::where('id', Auth::user()->id)->first();
                $last_wallet = $user_wallet->balance - $order->wallet_amount;
                $referall_balance = \App\User::where('id', Auth::user()->id)->update([
                                                'balance' => $last_wallet,
                                            ]);
                // Insert Record into wallet table
                $this->createHistoryInWallet($order);
    
                }
        }

       
        if (\App\Addon::where('unique_identifier', 'seller_subscription')->first() == null || !\App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated) {
            if (\App\BusinessSetting::where('type', 'category_wise_commission')->first()->value != 1) {
                $commission_percentage = \App\BusinessSetting::where('type', 'vendor_commission')->first()->value;
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
        echo "Everything done successfully";
        exit;
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

public function updateReferalDiscount(){
        
    try{
        DB::beginTransaction();
        //get all orders of android by razorpay and not logged order
        $orders = \App\Order::where('platform','android')->where('payment_type','razorpay')->where('order_status','!=','cancel')->where('log',0)->get();
        $orderIds = [];
        foreach($orders as $key => $order){
            $ref_discount = 0;
            $orderDetails = \App\OrderDetail::where('order_id',$order->id)->where('payment_status','!=','refund')->where('delivery_status','!=','cancel')->whereNull('deleted_at')->get();
            foreach($orderDetails as $key => $orderDetail){
                $ref_discount += $orderDetail->peer_discount;
                
            }
            $orderIds[] = $order->id;
            info($orderIds);
            //update peer discount here 
            \App\Order::where('id',$order->id)->update(['referal_discount'=>$ref_discount]);
            DB::commit();
    }
    } catch(\Exception $e){
        DB::rollback();
        dd($e);
    }
}

public function orderExportWithDate(){
    ini_set('memory_limit', '-1');
    ini_set ( 'max_execution_time', 3600);
    $startDate = "2021-11-01";
    $endDate = "2021-12-01";

    $orders = \App\Order::join('order_details','orders.id','=','order_details.order_id')
    ->join('products','order_details.product_id','=','products.id')
    ->join('referral_usages','referral_usages.order_id','=','orders.id')
    ->join('assign_orders','assign_orders.order_id','=','orders.id')
    ->join('delivery_boy','delivery_boy.id','assign_orders.delivery_boy_id')
    ->join('users','users.id','=','delivery_boy.user_id')
    ->whereBetween('orders.created_at',[$startDate,$endDate])
    ->select('orders.id','orders.code','orders.shipping_address','orders.order_status','orders.payment_status','orders.shipping_pin_code','orders.grand_total','orders.payment_type','orders.created_at','products.name as product_name','referral_usages.referal_code','assign_orders.delivery_boy_id','order_details.product_id','users.name as dboy_name','delivery_boy.id as dboy_id')
    ->orderBy('orders.id','desc')->groupBy('orders.id')->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $sheet->setCellValue('A1', 'Order Code');
    $sheet->setCellValue('B1', 'Order Date');
    $sheet->setCellValue('C1', 'Products');
    $sheet->setCellValue('D1', 'Customer');
    $sheet->setCellValue('E1', 'Address');
    $sheet->setCellValue('F1', 'Pincode');
    $sheet->setCellValue('G1', 'Assign Order');
    $sheet->setCellValue('H1', 'Sorting Hub');
    $sheet->setCellValue('I1', 'Amount');
    $sheet->setCellValue('J1', 'Delivery Status');
    $sheet->setCellValue('K1', 'Delivery Date');
    $sheet->setCellValue('L1', 'Payment Method');
    $sheet->setCellValue('M1', 'Payment Status');

    foreach($orders as $key => $order)
    {
    $shipping_address = json_decode($order->shipping_address);
    $sheet->setCellValue('A'.($key+2), $order->code);
    $sheet->setCellValue('B'.($key+2), $order->created_at);
    $sheet->setCellValue('C'.($key+2), $order->product_name);
    $sheet->setCellValue('D'.($key+2), $shipping_address->name." ".$shipping_address->phone." ".$order->referal_code);
    $sheet->setCellValue('E'.($key+2), $shipping_address->address);
    $sheet->setCellValue('F'.($key+2), $order->shipping_pin_code);
    $sheet->setCellValue('G'.($key+2), $order->dboy_name);
    $sheet->setCellValue('H'.($key+2), $order->shipping_pin_code);
    $sheet->setCellValue('I'.($key+2), $order->grand_total);
    $sheet->setCellValue('J'.($key+2), $order->order_status);
    $sheet->setCellValue('K'.($key+2), $order->updated_at);
    $sheet->setCellValue('L'.($key+2), $order->payment_type);
    $sheet->setCellValue('M'.($key+2), $order->payment_status);
    }

    $filename = "inhouseorders.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);


}

public function user_info(){
    return view('user_info');
}
public function updateUserInfo(Request $request){
    
    if(!empty($request->mobile)){
        $user = \App\User::where('phone',$request->mobile)->first();
        if(!is_null($user)){
            $user = \App\User::where('phone',$request->mobile)->update(['name'=>$request->name,'email'=>$request->email,'email_verification'=>1]);
        if($user){
           session()->put('msg','info updated');
            return back();
        }
        }
        
        session()->put('msg','Phone number does not exist');
        return back();
        
    }
    session()->put('msg','Something went wrong');
        return back();
}

public function updateSubOrder($startDate,$endDate){
    //$startDate = "2022-04-29";
    //$endDate = "2022-04-30";
   // dd($startDate);
    $orders = \App\Order::where('created_at', '>', Carbon::now()->subMinutes(1)->toDateTimeString())->select('id')->get();
    //$orders = \App\Order::whereBetween('created_at',[$startDate,$endDate])->select('id')->get();
    //dd(count($orders));
    $order_ids = [];
    foreach($orders as $key => $order){
    $order_ids[] = $order->id;
        $orderDetails = \App\OrderDetail::where('order_id',$order->id)->get();
        $totalOrderAmountfresh = 0;
        $totalOrderAmountGrocery =0;
        $totalDiscountFresh=0;
        $totalDiscountGrocery = 0;
        $noOfFreshItems = 0;
        $noOfGroceyItems = 0;
        $types = [];
        foreach($orderDetails as $key => $orderDetail){
            if(isFreshInCategories($orderDetail->product->category_id) || isFreshInSubCategories($orderDetail->product->subcategory_id)){
                $types[]="fresh";
                $noOfFreshItems +=$orderDetail->quantity;
                $totalOrderAmountfresh += ($orderDetail->price+$orderDetail->shiiping_cost)-$orderDetail->peer_discount;
                $totalDiscountFresh+=$orderDetail->peer_discount;
            }else{
                $types[] = "grocery";
                $noOfGroceyItems +=$orderDetail->quantity;
                $totalDiscountGrocery += $orderDetail->peer_discount;
                $totalOrderAmountGrocery += ($orderDetail->price+$orderDetail->shiiping_cost)-$orderDetail->peer_discount;
            }
        
        }
        $types = array_unique($types);
        foreach($types as $type){
            if($type=='fresh'){
                \App\SubOrder::where('delivery_name','fresh')->where('order_id',$order->id)->update([
                                'no_of_items'=>$noOfFreshItems,
                                'payable_amount'=>$totalOrderAmountfresh,
                                'customer_discount'=>$totalDiscountFresh
                            ]);
            }

            if($type=='grocery'){
                \App\SubOrder::where('delivery_name','grocery')->where('order_id',$order->id)->update([
                                'no_of_items'=>$noOfGroceyItems,
                                'payable_amount'=>$totalOrderAmountGrocery,
                                'customer_discount'=>$totalDiscountGrocery
                            ]);
            }
            
        }
        sleep(1);
        
    }
    dd(implode(',',$order_ids));
    echo "Done";
   
}

public function updateSubOrderCron(){
    //$startDate = "2022-04-29";
    //$endDate = "2022-04-30";
    $orders = \App\Order::where('created_at', '>', Carbon::now()->subHours(24)->toDateTimeString())->select('id')->get();
    //$orders = \App\Order::whereBetween('created_at',[$startDate,$endDate])->select('id')->get();
    $order_ids = [];
    foreach($orders as $key => $order){
    $order_ids[] = $order->id;
        $orderDetails = \App\OrderDetail::where('order_id',$order->id)->get();
        $totalOrderAmountfresh = 0;
        $totalOrderAmountGrocery =0;
        $totalDiscountFresh=0;
        $totalDiscountGrocery = 0;
        $noOfFreshItems = 0;
        $noOfGroceyItems = 0;
        $types = [];
        foreach($orderDetails as $key => $orderDetail){
            if(isFreshInCategories($orderDetail->product->category_id) || isFreshInSubCategories($orderDetail->product->subcategory_id)){
                $types[]="fresh";
                $noOfFreshItems +=$orderDetail->quantity;
                $totalOrderAmountfresh += ($orderDetail->price+$orderDetail->shiiping_cost)-$orderDetail->peer_discount;
                $totalDiscountFresh+=$orderDetail->peer_discount;
            }else{
                $types[] = "grocery";
                $noOfGroceyItems +=$orderDetail->quantity;
                $totalDiscountGrocery += $orderDetail->peer_discount;
                $totalOrderAmountGrocery += ($orderDetail->price+$orderDetail->shiiping_cost)-$orderDetail->peer_discount;
            }
        
        }
        $types = array_unique($types);
        foreach($types as $type){
            if($type=='fresh'){
                \App\SubOrder::where('delivery_name','fresh')->where('order_id',$order->id)->update([
                                'no_of_items'=>$noOfFreshItems,
                                'payable_amount'=>$totalOrderAmountfresh,
                                'customer_discount'=>$totalDiscountFresh
                            ]);
            }

            if($type=='grocery'){
                \App\SubOrder::where('delivery_name','grocery')->where('order_id',$order->id)->update([
                                'no_of_items'=>$noOfGroceyItems,
                                'payable_amount'=>$totalOrderAmountGrocery,
                                'customer_discount'=>$totalDiscountGrocery
                            ]);
            }
            
        }
        
    }
    dd(implode(',',$order_ids));
    echo "Done";
   
}

public function orderExport($download_type = null){
    ini_set('memory_limit', '-1');
    ini_set ( 'max_execution_time', 3600);
    $user_type = Auth::user()->user_type;
    $startDate = date('Y-m-d',strtotime(request('date_from_export')))." 00:00:01.000000";
    $endDate = date('Y-m-d',strtotime(request('date_to_export')))." 23:59:59.000000";
    $payment_status = request('pay_status');
    $delivery_status = request('delivery_status');
    
    $payment_mode = request('payment_mode');
    //dd($payment_mode);
    $orders = \App\Order::where('dofo_status',0)->where('log',0);
    if(request()->has('download_type')){
        $orders = \App\ArchivedOrder::where('dofo_status',0)->where('log',0);
    }
    if(!empty($payment_status) && !is_null($payment_status)){
        $orders->where('payment_status',$payment_mode);
    }
    if(!empty($payment_mode) && !is_null($payment_mode)){
        $orders->where('payment_type',$payment_status);
    }

    if(!empty($delivery_status) && !is_null($delivery_status)){
        $orders->where('order_status',$delivery_status); 
    }

    if($user_type!='admin'){

        if(auth()->user()->sorting_hub_id){
        
        $orders->where('sorting_hub_id',auth()->user()->sorting_hub_id);
    
    }else{
                $orders->where('sorting_hub_id',auth()->user()->id);

    }

    }

    $orders = $orders->whereBetween('created_at',[$startDate,$endDate])->select('id','code','shipping_address','order_status','sorting_hub_id','payment_status','referal_code','shipping_pin_code','grand_total','payment_type','created_at')
    ->orderBy('id','desc')->groupBy('id')->get();
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $sheet->setCellValue('A1', 'Order Code');
    $sheet->setCellValue('B1', 'Order Date');
    $sheet->setCellValue('C1', 'Time');
    $sheet->setCellValue('D1', 'Num. OF Products');
    $sheet->setCellValue('E1', 'Customer');
    $sheet->setCellValue('F1', 'Address');
    $sheet->setCellValue('G1', 'Pincode');
    $sheet->setCellValue('H1', 'Phone Number');
    $sheet->setCellValue('I1', 'Sorting Hub');
    $sheet->setCellValue('J1', 'Payment mode');
    $sheet->setCellValue('K1', 'Total Amount');
    $sheet->setCellValue('L1', 'Delivery status');
    $sheet->setCellValue('M1', 'Email');
    $sheet->setCellValue('N1', 'Peer code');
    $sheet->setCellValue('O1', 'Payment status');

    foreach($orders as $key => $order)
    {
    
    $shortId = \App\User::find($order->sorting_hub_id);
   
    $shipping_address = json_decode($order->shipping_address);
    $sheet->setCellValue('A'.($key+2), $order->code);
    $sheet->setCellValue('B'.($key+2), date('d-m-Y',strtotime($order->created_at)));
    $sheet->setCellValue('C'.($key+2), date('H:i A', strtotime($order->created_at)));
    $sheet->setCellValue('D'.($key+2), $order->orderDetails->sum('quantity'));
    $sheet->setCellValue('E'.($key+2), @$shipping_address->name);
    $sheet->setCellValue('F'.($key+2), @$shipping_address->address);
    $sheet->setCellValue('G'.($key+2), $order->shipping_pin_code);
    $sheet->setCellValue('H'.($key+2), @$shipping_address->phone);
    $sheet->setCellValue('I'.($key+2), @$shortId->name);
    $sheet->setCellValue('J'.($key+2), $order->payment_type);
    $sheet->setCellValue('K'.($key+2), $order->grand_total);
    $sheet->setCellValue('L'.($key+2), $order->order_status);
    $sheet->setCellValue('M'.($key+2), @$shipping_address->email);
    $sheet->setCellValue('N'.($key+2), $order->referal_code);
    $sheet->setCellValue('O'.($key+2), $order->payment_status);
    }

    $filename = "inhouseorders.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    // $data = [];
    // foreach($orders as $key => $order)
    // {
    //     $data['Order Code'] = $order->code;
    //     $data['Order Date'] = date('d-m-Y',strtotime($order->created_at));
    //     $data['Time'] = date('H:i A', strtotime($order->created_at));
    //     $data['Num. OF Products'] = $order->orderDetails->sum('quantity');

    // }

    // return (new FastExcel(collect($data)))->export('inhouseorders.xlsx');

}

public function orderExportProductWise(){
    ini_set('memory_limit', '-1');
    ini_set ( 'max_execution_time', 3600);
    $user_type = Auth::user()->user_type;
    $startDate = date('Y-m-d',strtotime(request('date_from_export')))." 00:00:01.000000";
    $endDate = date('Y-m-d',strtotime(request('date_to_export')))." 23:59:59.000000";
    $payment_status = request('pay_status');
    $delivery_status = request('delivery_status');
    
    $payment_mode = request('payment_mode');
    //dd($payment_mode);
    $orders = \App\Order::where('dofo_status',0)->where('log',0);
    if(!empty($payment_status) && !is_null($payment_status)){
        $orders->where('payment_status',$payment_mode);
    }
    if(!empty($payment_mode) && !is_null($payment_mode)){
        $orders->where('payment_type',$payment_status);
    }

    if(!empty($delivery_status) && !is_null($delivery_status)){
        $orders->where('order_status',$delivery_status); 
    }

    if($user_type!='admin'){

        if(auth()->user()->sorting_hub_id){
        
        $orders->where('sorting_hub_id',auth()->user()->sorting_hub_id);
    }else{
                $orders->where('sorting_hub_id',auth()->user()->id);

    }

    }


    $orders = $orders->where('log',0)->whereBetween('orders.created_at',[$startDate,$endDate])->select('orders.id','orders.code','orders.shipping_address','orders.order_status','orders.sorting_hub_id','orders.payment_status','orders.referal_code','orders.shipping_pin_code','orders.grand_total','orders.payment_type','orders.created_at')
    ->orderBy('orders.id','desc')->groupBy('orders.id')->get();
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $sheet->setCellValue('A1', 'Order Code');
    $sheet->setCellValue('B1', 'Order Date');
    $sheet->setCellValue('C1', 'Time');
    $sheet->setCellValue('D1', 'Num. OF Products');
    $sheet->setCellValue('E1', 'Customer');
    $sheet->setCellValue('F1', 'Address');
    $sheet->setCellValue('G1', 'Pincode');
    $sheet->setCellValue('H1', 'Phone Number');
    $sheet->setCellValue('I1', 'Sorting Hub');
    $sheet->setCellValue('J1', 'Payment mode');
    $sheet->setCellValue('K1', 'Total Amount');
    $sheet->setCellValue('L1', 'Delivery status');
    $sheet->setCellValue('M1', 'Email');
    $sheet->setCellValue('N1', 'Peer code');
    $sheet->setCellValue('O1', 'Peer code');
    $sheet->setCellValue('P1','Product Name');
    $sheet->setCellValue('Q1', 'quantity');
    $sheet->setCellValue('R1', 'tax');
    $sheet->setCellValue('S1', 'price');
    $sheet->setCellValue('T1', 'Shipping');
    $sheet->setCellValue('U1', 'Discount');
    $i=0;
    foreach($orders as $key => $order)
    {
    
    $shortId = \App\User::find($order->sorting_hub_id);
    foreach($order->orderDetails as $kk => $orderDetail){
        $shipping_address = json_decode($order->shipping_address);
        $sheet->setCellValue('A'.($i+2), $order->code);
        $sheet->setCellValue('B'.($i+2), date('d-m-Y',strtotime($order->created_at)));
        $sheet->setCellValue('C'.($i+2), date('H:i A', strtotime($order->created_at)));
        $sheet->setCellValue('D'.($i+2), $order->orderDetails->sum('quantity'));
        $sheet->setCellValue('E'.($i+2), @$shipping_address->name);
        $sheet->setCellValue('F'.($i+2), @$shipping_address->address);
        $sheet->setCellValue('G'.($i+2), $order->shipping_pin_code);
        $sheet->setCellValue('H'.($i+2), @$shipping_address->phone);
        $sheet->setCellValue('I'.($i+2), @$shortId->name);
        $sheet->setCellValue('J'.($i+2), $order->payment_type);
        $sheet->setCellValue('K'.($i+2), $order->grand_total);
        $sheet->setCellValue('L'.($i+2), $order->order_status);
        $sheet->setCellValue('M'.($i+2), @$shipping_address->email);
        $sheet->setCellValue('N'.($i+2), $order->referal_code);
        $sheet->setCellValue('O'.($i+2), $order->payment_status);
        $sheet->setCellValue('P'.($i+2), $orderDetail->product->name);
        $sheet->setCellValue('Q'.($i+2), $orderDetail->quantity);
        $sheet->setCellValue('R'.($i+2), $orderDetail->tax);
        $sheet->setCellValue('S'.($i+2), $orderDetail->price);
        $sheet->setCellValue('T'.($i+2), $order->total_shipping_cost);
        $sheet->setCellValue('U'.($i+2), $orderDetail->peer_discount);
        $i++;
    }
    
    }

    $filename = "inhouseorders.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);


}

public function clusterHubOrderExportProductWise(){
    ini_set('memory_limit', '-1');
    ini_set ( 'max_execution_time', 3600);
    $user_type = 'clusterhub';//Auth::user()->user_type;
    //$startDate = date('Y-m-d',strtotime(request('date_from_export')))." 00:00:01.000000";
    //$endDate = date('Y-m-d',strtotime(request('date_to_export')))." 23:59:59.000000";
    $startDate = "2022-04-01 00:00:01.000000";
    $endDate = "2022-05-17 23:59:59.000000";
    $payment_status = request('pay_status');
    $delivery_status = request('delivery_status');
    $sortingHubs = [];

    $payment_mode = request('payment_mode');
    //dd($payment_mode);
    $orders = \App\Order::where('dofo_status',0)->where('log',0);
    if(!empty($payment_status) && !is_null($payment_status)){
        $orders->where('payment_status',$payment_mode);
    }
    if(!empty($payment_mode) && !is_null($payment_mode)){
        $orders->where('payment_type',$payment_status);
    }

    if(!empty($delivery_status) && !is_null($delivery_status)){
        $orders->where('order_status',$delivery_status); 
    }

    if($user_type!='admin'){
        $sortingHubs = \App\ShortingHub::where('cluster_hub_id',171)->pluck('user_id');
        $orders->whereIn('sorting_hub_id',$sortingHubs);
    }


    $orders = $orders->where('log',0)->where('orders.order_status','!=','cancel')->whereBetween('orders.created_at',[$startDate,$endDate])->select('orders.id','orders.code','orders.shipping_address','orders.order_status','orders.sorting_hub_id','orders.payment_status','orders.referal_code','orders.shipping_pin_code','orders.grand_total','orders.payment_type','orders.created_at')
    ->orderBy('orders.id','desc')->groupBy('orders.id')->get();
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $sheet->setCellValue('A1', 'Order Code');
    $sheet->setCellValue('B1', 'Order Date');
    $sheet->setCellValue('C1', 'Time');
    $sheet->setCellValue('D1', 'Num. OF Products');
    $sheet->setCellValue('E1', 'Customer');
    $sheet->setCellValue('F1', 'Address');
    $sheet->setCellValue('G1', 'Pincode');
    $sheet->setCellValue('H1', 'Phone Number');
    $sheet->setCellValue('I1', 'Sorting Hub');
    $sheet->setCellValue('J1', 'Payment mode');
    $sheet->setCellValue('K1', 'Total Amount');
    $sheet->setCellValue('L1', 'Delivery status');
    $sheet->setCellValue('M1', 'Email');
    $sheet->setCellValue('N1', 'Peer code');
    $sheet->setCellValue('O1', 'Payment status');
    $sheet->setCellValue('P1','Product Name');
    $sheet->setCellValue('Q1', 'quantity');
    $sheet->setCellValue('R1', 'tax');
    $sheet->setCellValue('S1', 'price');
    $sheet->setCellValue('T1', 'Shipping');
    $sheet->setCellValue('U1', 'Discount');
    $sheet->setCellValue('V1', 'Delivery Boy ID');
    $sheet->setCellValue('W1', 'HSN Code');
    $sheet->setCellValue('X1', 'Unit Of measurement');
    $i=0;
    foreach($orders as $key => $order)
    {
    
        $getAssignedBoy =\App\AssignOrder::on('mysql2')->where('order_id',$order->id)->first('delivery_boy_id');
        $dboyid = "";
        if($getAssignedBoy != NULL){
            $dboyid = $getAssignedBoy['delivery_boy_id'];
            // $deliveryBoy = \App\DeliveryBoy::on('mysql2')->where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
            // $deliveryBoyName = User::on('mysql2')->where('id',$deliveryBoy['user_id'])->first('name');
            // $deliveryBoyName = $deliveryBoyName['name'];
        }else{
            $deliveryBoyName = ' ';
        }

    $shortId = \App\User::find($order->sorting_hub_id);
    foreach($order->orderDetails as $kk => $orderDetail){
        $shipping_address = json_decode($order->shipping_address);
        $sheet->setCellValue('A'.($i+2), $order->code);
        $sheet->setCellValue('B'.($i+2), date('d-m-Y',strtotime($order->created_at)));
        $sheet->setCellValue('C'.($i+2), date('H:i A', strtotime($order->created_at)));
        $sheet->setCellValue('D'.($i+2), $order->orderDetails->sum('quantity'));
        $sheet->setCellValue('E'.($i+2), @$shipping_address->name);
        $sheet->setCellValue('F'.($i+2), @$shipping_address->address);
        $sheet->setCellValue('G'.($i+2), $order->shipping_pin_code);
        $sheet->setCellValue('H'.($i+2), @$shipping_address->phone);
        $sheet->setCellValue('I'.($i+2), @$shortId->name);
        $sheet->setCellValue('J'.($i+2), $order->payment_type);
        $sheet->setCellValue('K'.($i+2), $order->grand_total+ $order->wallet_amount);
        $sheet->setCellValue('L'.($i+2), $order->order_status);
        $sheet->setCellValue('M'.($i+2), @$shipping_address->email);
        $sheet->setCellValue('N'.($i+2), $order->referal_code);
        $sheet->setCellValue('O'.($i+2), $order->payment_status);
        $sheet->setCellValue('P'.($i+2), $orderDetail->product->name);
        $sheet->setCellValue('Q'.($i+2), $orderDetail->quantity);
        $sheet->setCellValue('R'.($i+2), $orderDetail->product->tax." %");
        $sheet->setCellValue('S'.($i+2), $orderDetail->price);
        $sheet->setCellValue('T'.($i+2), $order->total_shipping_cost);
        $sheet->setCellValue('U'.($i+2), $orderDetail->peer_discount);
        $sheet->setCellValue('V'.($i+2), $dboyid);
        $sheet->setCellValue('W'.($i+2), $orderDetail->product->hsn_code);
        $sheet->setCellValue('X'.($i+2), $orderDetail->variation);

        $i++;
    }
    
    }

    $filename = "inhouseorders.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);


}

public function orderExportAssignOrder(){
    $user_type = Auth::user()->user_type;
    
    ini_set('memory_limit', '-1');
    ini_set ( 'max_execution_time', 3600);
    $startDate = date('Y-m-d',strtotime(request('date_from_export')))." 00:00:01.000000";
    $endDate = date('Y-m-d',strtotime(request('date_to_export')))." 23:59:59.000000";
    $payment_status = request('pay_status');
    $delivery_status = request('delivery_status');
    
    $payment_mode = request('payment_mode');
    //dd($payment_mode);
    $orders = \App\Order::where('dofo_status',0)->where('log',0);
    if(!empty($payment_status) && !is_null($payment_status)){
        $orders->where('payment_status',$payment_mode);
    }
    if(!empty($payment_mode) && !is_null($payment_mode)){
        $orders->where('payment_type',$payment_status);
    }

    if(!empty($delivery_status) && !is_null($delivery_status)){
        $orders->where('order_status',$delivery_status); 
    }

    if($user_type!='admin'){

        if(auth()->user()->sorting_hub_id){
        
        $orders->where('sorting_hub_id',auth()->user()->sorting_hub_id);
    }else{
                $orders->where('sorting_hub_id',auth()->user()->id);

    }

    }


    $orders = $orders->where('log',0)->where('orders.order_status','!=','cancel')->whereBetween('orders.created_at',[$startDate,$endDate])->select('orders.id','orders.code','orders.shipping_address','orders.order_status','orders.sorting_hub_id','orders.payment_status','orders.referal_code','orders.shipping_pin_code','orders.grand_total','orders.payment_type','orders.created_at')
    ->orderBy('orders.id','desc')->groupBy('orders.id')->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    $sheet->setCellValue('A1', 'Order Code');
    $sheet->setCellValue('B1','Date And Time');
    $sheet->setCellValue('C1', 'Delivery boy');
    $sheet->setCellValue('D1', 'Delivery status');
    $sheet->setCellValue('E1', 'Sorting Hub');

    foreach($orders as $key => $order)
    {
    
    $shortId = \App\User::find($order->sorting_hub_id);
    $getAssignedBoy = \App\AssignOrder:: where('order_id',$order->id)->first('delivery_boy_id');
    $deliveryBoyName = "NA";
    if($getAssignedBoy != NULL){
        $deliveryBoy = \App\DeliveryBoy:: where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
        $deliveryBoyName = \App\User:: where('id',$deliveryBoy['user_id'])->first('name');
        $deliveryBoyName = $deliveryBoyName['name'];
    }else{
        $deliveryBoyName = 'NA';
    }
    $shipping_address = json_decode($order->shipping_address);
    $sheet->setCellValue('A'.($key+2), $order->code);
    $sheet->setCellValue('B'.($key+2), $order->created_at);
    $sheet->setCellValue('C'.($key+2), $deliveryBoyName);
    $sheet->setCellValue('D'.($key+2), $order->order_status);
    $sheet->setCellValue('E'.($key+2), @$shortId->name);

    }

    $filename = "deliveryassign.xlsx";
        $writer = new Xlsx($spreadsheet);
        $writer->save(base_path()."/public/sorting_hub_excels/".$filename);
        
        return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);

}

public function orders_export(){
       

    ini_set('memory_limit','1024M');
    set_time_limit(0); //You can use 0 to remove limits

    $from = $_GET['date_from_export']." 00:00:00.000000";
    $to = $_GET['date_to_export']." 23:59:59.000000";
    $sorting_hub_id = $_GET['sorting_hub_id'];
    $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
    $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
    $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

    // dd($from);
    // ini_set('max_execution_time', -1);
    // return Excel::download(new OrdersExport($sorting_hub_id,$from,$to,$deliveryStatus,$payStatus,$paymentStatus), 'inhouseorders.xlsx');

    
    if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
        $sorting_hub_id = $sorting_hub_id;
        $sorting_hub = \App\ShortingHub::on('mysql2')->where('user_id', $sorting_hub_id)->first();
        $result = json_decode($sorting_hub['area_pincodes']);
    }else{
        $sorting_hub_id = $sorting_hub_id;
    }

    if(isset($result)){
        $orders = \App\Order::on('mysql2')->where('dofo_status',0)->whereIn('shipping_pin_code', $result)->where('log',0);
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

        $orders = $orders->orderBy('created_at','desc')->get();
        
    }else{

        if(isset($from)){

            if($from == $to){
                $from = date('Y-m-d',strtotime($from));
                $to = date('Y-m-d',strtotime($to));
            }else{
                $from = date('Y-m-d',strtotime($from));
                $to = date('Y-m-d',strtotime($to.' +1 day'));
                // $to = date('Y-m-d',strtotime($to));
            }
            
            if($from != $to){
                $orders = \App\Order::on('mysql2')->where('dofo_status',0)->whereBetween('created_at', [$from, $to]);
            }else{
                $orders = \App\Order::on('mysql2')->where('dofo_status',0)->whereDate('created_at',$from);
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
            // DB::enableQueryLog();
            $orders = $orders->where('log',0)->orderBy('created_at','desc')->get();
            // dd(DB::getQueryLog());
        }else{
            $orders = \App\Order::on('mysql2')->where('dofo_status',0)->all();
        }
        
    }
    // echo '<pre>';
    // print_r($orders);
    // die;

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
    $sheet->setCellValue('H1', 'Phone Number');
    // $sheet->setCellValue('H1', 'HSN Code');
    $sheet->setCellValue('I1', 'Sorting HUB');
    // $sheet->setCellValue('J1', 'Product  Name');
    // $sheet->setCellValue('K1', 'Qty');
    // $sheet->setCellValue('L1', 'GST Rate');
    // $sheet->setCellValue('M1', 'Price');
    // $sheet->setCellValue('N1', 'Discount Price');        
    // $sheet->setCellValue('J1', 'Shipping Cost');
    $sheet->setCellValue('J1', 'Payment Mode');
    $sheet->setCellValue('K1', 'Total Amount');
    $sheet->setCellValue('L1', 'Delivery Status');
    $sheet->setCellValue('M1', 'Delivery Date');
    $sheet->setCellValue('N1', 'Payment Method');
    $sheet->setCellValue('O1', 'Payment Status');
    $sheet->setCellValue('P1', 'Email');
    $sheet->setCellValue('Q1', 'Peer Code');
    $sheet->setCellValue('R1', 'Delivery Boy');
    $sheet->setCellValue('S1', 'Delivery Slot');
    $sheet->setCellValue('T1', 'Delivery Time');

    $i = 0;

    foreach($orders as $key => $order)
    {
        
    $date = date("d/m/Y h:i:s A", $order->date);

    $numProduct = $order->orderDetails->where('order_id', $order->id)->sum('quantity');
    
    $delivery_peercode = \App\ReferalUsage::on('mysql2')->where('order_id',$order->id)->first('referal_code');
    
    if(!empty($delivery_peercode)){
        $peercode = $delivery_peercode->referal_code;
    }else{
        $peercode = 'NA';
    }
    

    $address = json_decode($order->shipping_address);
    $phone = "";

    if($order->user != null){

        // $customer = $order->user->name.' '.@$address->phone;
        $customer = $order->user->name;
        $phone = @$address->phone;
    }else{
        if(!empty($address->name) && !empty($address->phone)){
            $customer = 'Guest-'.$address->name.''.$address->phone;
            $phone = $address->phone;
        }else{
            $customer = 'Guest';
            $phone = '';
        }
    }

    $customer_detail = $customer;
    
    $getAssignedBoy = \App\AssignOrder::on('mysql2')->where('order_id',$order->id)->first('delivery_boy_id');

    if($getAssignedBoy != NULL){
        $deliveryBoy = \App\DeliveryBoy::on('mysql2')->where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
        $deliveryBoyName = \App\User::on('mysql2')->where('id',$deliveryBoy['user_id'])->first('name');
        $deliveryBoyName = $deliveryBoyName['name'];
    }else{
        $deliveryBoyName = ' ';
    }
    
    $sortingHub = \App\ShortingHub::on('mysql2')->whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
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
          $total_discount = $order->orderDetails->where('order_id', $order->id)->sum('peer_discount');
    }
    if($order->wallet_amount > 0){
        $wallet = $order->wallet_amount;
        // $total_amount = $total_amount - $wallet;
        if(!empty($total_discount)){
            $total_amount = $total_amount - $total_discount;
        }else{
            $total_amount = $total_amount;
        }
        
    }

    if($order->payment_type=='wallet'){
        $total_amount = $order->wallet_amount;
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

    $slot = array(); 
    $d_slottime = array();
    foreach($order->sub_orders as $key => $value)
    {
        if(($value['delivery_name']==null) || ($value['delivery_name']=='')){
            $d_name = 'NA';
        }else{
            $d_name = strtoupper($value['delivery_name']);
        }

        if(($value['delivery_type']==null) || ($value['delivery_type']=='')){
            $d_type = 'NA';
        }else{
            $d_type = strtoupper($value['delivery_type']);
        }

        if(($value['delivery_date']==null) || ($value['delivery_date']=='')){
            $d_date = 'NA';
        }else{
            $d_date = date('d M, Y',strtotime($value['delivery_date']));
        }

        if(($value['delivery_time']==null) || ($value['delivery_time']=='')){
            $d_time = 'NA';
        }else{
            $d_time = strtoupper($value['delivery_time']);
        }

       array_push($slot, $d_name.'-'.$d_type); 
       array_push($d_slottime, $d_date.' '.$d_time);         
    }
    $slot = implode(',',$slot);
    $d_slottime = implode(',',$d_slottime);

    $total_shipping = \App\OrderDetail::on('mysql2')->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('shipping_cost');
        // foreach($order->orderDetails as $k=>$v)
        // {
            $sheet->setCellValue('A'.($i+2), $i+1);
            $sheet->setCellValue('B'.($i+2), $order->code);
            $sheet->setCellValue('C'.($i+2), $date);
            $sheet->setCellValue('D'.($i+2), $numProduct);
            $sheet->setCellValue('E'.($i+2), $customer_detail);
            $sheet->setCellValue('F'.($i+2), $user_address);
            $sheet->setCellValue('G'.($i+2), $order->shipping_pin_code);
            $sheet->setCellValue('H'.($i+2),  $phone); 
            // $sheet->setCellValue('H'.($i+2), 'hsn');
            $sheet->setCellValue('I'.($i+2), $sortingHub);
            // $sheet->setCellValue('J'.($i+2), 'name');
            // $sheet->setCellValue('K'.($i+2), 'quantity');
            // $sheet->setCellValue('L'.($i+2), 'tax');
            // $sheet->setCellValue('M'.($i+2), 'price');
            // $sheet->setCellValue('N'.($i+2), 'll');
            // $sheet->setCellValue('J'.($i+2), $total_shipping);
            $sheet->setCellValue('J'.($i+2), $order->payment_type);
            $sheet->setCellValue('K'.($i+2), $amount);
            $sheet->setCellValue('L'.($i+2), $deliveryStatus);
            $sheet->setCellValue('M'.($i+2), $deliveryDate);
            $sheet->setCellValue('N'.($i+2), $paymentType);
            $sheet->setCellValue('O'.($i+2), $order->payment_status);
            $sheet->setCellValue('P'.($i+2), @$address->email);
            $sheet->setCellValue('Q'.($i+2),  $peercode); 
            $sheet->setCellValue('R'.($i+2),  $deliveryBoyName);
            $sheet->setCellValue('S'.($i+2),  @$slot); 
            $sheet->setCellValue('T'.($i+2),  @$d_slottime);               
            $i++;

        // }
   
    }

    $filename = "inhouseorders.xlsx";
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
    return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => 'inline; filename="' . $filename . '"'
    ]);
}


public function orders_productexport(){
    ini_set('memory_limit','1024M');
    set_time_limit(0); //You can use 0 to remove limits
    
    
    $from = $_GET['date_from_export']." 00:00:00.000000";
    $to = $_GET['date_to_export']." 23:59:59.000000";
    $sorting_hub_id = $_GET['sorting_hub_id'];
    $deliveryStatus = empty($_GET['deliveryStatus'])?NULL:$_GET['deliveryStatus'];
    $payStatus = empty($_GET['payStatus'])?NULL:$_GET['payStatus'];
    $paymentStatus = empty($_GET['paymentStatus'])?NULL:$_GET['paymentStatus'];

    // ini_set('max_execution_time', -1);
    // return Excel::download(new OrdersExport($sorting_hub_id,$from,$to,$deliveryStatus,$payStatus,$paymentStatus), 'inhouseorders.xlsx');

    
    if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
        $sorting_hub_id = $sorting_hub_id;
        $sorting_hub = \App\ShortingHub::on('mysql2')->where('user_id', $sorting_hub_id)->first();
        $result = json_decode($sorting_hub['area_pincodes']);
    }else{
        $sorting_hub_id = $sorting_hub_id;
    }

    if(isset($result)){
        $orders = \App\Order::on('mysql2')->whereIn('shipping_pin_code', $result)->where('log',0);
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

        $orders = $orders->orderBy('created_at','desc')->get();
        
    }else{

        if(isset($from)){

            if($from == $to){
                $from = date('Y-m-d',strtotime($from));
                $to = date('Y-m-d',strtotime($to));
            }else{
                $from = date('Y-m-d',strtotime($from));
                $to = date('Y-m-d',strtotime($to.' +1 day'));
                // $to = date('Y-m-d',strtotime($to));
            }
            
            if($from != $to){
                $orders = \App\Order::on('mysql2')->whereBetween('created_at', [$from, $to]);
            }else{
                $orders = \App\Order::on('mysql2')->whereDate('created_at',$from);
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
            // DB::enableQueryLog();
            $orders = $orders->where('log',0)->orderBy('created_at','desc')->get();
            // dd(DB::getQueryLog());
        }else{
            $orders = \App\Order::on('mysql2')->all();
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
    $sheet->setCellValue('Y1', 'Delivery Slot');
    $i = 0;

    foreach($orders as $key => $order)
    {
        
    $date = date("d/m/Y h:i:s A", $order->date);

    $numProduct = $order->orderDetails->where('order_id', $order->id)->sum('quantity');
    
    $delivery_peercode = \App\ReferalUsage::on('mysql2')->where('order_id',$order->id)->first('referal_code');
    
    if(!empty($delivery_peercode)){
        $peercode = $delivery_peercode->referal_code;
    }else{
        $peercode = 'NA';
    }
    

    $address = json_decode($order->shipping_address);
    $phone = "";

    if($order->user != null){

        $customer = $order->user->name.' '.@$address->phone;
        $phone = @$address->phone;
    }else{
        if(!empty($address->name) && !empty($address->phone)){
            $customer = 'Guest-'.$address->name.''.$address->phone;
            $phone = $address->phone;
        }else{
            $customer = 'Guest';
            $phone = '';
        }
    }

    $customer_detail = $customer;
    
    $getAssignedBoy =\App\AssignOrder::on('mysql2')->where('order_id',$order->id)->first('delivery_boy_id');

    if($getAssignedBoy != NULL){
        $deliveryBoy = \App\DeliveryBoy::on('mysql2')->where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
        $deliveryBoyName = User::on('mysql2')->where('id',$deliveryBoy['user_id'])->first('name');
        $deliveryBoyName = $deliveryBoyName['name'];
    }else{
        $deliveryBoyName = ' ';
    }
    
    $sortingHub = \App\ShortingHub::on('mysql2')->whereRaw('json_contains(area_pincodes, \'["' . $order->shipping_pin_code . '"]\')')->first();
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

    $ordertype = \App\OrderDetail::on('mysql2')->where('order_id',$order->id)->select('order_type')->first();
    if($ordertype['order_type']!=''){
            // dd($ordertype->order_type);
            if($ordertype['order_type'] == 'fresh'){
                $schedule = \App\SubOrder::where('order_id',$order->id)->where('delivery_name', $ordertype->order_type)->where('status',1)->first();
                if($schedule['delivery_type'] == 'normal'){
                    if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                        $d_name = 'NA';
                    }else{
                        $d_name = strtoupper($schedule['delivery_name']);
                    }
                    
                    if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                        $d_type = 'NA';
                    }else{
                        $d_type = strtoupper($schedule['delivery_type']);
                    }


                    if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                        $d_date = 'NA';
                    }else{
                        $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                    }

                    if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                        $d_time = 'NA';
                    }else{
                        $d_time = $schedule['delivery_time'];
                    }
                    $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                }else{
                    if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                        $d_name = 'NA';
                    }else{
                        $d_name = strtoupper($schedule['delivery_name']);
                    }
                    
                    if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                        $d_type = 'NA';
                    }else{
                        $d_type = strtoupper($schedule['delivery_type']);
                    }


                    if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                        $d_date = 'NA';
                    }else{
                        $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                    }

                    if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                        $d_time = 'NA';
                    }else{
                        $d_time = $schedule['delivery_time'];
                    }
                    $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                }
                    
            }else{
                $schedule = \App\SubOrder::on('mysql2')->where('order_id',$order->id)->where('delivery_name', $ordertype->order_type)->where('status',1)->first();
                if($schedule['delivery_type'] == 'normal'){
                    if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                        $d_name = 'NA';
                    }else{
                        $d_name = strtoupper($schedule['delivery_name']);
                    }
                    
                    if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                        $d_type = 'NA';
                    }else{
                        $d_type = strtoupper($schedule['delivery_type']);
                    }


                    if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                        $d_date = 'NA';
                    }else{
                        $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                    }

                    if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                        $d_time = 'NA';
                    }else{
                        $d_time = $schedule['delivery_time'];
                    }
                    $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                }else{
                    if(($schedule['delivery_name']==null) || ($schedule['delivery_name']=='')){
                        $d_name = 'NA';
                    }else{
                        $d_name = strtoupper($schedule['delivery_name']);
                    }
                    
                    if(($schedule['delivery_type']==null) || ($schedule['delivery_type']=='')){
                        $d_type = 'NA';
                    }else{
                        $d_type = strtoupper($schedule['delivery_type']);
                    }


                    if(($schedule['delivery_date']==null) || ($schedule['delivery_date']=='')){
                        $d_date = 'NA';
                    }else{
                        $d_date = date('d M, Y',strtotime($schedule['delivery_date']));
                    }

                    if(($schedule['delivery_time']==null) || ($schedule['delivery_time']=='')){
                        $d_time = 'NA';
                    }else{
                        $d_time = $schedule['delivery_time'];
                    }
                    $slot = $d_name.'('.$d_type.') '.$d_date.' '.$d_time;
                }
                 
            }
        }else{
            $slot = 'NA';
        }

      // dd($slot);

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
            $sheet->setCellValue('V'.($i+2), @$address->email);
            $sheet->setCellValue('W'.($i+2),  $peercode); 
            $sheet->setCellValue('X'.($i+2),  $phone); 
            $sheet->setCellValue('Y'.($i+2),  $slot);
            $i++;

        }
   
    }

    $filename = "inhouseorders.xlsx";
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
    $writer->save(base_path()."/public/sorting_hub_excels/".$filename);        
    return response()->download(base_path()."/public/sorting_hub_excels/".$filename, $filename, [
        'Content-Type' => 'application/vnd.ms-excel',
        'Content-Disposition' => 'inline; filename="' . $filename . '"'
    ]);
}


}
