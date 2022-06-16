@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Edit Delivery Boy') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('delivery_boy.update', encrypt($deliveryboy->id))}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<input type="hidden" name="_method" value="PATCH">
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Delivery Boy Information')}}</h3>
				</div>
				<div class="panel-body">

					

					@if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Sorting Hub")
						<?php $cluster_hub_id = \App\ShortingHub::where('user_id', auth()->user()->id)->first()->cluster_hub_id;?>
						<input type="hidden" name="cluster_hub_id" value="{{$cluster_hub_id}}">
						<input type="hidden" name="sorting_hub_id" value="{{auth()->user()->id}}">
					@endif

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Cluster Name')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control" name="cluster_hub_name" value="{{$sorting_hub_name->cluster->user->name}}""  disabled>
							
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Sorting Hub Name')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control" name="sorting_hub_name" value = "<?php echo $sorting_hub_name->user->name; ?>" placeholder="{{ translate('Distributor Name') }}" disabled>
						</div>
					</div>

				
					
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Delivery Boy Name')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control" name="name" value = {{$deliveryboy->user->name}} placeholder="{{ translate('Delivery Boy Name') }}" required>
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label" for="coupon_code">{{translate('Email')}}</label>
						<div class="col-lg-7">
							<input type="email" placeholder="{{translate('email')}}" id="user_id" name="email"  value = {{$deliveryboy->user->email}} class="form-control" disabled>
						</div>
                    </div>

					<div class="form-group">
						<label class="col-lg-2 control-label">Phone</label>
						<div class="col-lg-7">
							<input type="text" placeholder="Phone" name="phone" value={{$deliveryboy->phone}} class="form-control" required>
						</div>
					</div>


					{{-- <div class="form-group">
                   <label class="col-lg-2 control-label">{{translate('Area')}}</label>
                   <div class="col-lg-7">
				   @php
				   $pincode = json_decode($sorting_hub_name->area_pincodes);
				   $area_name =\App\Area::whereIn('pincode',$pincode)->get();
				   @endphp
                      <select class="form-control region_id demo-select2" name="area_id" required>
                         <option value="">{{translate('Select Area') }}</option>
                         @foreach ($area_name as $key => $value)
                             <option value="{{$value->area_id}}" selected =  //$deliveryboy->area_id == $value->area_id ?"selected":""; ?>>{{$value->area_name}}</option>
                         @endforeach
                      </select>
                   </div>
                </div> --}}
					

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

		$('.cluster_id').on('change', function() {
        	get_sortinghub_by_cluster_id(this);
    	});

	</script>
@endsection
