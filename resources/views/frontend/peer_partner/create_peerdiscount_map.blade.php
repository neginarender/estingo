@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Peer Discount') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('product.store_peer_discount')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Peer Discount')}}</h3>
				</div>
				<div class="panel-body">

					<div class="product-choose">

					<div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Sorting Hub')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control hub_id demo-select2" name="hub_ids[]" required>
			              		<option value="select one">Select Sorting Hub</option>
			                 @foreach($shorting_hub as $key => $hub)
			                 
			                     <option value="{{$hub->user_id}}">
					                 @php
						                 $hub_name= App\User::where('id', $hub->user_id)->select('name')->first();
						                 echo $hub_name->name;
					                 @endphp
			             		</option>
			                 @endforeach
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

			       

			        <!-- <div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Category')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control category_id demo-select2" name="category_ids[]" required>
			                 @foreach(\App\Category::all() as $key => $category)
			                     <option value="{{$category->id}}">{{$category->name}}</option>
			                 @endforeach
			              </select>
			           </div>
			        </div> -->
			        <div class="form-group" id="subcategory">
			           <label class="col-lg-2 control-label">{{translate('Sub Category')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control subcategory_id demo-select2" name="subcategory_ids[]" required>

			              </select>
			           </div>
			        </div>

			         <div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Discount')}}</label>
			           <div class="col-lg-7">
			              <select class="demo-select2" name="discount">
                                <!-- <option value="amount">{{translate('Flat')}}</option> -->
                                <option value="percent">{{translate('Percent')}}</option>
                            </select>
			           </div>
			        </div>

			        <div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Customer Discount')}}</label>
                        <div class="col-lg-7">
                            <input type="number" min="0" value="0" step="0.01" placeholder="{{ translate('Customer Discount') }}" name="customer_discount" class="form-control customer_discount" required>
                        </div>
			        </div>


			        <div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Peer Commission')}}</label>
                        <div class="col-lg-7">
                            <input type="number" min="0" value="0" step="0.01" placeholder="{{ translate('Peer Discount') }}" name="peer_discount" class="form-control peer_commission" required>
                        </div>
			        </div>

			        

			        <div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Master Margin')}}</label>
                        <div class="col-lg-7">
                            <input type="number" min="0" value="0" step="0.01" placeholder="{{ translate('Company Margin') }}" name="company_margin" class="form-control company_margin" required>
                        </div>
			        </div>

			    	</div>

			    	<br><hr>
			    	 <div id="product_table" style="max-height: 615px;overflow-y: auto;border: 1px solid #ddd;">
			        </div>
			        <div class="text-right" style="margin-top: 15px;">
			        	<?php
			        	 	$var_b = rand(10,10000000);
			        	 ?>
			        	<input type="hidden" name="" class="code_data" value="{{$var_b}}" >
			        	<button type="button" class="btn btn-info otp_btn">{{ translate('Submit') }}</button>
			        	<button type="submit" class="btn btn-info newclick" style="display: none">{{ translate('Submit') }}</button>
			        	<!-- <button type="submit" class="btn btn-info">{{ translate('Submit') }}</button> -->
			        </div>
			        <br>
			</div>
		</div>

	</form>
	</div>
</div>



<!-- Modal -->
	<div id="myModal" class="modal fade" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Verify OTP</h4>
		</div>
		<div class="modal-body">
		<form class="form form-horizontal mar-top" action="" method="POST" id="otp_form">
		@csrf
		
			<div class="form-group">
				<label class="col-lg-2 control-label">OTP</label>
				<div class="col-lg-7">
				<input type="hidden" value="" name="check_code" class="check_code">	
				<input type="text" placeholder="OTP" name="otp" class="form-control get_otp" autocomplete="off" required>
				</div>
				<div class="mar-all text-right">
				<button type="button" class="btn btn-info verify_otp">{{ translate('Verify') }}</button>
				</div>
			</div>
		</div>
		</form>
		<div class="modal-footer">
			<button type="button" class="btn btn-default close_modal" data-dismiss="modal">Close</button>
		</div>
		</div>

	</div>
	</div>
@endsection

@section('script')

	<script type="text/javascript">
		$('.verify_otp').on('click', function() {
			var codeval = $('.check_code').val();
			var otp = $('.get_otp').val();
			$.post('{{ route('mapped.set_product_otp') }}',{_token:'{{ csrf_token() }}', codeval:codeval, otp:otp}, function(data){
	           console.log(data);
	           if(data==1){
	           		$('.close_modal').trigger("click");
	           		 $(".newclick").trigger("click");
	           }else{
	           		alert('The OTP you entered is incorrect.');
	           }
	            
	      });
		});

		$('.otp_btn').on('click', function() {
		var voyageId = []; 
			$("input[name='products[]']:checked:enabled").each(function () {
				voyageId.push($(this).data('myval'));
			}); 

			if (jQuery.inArray(0, voyageId) !== -1)
			{
			  // alert('found zero');
			  var codeval = $('.code_data').val();
			  // alert(codeval);
			  $('.check_code').val(codeval);
			  $.post('{{ route('mapped.get_product_otp') }}',{_token:'{{ csrf_token() }}', codeval:codeval}, function(data){
		           //console.log(data);
		           if(data==1){
		           		$('#myModal').modal('show');
		           }else{
		           		alert('Something went wrong.');
		           }
		            
		      });
			}else{
			  // alert('not found');
			  $(".newclick").trigger("click");
			}
		});

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
        $('.peer_commission').val(0);
		$('.customer_discount').val(0);
		$('.company_margin').val(0);

        $.post('{{ route('mapped.get_product_list_hub') }}',{_token:'{{ csrf_token() }}', subcategory_id:subcategory_id, hub_id:hub_id}, function(data){
           $('#product_table').html(data);
        });
	}

	 

	 $(".peer_commission").blur(function(){
	 	var peer_map = $('.peer_commission').val();
	 	var customer_map = $('.customer_discount').val();
	 	var company_map = $('.company_margin').val();

	 	var subcategory_id = $('.subcategory_id').val();
	 	var hub_id = $('.hub_id').val();	 		
	 		
	    if(subcategory_id!=null){	    
		 	$.post('{{ route('mapped.get_product_list_discount') }}',{_token:'{{ csrf_token() }}', subcategory_id:subcategory_id, hub_id:hub_id, peer_map:peer_map, customer_map:customer_map, company_map:company_map}, function(data){
	           $('#product_table').html(data);
	        });
	    }else{
	    	alert('Please select categories');
	    }     
	       
	 });

	  $(".customer_discount").blur(function(){
	 	var peer_map = $('.peer_commission').val();
	 	var customer_map = $('.customer_discount').val();
	 	var company_map = $('.company_margin').val();

	 	var subcategory_id = $('.subcategory_id').val();
	 	var hub_id = $('.hub_id').val();
	 	if(subcategory_id!=null){	
		 	$.post('{{ route('mapped.get_product_list_discount') }}',{_token:'{{ csrf_token() }}', subcategory_id:subcategory_id, hub_id:hub_id, peer_map:peer_map, customer_map:customer_map, company_map:company_map}, function(data){
	           $('#product_table').html(data);
	        });
		}else{
	    	alert('Please select categories');
	    }
	 });

	  $(".company_margin").blur(function(){
	 	var peer_map = $('.peer_commission').val();
	 	var customer_map = $('.customer_discount').val();
	 	var company_map = $('.company_margin').val();

	 	var subcategory_id = $('.subcategory_id').val();
	 	var hub_id = $('.hub_id').val();
	 	if(subcategory_id!=null){	
		 	$.post('{{ route('mapped.get_product_list_discount') }}',{_token:'{{ csrf_token() }}', subcategory_id:subcategory_id, hub_id:hub_id, peer_map:peer_map, customer_map:customer_map, company_map:company_map}, function(data){
	           $('#product_table').html(data);
	        });
		}else{
	    	alert('Please select categories');
	    }
	 });

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


   

	</script>
@endsection

