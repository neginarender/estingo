
<html>
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
			font-size: .9rem; color: #000; letter-spacing: .05rem; font-weight: 500
		}
		.gry-color *,
		.gry-color{
			color:#000;
		}
		table{
			width: 450px;
			margin: 0 auto;
		}
		table th{
			font-weight: normal;
		}
		table.padding th{
			padding: .5rem .7rem;
		}
		table.padding td{
			padding: .3rem; font-size: 13px;
		}
		table.sm-padding td{
			padding: .2rem .7rem;
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
		.small{
			font-size: .90rem; font-weight:500
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

		@media print {
  #printPageButton {
    display: none;
  }
  

}
	</style>
</head>
<body>
	<div style="margin-left:auto;margin-right:auto;">

		@php
			$generalsetting = \App\GeneralSetting::first();
			//$site_name = "Rozana Social Commerce Private Limited";
			$site_name = "FRESHCARTONS RETAIL AND DISTRIBUTION PVT LTD";
			$shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$order->shipping_pin_code.'"]\')')->selectRaw('user_id as sorting_hub_id,cin_no,gstn_no,address')->first('sorting_hub_id');

		@endphp

		<div style="background: #eceff4;padding: 1.5rem;">
			<table style="width:53%">
				<tr>
					<td>
						@if (Auth::user()->user_type == 'seller')
							@if(Auth::user()->shop->logo != null)
								<img loading="lazy"  src="{{ my_asset(Auth::user()->shop->logo) }}" height="40" style="display:inline-block;">
							@else
								<img loading="lazy"  src="{{ static_asset('frontend/images/logo/logo.png') }}" height="40" style="display:inline-block;">
							@endif
						@else
							@if($generalsetting->logo != null)
								<img loading="lazy"  src="{{ my_asset($generalsetting->logo) }}" height="40" style="display:inline-block;">
							@else
								<img loading="lazy"  src="{{ static_asset('frontend/images/logo/logo.png') }}" height="40" style="display:inline-block;">
							@endif
						@endif
					</td>
					<td style="font-size: 2.5rem;" class="text-right strong">{{ translate('INVOICE') }}</td>
					<td><button class="btn btn-success" id="printPageButton" onclick="window.print();" style="float:right;">Print</button></td>
				
				</tr>
			</table>
			<table style="width:53%">
				@if (Auth::user()->user_type == 'seller')
					<tr>
						<td style="font-size: 1.2rem;" class="strong">{{ Auth::user()->shop->name }}</td>
						<td class="text-right"></td>
					</tr>
					<tr>
						<td class="gry-color small">{{ Auth::user()->shop->address }}</td>
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
				@else
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
						<td class="gry-color small">{{ translate('Email') }}: {{ $generalsetting->email }}</td>
						<td class="text-right small"><span class="gry-color small">{{ translate('Order ID') }}:</span> <span class="strong">{{ $order->code }}</span></td>
					</tr>
					<tr>
						<td class="gry-color small">{{ translate('Phone') }}: {{ $generalsetting->phone }}</td>
						<td class="text-right small"><span class="gry-color small">{{ translate('Order Date') }}:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span></td>
					</tr>
				@endif
			</table>

		</div>

		<div style="padding: 1.5rem;padding-bottom: 0">
            <table style="width:53%">
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
			<table class="padding text-left small border-bottom" style="width:53%">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
					    <th class="min-col">#</th>
					    {{--<th width="10%">{{translate('Photo')}}</th>--}}
	                    <th width="15%">{{ translate('Product Name (UOM)') }}</th>
						{{-- <th width="10%">{{ translate('Delivery Type') }}</th> --}}
	                    <th width="10%">{{ translate('Qty') }}</th>
	                    <th width="10%">{{ translate('Unit Price (MRP)') }}</th>
						@if(!empty($order->referal_discount))
						<th width="10%">{{ translate('Discounted (Value)') }}</th>
						@endif
						<th width="9%">{{ translate('Taxable Value') }}</th>
						<th width="9%">{{ translate('Tax Rate') }}</th>
	                    <th width="10%">{{ translate('Tax Amount') }}</th>
	                    <th width="10%" class="text-right">{{ translate('Total Amount') }}</th>
	                </tr>
				</thead>
				<tbody class="strong">
					@php
						if ((Auth::user()->user_type == 'seller')) {
							$user_id = Auth::user()->id;
						}
						else {
							$user_id = \App\User::where('user_type', 'admin')->first()->id;
						}
					$i = 0;
					$total_unpaid = 0;
					@endphp
	               
	                 @foreach ($order->orderDetails as $key => $orderDetail)
					 	@php
						if($order->payment_type!="cash_on_delivery" && $orderDetail->add_by_admin==1 && $orderDetail->payment_status=="unpaid"){
							$total_unpaid +=($orderDetail->price-$orderDetail->peer_discount);
						}

						$discountedprice = $orderDetail->price/$orderDetail->quantity - $orderDetail->peer_discount/$orderDetail->quantity;
						$taxableprice = ($discountedprice*100)/(100+$orderDetail->product->tax);
						if($orderDetail->product->tax==0){
							$taxableprice = 0;
						}else{
							$taxableprice = ($discountedprice*100)/(100+$orderDetail->product->tax);
						}
						$taxenew = ($taxableprice*$orderDetail->product->tax)/100;
						
						@endphp
							<tr class="">
							<td>{{ $i+1 }}</td>
							    {{--<td>
                                        @if ($orderDetail->product != null)
                    						<img height="50" src="{{ my_asset($orderDetail->product->thumbnail_img) }}">
                                        @else
                                            <strong>{{ translate('N/A') }}</strong>
                                        @endif
                                </td> --}}
								<td>{{ $orderDetail->product->name }} @if($orderDetail->variation != null) ({{ $orderDetail->variation }}) @endif
									@if($orderDetail->add_by_admin==1)
										<div style="background:#DCDCDC;padding:6px; border-radius: 3px;width: 40px;margin-top: 6px; ">Added</div>
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
								<td class="gry-color currency"><span><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"></span> {{ pdf_single_price($orderDetail->price/$orderDetail->quantity) }}</td>
								@if(!empty($order->referal_discount))
								<td class="gry-color currency"><span><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"></span> {{ pdf_single_price($orderDetail->price/$orderDetail->quantity - $orderDetail->peer_discount/$orderDetail->quantity) }}</td>
								@endif

								<td class="gry-color currency"><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"></span> {{ pdf_single_price($taxableprice*$orderDetail->quantity) }}</td>

								<td class="gry-color">{{ $orderDetail->product->tax }}%</td>

								<td class="gry-color currency"><span><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"></span> {{ pdf_single_price($taxenew*$orderDetail->quantity) }}
									<!-- {{ pdf_single_price(($orderDetail->tax/$orderDetail->quantity)*$orderDetail->quantity) }} 
								}
								}-->
								</td>
								 
								 <td class="text-right currency"><span><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span> {{ pdf_single_price($orderDetail->price - $orderDetail->peer_discount) }}</td>
			                   <!--  <td class="text-right currency">{{ pdf_single_price($orderDetail->price+$orderDetail->tax  - $orderDetail->peer_discount) }}</td> -->
								
							</tr>
		                
						@php
						$i++;
						@endphp
					@endforeach
	            </tbody>
			</table>
		</div>

	    <div style="padding:0 1.5rem;">
	        <table style="margin-left:auto; width:53%" class="text-right sm-padding small strong">
		        <tbody>
			        <tr>
			            <th class="gry-color text-left">{{ translate('Total Value') }}</th>
			            <td class="currency"><span  ><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span>{{pdf_single_price($order->orderDetails->where('delivery_status','!=','return')->sum('price')) }}</td>
			        </tr>
			        <tr>
			            <th class="gry-color text-left">{{ translate('Delivery Charges') }}</th>
			            <td class="currency"><span  ><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span>{{ pdf_single_price($order->orderDetails->where('delivery_status','!=','return')->sum('shipping_cost')) }}</td>
			        </tr>
			        <tr class="border-bottom">
			            <th class="gry-color text-left">{{ translate('Total Tax') }}</th>
			            <td class="currency"><span  ><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span>{{ pdf_single_price($order->orderDetails->where('delivery_status','!=','return')->sum('tax')) }}</td>
			        </tr>

			       
                	
					@php 
					$check_refund = \App\RefundRequest::where('order_id',$order->id)->first();
				@endphp
				@if(!empty($check_refund))
				@php 
					$refunded_amount = \App\RefundRequest::where('order_id',$order->id)->sum('refund_amount');
				@endphp
				@endif


				@php
						
                    $total_amount = $order->orderDetails->where('delivery_status','!=','return')->sum('price') + $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('shipping_cost') - $order->orderDetails->where('order_id', $order->id)->where('delivery_status','!=','return')->sum('peer_discount');

                    if($order->referal_discount > 0){
                          $referral = $order->referal_discount;
                          //$total_amount = $total_amount - $referral;
                    }

                    if($order->wallet_amount > 0){
                        $wallet = $order->wallet_amount;
                        $total_amount = $total_amount - $wallet;
                    }
                    
                @endphp

                @if($order->referal_discount > 0)
	                 	<tr class="border-bottom">
				            <th class="gry-color text-left"><span style="color: green;font-weight: bold">{{ translate('Total Savings') }}</span></th>
				            <td class="currency"><span  ><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span>{{ pdf_single_price($order->referal_discount) }}</td>
				        </tr>
                	@endif

                	@if(!empty($order->wallet_amount))
                	<tr class="border-bottom">
				            <th class="gry-color text-left">{{ translate('Amount Paid from Wallet') }}</th>
				            <td class="currency"><span  ><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span>{{ pdf_single_price($order->wallet_amount) }}</td>
				    </tr>
					@endif
			        <tr>
			            <th class="text-left strong" style="font-weight: bold">{{ translate('Total Amount Payable') }}</th>
			            <td class="currency"><span  ><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span>
						
						  {{ pdf_single_price($total_amount) }}
			            	<!-- {{ pdf_single_price($order->grand_total) }} -->
			            	<!-- {{ pdf_single_price($order->orderDetails->sum('price') + $order->orderDetails->sum('shipping_cost') + $order->orderDetails->sum('tax')) }} -->

			            </td>
			        </tr>

					@if($total_unpaid>0)
					<tr>
						<th class="text-left strong">{{ translate('Total Amount Unpaid') }}</th> 
						<td class="currency"><span  ><img src="http://i.stack.imgur.com/nGbfO.png" width="8" height="10"> </span>
						{{ pdf_single_price($total_unpaid) }} 
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
