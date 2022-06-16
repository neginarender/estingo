
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
	</style>
</head>
<body>
	<div>

		@php
			$generalsetting = \App\GeneralSetting::first();
			$site_name = "Rozana Social Commerce Private Limited";
			//$site_name = "FRESHCARTONS RETAIL AND DISTRIBUTION PVT LTD";
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
					<td style="font-size: 1.5rem;" class="text-right strong">{{  $category_name['name'] }}</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1.2rem;" class="strong">{{ $site_name }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{ $generalsetting->address }}</td>
					<td class="text-right"></td>
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Email') }}: {{ $generalsetting->email }}</td>
					{{-- <td class="text-right small"><span class="gry-color small">{{  translate('Order ID') }}:</span> <span class="strong">{{ $order->code }}</span></td> --}}
				</tr>
				<tr>
					<td class="gry-color small">{{  translate('Phone') }}: {{ $generalsetting->phone }}</td>
					{{-- <td class="text-right small"><span class="gry-color small">{{  translate('Order Date') }}:</span> <span class=" strong">{{ date('d-m-Y', $order->date) }}</span></td> --}}
				</tr>
			</table>

		</div>

		<div style="padding: 1.5rem;padding-bottom: 0">
            {{-- <table>
				@php
					$shipping_address = json_decode($order->shipping_address);
				@endphp
				<tr><td class="strong small gry-color">{{ translate('Bill to') }}:</td></tr>
				<tr><td class="strong">{{ $shipping_address->name }}</td></tr>
				<tr><td class="gry-color small">{{ $shipping_address->address }}, {{ $shipping_address->city }}, {{ $shipping_address->country }}</td></tr>
				<tr><td class="gry-color small">{{ translate('Email') }}: {{ $shipping_address->email }}</td></tr>
				<tr><td class="gry-color small">{{ translate('Phone') }}: {{ $shipping_address->phone }}</td></tr>
			</table> --}}
		</div>

	    <div style="padding: 1.5rem;">
			<table class="padding text-left small border-bottom">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
                    <th>#</th>
                        <th width="35%">{{ translate('Product Image') }}</th>
	                    <th width="35%">{{ translate('Product Name') }}</th>
                        <th width="35%">{{ translate('quantity') }}</th>
	                </tr>
				</thead>
				<tbody class="strong">
                @php
                $i=1;
                @endphp
	                @foreach ($products as $key => $value)
		                @if ($value != null)
							<tr class="">
                                <td>{{$i}}</td>
                                <td>
                                <img loading="lazy"  src="{{ my_asset($value->thumbnail_img) }}" height="40" style="display:inline-block;">
					            </td>
								<td>{{ $value->name }}</td>
                                @php
                                    $variant = json_decode($value->choice_options);
                                @endphp
                                @foreach($variant as $k=>$v)
                                  @foreach($v->values as $varkey=>$varvalue)
                                  <td>
                                  {{$varvalue}}
                                  </td>
                                  @endforeach
                                @endforeach
							</tr>
                            @php
                            $i++;
                            @endphp
		                @endif
					@endforeach
	            </tbody>
			</table>
		</div>
	</div>
</body>
</html>
