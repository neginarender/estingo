@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Mapping Edit') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('mapped.product.update', $map_product->id)}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Mapping Edit')}}</h3>
				</div>
				<div class="panel-body">

					<input type="hidden" name="mapping_id" value="{{ $map_product->id }}">
					<div class="form-group">
                    <label class="col-lg-2 control-label">{{translate('Products')}}</label>
	                    <div class="col-lg-7">
	                        <select name="product_id" id="products" class="form-control demo-select2" multiple required data-placeholder="{{ translate('Choose Products') }}" disabled="">
	                                <option value="{{$map_product->product->id}}" selected="" disabled="">{{ $map_product->product->name }}}</option>
	                        </select>
	                    </div>
                	</div>

                	<?php $distributorids = json_decode($map_product->distributor_id); ?>
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Distributors')}}</label>
						<div class="col-lg-7">
							<select class="form-control demo-select2-placeholder" name="distributor_id[]" id="sorting_hub" multiple data-selected-text-format="count" data-actions-box="true" required="">
								<option value="">{{ ('Select Sorting Hub') }}</option>
								@forelse($distributors as $key => $distributor)
									<option value="{{ $distributor->id }}"
											<?php if(in_array($distributor->id, $distributorids)) {
												echo "selected";
											} ?>>{{ $distributor->name }}
									</option>
								@empty
								@endforelse
							</select>
						</div>
					</div>

					

                	<br>
               		<div class="form-group" id="discount_table">
                	</div>

				<div class="mar-all text-right">
					<button type="submit" class="btn btn-info">{{ translate('Submit') }}</button>
				</div>

			</div>
		</div>

	</form>
	</div>
</div>

@endsection

@section('script')
   <!--  <script type="text/javascript">
        $(document).ready(function(){
            $('#products').on('change', function(){
                var product_ids = $('#products').val();
                if(product_ids.length > 0){
                    $.post('{{ route('flash_deals.product_discount') }}', {_token:'{{ csrf_token() }}', product_ids:product_ids}, function(data){
                        $('#discount_table').html(data);
                        $('.demo-select2').select2();
                    });
                }
                else{
                    $('#discount_table').html(null);
                }
            });
        });
    </script> -->
@endsection

