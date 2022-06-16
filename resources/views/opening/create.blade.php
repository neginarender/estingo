@extends('layouts.app')
@section('content')

  <div class="col-lg-8 col-lg-offset-2">
    <div class="panel">
      <div class="panel-heading">
          <h3 class="panel-title">{{translate('Create Job Vaccancy')}}</h3>
      </div>
      <form class="form-horizontal" action="{{ route('opening.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
          <div class="panel-body">
            <div class="product-choose-list">
              <div class="product-choose">
              <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Designation')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Designation')}}" id="designation" name="designation"  class="form-control" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Job Role')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Job Role')}}" id="role" name="role"  class="form-control" required>
                  </div>
                </div>

                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Number of Positions')}}</label>
                   <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Number of Positions')}}" id="num_position" name="num_position"  class="form-control" required>
                  </div>
                </div>
                <!-- checkBox -->

                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Location')}}</label>
                   <div class="col-lg-9">
                    @foreach($jobLocations as $key => $value)
                      <input type="checkbox" id="location.{{$key}}" name="location[]" value="{{$value->id}}"> {{$value->city}}
                    @endforeach
                      <!-- <input type="checkbox" id="location2" name="location[]" value="NCR"> NCR
                      <input type="checkbox" id="location3" name="location[]" value="Bangalore"> Bangalore -->
                  </div>
                </div>
                <!-- checkBox -->
                <!-- <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Location')}}</label>
                   <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Location')}}" id="location" name="location"  class="form-control" required>
                  </div>
                </div> -->

                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Monthly Take Home Salary')}}</label>
                   <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Monthly Take Home Salary')}}" id="salary" name="salary"  class="form-control" required>
                  </div>
                </div>
                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Education Required')}}</label>
                   <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Education Required')}}" id="education_req" name="education_req"  class="form-control" required>
                  </div>
                </div>
                <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Experience Required')}}</label>
                   <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Experience Required')}}" id="experience_req" name="experience_req"  class="form-control" required>
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