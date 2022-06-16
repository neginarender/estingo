<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Order;
use App\OrderDetail;
use PDF;
use Auth;
use Session;
use App\SubOrder;
use DB;

class InvoiceController extends Controller
{   
    public function __construct(){
        Session::put('invoice_size','reel');
    }
    //downloads customer invoice
    public function customer_invoice_download($id)
    {
        $order = Order::on('mysql2')->findOrFail($id);
        $orderdetail = OrderDetail::on('mysql2')->where('order_id',$order->id)->get();
        $orderproducts = $orderdetail->groupBy(function($item){
            return (string)$item->product['tax'];
        })->sortKeys();
        //$template = 'invoices.customer_invoice';
        $template = "invoices.new_customer_invoice";
        $schedules = [];
        if(count($order->sub_orders)){
            $schedules = $order->sub_orders;
        }
        $pdf = PDF::setOptions([
                        'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                        'logOutputFile' => storage_path('logs/log.htm'),
                        'tempDir' => storage_path('logs/')
                    ])->loadView($template, compact(['orderdetail','order','orderproducts','schedules']));
                    //return view($template,compact(['orderdetail','order','orderproducts']));
        return $pdf->download('order-'.$order->code.'.pdf');
    }

    //downloads seller invoice
    public function seller_invoice_download($id)
    {
        
        $order = Order::findOrFail($id);
        $inv_type = "full_invoice";
        $orderdetail = OrderDetail::where('order_id',$order->id)->get();
        $orderproducts = $orderdetail->groupBy(function($item){
            return (string)$item->product['tax'];
        })->sortKeys();
        //$template = "invoices.seller_invoice";
        $schedules = [];
        if(count($order->sub_orders)){
            $schedules = $order->sub_orders;
        }
       
        $template = "invoices.new_sample_invoice";
        if(Session::has('invoice_size')){
            if(Session::get('invoice_size')=="A4"){
                //$template = 'invoices.seller_invoice_a4';
            }else{
                return view($template,compact(['orderdetail','order','orderproducts','inv_type','schedules']));
            }
        }
        

        $pdf = PDF::setOptions([
                        'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                        'logOutputFile' => storage_path('logs/log.htm'),
                        'tempDir' => storage_path('logs/')
                    ])->loadView($template, compact(['orderdetail','order','orderproducts']));
                    
        return $pdf->download('order-'.$order->code.'.pdf');
        
    }

    //downloads admin invoice
    public function admin_invoice_download($id)
    {
        $order = Order::on('mysql2')->findOrFail($id);
        $orderdetail = OrderDetail::on('mysql2')->where('order_id',$order->id)->get();
        $orderproducts = $orderdetail->groupBy(function($item){
            return (string)$item->product['tax'];
        })->sortKeys();
        
        //$template = 'invoices.admin_invoice';
        $template = "invoices.new_sample_invoice";
        $pdf = PDF::setOptions([
                        'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                        'logOutputFile' => storage_path('logs/log.htm'),
                        'tempDir' => storage_path('logs/')
                    ])->loadView($template, compact(['orderdetail','order','orderproducts']));
        return $pdf->download('order-'.$order->code.'.pdf');
    }

    public function breakUpInvoice($orderdetailId){
       
        $result = array();
        parse_str($orderdetailId,$result);
        $inv_type = $result['type'];
        $orderdetail = OrderDetail::on('mysql2')->findOrFail($result['aParam']);
        $orderproducts = $orderdetail->groupBy(function($item){
            return (string)$item->product['tax'];
        })->sortKeys();
       
        $order = Order::on('mysql2')->where('id',$orderdetail[0]['order_id'])->first();
        //$template = "invoices.breakup_invoice";
        $schedules = [];
        $delivery_type = "";
        if($inv_type=="fresh_invoice"){
            $schedules = SubOrder::on('mysql2')->where('order_id',$order->id)->where('delivery_name','fresh')->get();
        }
        else{
            $schedules = SubOrder::on('mysql2')->where('order_id',$order->id)->where('delivery_name','grocery')->get();
        }
        if(is_null($schedules)){
            $schedules = [];
        }
        $template = "invoices.new_sample_invoice";
        if(Session::has('invoice_size')){
            if(Session::get('invoice_size')=="A4"){
                //$template = 'invoices.breakup_invoice_a4';
                $template = "invoices.new_sample_invoice";
            }else{
                return view($template,compact(['orderdetail','order','orderproducts','inv_type','schedules','delivery_type']));
            }
        }

       
        //print_r(session()->all());
        $pdf = PDF::setOptions([
                        'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                        'logOutputFile' => storage_path('logs/log.htm'),
                        'tempDir' => storage_path('logs/')
                    ])->loadView($template, compact(['orderdetail','order','orderproducts']));
        return $pdf->download('order-'.$order->code.'.pdf');

    }

    public function set_invoice_size(Request $request){
        //echo $request->size;exit;
        Session::put('invoice_size','reel');
        if($request->size=="A4"){
            Session::put('invoice_size','A4');
        }

        return Session::get('invoice_size');
    }

    public function store_print_invoice(Request $request){
        $order_id = $request->order_id;
        $inv_type = $request->type;
        $check_invoice = \App\Invoice::on('mysql2')->where('order_id',$order_id)->first();
        if(!is_null($check_invoice)){
            $inv_inc = $check_invoice->$inv_type+1;
            $update = \App\Invoice::on('mysql2')->where('order_id',$order_id)->update([$inv_type=>$inv_inc]);
            if($update){
                return "invoice updated";
            }
            return "invoice not updated";
        }else{
            $invoice = new \App\Invoice;
            $invoice->order_id = $order_id;
            $invoice->$inv_type = 1;
            if($invoice->save()){
                return "invoice created";
            }
                return "invoice not created";
        }
    }

    public function all_invoice_download(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $from_date = $start_date;
        $to_date   = $end_date;
        $area_pincodes = \App\ShortingHub::on('mysql2')->where('user_id',$request->sorting_id)->first()->area_pincodes;
            
        $area_pincodes = explode('","', $area_pincodes);  
        $area_pincodes = str_replace('["','',$area_pincodes);
        $area_pincodes = str_replace('"]','',$area_pincodes);

        $orders = DB::connection('mysql2')->table('orders')
                //->whereIn('orders.shipping_pin_code', $area_pincodes)
                ->where('orders.sorting_hub_id', $request->sorting_id)  
                ->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))
                ->orderBy('orders.created_at', 'DESC')
                ->select('orders.*')
                ->get();
        $inv_type = "full_invoice";       
        $template = "invoices.all_sample_invoice";
        if(Session::has('invoice_size')){
            if(Session::get('invoice_size')=="A4"){
                //$template = 'invoices.seller_invoice_a4';
            }else{
                return view($template,compact(['orders','inv_type']));
            }
        }
        
        $invoicecode = rand(10,100);
        $pdf = PDF::setOptions([
                        'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                        'logOutputFile' => storage_path('logs/log.htm'),
                        'tempDir' => storage_path('logs/')
                    ])->loadView($template, compact(['orders']));
                    
        return $pdf->download('allorder-'.$invoicecode.'.pdf');
        
    }


    public function subOrderInvoiceDownload($id)
    {
        $result = array();
        $orderdetail = OrderDetail::where('sub_order_id',decrypt($id))->get();
        $subOrder = SubOrder::where('id',decrypt($id))->first();
        $orderproducts = $orderdetail->groupBy(function($item){
            return (string)$item->product['tax'];
        })->sortKeys();
        $order = Order::where('id',$subOrder->order_id)->first();

        $template = "invoices.sub_order_invoice";
        if(Session::has('invoice_size')){
            if(Session::get('invoice_size')=="A4"){
                //$template = 'invoices.breakup_invoice_a4';
                $template = "invoices.sub_order_invoice";
            }else{
                return view($template,compact(['orderdetail','order','orderproducts','subOrder']));
            }
        }

       
        //print_r(session()->all());
        $pdf = PDF::setOptions([
                        'isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true,
                        'logOutputFile' => storage_path('logs/log.htm'),
                        'tempDir' => storage_path('logs/')
                    ])->loadView($template, compact(['orderdetail','order','orderproducts']));
        return $pdf->download('order-'.$order->code.'.pdf');
        
    }


    
}
