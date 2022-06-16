<?php

namespace App;

use App\Order;
use App\Product;
use App\Color;
use App\OrderDetail;
use App\User;
use App\RefundRequest;
use App\ReferalUsage;
use App\DeliveryBoy;
use App\AssignOrder;
use Auth;
use Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;
use App\ShortingHub;

class OrdersExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{	
	public function __construct($sorting_hub_id = NULL,$from = NULL,$to = NUll,$deliveryStatus = NULL,$payStatus  = NULL,$paymentStatus  = NULL)
    {
    	
        $this->from = $from;
        $this->to = $to;
        if($sorting_hub_id != 9 && $sorting_hub_id != NULL){
        	$this->sorting_hub_id = $sorting_hub_id;
        	$sorting_hub = ShortingHub::where('user_id', $sorting_hub_id)->first();
			$this->result = json_decode($sorting_hub->area_pincodes);
        }else{
        	$this->sorting_hub_id = $sorting_hub_id;
        }

        $this->deliveryStatus = $deliveryStatus;
        $this->payStatus = $payStatus;
        $this->paymentStatus = $paymentStatus;

        
    }
    public function collection()
    {

    	if(isset($this->result)){

    		$orders = Order::whereIn('shipping_pin_code', $this->result)->where('log',0);
    		if(isset($this->from)){
				$from = date('Y-m-d',strtotime($this->from));
				$to = date('Y-m-d',strtotime($this->to.' +1 day'));

				if($from != $to){
					$orders = $orders->whereBetween('created_at', [$from, $to]);
				}else{
					$orders = $orders->whereDate('created_at',$from);
				}
			}
			if(isset($this->deliveryStatus) && $this->deliveryStatus != NULL){
				$orders = $orders->where('order_status', $this->deliveryStatus);
        	}

        	if(isset($this->payStatus) && $this->payStatus != NULL){
				$orders = $orders->where('payment_type', $this->payStatus);
        	}

        	if(isset($this->paymentStatus) && $this->paymentStatus != NULL){
				$orders = $orders->where('payment_status', $this->paymentStatus);
        	}

			$orders = $orders->orderBy('created_at','desc')->get();
			
    	}else{
    		if(isset($this->from)){

				$from = date('Y-m-d',strtotime($this->from));
				$to = date('Y-m-d',strtotime($this->to.' +1 day'));

				if($from != $to){
					$orders = Order::whereBetween('created_at', [$from, $to]);
				}else{
					$orders = Order::whereDate('created_at',$from);
				}

				if(isset($this->deliveryStatus) && $this->deliveryStatus != NULL){
					$orders = $orders->where('order_status', $this->deliveryStatus);
	        	}

	        	if(isset($this->payStatus) && $this->payStatus != NULL){
					$orders = $orders->where('payment_type', $this->payStatus);
	        	}

	        	if(isset($this->paymentStatus) && $this->paymentStatus != NULL){
					$orders = $orders->where('payment_status', $this->paymentStatus);
	        	}
				$orders = $orders->orderBy('created_at','desc')->get();
			}else{
				$orders = Order::all();
			}
    		
    	}

    	
		return $orders;

    }

    public function headings(): array
    {
			
        return [
            'Order Code',
            'Order Date',
            'Num. of Products',
            'Customer',
            'Address',
            'Pin Code',
            'Assign Order',
            'Sorting HUB',
            'Amount',
            'Delivery Status',
            'Delivery Date',
            'Payment Method',
            'Payment Status', 
        ];
    }

    /**
    * @var Order $order
    */
    public function map($orders): array
    {
    	// $date = date("jS F, Y h:i:s A", $orders->date);
    	// $date = date('d/m/Y H:i:s', strtotime($orders->updated_at));
		 $date = date("d/m/Y h:i:s A", $orders->date);
		// die;
		$numProduct = $orders->orderDetails->where('order_id', $orders->id)->sum('quantity');
		
		$delivery_peercode = ReferalUsage::where('order_id',$orders->id)->first('referal_code');
		if(!empty($delivery_peercode)){
			$peercode = $delivery_peercode->referal_code;
		}else{
			$peercode = 'NA';
		}
		
		//dd($orders->shipping_address);
		$address = json_decode($orders->shipping_address);

		if($orders->user != null){
			$customer = $orders->user->name.' '.$address->phone;
		}else{
			if(!empty($address->name) && !empty($address->phone)){
				$customer = 'Guest-'.$address->name.''.$address->phone;
			}else{
				$customer = 'Guest';
			}
		}
		$customer_detail = $customer.' '.$peercode;

		$getAssignedBoy = AssignOrder::where('order_id',$orders->id)->first('delivery_boy_id');

		if($getAssignedBoy != NULL){
			$deliveryBoy = DeliveryBoy::where('id',$getAssignedBoy['delivery_boy_id'])->first('user_id');
			$deliveryBoyName = User::where('id',$deliveryBoy['user_id'])->first('name');
			$deliveryBoyName = $deliveryBoyName['name'];
		}else{
			$deliveryBoyName = ' ';
		}

		$sortingHub = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $orders->shipping_pin_code . '"]\')')->first();
			if(!empty($sortingHub)){
			  $sortingHub = $sortingHub->user->name;

			}else{
				$sortingHub = "Not Available";
			}
			
		if($orders->wallet_amount == 0){
			$total_amount = $orders->orderDetails->where('order_id', $orders->id)->where('delivery_status','!=','return')->sum('price') + $orders->orderDetails->where('delivery_status','!=','return')->where('order_id', $orders->id)->sum('shipping_cost') - $orders->orderDetails->where('delivery_status','!=','return')->where('order_id', $orders->id)->sum('peer_discount');
		}else{
			$total_amount = $orders->orderDetails->where('order_id', $orders->id)->sum('price') + $orders->orderDetails->where('order_id', $orders->id)->sum('shipping_cost'); 
		}

		if($orders->referal_discount > 0){
			  $referral = $orders->referal_discount;
		}

		if($orders->wallet_amount > 0){
			$wallet = $orders->wallet_amount;
			$total_amount = $total_amount - $wallet;
		}
		$amount = single_price($total_amount);
		
		// $deliveryStatus = ucfirst(str_replace('_', ' ', $orders->order_status)).' '.date('d M,Y H:i:s', strtotime($orders->updated_at));
		$deliveryStatus = ucfirst(str_replace('_', ' ', $orders->order_status));

		$deliveryDate = date('d/m/Y H:i:s', strtotime($orders->updated_at));
		$paymentType = ucfirst(str_replace('_', ' ', $orders->payment_type));

		if(!empty($address->address)){
			$user_address = $address->address;
		}else{
			$user_address = "";
		}

		return [
            // $orders->code.' '.$date,
            $orders->code,
            $date,
			$numProduct,
            $customer_detail,
            $user_address,
            $orders->shipping_pin_code,
            $deliveryBoyName,
            $sortingHub,
            $amount,
            $deliveryStatus,
            $deliveryDate,
            $paymentType,
            $orders->payment_status,
            
        ]; 
    }
}
