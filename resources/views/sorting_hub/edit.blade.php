@extends('layouts.app')
@section('content')

  <div class="col-lg-8 col-lg-offset-2">
    <div class="panel">
      <div class="panel-heading">
          <h3 class="panel-title">{{translate('Edit Sorting Hub')}}</h3>
      </div>
      <form class="form-horizontal" action="{{ route('sorthinghub.update', encrypt($ShortingHub->id)) }}" method="POST" enctype="multipart/form-data">
        @csrf
         <input type="hidden" name="_method" value="PATCH">
          <div class="panel-body">
            <div class="product-choose-list">
              <div class="product-choose">

               {{-- <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Region')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control region_id demo-select2" name="region_id" required>
                         <option value="">{{translate('Select Region') }}</option>
                         @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}" @if($ShortingHub->cluster->region_id == $region->id) Selected @endif>{{$region->name}}</option>
                         @endforeach
                      </select>
                   </div>
                </div>

                <div class="form-group" id="states">
                   <label class="col-lg-3 control-label">{{translate('States')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control state_id demo-select2" name="state_id" required="">
                        @foreach (\App\State::where('status', 1)->get() as $key => $state)
                          <option value="{{$state->id}}" @if($ShortingHub->cluster->state_id == $state->id) Selected @endif>{{$state->name}}</option>
                        @endforeach
                      </select>
                   </div>
                </div>

                @php $mapped_city = json_decode($ShortingHub->cluster->cities); @endphp
                <div class="form-group" id="city">
                   <label class="col-lg-3 control-label">{{translate('City')}}</label>
                   <div class="col-lg-9">
                         <select class="form-control city_id demo-select2" name="city_id" multiple data-selected-text-format="count" data-actions-box="true">
                        @foreach (\App\City::where('status', 1)->get() as $key => $city)
                            <option value="{{$city->name}}" @if(in_array($city->id, $mapped_city)) Selected @endif>{{$city->name}}</option>
                        @endforeach
                      </select>
                   </div>
                </div> --}}

                @if(auth()->user()->user_type == "admin")
                <div class="form-group" id="cluster">
                     <label class="col-lg-3 control-label">{{translate('Cluster Hub')}}</label>
                     <div class="col-lg-9">
                        <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
                           <option value="">{{translate('Select Cluster Hub') }}</option>
                            @foreach (\App\Cluster::where('status', 1)->get() as $key => $vale)
                            <option value="{{$vale->user_id}}" @if($vale->user_id == $ShortingHub->cluster_hub_id) Selected @endif>{{$vale->user->name}}</option>
                            @endforeach
                        </select>
                     </div>
                  </div>
                @endif


                @if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Cluster Hub")
                  <div class="form-group" id="cluster">
                     <label class="col-lg-3 control-label">{{translate('Cluster Hub')}}</label>
                     <div class="col-lg-9">
                        <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
                           <option value="">{{translate('Select Cluster Hub') }}</option>
                            @foreach (\App\Cluster::where('status', 1)->get() as $key => $vale)
                            <option value="{{$vale->user_id}}" @if($vale->user_id == $ShortingHub->cluster_hub_id) Selected @endif>{{$vale->user->name}}</option>
                            @endforeach
                        </select>
                     </div>
                  </div>
                  @endif

                  @if(auth()->user()->user_type == "staff" && auth()->user()->staff->role->name == "Sorting Hub")
                  <div class="form-group" id="cluster">
                     <label class="col-lg-3 control-label">{{translate('Cluster Hub')}}</label>
                     <div class="col-lg-9">
                        <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
                           <option value="">{{translate('Select Cluster Hub') }}</option>
                            @foreach (\App\Cluster::where('status', 1)->get() as $key => $vale)
                            @if($vale->user_id == $ShortingHub->cluster_hub_id)
                            <option value="{{$vale->user_id}}" selected="">{{$vale->user->name}}
                            </option>
                            @endif
                            @endforeach
                        </select>
                     </div>
                  </div>
                  @endif
                  <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Sorting Hub Name')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Sorting Name')}}" id="sorting_name" value="{{$ShortingHub->user->name}}" name="sorting_name"  class="form-control" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('User id')}}</label>
                  <div class="col-lg-9">
                      <input type="email" placeholder="{{translate('User id')}}" id="user_id" name="email" value="{{$ShortingHub->user->email}}" class="form-control" required readonly="">
                  </div>
                </div>
                
                <div class="form-group" id="areas">
                   <label class="col-lg-3 control-label">{{translate('Select Area Pincodes')}}</label>
                   <div class="col-lg-9">
                    <?php 
                          $sorting_pins = json_decode($ShortingHub->area_pincodes);
                          $unique_pins = get_unique_pins(get_mapped_pins(), $sorting_pins)
                    ?>
                    <textarea name="pincodes" id="pincodes" class="form-control" onBlur="filter_pincodes()" required="required">{{ implode(',',$sorting_pins) }}</textarea>
                    
                      <!-- <select class="form-control area_id demo-select2" name="area_ids[]" multiple data-selected-text-format="count" data-actions-box="true" required="">
                        @foreach (\App\Area::where('status', 1)->groupBy('pincode')->whereNotIn('pincode', $unique_pins)->get() as $key => $vale)
                          <option value="{{$vale->pincode}}" <?php //if(in_array($vale->pincode, $sorting_pins)) { echo 'selected'; } ?>>{{$vale->pincode}}</option>
                        @endforeach
                      </select> -->
                   </div>
                </div>

                <div class="form-group" id="phone">
                   <label class="col-lg-3 control-label">{{translate('Phone No.')}}</label>
                   <div class="col-lg-9">
                      <input type="text" name="phone" value="{{ $ShortingHub->user->phone }}" placeholder="Phone No." class="form-control" />
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
        $(el).closest('.product-choose').find('.city_id').html(null);
		$.post('{{ route('cities.get_city_by_state') }}',{_token:'{{ csrf_token() }}', state_id:state_id}, function(data){
		    for (var i = 0; i < data.length; i++) {
		        $(el).closest('.product-choose').find('.city_id').append($('<option>', {
		            value: data[i].name,
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

<script>
let pcode = [];
@foreach (\App\Area::where('status', 1)->groupBy('pincode')->whereNotIn('pincode', $unique_pins)->get() as $key => $vale)
pcode.push({{ $vale->pincode }});
@endforeach
console.log(pcode);
function filter_pincodes(){
    let a1 = $("#pincodes").val().split(',').map( Number );
    let a2  = pcode.unique();
    let filtered = $.map(a1,function(a){return $.inArray(a, a2) < 0 ? null : a;}); 
    $("#pincodes").val(filtered.unique().toString());
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