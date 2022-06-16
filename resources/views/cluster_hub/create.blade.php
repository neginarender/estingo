@extends('layouts.app')
@section('content')

  <div class="col-lg-8 col-lg-offset-2">
    <div class="panel">
      <div class="panel-heading">
          <h3 class="panel-title">{{translate('create Cluster Hub')}}</h3>
      </div>
      <form class="form-horizontal" action="{{ route('clusterhub.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="mapping_type" value="cluster_hub">
          <div class="panel-body">
            <div class="product-choose-list">
              <div class="product-choose">
              <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Cluster Name')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Name')}}" id="name" name="cluster_name"  class="form-control" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('User id')}}</label>
                  <div class="col-lg-9">
                      <input type="email" placeholder="{{translate('User id')}}" id="user_id" name="email"  class="form-control" required>
                  </div>
                </div>
                {{-- <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Region')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control region_id demo-select2" name="region_id" required>
                         <option value="">{{translate('Select Region') }}</option>
                         @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}">{{$region->name}}</option>
                         @endforeach
                      </select>
                   </div>
                </div> --}}

                <div class="form-group" id="states">
                   <label class="col-lg-3 control-label">{{translate('States')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control state_id demo-select2" name="state_id[]" multiple data-selected-text-format="count" data-actions-box="true">
                       <option value="">{{translate('Select Region') }}</option>
                         @foreach (\App\State::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}">{{$region->name}}</option>
                         @endforeach
                        
                      </select>
                   </div>
                </div>

                <div class="form-group" id="states">
                   <label class="col-lg-3 control-label">{{translate('City')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control city_id demo-select2" name="city_ids[]" multiple data-selected-text-format="count" data-actions-box="true">
                   
                      </select>
                   </div>
                </div>
              </div>
            </div>
          </div>  
          <div class="panel-footer text-right">
              <button class="btn btn-purple" type="submit">{{translate('Save')}}</button>
          </div>
      </form>

    </div>
  </div>

<script type="text/javascript">

    function get_states_by_region(el){
		var region_id = $(el).val();
        $(el).closest('.product-choose').find('.state_id').html(null);
		$.post('{{ route('states.get_states_by_region') }}',{_token:'{{ csrf_token() }}', region_id:region_id}, function(data){
		    for (var i = 0; i < data.length; i++) {
		        $(el).closest('.product-choose').find('.state_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		    }
            $(el).closest('.product-choose').find('.state_id').select2();
		});
	}

	function get_city_by_state(el){
		var state_id = $(el).val();
    console.log(state_id);
        console.log(state_id);
        $(el).closest('.product-choose').find('.city_id').html(null);
		$.post('{{ route('cities.get_city_by_state') }}',{_token:'{{ csrf_token() }}', state_id:state_id}, function(data){
		    for (var i = 0; i < data.length; i++) {
		        $(el).closest('.product-choose').find('.city_id').append($('<option>', {
		            value: data[i].id,
		            text: data[i].name
		        }));
		    }
            $(el).closest('.product-choose').find('.city_id').select2();
		});
	}

    $(document).ready(function(){
        $('.demo-select2').select2();
    });

    $('.region_id').on('change', function() {
        get_states_by_region(this);
    });

    $('.state_id').on('change', function() {
        get_city_by_state(this);
    });


</script>
@endsection