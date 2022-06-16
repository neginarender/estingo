@php
app()->setLocale(Session::get('invoice_locale'));
@endphp
@if(\App\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()->rtl == 1)
<html dir="rtl" lang="en">
@else
<html lang="en">
@endif
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rozana</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
	<style media="all">
		/*@font-face {
            font-family: 'Roboto';
            src: url("{{ my_asset('fonts/Roboto-Regular.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }
        *{
            margin: 0;
            padding: 0;
            line-height: 1.3;
            font-family: 'Roboto';
            color: #333542;
        }*/
        * {
		    font-size: 13px;
		    /*font-family: 'Roboto', sans-serif;*/
		    font-family: 'Roboto', TimesNewRoman, 'Times New Roman', Times, Baskerville, Georgia, serif, sans-serif;
		}

		body{
			font-size: .9rem; color: #000; letter-spacing: .05rem; font-weight: 600;
		}
		.gry-color *,
		.gry-color{
			color:#000;
		}
		table{
			@if(Session::has('invoice_size'))
				@if(Session::get('invoice_size')=="reel")
				width: 375px;
				@else
				width: 400px;
				@endif
				
			@else
			width: 400px;
			@endif
			
			margin: 0 auto;
			font-weight:700;
		}
		table th{
			font-weight: normal;
		}
		table.padding th{
			padding: .5rem .3rem; font-weight: 500
		}
		table.padding td{
			padding: .5rem .3rem; font-size: 13px; line-height: 18px
		}
		table.sm-padding th{
			padding: .3rem .3rem; font-size: 13px; line-height: 18px; font-weight: 600;
		}
		table.sm-padding td{
			padding: .2rem .3rem; font-size: 13px; line-height: 16px;
		}
		.strong {font-weight: 600}
		td,
		th,
		tr,
		table {
		     
		    border-collapse: collapse;
		}
		.border-bottom td,
		.border-bottom th{
			border-bottom:1px solid #eceff4;
		}
		.text-left{
			text-align:left;
		}
		.text-right{
			text-align:right;
		}
		.text-center{
			text-align:center;
		}
		.small{
			font-size: .90rem; font-weight:500
		}
		.currency{

		}
		.border{ border-top:dotted 1px #aaa; }



		@media print {
		  #printPageButton {
		    display: none;
		  }
		  body { 
		    -webkit-print-color-adjust: exact; 
		  }	

		}
	</style>
</head>

<body>
@php

if(Session::has('invoice_locale')){

$locale = Session::get('invoice_locale', Config::get('app.locale'));

}

else{

$locale = 'en';

}
@endphp

<select id = "lang_change">
@foreach (\App\Language::all() as $key => $language)
	<option value = "{{ $language->code}}" @if(Session::get('invoice_locale') == $language->code) {{"selected"}} @endif>{{translate( $language->name) }}</option>
@endforeach	
</select>
		@php
			$shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$order->shipping_pin_code.'"]\')')->selectRaw('user_id as sorting_hub_id,cin_no,gstn_no,address,base_state')->first('sorting_hub_id');
			$shipping_address = json_decode($order->shipping_address);
			$invoice = \App\Invoice::where('order_id',$order->id)->first();
			$text = "Original";
			if(!is_null($invoice)){
				
				$inv_no = $invoice->$inv_type;
				if($inv_no>0){
					$text = "Duplicate-".$inv_no;
				}

			}
		$shipping_district = \App\Area::where('pincode',$order->shipping_pin_code)->first('district_id');
		$shipping_state = 0;
		if(!is_null($shipping_district)){
			$shipping_state = \App\City::where('id',$shipping_district->district_id)->first('state_id');
		}
		@endphp
	<div style="margin-left:auto;margin-right:auto;">
		<table class="padding">
			<tr>
				<td colspan="2" class="text-center"> 
				<button class="btn btn-success" id="printPageButton" onclick='save_print("{{ $subOrder->delivery_name }}","{{ $order->id }}"),window.print()' style="float:right;">Print</button> 
				<strong style="float:right;">
				{{ $text }}
				</strong>
					 <img loading="lazy"  src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/logo/SPmFq00vYatU53v08nw5G9NXdWZFxVco6uuk7SGF.jpg" height="50"  >
				</td>
				
			</tr>

			<tr>
				<td colspan="2" class="text-center"> 
				<h4 style="margin: 0 0 3px; font-size: 16px">{{translate('FRESHCARTONS RETAIL AND DISTRIBUTION PVT LTD')}}</h4>	
				{{ translate($shortId['address'])}} <br />
				{{ translate("Email")}}: {{ translate("customercare@rozana.in")}}<br />
				{{ translate("Phone")}}: 9667018020
				</td>
				
			</tr>
				
			<tr>
				<td colspan="2" class="border text-center">
					{{ translate("CIN")}}:  {{ translate($shortId['cin_no'])}}<br>
					{{ translate("GSTIN")}}: {{ translate($shortId['gstn_no'])}} <br>
					{{ translate("FSSAI No")}}: 10020051003948
				</td>
			</tr>
			<!-- <tr>
				<td colspan="2" class="border text-center">
					 <h4 style="margin: 0 0 3px; font-size: 16px">Address</h4>
					Plot No: 4 Community Center <br>
						Area:  East of Kailash, Near National Heart Institute <br>
						City: New Delhi - 110065
						14 Community Center, East of Kailash, Near National Heart Institute, New Delhi - 110065
				</td>
			</tr> -->
				
			<tr>
				<td colspan="2" class="border text-center">
					 <h4 style="margin: 0 0 3px; font-size: 16px">{{ translate("Bill To")}}</h4>
						<!-- Plot No: 4 Community Center <br>
						Area:  East of Kailash, Near National Heart Institute <br>
						City: New Delhi - 110065 -->
						{{ translate('Name') }}:{{ translate($shipping_address->name) }},<br />
						{{ translate('Address') }}:{{ translate($shipping_address->address) }}, {{ translate($shipping_address->city) }}, {{ translate($shipping_address->country) }},{{ translate($shipping_address->postal_code) }}
						<br />
						{{ translate('Email') }}: {{ translate($shipping_address->email) }}<br />
						{{ translate('Phone') }}: {{ translate($shipping_address->phone) }}
				</td>
			</tr>
			<tr>
				<td style="padding-bottom: 0"  colspan="2" class="border text-center">
					 <h4 style="margin: 0 0 3px; font-size: 16px">{{ translate("Tax Invoice")}}</h4>
					 
				</td>
			</tr>
			<tr>
				<td  >
					 {{translate("Order No.")}}: {{ translate($order->code) }} <br>
					 
				</td>
				<td  >
					{{translate("Date")}}: {{ date('d-m-Y h:i:s a', $order->date) }} <br>
					 
				</td>
			</tr>
			<tr>
				<td  >
					{{translate("Payment Mode")}}: @if($order->payment_type=="cash_on_delivery") {{translate("COD")}} @else {{translate("Online")}} @endif <br>
					 
				</td>
				<td  >
					{{translate("Status")}}: {{translate(ucfirst($order->payment_status)) }} <br>
					 
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table class="text-left sm-padding ">
						<tr style="border-bottom:dotted 1px #aaa; ">
							<!-- <th class="border" style="width: 20px">HSN </th> -->
							<th class="border" style="width: 200px">{{translate("Particulars")}}</th>
							<th class="border">{{translate("Qty")}}</th>
							<th class="border">{{translate("MRP")}}</th>
							@if($shortId->sorting_hub_id == 1839)
							<th class="border">{{translate("Discounted Price")}}</th>
							@endif
							<th class="border">{{translate("Value")}}</th>
						</tr>
						@php 
						$tax_key = 0; 
						$no_of_items = 0;
						$total_qty = 0;
						$total_amount = 0;
						$total_saving = 0;
						$collect_tax = [];
						$total_tax_amount = [];
						$complementary = 0;
						$total_saving_in_free = 0;
						$total_mrp = 0;
						$total_shipping_cost = 0;
						
						@endphp
						@foreach ($orderproducts as $key => $orderDetail)
						@php 
							$taxable_amount = 0;
							$total_tax = 0;
						@endphp
						
						<tr>
							<!-- <td></td> -->
							<td colspan="4">
								{{ ++$tax_key }})
								@if($shortId['base_state']==$shipping_state->state_id)
								{{translate("CGST")}} @ {{ $key/2 }}%, {{translate("SGST")}} @ {{ $key/2 }}%
								@else
								{{translate("IGST")}} @ {{ $key }}%
								@endif
								</td>
						</tr>
						@foreach($orderDetail as $k=>$odDetail)
						@php

						if($odDetail->product['category_id'] == '18' || $odDetail->product['category_id']=='26' ||$odDetail->product['category_id']=='33'){
							$complementary = 1;
						}

						 ++$no_of_items;
						 $total_qty+=$odDetail->quantity;
						 $total_mrp+=$odDetail->price;
						 $total_shipping_cost+=$odDetail->shipping_cost;
						 $total_amount += $odDetail->price-$odDetail->peer_discount;
						 $total_saving += $odDetail->peer_discount;
						 $taxable_amount += (($odDetail->price - $odDetail->peer_discount)*100)/(100+$odDetail->product->tax);
						 $total_tax += $odDetail->tax/$odDetail->quantity*$odDetail->quantity;
						 @endphp
						<tr>
							<!-- <td>1001</td> -->
							<td>{{ translate($odDetail->product->name) }} @if($odDetail->variation != null) ({{ translate($odDetail->variation) }}) @endif</td>
							<td>{{ $odDetail->quantity }}</td>
							<td><span>₹ </span>{{ pdf_single_price($odDetail->price/$odDetail->quantity) }}</td>
							@if($shortId->sorting_hub_id == 1839)
							<td><span>₹ </span>{{translate(pdf_single_price($odDetail->price/$odDetail->quantity) - pdf_single_price($odDetail->peer_discount/$odDetail->quantity))}}</td>
							@endif
							<td><span>₹ </span>{{ pdf_single_price($odDetail->price - $odDetail->peer_discount)}}</td>
						</tr>
						@endforeach
						
						@if($key==0 && $complementary==1)
						@foreach(complementary_products($shortId) as $key => $product)
						<tr>
							@php 
							++$no_of_items;	
							++$total_qty;
							$total_saving_in_free += ($order->used_referral_code==1) ? peer_discounted_newbase_price($product->id,$shortId) : price($product->id,$shortId);
							@endphp
							<!-- <td>1001</td> -->
							<td>{{translate($product->name) }} @if(!is_null($product->stocks()->first()))({{ $product->stocks()->first()->variant }})@endif</td>
							<td>1</td>
							
							<td><span>₹ </span>{{ pdf_single_price(price($product->id,$shortId)) }}</td>
							@if($shortId->sorting_hub_id == 1839)
							<td><span>₹ </span>0</td>
							@endif
							<td><span>₹ </span>0 </td>
						</tr>
						@endforeach
						@endif
						@php 
						$collect_tax[$key] = $taxable_amount;
						$total_tax_amount[$key]= $total_tax;
						@endphp 
						@endforeach
					</table>

				</td>
			</tr>
			<tr>

				<td colspan="2" class="border">
					<table class="text-left sm-padding">
						<tr>
							<th  style="font-size: 16px; font-weight: 600; width: 60px" >{{translate("Items")}}:  </th>
							<th style="font-size: 16px; font-weight: 600; width: 60px">{{ translate($no_of_items) }}</th>
							<th style="font-size: 16px; font-weight: 600; width: 60px" >{{translate("Qty")}}: </th>
							<th style="font-size: 16px; font-weight: 600; width: 60px" >{{ translate($total_qty) }}</th>
							<th style="font-size: 16px; font-weight: 600;width: 80px;"><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> {{ pdf_single_price(round($total_amount,0,2)) }}</th>
							 
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2"  style="padding: 0">

					<hr style="margin: 0">
					<hr style="margin: 0">
				</td>
			</tr>
			<tr>
				<td colspan="2"  >
					<table class="text-left sm-padding   ">
						<tr>
							<th colspan="5"><span style="display: inline-block; border:dotted 1px #aaa; width: 50px"></span> {{translate("GST Breakup Details")}}<span style="display: inline-block; border:dotted 1px #aaa; width: 50px"></span></th>
							<th colspan="1">{{translate("Amount INR")}}</th>
						</tr>
					
						<tr>
							<th class="border">#</th>
							<th class="border">{{translate("Taxable Amt")}}</th>
							@if($shortId['base_state']==$shipping_state->state_id)
							<th class="border">{{translate("CGST")}}</th>
							<th class="border">{{translate("SGST")}}</th>
							@else
							<th class="border">{{translate("IGST")}}</th>
							@endif
							<th class="border">{{translate("CESS")}}</th>
							<th class="border">{{translate("Total Amt.")}}</th>

						</tr>
						@php $no=0;@endphp
						@foreach($collect_tax as $key => $tax)
						<tr>
							<td>{{ ++$no }}</td>
							<td><span>₹ </span>{{ round($tax,2) }}</td>
							@if($shortId['base_state']==$shipping_state->state_id)
							<td><span>₹ </span>{{ pdf_single_price($total_tax_amount[$key]/2) }}</td>
							<td><span>₹ </span>{{ pdf_single_price($total_tax_amount[$key]/2) }}</td>
							@else
							<td><span>₹ </span>{{ pdf_single_price($total_tax_amount[$key]) }}</td>
							@endif
							<td>----</td>
							<td><span>₹ </span> {{ pdf_single_price($total_tax_amount[$key]) }}</td>
						</tr>
						@endforeach
						
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="2"  style="padding: 0">

					<hr style="margin: 0">
					<hr style="margin: 0">
				</td>
			</tr>
			@if($order->payment_status=="paid" && $subOrder->delivery_name=="full_invoice")
			<tr>
				<th colspan="2" style="text-align: center;"><span style="display: inline-block; border:dotted 1px #aaa; width: 60px"></span>{{translate("Amount Received from customer")}}<span style="display: inline-block; border:dotted 1px #aaa; width: 60px"></span></th>
				 
			</tr>

			<tr>
				<th colspan="2" class="border" style="text-align: center;">
				@if($order->payment_type=="cash_on_delivery")
 					{{translate("Cash Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price(($total_mrp-$total_saving)+$total_shipping_cost) }}</span>	
				@elseif($order->payment_type=="wallet")
					{{translate("Wallet Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price(($total_mrp-$total_saving)+$total_shipping_cost) }}</span>	
				@elseif($order->payment_type=="razorpay" && $order->wallet_amount==0)
					{{translate("Razorpay Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price(($total_mrp-$total_saving)+$total_shipping_cost) }}</span>	
				@elseif($order->payment_type=="paytm" && $order->wallet_amount==0)
				{{translate("Paytm Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price(($total_mrp-$total_saving)+$total_shipping_cost) }}</span>	
				@elseif($order->payment_type=="razorpay" && $order->wallet_amount!=0)
					{{translate("Razorpay Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price($order->grand_total) }}</span>	
					<br>{{translate("Wallet Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price($order->wallet_amount) }}</span>
				@elseif($order->payment_type="paytm" && $order->wallet_amount!=0)
					{{translate("Paytm Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price($order->grand_total) }}</span>	
					<br>{{translate("Wallet Payment")}}: <span style="padding-left: 30px"><span>₹ </span> {{ pdf_single_price($order->wallet_amount) }}</span>	
				
				@endif	
				</th>
			</tr>

			<tr>
				<td colspan="2"  style="padding: 0">

					<hr style="margin: 0">
					<hr style="margin: 0">
				</td>
			</tr>
			@endif
			<tr>
			<td style="padding: 0;width:200px; float:right;">{{translate("MRP (Inclusive of all taxes)")}}</td>
			<td  style="padding: 0"><span>₹ </span> {{ pdf_single_price($total_mrp)}}</td>
			</tr>
			<tr>
			<td class="border" style="padding: 0; width:200px; float:right;">{{translate("Discount (On MRP)")}}</td>
			<td class="border"  style="padding: 0"><span>₹ </span> {{ pdf_single_price($total_saving) }}</td>
			</tr>
			<!-- <tr>
			<td class="border" style="padding: 0; width:200px; float:right;">Tax</td>
			<td class="border"  style="padding: 0"><span>₹ </span> 100</td>
			</tr> -->
			<tr>
			<td class="border" style="padding: 0; width:200px; float:right;">{{translate("Sub Total")}}</td>
			<td class="border" style="padding: 0"><span>₹ </span> {{ pdf_single_price(round($total_mrp-$total_saving,0,2)) }}</td>
			</tr>
			<tr>
			<td class="border" style="padding: 0; width:200px; float:right;">{{translate("Delivery Charges")}}</td>
			<td class="border" style="padding: 0"><span>₹ </span> {{ pdf_single_price($total_shipping_cost) }}</td>
			</tr>
			<tr>
			<td class="border" style="padding: 0; width:200px; float:right;">{{translate("Total payable amount")}}</td>
			<td class="border" style="padding: 0"><span>₹ </span> {{ pdf_single_price(round(($total_mrp-$total_saving)+$total_shipping_cost,0,2)) }}</td>
			</tr>
			<tr>
			@if($subOrder->delivery_name!="full_invoice")
			@php 
			$generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG(); 
			@endphp
				<td colspan="2"  class="text-center" style="font-size: 15px; font-weight: 600; width: 80px; padding-bottom: 20px; padding-top: 20px">
					 * * {{translate("You have saved")}} Rs. {{ pdf_single_price($total_saving+$total_saving_in_free) }}/- {{translate("In this invoice")}} * * <br><br />
					 <img src="data:image/png;base64,{{ base64_encode($generatorPNG->getBarcode($subOrder, $generatorPNG::TYPE_CODE_128)) }}" style="height: 37px; max-width:180px;"> <br />
					 <small style="font-weight: 400">{{translate("This is a computer generated invoice not requiring signature in billing")}} </small>
				</td>
			</tr>
			@endif
			<!-- <tr>

				<th colspan="2" class="border"   >
					<table class="sm-padding text-left">
						<tr>
							<td>
								Date/Time: 2021-11-30 02:00 PM
							</td>
						</tr>
						<tr>	<td>Card Number: 454874******777  swipe</td></tr>
						<tr>	<td>Card Type: Master</td></tr>
						<tr>	<td>APPR Code: 12122   </td> </tr>
						<tr>	<td>RREF No: 00000454545   </td></tr>
						<tr>	<td>INV No: 9622   </td></tr>
						<tr>	<td>Amount : Rs. 8000.00/-   </td></tr>
						<tr>	<td>Name : Hasan   </td></tr>
						 
					</table>
				</th>
			</tr> -->

			<tr>
				<td colspan="2"  style="padding: 0">

					<hr style="margin: 0">
					<hr style="margin: 0">
				</td>
			</tr>
			
			
			@if($subOrder->delivery_type!="normal")
			<tr>
			
			<td colspan="2" style="padding: 0">
			{{ translate(ucfirst($subOrder->delivery_name)) }} {{translate("Order Delivery Slot")}} : {{ date('d-m-Y',strtotime($subOrder->delivery_date)) }} {{ $subOrder->delivery_time }}
				</td>
			</tr>
			@else
			<tr>
			<td colspan="2" style="padding: 0">
					{{ ucfirst($subOrder->delivery_name) }} Order(#{{ $subOrder->sub_order_code }}) will be delivered with in 24 hrs	
			</td>
			</tr>
			@endif
			<tr>
				
			<td colspan="2" style="padding: 0">

				<hr style="margin: 0">
				<hr style="margin: 0">
			</td>
			</tr>
		

			 

		</table>


	</div>
</body>
<script src=" {{ static_asset('js/jquery.min.js') }}"></script>
<script type="text/javascript">
	function save_print(type,order_id){
		$.post("{{ route('invoice.save_print_invoice') }}",{_token:"{{ csrf_token() }}",type:type,order_id:order_id},function(data){
			console.log(data);
		});
	}

	$(document).ready(function(){
		$("#lang_change").on('change',function(){
			var invoice_locale = $(this).val();
			 $.post('{{ route('language.change-invoice') }}',{_token:'{{ csrf_token() }}', invoice_locale:invoice_locale}, function(data){
                        location.reload();
                    });
 
		});

	});
</script>
</html>