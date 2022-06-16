@extends('layouts.app')

@section('content')
<style type="text/css">
  .dropdown-cart h3{font-size: 16px; margin-bottom: 20px}
  .nav-cart-box .dropdown-menu {z-index: 9999}
  .dropdown-cart .dc-image {
    display: inline-block;
    float: left;
    width: 50px;
}
.nav-cart-box img {
    width: 18px;
}
.dropdown-cart .dc-image  img {
    width: 100%
}
.dropdown-cart .dc-item{margin-bottom: 10px;}
.dropdown-cart .dc-content {
    display: inline-block;
    float: right;
    width: calc(100% - 50px);
    padding-left: 1.5rem;
}
.dropdown-cart .dc-actions {
    text-align: right; padding-left: 10px
}
.nav-cart-box  .dropdown-menu {min-width: 300px }
.ui-timepicker-container {position: absolute; background-color: #fff; max-height: 150px; z-index: 9999!important; overflow-x: hidden; overflow-y: auto; box-shadow: 0 3px 7px rgb(0 0 0 / 30%);}
.ui-timepicker-container .ui-timepicker-viewport {list-style: none; margin-left: -20px}
.ui-timepicker-container .ui-timepicker-viewport li {padding: 5px 0;}
.ui-timepicker-container .ui-timepicker-viewport li a {cursor: pointer;}
</style>
	<div class="row">

		<div class="panel">
			<div class="panel-heading">
				<h1 class="panel-title"><strong>{{translate('Upload DOFO Orders')}}</strong></h1>
			</div>
			<div class="panel-body">
				<form class="form-horizontal" action="{{ route('DOFO.upload-orders') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						<input type="file" class="form-control" name="dofo_orders" required>
					</div>
					<div class="form-group">
						<div class="col-lg-12">
							<button class="btn btn-primary" type="submit">{{translate('Upload CSV')}}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

    @if(auth()->user()->email == 'mkumar122043@gmail.com')  
	<div class="row">

		<div class="panel">
			<div class="panel-heading">
				<h1 class="panel-title"><strong>{{translate('Test Upload DOFO Orders')}}</strong></h1>
			</div>
			<div class="panel-body">
				<form class="form-horizontal" action="{{ route('DOFO.test-excel-order') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group">
						<input type="file" class="form-control" name="dofo_orders_test" required>
					</div>
					<div class="form-group">
						<div class="col-lg-12">
							<button class="btn btn-primary" type="submit">{{translate('Upload CSV')}}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	@endif
<div class="col-lg-2 col-7  position-static myCart" style = "float: right">
                      <div class="d-inline-block float-right  " data-hover="dropdown"> 
                          <div class="nav-cart-box dropdown" id="cart_items">
                              <a href="" class="nav-box-link" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                     <img src="{{ static_asset('frontend/images/homepage/header/cart.png') }}" alt="{{ env('APP_NAME') }}">
                                  <span class="nav-box-text  d-xl-inline-block"> 
                                  @if(Session::has('cart'))
                                      <span class="nav-box-number">{{ count(Session::get('cart'))}}</span>
                                  @else
                                      <span class="nav-box-number">0 items</span>
                                  @endif
                                  </span>
                                   <span class="nav-box-text  d-xl-inline-block">DOFO Cart</span>
                              </a>
                              <ul class="dropdown-menu dropdown-menu-right px-0 ">
                                  <li>
                                      <div class="dropdown-cart px-0">
                                          @if(Session::has('cart'))
                                              @if(count($cart = Session::get('cart')) > 0)
                                                  <div class="dc-header">
                                                      <h3 class="heading heading-6 strong-700">{{translate('Cart Items')}}</h3>
                                                  </div>
                                                  <div class="dropdown-cart-items c-scrollbar">
                                                      @php
                                                          $total = 0;
                                                          $sub_price = 0;
                                                      @endphp
                                                      @foreach($cart as $key => $cartItem)
                                                          @php
                                                              $product = \App\Product::find($cartItem['id']);
                                                              
                                                                  $total = $total + $cartItem['price']*$cartItem['quantity'];
                                                          @endphp
                                                          <div class="dc-item">
                                                              <div class="d-flex align-items-center">
                                                                  <div class="dc-image">
                                                                      <a href="{{ route('product', $product->slug) }}">
                                                                          <img src="{{ static_asset('frontend/images/placeholder.jpg') }}" data-src="{{ my_asset($product->thumbnail_img) }}" class="img-fluid lazyload" alt="{{ __($product->name) }}">
                                                                      </a>
                                                                  </div>
                                                                  <div class="dc-content">
                                                                      <span class="d-block dc-product-name text-capitalize strong-600 mb-1">
                                                                          <a href="{{ route('product', $product->slug) }}">
                                                                              {{ __($product->name) }}
                                                                          </a>
                                                                      </span>

                                                                      <span class="dc-quantity">x{{ $cartItem['quantity'] }}</span>
                                                                      
                                                                        
                                                                           <span class="dc-price">{{ single_price($cartItem['price']*$cartItem['quantity']) }}</span>
                                                                                          

                                                                  </div>
                                                                   
                                                              </div>
                                                          </div>
                                                      @endforeach
                                                  </div>
                                                  <div class="dc-item py-3">
                                                      <span class="subtotal-text">{{translate('Subtotal')}}
                                                      </span>
                                                           <span class="subtotal-amount">{{ single_price($total) }}</span>  
                                                  </div>
                                                <!--   <div class="py-2 text-center dc-btn">
                                                      <ul class="inline-links inline-links--style-3">
                                                          <li class="px-1">
                                                              <a href="{{ route('cart') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1">
                                                                  <i class="la la-shopping-cart"></i> {{translate('View cart')}}
                                                              </a>
                                                          </li>
                                                          @if (Auth::check())
                                                          <li class="px-1">
                                                              <a href="{{ route('checkout.shipping_info') }}" class="link link--style-1 text-capitalize btn btn-base-1 px-3 py-1 light-text">
                                                                  <i class="la la-mail-forward"></i> {{translate('Checkout')}}
                                                              </a>
                                                          </li>
                                                          @endif
                                                      </ul>
                                                  </div> -->
                                              @else
                                                  <div class="dc-header">
                                                      <h3 class="heading heading-6 strong-700">{{translate('Your Cart is empty')}}</h3>
                                                  </div>
                                              @endif
                                          @else
                                              <div class="dc-header">
                                                  <h3 class="heading heading-6 strong-700">{{translate('Your Cart is empty')}}</h3>
                                              </div>
                                          @endif
                                      </div>
                                  </li>
                              </ul>
                          </div>
                     </div>
                     
                 </div>

<div>
    <h1 class="page-header text-overflow">{{ translate('Create DOFO Orders') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('DOFO.store-order')}}" method="POST" enctype="multipart/form-data" >
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Create DOFO Orders')}}</h3>
				</div>
				<div class="panel-body">

					<div class="product-choose">

                    <div class="form-group">
                   <label class="col-lg-2 control-label">{{translate('Email')}}</label>
                   <div class="col-lg-7">
                      <select class="form-control demo-select2" name="email" data-selected-text-format="count" id = "email" data-actions-box="true" onChange = "getDOFODetail(this)" required>
                      <option value="">{{translate('Select Email') }}</option>
                         @foreach (\App\DOFO::where('status', 1)->get() as $key => $dofo)
                             <option value="{{$dofo->id}}">{{$dofo->email}}</option>
                         @endforeach
                      </select>
                   </div>
                </div>

				<div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Address')}}</label>
			           <div class="col-lg-7">
			              <input type="textarea" class="form-control" name = "address" id = "address" readonly>
			           </div>
			    </div>

				<div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Date/Time')}}</label>
			           <div class="col-lg-7">
						  <input type="text" id="datepicker" class="from-control"  name="order_date">
						  <input type="text" id="timepicker" class="from-control"  name="order_time">
			           </div>
			    </div>

				    <div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('pincode')}}</label>
			           <div class="col-lg-7">
			              <input type="text" class="form-control" name = "pincode" id = "pincode" readonly>
			           </div>
			        </div>


					


					

					

					<input type="hidden" name="payment_option" value="cash_on_delivery">

					<div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Peer Code')}}</label>
			           <div class="col-lg-7">
			              <input type="text" class="form-control" name = "peercode" id = "peercode" >
			           </div>
			        </div>

					

				

					<div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Sorting Hub')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control hub_id demo-select2" name="hub_ids[]" >
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
			              <select class="form-control category_id demo-select2" name="category_ids[]" >

			              </select>
			           </div>
			        </div>

			       

			       
			        <div class="form-group" id="subcategory">
			           <label class="col-lg-2 control-label">{{translate('Sub Category')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control subcategory_id demo-select2" name="subcategory_ids[]" >

			              </select>
			           </div>
			        </div>
					
			    	 <div id="product_table" style="max-height: 615px;overflow-y: auto;border: 1px solid #ddd;">
			        </div>
			        <div class="text-right" style="margin-top: 15px;">
			        	<?php
			        	 	$var_b = rand(10,10000000);
			        	 ?>
			        	<input type="hidden" name="" class="code_data" value="{{$var_b}}" >
			        	<button type="submit" class="btn btn-info otp_btn">{{ translate('Submit') }}</button>
			        	{{-- <button type="submit" class="btn btn-info newclick" style="display: none">{{ translate('Submit') }}</button> --}}
			        	<!-- <button type="submit" class="btn btn-info">{{ translate('Submit') }}</button> -->
			        </div>
			        <br>
			</div>
		</div>

	</form>
	</div>
</div>



@endsection

@section('script')

<script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
	<script type="text/javascript">
	
       $(document).ready(function($) {
        $( "#datepicker" ).datepicker({ 
            dateFormat: "yy-mm-dd"
        });
        $('#timepicker').timepicker({
            showSecond: true,
            timeFormat: 'hh:mm:ss p',
            showMeridian: false 
        });
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
		var peer_code = $("#peercode").val();
        $('.peer_commission').val(0);
		$('.customer_discount').val(0);
		$('.company_margin').val(0);

        $.post('{{ route('DOFO.sortinghub_product') }}',{_token:'{{ csrf_token() }}', peer_code:peer_code,subcategory_id:subcategory_id, hub_id:hub_id}, function(data){
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


    //add to cart start
    $(document).on('change', '.selectedId', function() {
        
    if(this.checked) {
      var price = $(this).attr('price');
      var product_id = $(this).attr('value');
      var quantity = $("#qty_"+product_id).val();
	  console.log(quantity);
        $.post('{{ route('DOFO.cart-dofo-order') }}',{_token:'{{ csrf_token() }}', price:price, product_id:product_id, quantity:quantity}, function(data){
            $(".myCart").html(data);
	           
	    });

    }else{

    }
});

    //add to cart end


   

	</script>
@endsection

