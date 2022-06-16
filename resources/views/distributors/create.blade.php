@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-header text-overflow">{{ translate('Add New Distributor') }}</h1>
</div>
<div class="row">
	<div class="col-lg-8 col-lg-offset-2">
		<form class="form form-horizontal mar-top" action="{{route('distributor.store')}}" method="POST" enctype="multipart/form-data" id="choice_form">
			@csrf
			<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Distributor Information')}}</h3>
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

			        <div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Sorting Hubs')}}</label>
						<div class="col-lg-7">
							<select class="form-control demo-select2-placeholder" name="sorting_hub_id" id="sorting_hub" data-selected-text-format="count" data-actions-box="true" required="">
								<option value="">{{ ('Select Sorting Hub') }}</option>
							</select>
						</div>
					</div>

			        @endif

			        @if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Cluster Hub")
			        <div class="form-group" id="cluster">
			           <label class="col-lg-2 control-label">{{translate('Cluster Hub')}}</label>
			           <div class="col-lg-7">
			              <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
			              	@foreach (\App\Cluster::get() as $key => $vale)
			              	  	@if(auth()->user()->id == $vale->user_id)
	                          		<option value="{{$vale->user_id}}" selected="">{{$vale->user->name}}</option>
	                          	@endif
	                         @endforeach
			              </select>
			           </div>
			        </div>

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Sorting Hubs')}}</label>
						<div class="col-lg-7">
							<select class="form-control demo-select2-placeholder" name="sorting_hub_id" id="sorting_hub" data-selected-text-format="count" data-actions-box="true" required="">
								<option value="">{{ ('Select Sorting Hub') }}</option>
									@foreach (\App\ShortingHub::where('cluster_hub_id', auth()->user()->id)->get() as $key => $vale)
										<option value="{{$vale->user_id}}" selected="">{{$vale->user->name}}</option>
									@endforeach
							</select>
						</div>
					</div>
					@endif

					@if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Sorting Hub")
						<?php $cluster_hub_id = \App\ShortingHub::where('user_id', auth()->user()->id)->first()->cluster_hub_id;?>
						<input type="hidden" name="cluster_hub" value="{{$cluster_hub_id}}">
						<input type="hidden" name="sorting_hub_id" value="{{auth()->user()->id}}">
					@endif
					
					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Name')}}</label>
						<div class="col-lg-7">
							<input type="text" class="form-control" name="name" placeholder="{{ translate('Distributor Name') }}" required>
						</div>
					</div>
					<?php
								$sortingPin = \App\ShortingHub::where('user_id', auth()->user()->id)->first('area_pincodes');
								$pincode = json_decode($sortingPin['area_pincodes']);
								?>

					<div class="form-group">
						<label class="col-lg-2 control-label">{{translate('Select Pin Code')}}</label>
						<div class="col-lg-7">
						<textarea name="pincodes" id="pincodes" class="form-control" onBlur="filter_pincodes()" placeholder="Enter Pincode" required="required"></textarea>
                   
							<!-- <select class="form-control demo-select2-placeholder" name="pincode[]" id="pincode"  multiple   data-actions-box="true" required="">
								<option value="">{{ ('Select Pin Code') }}</option>
									@foreach ($pincode as $key => $vale)
										<option value="{{$vale}}" >{{$vale}}</option>
									@endforeach
							</select> -->
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">Phone</label>
						<div class="col-lg-7">
							<input type="text" placeholder="Phone" name="phone" class="form-control" required>
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">Address</label>
						<div class="col-lg-7">
							<textarea placeholder="Address" name="address" class="form-control" required></textarea>
						</div>
					</div>
					

				

			</div>
		</div>
		<div class="panel">
				<div class="panel-heading bord-btm">
					<h3 class="panel-title">{{translate('Upload Documents')}}</h3>
				</div>
				<div class="panel-body">
					<div class="form-group">
						<label class="col-lg-2 control-label">Adhar Card</label>
						<div class="col-lg-7">
							<input type="file" name="adhar_card" class="form-control" />
						</div>
					</div>

					<div class="form-group">
						<label class="col-lg-2 control-label">Pan Card</label>
						<div class="col-lg-7">
							<input type="file" name="pan_card" class="form-control" />
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

<script>
let pcode = [];
@foreach ($pincode as $key => $vale)
pcode.push({{ $vale }});
@endforeach
console.log(pcode);
function filter_pincodes(){
    let a1 = $("#pincodes").val().split(',').map( Number );
    let a2  = pcode.unique();
    let filtered = $.map(a1,function(a){return $.inArray(a, a2) < 0 ? null : a;}); 
    $("#pincodes").val(filtered.toString());
  }

  Array.prototype.unique =
  function() {
    var a = [];
    var l = this.length;
    for(var i=0; i<l; i++) {
      for(var j=i+1; j<l; j++) {
        // If this[i] is found later in the array
        if (this[i] === this[j])
          j = ++i;
      }
      a.push(this[i]);
    }
    return a;
  };
</script>

@endsection
