<?php

namespace App;

use App\Distributor;
use App\Cluster;
use App\User;
use App\ShortingHub;
use App\DeliveryBoy;
use App\Staff;
use App\Order;
use App\Product;
use App\AssignOrder;
use Auth;
use Session;
use App\Traits\OrderTrait;
use App\OrderReferalCommision;
use App\PeerPartner;
use App\ReferalUsage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;
use App\Wallet;
use App\OrderDetail;
use CoreComponentRepository;


class DeliveryBoyOrderExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{	
	public function __construct($deliveryStatus = NULL,$paymentStatus = NULL,$orderID = NULL)
    {

        $this->deliveryStatus = $deliveryStatus;
        $this->paymentStatus = $paymentStatus;
        $this->orderID = $orderID;
    }
    public function collection()
    {
        $id = Auth()->user()->id;
        $orderID = $this->orderID;

        $orders = DB::table('orders')
                    ->whereIn('orders.id', $orderID)
                    ->select('orders.*')
                    ->orderBy('code', 'desc')
                    // ->distinct('orders.code'); 
                    ->groupBy('code');         

        if ($this->paymentStatus != null){
            $orders = $orders->where('orders.payment_status', $this->paymentStatus);
        }

        if ($this->deliveryStatus != null) {
            $orders = $orders->join('order_details', 'orders.id', '=', 'order_details.order_id')->where('order_details.delivery_status', $this->deliveryStatus);
        }

        $orders = $orders->get();

		return $orders;

    }

    public function headings(): array 
    {
			
        return [
            'Order Code',
            'Num. of Products',
            'Customer',
            'Address',
            'Pin Code',
            'Ordered Date',
            'Amount',
            'Delivery Status',
            'Delivered Date',
            'Payment Method',
            'Payment Status', 
        ];
    }

    /**
    * @var Order $order
    */
    public function map($orders): array
    {

        $order = Order::find($orders->id);

		$numProduct = orderDetail::where('order_id', $orders->id)->sum('quantity');
			
        if ($order['user_id'] != null){
            $user = User::where('id',$order['user_id'])->first();
            $customer = (!is_null($user)) ? $user->name:"User Not Found"; 
        }else{
            $customer = 'Guest-('.$order['guest_id'].')';
        }
            
		$priceSum = OrderDetail::where('order_id', $orders->id)->sum('price');
		$tax = OrderDetail::where('order_id', $orders->id)->sum('tax');
		$amount = single_price($priceSum + $tax);
			
	    $del_status = OrderDetail::where('order_id',$orders->id)->first();

		$deliveryStatus = ucfirst(str_replace('_', ' ', $del_status['delivery_status']));
		$paymentStatus = ucfirst(str_replace('_', ' ', $orders->payment_status));
        $paymentType = $orders->payment_type;
        $shipping_pin_code = $order['shipping_pin_code'];

        $orderedDate = $order['created_at'];
        $address = json_decode($order['shipping_address'],true);
        $user_address = $address['address'];

        // $delivered = OrderDetail::where('id',$orders->id)->first();

        if($del_status['delivery_status'] == 'delivered'){
            $deliveredDate = $del_status['updated_at'];
        }else{
            $deliveredDate = '-';
        }

		return [
            $order['code'],
			$numProduct,
            $customer,
            $user_address,
            $shipping_pin_code,
            $orderedDate,
            $amount,
            $deliveryStatus,
            $deliveredDate,
            $paymentType,
            $orders->payment_status,
            
        ]; 
    }
}
