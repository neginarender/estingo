<?php

namespace App;

use App\Product;
use App\ProductStock;
use App\Seller;
use App\User;
Use App\ShortingHub;
use App\Order;
use App\PeerPartner;
use App\OrderReferalCommision;
use App\Category;
use App\SubCategory;
use App\SubSubCategory;
use App\Brand;
use App\ReferalUsage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use DB;

class SaleDataExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize
{	
	public function __construct($sortinghubid = NULL,$from_date = NULL,$to_date = NUll)
    {
        $this->sorting_hub_id = $sortinghubid;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }
    public function collection()
    {
        $pay_status = "paid";
        $from_date = $this->from_date;
        $to_date = $this->to_date;
        // dd($this->sorting_hub_id);
    	if(isset($this->sorting_hub_id)){
            // dd('1');
                $sorting_hub_id = $this->sorting_hub_id;

                $area_pincodes = ShortingHub::where('user_id',$sorting_hub_id)->first()->area_pincodes;
                
                $area_pincodes = explode('","', $area_pincodes);
                $area_pincodes = str_replace('["',' ',$area_pincodes);
                $area_pincodes = str_replace('"]',' ',$area_pincodes);

                $products = DB::table('orders')->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))
                ->orderBy('orders.created_at', 'DESC')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->whereIn('orders.shipping_pin_code', $area_pincodes) 
                ->where('log', 0)
                ->where('orders.payment_status', $pay_status)
                ->select('orders.*','order_details.*');

            }else{
                // dd('2');
                $sortinghubids = ShortingHub::first();
                $this->sorthub = $sorting_hub_id = $sortinghubids->user_id;

                $area_pincodes = ShortingHub::where('user_id',$sorting_hub_id)->first()->area_pincodes;
                
                $area_pincodes = explode('","', $area_pincodes);  
                $area_pincodes = str_replace('["','',$area_pincodes);
                $area_pincodes = str_replace('"]','',$area_pincodes);

                $products = DB::table('orders')->whereBetween(DB::raw('DATE(orders.created_at)'), array($from_date, $to_date))
                ->orderBy('orders.created_at', 'DESC')
                ->join('order_details', 'orders.id', '=', 'order_details.order_id')
                ->whereIn('orders.shipping_pin_code', $area_pincodes) 
                ->where('log', 0)
                ->where('orders.payment_status', $pay_status)
                ->select('orders.*','order_details.*');


            }
                $products = $products->get();
                // $products = $products->limit(100)->get();
		return $products;

    }

    public function headings(): array
    {
			
        return [
            'Order ID',
            'Date',
            'Order No.',
            'Sorting Hub',
            'SKU Code',
            'SKU Name',
            'Customer Name',
            'Mobile No.',
            'Address',
            'MRP',
            'Buying Price',
            'Selling Price',
            'Weight',
            'Weight unit',
            'HSN Code',
            'GST%',
            'Margin',
            'Peer Commission',
            'Master Commission',
            'Rozana Margin',
            'Mode Of Payment',
            'Category',
            'Sub Category',
            'Sub Sub Category',
            'Brand',
            'Peer Code',
            'Master Code'
        ];
    }

    /**
    * @var Order $order
    */
    public function map($products): array
    {
        // $sorts = isset($this->sorthub)?$this->sorthub:$this->sorting_hub_id;       
        // dd($this->sorting_hub_id);
        $sku = ProductStock::where('product_id', $products->product_id)->first();
        $product_detail = Product::where('id', $products->product_id)->first();
        $product_mapped = MappingProduct::where('product_id', $products->product_id)->where('sorting_hub_id', $this->sorting_hub_id)->first();

        $product_cat = Category::where('id', $product_detail['category_id'])->first();
        $product_subcat = SubCategory::where('id', $product_detail['subcategory_id'])->first();
        $product_subsubcat = SubSubCategory::where('id', $product_detail['subsubcategory_id'])->first();
        $product_brand = Brand::where('id', $product_detail['brand_id'])->first();

        // dd($products->product_id);

		$order_date = date('d-m-Y H:i:s',strtotime($products->created_at));
        if(!empty($product_mapped->sorting_hub_id)){
            $sortingHub = User::where('id', $product_mapped->sorting_hub_id)->first()['name'];
        }else{
            $sortingHub = 'NA';
        }        
		
		$stock_price = '₹ '.$sku['price'];
		if($product_mapped['purchased_price'] == 0){
			$purchase_price =  '₹ '.$product_detail['purchase_price'];
		}else{
			$purchase_price =  '₹ '.$product_mapped['purchased_price'];
		}

		// if($product_mapped['selling_price'] == 0){
		// 	$selling_price = '₹ '.$sku['price'];
		// }else{
		// 	$selling_price = '₹ '.$product_mapped['selling_price'];
		// }

        $selling_price = single_price($products->price/$products->quantity - $products->peer_discount/$products->quantity);

		$weight = "";
		if (@$product_detail['choice_options'] != null){
			foreach (json_decode($product_detail['choice_options']) as $key => $choice){
				foreach ($choice->values as $key => $value){
					$weight .= $value;
				}
			}
											
			$weight .= '/';								
		}
																				
		if ($product_detail['choice_options'] != null){
			$arr = array();
			foreach (json_decode($product_detail['choice_options']) as $key => $choice){
				$name = Attribute::where('id',$choice->attribute_id)->first()->name;
				array_push($arr,$name);
			}
			$weightUnit =  implode(' /',$arr);
		}							
									
		if($product_detail['tax_type'] == 'percent'){
		 	$tax = $product_detail['tax'].' %';
		}else{
			$tax = '₹ '.$product_detail['tax'];
		}

       

        if(!empty(@$products->shipping_address)){           
            $customer_name = @json_decode($products->shipping_address)->name; 
        }else{
            $customer_name = 'NA';
        }


        $delivery_peercode = ReferalUsage::where('order_id',$products->order_id)->first('referal_code');
        // dd($delivery_peercode);
        if(!empty($delivery_peercode)){
            $peercode = $delivery_peercode->referal_code;
            $parents = PeerPartner::where('code', $peercode)->first();

            $parents_code = PeerPartner::where('id', $parents->parent)->first();
            $peer_parentcode = $parents_code->code;
        }
        else{
            $peercode = 'NA';
            $peer_parentcode = 'NA';
        }
        
        $customer_email = @json_decode($products->shipping_address)->phone;
        $customer_address = @json_decode($products->shipping_address)->address;

		//if($products->published == '1'){ $published = 'Published';}else{ $published = 'Unpublished';}
		return [
            $products->id,
            $order_date,
            $products->code,
            $sortingHub,
            $sku['sku'],
            $product_detail['name'],
            $customer_name,
            $customer_email,
            $customer_address,
            $stock_price,
            $purchase_price,
            $selling_price,           
            $value,
            $weightUnit,
            $product_detail['hsn_code'],
            $tax,
            $products->sub_peer,
            $products->master_peer,
            $products->orderrozana_margin,
            $products->order_margin,
            $products->payment_type,
            $product_cat['name'],
            $product_subcat['name'],
            $product_subsubcat['name'],
            $product_brand['name'],
            $peercode,
            $peer_parentcode
        ]; 
    }
}
