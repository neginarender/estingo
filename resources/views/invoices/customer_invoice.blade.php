<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <meta http-equiv="Content-Type" content="text/html;"/>
    <meta charset="UTF-8">
	<style media="all">
		@font-face {
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
        }
		body{
			font-size: .875rem;
		}
		.gry-color *,
		.gry-color{
			color:#878f9c;
		}
		table{
			width: 100%;
		}
		table th{
			font-weight: normal;
		}
		table.padding th{
			padding: .5rem .7rem;
		}
		table.padding td{
			padding: .7rem;
		}
		table.sm-padding td{
			padding: .2rem .7rem;
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
		.small{
			font-size: .85rem;
		}
		.currency{

		}

		.badge {
			display: inline-block;
			min-width: 10px;
			padding: 3px 7px;
			font-size: 12px;
			font-weight: 700;
			line-height: 1;
			color: #fff;
			text-align: center;
			white-space: nowrap;
			vertical-align: middle;
			background-color: #777;
			border-radius: 10px;
		}

		.badge-success {
    		background-color: #44ad4c;
		}	

		#container .badge {
			font-size: .9em;
			font-weight: 600;
		}
		#container .badge:not(.badge-default) {
			color: #fff;
		}

	</style>
</head>
<body>
	<div>

		@php
			$generalsetting = \App\GeneralSetting::first();
			//$site_name = "Rozana Social Commerce Private Limited";
			$site_name = "FRESHCARTONS RETAIL AND DISTRIBUTION PVT LTD";
			$shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$order->shipping_pin_code.'"]\')')->selectRaw('user_id as sorting_hub_id,cin_no,gstn_no,address')->first('sorting_hub_id');

			$i=0;

		@endphp

		<div style="background: #eceff4;padding: 1.5rem;">
			<table>
				<tr>
					<td>
						@if($generalsetting->logo != null)
							<img loading="lazy"  src="{{ my_asset($generalsetting->logo) }}" height="40" style="display:inline-block;">
						@else
							<img loading="lazy"  src="{{ static_asset('frontend/images/logo/logo.png') }}" height="40" style="display:inline-block;">
						@endif
					</td>
					<td style="font-size: 2.5rem;" class="text-right strong">{{  translate('INVOICE') }}</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1.2rem;" class="strong">{{ $site_name }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{ $shortId->address }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">GSTN No: {{ $shortId->gstn_no }}</td>
					<td class="text-right"></td>
				</tr> 
				<tr>
					<td class="gry-color small">{{  translate('Email') }}: {{ $generalsetting->email }}</td>
					<td class="text-right small"><span class="gry-color small">{{  translate('Order ID') }}:</span> <span class="strong">{{ $order->code }}</span></td>
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Phone') }}: {{ $generalsetting->phone }}</td>
					<td class="text-right small"><span class="gry-color small">{{  translate('Order Date') }}:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span></td>
				</tr>
				<!-- 18-10-2021 -->
				<!--<tr>
					<td class="gry-color small">{{  translate('Delivery Slot') }}: {{ $order->delivery_slot }}</td>
					
				</tr>-->
			</table>

		</div>

		<div style="padding: 1.5rem;padding-bottom: 0">
            <table>
				@php
					$shipping_address = json_decode($order->shipping_address);
				@endphp
				<tr><td class="strong small gry-color">{{ translate('Bill to') }}:</td></tr>
				<tr><td class="strong">{{ $shipping_address->name }}</td></tr>
				<tr><td class="gry-color small">{{ $shipping_address->address }}, {{ $shipping_address->city }}, {{ $shipping_address->country }}</td></tr>
				<tr><td class="gry-color small">{{ translate('Email') }}: {{ $shipping_address->email }}</td></tr>
				<tr><td class="gry-color small">{{ translate('Phone') }}: {{ $shipping_address->phone }}</td></tr>
			</table>
		</div>

	    <div style="padding: 1.5rem;">
			<table class="padding text-left small border-bottom">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
					    <th width="4%" class="min-col">#</th>
					    <th width="7%">{{translate('Photo')}}</th>
	                    <th width="10%">{{ translate('Product Name (UOM)') }}</th>
						{{-- <th width="15%">{{ translate('Delivery Type') }}</th> --}}
	                    <th width="7%">{{ translate('Qty') }}</th>
	                    <th width="10%">{{ translate('Unit Price (MRP)') }}</th>
						@if(!empty($order->referal_discount))
						<th width="9%">{{ translate('Discounted (Value)') }}</th>
						@endif
						<th width="9%">{{ translate('Taxable Value') }}</th>
						<th width="9%">{{ translate('Tax Rate') }}</th>
	                    <th width="10%">{{ translate('Tax Amount') }}</th>
	                    <th width="15%" class="text-right">{{ translate('Total Amount') }}</th>
	                </tr>
				</thead>
				@php
				$total_unpaid = 0;
				@endphp
				<tbody class="strong">
	                @foreach ($order->orderDetails->where('delivery_status','!=','return') as $key => $orderDetail)
		                @if ($orderDetail->product != null)
		                @php
						

						$discountedprice = $orderDetail->price/$orderDetail->quantity - $orderDetail->peer_discount/$orderDetail->quantity;
						$taxableprice = ($discountedprice*100)/(100+$orderDetail->product->tax);
						if($orderDetail->product->tax==0){
							$taxableprice = 0;
						}else{
							$taxableprice = ($discountedprice*100)/(100+$orderDetail->product->tax);
						}
						@endphp
							<tr class="">
							    <td>{{ $i+1 }}</td>
							    <td>
                                        @if ($orderDetail->product != null)
                    						<img height="50" src="{{ my_asset($orderDetail->product->thumbnail_img) }}">
                                        @else
                                            <strong>{{ translate('N/A') }}</strong>
                                        @endif
                                </td> 
								<td>{{ $orderDetail->product->name }} @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif
								<br />			
								@if($orderDetail->add_by_admin==1)
											 	<span class="badge badge-success">Added</span>
											@endif
								</td>
								{{-- <td>
									@if ($orderDetail->shipping_type != null && $orderDetail->shipping_type == 'home_delivery')
										{{ translate('Home Delivery') }}
									@elseif ($orderDetail->shipping_type == 'pickup_point')
										@if ($orderDetail->pickup_point != null)
											{{ $orderDetail->pickup_point->name }} ({{ translate('Pickip Point') }})
										@endif
									@endif
								</td> --}}
								<td class="gry-color">{{ $orderDetail->quantity }}</td>
								<td class="gry-color currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($orderDetail->price/$orderDetail->quantity) }}</td>
								@if(!empty($order->referal_discount))
								<td class="gry-color currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($orderDetail->price/$orderDetail->quantity - $orderDetail->peer_discount/$orderDetail->quantity) }}</td>
								@endif

								
								<td class="gry-color currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($taxableprice*$orderDetail->quantity) }}</td>
								<td class="gry-color">{{ $orderDetail->product->tax }}%</td>

								<td class="gry-color currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price(($orderDetail->tax/$orderDetail->quantity)*$orderDetail->quantity) }}</td>
								 <td class="text-right currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($orderDetail->price - $orderDetail->peer_discount) }}</td>
			                   <!--  <td class="text-right currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($orderDetail->price+$orderDetail->tax  - $orderDetail->peer_discount) }}</td> -->
							</tr>
							@php
							$i++;
							@endphp
		                @endif

						
						
					@endforeach
	            </tbody>
			</table>
		</div>

	    <div style="padding:0 1.5rem;">
	        <table style="width: 40%;margin-left:auto;" class="text-right sm-padding small strong">
		        <tbody>
				@if($order->coupon_discount !="0.00")
					<tr>
			            <th class="gry-color text-left">{{ translate('Coupon Discount') }}</th>
			            <td class="currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->coupon_discount) }}</td>
			        </tr>
					@endif
			        <tr>
			            <th class="gry-color text-left">{{ translate('Total Value') }}</th>
			            <td class="currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->orderDetails->where('delivery_status','!=','return')->sum('price')) }}</td>
			        </tr>
			        <tr>
			            <th class="gry-color text-left">{{ translate('Delivery Charges') }}</th>
			            <td class="currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->orderDetails->where('delivery_status','!=','return')->sum('shipping_cost')) }}</td>
			        </tr>
			        <tr class="border-bottom">
			            <th class="gry-color text-left">{{ translate('Total Tax') }}</th>
			            <td class="currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->orderDetails->where('delivery_status','!=','return')->sum('tax')) }}</td>
			        </tr>
			       
				    	@php
		                    $total_amount = $order->orderDetails->where('delivery_status','!=','return')->sum('price') + $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('shipping_cost') - $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('peer_discount');

		                    if($order->referal_discount > 0){
		                          $referral = $order->referal_discount;
		                         
		                    }

		                    if($order->wallet_amount > 0){
		                        $wallet = $order->wallet_amount;
		                        $total_amount = $total_amount - $wallet;
		                    }
		                    
		                @endphp

		                @if($order->referal_discount > 0)
	                 	<tr class="border-bottom">
				            <th class="gry-color text-left"><span style="color: green;font-weight: bold">{{ translate('Total Savings') }}</span></th>
				            <td class="currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->referal_discount) }}</td>
				        </tr>
                	@endif

                	@if($order->payment_type != 'wallet')
                	<tr class="border-bottom">
				            <th class="gry-color text-left">{{ translate('Amount Paid from Wallet') }}</th>
				            <td class="currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->wallet_amount) }}</td>
				    </tr>
				    @endif

		            @if($order->payment_type != 'wallet')
			        <tr>
			            <th class="text-left strong" style="font-weight: bold">{{ translate('Total Amount Payable') }}</th>
			            <td class="currency" style="font-weight: bold"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($total_amount) }}</td>
			        </tr>
			        @endif

			         
                	
			        @if($order->payment_type == 'wallet')

			        <tr>
	                        <th class="text-left strong">{{ translate('Amount Paid from Wallet')}}</th>
	                        <td class="currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->wallet_amount) }}</span>
	                        </td>
	                    </tr>
	                    <tr>
                              <th class="text-left strong" style="font-weight: bold">{{ translate('Total Amount Payable')}}</th>
                              <td class="currency" style="font-weight: bold"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price($order->wallet_amount) }}</span>
                            </td>
                        </tr>
	                    
	                    <tr>
	                        <th class="text-left strong" style="font-weight: bold">{{ translate('Total Amount Payable')}}</th>
	                        <td class="currency" style="font-weight: bold"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span>{{ pdf_single_price(0) }}</span>
	                        </td>
	                    </tr>

	                    
	                @endif
	                <tr><th></th><td class="text-right">Inclusive of all Taxes</td></tr>
		        </tbody>
		    </table>
	    </div>

	</div>
</body>
</html>
