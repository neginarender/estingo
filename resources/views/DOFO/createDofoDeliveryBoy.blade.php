@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Add New Delivery Boy') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('DOFO.store-delivery-boy')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Delivery Boy Information')}}</h3>
				</div>
				<div class="panel-body">

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Cluster Name')}}</label>
						<div class="col-lg-7">
						<select class="form-control region_id demo-select2" name="cluster_hub_id" onchange = "getSortingHub(this)" required>
						        <option value="">Select Cluster Hub</option>
							@foreach ($clusterHub as $key => $value)
								<option value="{{$value->user_id}}">{{$value->user->name}}</option>
							@endforeach
						</select>	
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Sorting Hub Name')}}</label>
						<div class="col-lg-7">
							<select class="form-control region_id demo-select2" name="sorting_hub_id" id = "sorting_hub_id" required>
							</select>
						</div>
					</div>

				
					
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Delivery Boy Name')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control" name="name" placeholder="{{ translate('Delivery Boy Name') }}" required>
						</div>
					</div>

					
					<div class="form-group">
						<label class="col-lg-2 control-label" for="coupon_code">{{translate('Email')}}</label>
						<div class="col-lg-7">
							<input type="email" placeholder="{{translate('email')}}" id="user_id" name="email"  class="form-control" required>
						</div>
                    </div>

					<div class="form-group">
						<label class="col-lg-2 control-label">Phone</label>
						<div class="col-lg-7">
							<input type="text" placeholder="Phone" name="phone" class="form-control" required>
						</div>
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
	<script type="text/javascript">
	function getSortingHub(el){
		console.log(el.value);
		let cluster_hub_id = el.value;
		if(cluster_hub_id != ""){
			$.post('{{ route('DOFO.get-sorting-hub') }}',{_token:'{{ csrf_token() }}', cluster_hub_id:cluster_hub_id}, function(data){
				
					if(data.status == 1){
						if(data.sorting_hub.length >0){
							var html = "";
							var i = 0;
							html += "<option value = ''>Select SortingHub</option>";
							for(i;i<data.sorting_hub.length;i++){
								console.log(data.sorting_hub[i].id);
								html += "<option value="+ data.sorting_hub[i].id+">"+data.sorting_hub[i].name+"</option>";
							}
							console.log(html);
							$("#sorting_hub_id").html(html);


						}else{
							$("#sorting_hub_id").html('');
						}
						
					}
                   
            });

		}

		

	}
	</script>
@endsection
