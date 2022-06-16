@if(count($combinations[0]) > 0)
	<table class="table table-bordered">
		<thead>
			<tr>
				<td class="text-center">
					<label for="" class="control-label">{{translate('Variant')}}</label>
				</td>
				<td class="text-center">
					<label for="" class="control-label">{{translate('MRP')}}</label>
				</td>
				<td class="text-center">
					<label for="" class="control-label">{{translate('SKU')}}</label>
				</td>
				<td class="text-center">
					<label for="" class="control-label">{{translate('Discount')}}</label>
				</td>
				{{-- <td class="text-center">
					<label for="" class="control-label">{{translate('Quantity')}}</label>
				</td> --}}
			</tr>
		</thead>
		<tbody>

@foreach ($combinations as $key => $combination)
	@php
		$sku = '';
		foreach (explode(' ', $product_name) as $key => $value) {
			$sku .= substr($value, 0, 1);
		}

		$str = '';
		foreach ($combination as $key => $item){
			if($key > 0 ){
				$str .= '-'.str_replace(' ', '', $item);
				$sku .='-'.str_replace(' ', '', $item);
			}
			else{
				if($colors_active == 1){
					$color_name = \App\Color::where('code', $item)->first()->name;
					$str .= $color_name;
					$sku .='-'.$color_name;
				}
				else{
					$str .= str_replace(' ', '', $item);
					$sku .='-'.str_replace(' ', '', $item);
				}
			}
		}
	@endphp
	@if(strlen($str) > 0)
	@php 
	
	$explode = explode("-", $str);
	$skuval = array();
	foreach($explode as $val){
		if(!empty($val)){
		 	$skuval[] = substr($val, 0,1);
		}
	}
	$skuval = (implode('-', $skuval));
	@endphp
		<tr>
			<td>
				<label for="" class="control-label">{{ $str }}</label>
			</td>
			<td>

			<?php 
					
					$variant_price = (Session::has('vprice')) ? (!is_null(Session::get('vprice')[str_replace('.','_',$str)]) ? Session::get('vprice')[str_replace('.','_',$str)]: $unit_price): $unit_price;
					$variant_qty = (Session::has('vqty')) ? (!is_null(Session::get('vqty')[str_replace('.','_',$str)]) ?Session::get('vqty')[str_replace('.','_',$str)]:10) :10;
					//$variant_purchase = (Session::has('vpprice')) ? (!is_null(Session::get('vpprice')[$str])? Session::get('vpprice')[$str] : 0):0;
					$variant_discount = (Session::has('variant_discount')) ? (!is_null(Session::get('variant_discount')[str_replace('.','_',$str)]) ? Session::get('variant_discount')[str_replace('.','_',$str)] : $discount):$discount;
					$variant_discount_type = (Session::has('variant_discount_type')) ? (!is_null(Session::get('variant_discount_type')[str_replace('.','_',$str)]) ? Session::get('variant_discount_type')[str_replace('.','_',$str)] :'amount'):'amount';
					 ?>



<input type="number" name="price_{{ str_replace('.','_',$str) }}" value="@php

if (is_null(Session::get('vprice')[str_replace('.','_',$str)])) {

	if(($stock = $product->stocks->where('variant', $str)->first()) != null){

		echo $stock->price;

	}

	else{

		echo $unit_price;

	}

}

else{

	echo $variant_price;

}

@endphp" min="0.01" step="0.01" class="form-control" required>
			</td>
			<td>
				<input type="text" name="sku_{{ $str }}" value="{{ $stock->sku }}" class="form-control" required readonly="">
				<!-- <input type="text" name="sku_{{ $str }}" value="{{ strtoupper($data.'-'.$skuval.'-'.$productStockID) }}" class="form-control" required> -->
					<!-- <input type="text" name="sku_{{ str_replace('.','_',$str) }}" value="{{ strtoupper($data.'-'.$skuval.'-'.$productStockID) }}" class="form-control" required readonly=""> -->
			</td>
			<td>
			<input type="number" name="discount_{{ str_replace('.','_',$str) }}" value="@php

if (is_null(Session::get('variant_discount')[str_replace('.','_',$str)])) {

	if(($stock = $product->stocks->where('variant', $str)->first()) != null){

		echo $stock->discount;

	}

	else{

		echo $discount;

	}	

}

else{

	echo $variant_discount;

}

@endphp"  class="form-control" style="display:inline-block!important;" required>
					<select class="demo-select2" name="discount_type_{{ str_replace('.','_',$str) }}" style="display: inline-block!important;position: absolute!important;margin: 8px -70px!important;">
						<!-- <option value="amount">{{translate('Flat')}}</option> -->
						<option value="percent">{{translate('Percent')}}</option>
					</select>
				</td>
			{{--<td>
				<input type="number" name="qty_{{ str_replace('.','_',$str) }}" value="@php
                    if(($stock = $product->stocks->where('variant', $str)->first()) != null){
                        echo $stock->qty;
                    }
                    else{
                        echo '0';
                    }
                @endphp" min="0" step="1" class="form-control" required @if($manageby == 0) {{'required'}} @else {{'required'}} @endif>
			</td>--}}
		</tr>
	@endif
@endforeach

	</tbody>
</table>
@endif
