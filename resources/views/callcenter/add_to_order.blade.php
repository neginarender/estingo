@extends('layouts.ccapp')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Add To Order') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('admin.store_added_to_order')}}" onkeydown="return event.key != 'Enter';" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<input type="hidden" name="order_id" value="{{ $order->id }}" id="order_id" />
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Add To Order')}}</h3>
				</div>
				<div class="panel-body">

					<div class="product-choose">

					<div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Sorting Hub')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control hub_id demo-select2" id="sorting_hub" name="hub_ids[]" required>
			              		<option value="select one">Select Sorting Hub</option>
			                 
			                     
					                 @php
						                 $hub_name= App\User::where('id', auth()->user()->sorting_hub_id)->select('name','id')->first();
     @endphp
						                 <option value="<?php echo $hub_name->id; ?>">
						                <?php echo $hub_name->name; ?>
						             
					                 
			             		</option>
			            
			              </select>
			           </div>
			        </div>

			         <div class="form-group" id="category">
			           <label class="col-lg-2 control-label">{{translate('Category')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control category_id demo-select2" name="category_ids[]" required>

			              </select>
			           </div>
			        </div>

			       
			        <div class="form-group" id="subcategory">
			           <label class="col-lg-2 control-label">{{translate('Sub Category')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control subcategory_id demo-select2" name="subcategory_ids[]" required>

			              </select>
			           </div>
			        </div>
					@if(@\App\SubOrder::where(['order_id'=>$order->id])->first()->delivery_type=='scheduled')
					<input type="hidden" name="delivery_type" value="scheduled" />
					<div class="form-group" id="order_type">
			           <label class="col-lg-2 control-label">{{translate('Order Type')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control demo-select2" onchange="load_slots(this.value)" name="order_type" required>
							  <option value="">Select</option>
							<option value="1">Fresh</option>
							<option value="2">Grocery</option>
			              </select>
			           </div>
			        </div>
					<div class="form-group" id="order_type">
			           <label class="col-lg-2 control-label"></label>
			           <div class="col-lg-10">
					   <div id="slots"></div>
			           </div>
			        </div>

					@else
						<input type="hidden" name="delivery_type" value="normal" />
					@endif

					<div class="form-group">
                    <label class="col-lg-2 control-label" for="products">{{translate('Products')}}</label>
                    <div class="col-lg-7">
                        <select name="products[]" id="products" class="form-control demo-select2" multiple required data-placeholder="{{ translate('Choose Products') }}">
                            <!-- @foreach(\App\Product::all() as $product)
                                <option value="{{$product->id}}">{{__($product->name)}}</option>
                            @endforeach -->
                        </select>
                    </div>
                </div>


			    	</div>

			    	 <div id="product_table" style="max-height: 615px;overflow-y: auto;">
			        </div>
			        <div class="text-right" style="margin-top: 15px;">
			        	
			        	<button type="button" class="btn btn-info submit_btn" name="submitForm">{{ translate('Submit') }}</button>
			        </div>
			        <br>
			</div>
		</div>

	</form>
	</div>
</div>

@endsection

@section('script')

	<script type="text/javascript">

		function get_sortinghub_by_cluster_id(el){
		    var cluster_id = $(el).val();

			  $.post('{{ route('sorting.get_sortinghub_by_cluster') }}',{_token:'{{ csrf_token() }}', cluster_id:cluster_id}, function(data){
			    for (var i = 0; i < data.length; i++) {
	              $('#sorting_hub').append($('<option>', {
	                  value: data[i].user_id,
	                  text: data[i].email
	              }));
	          }
	          $("#sorting_hub").select2();
		    });
		  }

	function get_subcategories_by_category(el){
		var category_id = $(el).val();
		$('.peer_commission').val(0);
		$('.customer_discount').val(0);
		$('.company_margin').val(0);
		// var hub_id = $('.hub_id').val();
        // console.log(test);
        $(el).closest('.product-choose').find('.subcategory_id').html(null);
		$.post('{{ route('subcategories.get_subcategories_by_category') }}',{_token:'{{ csrf_token() }}', category_id:category_id}, function(data){
		    for (var i = 0; i < data.length; i++) {
		        $(el).closest('.product-choose').find('.subcategory_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		    }
            $(el).find('.subcategory_id').select2();
		    get_products_by_subcategory($(el).closest('.product-choose').find('.subcategory_id'));
		});
	}

	function get_categories_by_hub(el){
		var hub_id = $(el).val();
		$('.peer_commission').val(0);
		$('.customer_discount').val(0);
		$('.company_margin').val(0);
        // console.log(hub_id);
        $(el).closest('.product-choose').find('.category_id').html(null);
        $(el).closest('.product-choose').find('.subcategory_id').html(null);
		$.post('{{ route('categories.get_categories_by_hub') }}',{_token:'{{ csrf_token() }}', hub_id:hub_id}, function(data){
			$(el).closest('.product-choose').find('.category_id').append($('<option>Select One</option>'));
		    for (var i = 0; i < data.length; i++) {
		        $(el).closest('.product-choose').find('.category_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		    }
            $(el).find('.category_id').select2();
		    get_products_by_subcategory($(el).closest('.product-choose').find('.category_id'));
		});
	}

    function get_products_by_subcategory(el){
        var subcategory_id = $(el).val();
        var hub_id = $('.hub_id').val();
				$("#products").empty();
                $.post('{{ route('admin.products_add_to_order') }}', {_token:'{{ csrf_token() }}', subcategory_id:subcategory_id, hub_id:hub_id}, function(data){
                        var product = $("#products");
                        $.map(data,function(item){
                            product.append("<option value="+item.id+">"+item.name+"</option>");
                        });
                        $('.demo-select2').select2();
                    });
	}

	 

	$('#products').on('change', function(){
                var product_ids = $('#products').val();
				var order_id = $("#order_id").val();
				
                if(product_ids.length > 0){
                    $.post('{{ route('admin.load_products_added_to_order') }}', {_token:'{{ csrf_token() }}', product_ids:product_ids,sorting_hub_id:$("#sorting_hub").val(),order_id:order_id}, function(data){
                        $('#product_table').html(data);
                        $('.demo-select2').select2();
						document.getElementsByName("submitForm")[0].type = "submit";
                    });
                }
                else{
                    $('#discount_table').html(null);
                }
            });

	function update_quantity(){
				$(".submit_btn").attr('disabled','disabled');
				var product_ids = $('#products').val();
				var order_id = $("#order_id").val();
				var quantity = [];
				$("input[name='qty[]']").each(function() {
					quantity.push($(this).val());
				});
				console.log(quantity);
				if(product_ids.length > 0){
                    $.post('{{ route('admin.load_products_added_to_order') }}', {_token:'{{ csrf_token() }}', product_ids:product_ids,sorting_hub_id:$("#sorting_hub").val(),order_id:order_id,quantity:quantity}, function(data){
                        $('#product_table').html(data);
                        $('.demo-select2').select2();
						$(".submit_btn").removeAttr('disabled');
                    });
                }
                else{
                    $('#discount_table').html(null);
                }
	}

	 

    $(document).ready(function(){
        $('.demo-select2').select2();
        //get_subcategories_by_category($('.category_id'));
    });

    $('.cluster_id').on('change', function() {
        get_sortinghub_by_cluster_id(this);
    });

    $('.sorting_hub').on('change', function() {
        get_distributor_by_sorting_hub(this);
    });

    $('.category_id').on('change', function() {
        get_subcategories_by_category(this);
    });

    $('.subcategory_id').on('change', function() {
	    get_products_by_subcategory(this);
	});

	$('.hub_id').on('change', function() {
        get_categories_by_hub(this);
    });    


	function load_slots(type){
	$.post("{{ route('order.load_slots') }}",{type:type,_token:"{{ csrf_token() }}"},function(data){
		$("#slots").html(data);
				$('#deliveryDateTime').show();
                $('#today_avail_slot_grocery').show();
                $('#tommorow_avail_slot_grocery').hide();
                $('#today_avail_slot_fresh').show();
                $('#tommorow_avail_slot_fresh').hide();
                $('.delivery_slot_fresh_tom').prop('checked', false);
                $('.delivery_slot_grocery_tom').prop('checked', false);
                var flag = $('#slot_flag').val();
                if(flag == 0){
                    $('#tommorow_avail_slot_grocery').show();
                    $('#deliveryGrocerySlotTom_0').prop('checked', true);
                }

                var slot_flag_fresh = $('#slot_flag_fresh').val();
                if(slot_flag_fresh == 0){
                    $('#tommorow_avail_slot_fresh').show();
                    $('#deliveryFreshSlot_0').prop('checked', true);
                }
	});
} 

	</script>
@endsection

