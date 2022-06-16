@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Clone Distributor') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('clone.create.distributor')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Distributor Information')}}</h3>
				</div>
				<div class="panel-body">

				<div class="form-group">
					<label for="distributors">Distributors</label>
					<select name="distributor" class="form-control demo-select2">
						<option value="">Select</option>
						@foreach($distributors as $key => $distributor)
						<option value="{{ $distributor->id}}">{{ $distributor->name }}</option>
						@endforeach
					</select>
				</div>

				@if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Sorting Hub")
						<?php $cluster_hub_id = \App\ShortingHub::where('user_id', auth()->user()->id)->first()->cluster_hub_id;?>
						<input type="hidden" name="cluster_hub" value="{{$cluster_hub_id}}">
						<input type="hidden" name="sorting_hub_id" value="{{auth()->user()->id}}">
					@endif

				<div class="mar-all text-right">
					<button type="submit" class="btn btn-info">{{ translate('Create Clone') }}</button>
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
