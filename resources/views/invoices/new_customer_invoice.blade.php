<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rozana</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Hind:400,700&amp;subset=devanagari,latin-ext" rel="stylesheet">
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
		@font-face {
			font-family: Hind;
			font-style: normal;
			font-weight: normal;
			src: url(https://fonts.gstatic.com/s/hind/v11/5aU69_a8oxmIdGd4BCOz.woff2) format('woff2');
  			unicode-range: U+0100-024F, U+0259, U+1E00-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
			}
        * {
		    font-size: 13px;
		    /*font-family: 'Roboto', sans-serif;*/
		    font-family: Hind, DejaVu Sans, sans-serif;font-family: 'Roboto', TimesNewRoman, 'Times New Roman', Times, Baskerville, Georgia, serif, sans-serif;
		}

		body{
			font-size: .9rem; color: #000; letter-spacing: .05rem; font-weight: 600;
		}
		.gry-color *,
		.gry-color{
			color:#000;
		}
		table{
            width:450px;
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
	<div style="margin-left:auto;margin-right:auto;">
		<table class="padding">
			<tr>
				<td colspan="2" class="text-center"> 
				<button class="btn btn-success" id="printPageButton" onclick="window.print();" style="float:right;">Print</button>
					 <img loading="lazy"  src="https://rozaana.s3.ap-south-1.amazonaws.com/uploads/logo/SPmFq00vYatU53v08nw5G9NXdWZFxVco6uuk7SGF.jpg" height="50"  >
				</td>
				
			</tr>
			</tr>
				@php
					$shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$order->shipping_pin_code.'"]\')')->selectRaw('user_id as sorting_hub_id,cin_no,gstn_no,address')->first('sorting_hub_id');
					$shipping_address = json_decode($order->shipping_address);
					$shipping_district = \App\Area::where('pincode',$order->shipping_pin_code)->first('district_id');
					$shipping_state = 0;
					if(!is_null($shipping_district)){
						$shipping_state = \App\City::where('id',$shipping_district->district_id)->first('state_id');
					}
				@endphp
			<tr>
				<td colspan="2" class="text-center"> 
				<h4 style="margin: 0 0 3px; font-size: 16px">FRESHCARTONS RETAIL AND DISTRIBUTION PVT LTD</h4>	
				{{ $shortId['address']}} <br />
				Email: customercare@rozana.in<br />
				Phone: 9667018020
				</td>
				
			</tr>
			
			<tr>
				<td colspan="2" class="border text-center">
					CIN:  {{ $shortId['cin_no']}}<br>
					GSTIN: {{ $shortId['gstn_no']}} <br>
					FSSAI No: 10020051003948
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
					 <h4 style="margin: 0 0 3px; font-size: 16px"> Bill To</h4>
						<!-- Plot No: 4 Community Center <br>
						Area:  East of Kailash, Near National Heart Institute <br>
						City: New Delhi - 110065 -->
						{{ translate('Name') }}:{{ $shipping_address->name }},<br />
						{{ translate('Address') }}:{{ $shipping_address->address }}, {{ $shipping_address->city }}, {{ $shipping_address->country }},{{ $shipping_address->postal_code }}
						<br />
						{{ translate('Email') }}: {{ $shipping_address->email }}<br />
						{{ translate('Phone') }}: {{ $shipping_address->phone }}
				</td>
			</tr>
			<tr>
				<td style="padding-bottom: 0"  colspan="2" class="border text-center">
					 <h4 style="margin: 0 0 3px; font-size: 16px">Tax Invoice</h4>
					 
				</td>
			</tr>
			<tr>
				<td  >
					 Order No: {{ $order->code }} <br>
					 
				</td>
				<td  >
					Date: {{ date('d-m-Y', $order->date) }} <br>
					 
				</td>
			</tr>
			<tr>
				<td  >
					 Payment Mode: @if($order->payment_type=="cash_on_delivery") COD @else Online @endif <br>
					 
				</td>
				<td  >
					Status: {{ ucfirst($order->payment_status) }} <br>
					 
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<table class="text-left sm-padding ">
						<tr style="border-bottom:dotted 1px #aaa; ">
							<!-- <th class="border" style="width: 20px">HSN </th> -->
							<th class="border">Particulars</th>
							<th class="border">Qty</th>
							<th class="border">MRP</th>
							<th class="border">Value</th>
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
								CGST @ {{ $key/2 }}%, SGST @ {{ $key/2 }}%
								@else
								IGST @ {{ $key }}%
								@endif
								</td>
						</tr>
						@foreach($orderDetail as $k=>$odDetail)
						@php

						if($odDetail->product['category_id'] == '18' || $odDetail->product['category_id']=='26' || $odDetail->product['category_id']=='33'){
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
							<td>{{ translate($odDetail->product->name) }} @if($odDetail->variation != null) ({{ $odDetail->variation }}) @endif</td>
							<td style="text-align:center;">{{ $odDetail->quantity }}</td>
							<td><span>??? </span>{{ pdf_single_price($odDetail->price/$odDetail->quantity) }}</td>
							<td><span>??? </span>{{ pdf_single_price($odDetail->price - $odDetail->peer_discount)}}</td>
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
							<td>{{ translate($product->name) }} @if(!is_null($product->stocks()->first()))({{ $product->stocks()->first()->variant }})@endif</td>
							<td style="text-align:center;">1</td>
							<td><span>??? </span>{{ pdf_single_price(price($product->id,$shortId)) }}</td>
							<td><span>??? </span>0 </td>
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
							<th  style="font-size: 16px; font-weight: 600; width: 80px" >Items:  </th>
							<th style="font-size: 16px; font-weight: 600; width: 40px">{{ $no_of_items }}</th>
							<th style="font-size: 16px; font-weight: 600; width: 80px" >Qty: </th>
							<th style="font-size: 16px; font-weight: 600; width: 80px;text-align:left;" >{{ $total_qty }}</th>
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
							<th colspan="5"><span style="display: inline-block; border:dotted 1px #aaa; width: 50px"></span> GST Breakup Details<span style="display: inline-block; border:dotted 1px #aaa; width: 50px"></span></th>
							<th colspan="1">(Amount INR)</th>
						</tr>
						<tr>
							<th class="border">#</th>
							<th class="border">Taxable Amt</th>
							@if($shortId['base_state']==$shipping_state->state_id)
							<th class="border">CGST</th>
							<th class="border">SGST</th>
							@else
							<th class="border">IGST</th>
							@endif
							<th class="border">CESS</th>
							<th class="border">Total Amt.</th>

						</tr>
						@php $no=0;@endphp
						@foreach($collect_tax as $key => $tax)
						<tr>
							<td>{{ ++$no }}</td>
							<td><span>??? </span>{{ round($tax,2) }}</td>
							@if($shortId['base_state']==$shipping_state->state_id)
							<td><span>??? </span>{{ pdf_single_price($total_tax_amount[$key]/2) }}</td>
							<td><span>??? </span>{{ pdf_single_price($total_tax_amount[$key]/2) }}</td>
							@else
							<td><span>??? </span>{{ pdf_single_price($total_tax_amount[$key]) }}</td>
							@endif
							<td>----</td>
							<td><span>??? </span> {{ pdf_single_price($total_tax_amount[$key]) }}</td>
						</tr>
						@endforeach
						
					</table>
				</td>
			</tr>
			<!-- <tr>
				<td colspan="2"  style="padding: 0">

					<hr style="margin: 0">
					<hr style="margin: 0">
				</td>
			</tr> -->

			<!-- <tr>
				<th colspan="2" style="text-align: center;"><span style="display: inline-block; border:dotted 1px #aaa; width: 60px"></span> Amount Received from customer<span style="display: inline-block; border:dotted 1px #aaa; width: 60px"></span></th>
				 
			</tr>
			<tr>
				<th colspan="2" class="border" style="text-align: center;">
 					Card Payment: <span style="padding-left: 30px">8000.00</span>		
				</th>
			</tr> -->

			<tr>
				<td colspan="2"  style="padding: 0">

					<hr style="margin: 0">
					<hr style="margin: 0">
				</td>
			</tr>
			<tr>
			<td style="padding: 0;width:200px; text-align:right;">MRP (Inclusive of all taxes)</td>
			<td  style="padding: 0;text-align:center;"><span>??? </span> {{ pdf_single_price($total_mrp)}}</td>
			</tr>
			<tr>
			<td class="border" style="padding: 0; width:200px; text-align:right;">Discount (On MRP)</td>
			<td class="border"  style="padding: 0;text-align:center;"><span>??? </span> {{ pdf_single_price($total_saving) }}</td>
			</tr>
			<!-- <tr>
			<td class="border" style="padding: 0; width:200px; float:right;">Tax</td>
			<td class="border"  style="padding: 0"><span>??? </span> 100</td>
			</tr> -->
			<tr>
			<td class="border" style="padding: 0; width:200px; text-align:right;">Sub Total</td>
			<td class="border" style="padding: 0;text-align:center;"><span>??? </span> {{ pdf_single_price(round($total_mrp-$total_saving,0,2)) }}</td>
			</tr>
			<tr>
			<td class="border" style="padding: 0; width:200px; text-align:right;">Delivery Charges</td>
			<td class="border" style="padding: 0;text-align:center;"><span>??? </span> {{ pdf_single_price($total_shipping_cost) }}</td>
			</tr>
			<tr>
			<td class="border" style="padding: 0; width:200px; text-align:right;">Total payable amount</td>
			<td class="border" style="padding: 0;text-align:center;"><span>??? </span> {{ pdf_single_price(round(($total_mrp-$total_saving)+$total_shipping_cost,0,2)) }}</td>
			</tr>
			<tr>
			@php $generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG(); @endphp
				<td colspan="2"  class="text-center" style="font-size: 15px; font-weight: 600; width: 80px; padding-bottom: 20px; padding-top: 20px">
					 * * You have saved Rs. {{ pdf_single_price($total_saving+$total_saving_in_free) }}/- in this invoice * * <br><br />
					 <img src="data:image/png;base64,{{ base64_encode($generatorPNG->getBarcode($order->code, $generatorPNG::TYPE_CODE_128)) }}" style="height: 37px; max-width:180px;"> <br />
					 <small style="font-weight: 400">This is a computer generated invoice not requiring signature in billing </small>
				</td>
			</tr>

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
			@foreach($schedules as $key=> $schedule)
			@if($schedule->delivery_type!="normal")
			<tr>
			
			<td colspan="2" style="padding: 0">
			{{ translate(ucfirst($schedule->delivery_name)) }} {{translate("Order Delivery Slot")}} : {{ date('d-m-Y',strtotime($schedule->delivery_date)) }} {{ $schedule->delivery_time }}
				</td>
			</tr>
			@else
			<tr>
			<td colspan="2" style="padding: 0">
					{{ ucfirst($schedule->delivery_name) }} Order(#{{ $schedule->sub_order_code }}) will be delivered with in 24 hrs	
			</td>
			</tr>
			@endif
			@endforeach

			<tr>
				<td colspan="2"  style="padding: 0">

					<hr style="margin: 0">
					<hr style="margin: 0">
				</td>
			</tr>

			 

		</table>


	</div>
</body>
</html>