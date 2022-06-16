<?php

namespace App\Listeners;

use App\Events\OrderPlacedEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mail;
use App\Mail\InvoiceEmailManager;
use App\Order;
use PDF;
class SendOrderPlacedEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderPlacedEmail  $event
     * @return void
     */
    public function handle(OrderPlacedEmail $event)
    {
       $this->sendOrderMail($event->orderId);
    }

    public function sendOrderMail($order_id)
    {
        
      
        $order = Order::where('id',$order_id)->first();
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
                    $array = array(); 
                    $array['view'] = 'emails.invoice';
                    $array['subject'] = 'Rozana Order Placed - '.$order->code;
                    $array['from'] = env('mail_from_address');
                    $array['content'] = translate('Dear Customer, A new order has been placed. You can check your order details in the invoice attached below. Please reach out to us in the case of any queries on customercare@rozana.in');
                    $array['file'] = 'public/invoices/Order#'.$order->code.'.pdf';
                    $array['file_name'] = 'Order#'.$order->code.'.pdf';
            
                    if(env('MAIL_USERNAME') != null){
                        try {
                            Mail::to(json_decode($order->shipping_address)->email)->queue(new InvoiceEmailManager($array));
                            $array['subject'] = "New Order has been placed.";
                            $array['content'] = translate('Dear Admin, A new order has been placed. You can check  order details in the invoice attached below.');
                            //Mail::to(env("ADMIN_MAIL"))->queue(new InvoiceEmailManager($array));
                            return true;
                        } catch (Exception $e) {
                            echo $e->getMessage();
            
                        }
                    }


    }
}
