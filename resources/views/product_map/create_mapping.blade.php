@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Product Mapping') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('product-mapping.store')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Product Mapping')}}</h3>
				</div>
				<div class="panel-body">

					@if(auth()->user()->user_type == "admin")
					<div class="form-group" id="cluster">
			           <label class="col-lg-2 control-label">{{translate('Cluster Hub')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
			              	<option value="">{{translate('Select Cluster Hub') }}</option>
			              	@foreach (\App\Cluster::get() as $key => $vale)
	                          <option value="{{$vale->user_id}}">{{$vale->user->name}}</option>
	                         @endforeach
			              </select>
			           </div>
			        </div>
			        @endif

			        @if(auth()->user()->staff->role->name == "Cluster Hub")
			        <div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Sorting Hubs')}}</label>
						<div class="col-lg-7">
							<select class="form-control demo-select2-placeholder" name="sorting_hub_id" id="sorting_hub" data-selected-text-format="count" data-actions-box="true" required="">
								<option value="">{{ ('Select Sorting Hub') }}</option>
							</select>
						</div>
					</div>
					@endif

					@if(auth()->user()->user_type == "admin" || auth()->user()->staff->role->name == "Cluster Hub")
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Distributors')}}</label>
						<div class="col-lg-7">
							<select class="form-control demo-select2-placeholder distributors" name="distributor_id[]"  multiple data-selected-text-format="count" data-actions-box="true" required="">
								
							</select>
						</div>
					</div>
					@endif
					
					@if(auth()->user()->staff->role->name == "Sorting Hub")
					
						<input type="hidden" class="form-control" name="sorting_hub_id" value="{{auth()->user()->id}}">
						<div class="form-group">
							<label class="col-lg-2 control-label">{{translate('Distributors')}}</label>
							<div class="col-lg-7">
								<select class="form-control demo-select2-placeholder distributors" name="distributor_id[]" data-selected-text-format="count" data-actions-box="true" required="" multiple>
									<option value="">Select distributor</option>
									@forelse($distributors as $key => $distributor)
										<option value="{{$distributor->id}}">{{$distributor->name}}</option>
									@empty
									@endforelse
								</select>
							</div>
						</div>
					@endif
					@php 
						$mapped_categories = \App\MappedCategory::where('sorting_hub_id',auth()->user()->id)->pluck('category_id');
					@endphp
					<div class="product-choose">
			        <div class="form-group">
			           <label class="col-lg-2 control-label">{{translate('Category')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control category_id demo-select2" name="category_id" required>
						   <option value="">Select Category</option>
						   	 @foreach(\App\Category::whereIn('id',$mapped_categories)->orderBy('id','asc')->get() as $key => $category)
			                     <option value="{{$category->id}}">{{$category->name}}</option>
			                 @endforeach
			              </select>
			           </div>
			        </div>
			        <div class="form-group" id="subcategory">
			           <label class="col-lg-2 control-label">{{translate('Sub Category')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control subcategory_id demo-select2" name="subcategory_ids[]" multiple required>

			              </select>
			           </div>
			        </div>

			    	</div>

			    	<br><hr>
			    	 <div id="product_table" style="max-height: 615px;overflow-y: auto;border: 1px solid #ddd;">
			        </div>
			        <div class="text-right" style="margin-top: 15px;"><button type="submit" class="btn btn-info">{{ translate('Submit') }}</button></div>
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
        console.log(category_id);
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

    function get_products_by_subcategory(el){
        var subcategory_id = $(el).val();
		var distributor_id = $(".distributors").val();
		console.log(distributor_id);
        $.post('{{ route('mapped.get_product_list') }}',{_token:'{{ csrf_token() }}', subcategory_id:subcategory_id,distributor_id:distributor_id}, function(data){
           $('#product_table').html(data);
        });
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

	</script>
@endsection

