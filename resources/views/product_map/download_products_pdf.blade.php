<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product List</title>
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
					<td style="font-size: 2.5rem;" class="text-right strong">{{  translate($category_name) }}</td>
				</tr>
			</table>
			<table>
				<tr>
					<td style="font-size: 1.2rem;" class="strong">{{ $generalsetting->site_name }}</td>
					<td class="text-right"></td>
				</tr>
				
			</table>

		</div>

	    <div style="padding: 1.5rem;">
			<table class="padding text-left small border-bottom">
				<thead>
	                <tr class="gry-color" style="background: #eceff4;">
                        <th width="5%">Sr No.</th>
	                    <th width="35%">{{ translate('Product Name') }}</th>
						<th width="5%">{{ translate('Quantity') }}</th>
						<th width="15%">{{ translate('Distributor') }}</th>
	                    <th width="5%">{{ translate('Purchased Price') }}</th>
	                    <th width="5%">{{ translate('Selling Price') }}</th>
	                    <th width="15%">{{ translate('Added On') }}</th>
                        <th width="5%">{{ translate('Stock') }}</th>
                        <th width="20%">{{ translate('Published') }}</th>
                        
	                </tr>
				</thead>
				<tbody class="strong">
	                @foreach ($products as $key => $product)
		               
							<tr class="">
								
								<td class="gry-color">{{ $key+1}}</td>
								<td class="gry-color">{{ translate($product->name) }}</td>
								<td>
								@if (@$product->choice_options != null)
                                                        @foreach (json_decode($product->choice_options) as $key => $choice)
                                                            @foreach ($choice->values as $key => $value)
                                                                {{ $value }}
                                                            @endforeach   
                                                        @endforeach
                                @endif
								</td>
                                <td class="grey-color">{{ \App\Distributor::where('id',$product->distributor_id)->first()->name }}</td>
								<td class="gry-color currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span> {{ $product->purchased_price}}</td>
			                    <td class="text-right currency"><span style="font-family: DejaVu Sans; sans-serif;">&#8377; </span> {{ $product->selling_price }}</td>
                                <td class="gry-color">{{ $product->created_at }}</td>
                                <td class="gry-color">{{ $product->qty }}</td>
                                <td class="gry-color">{{ $product->published }}</td>
							</tr>
					@endforeach
	            </tbody>
			</table>
		</div>


	</div>
</body>
</html>
