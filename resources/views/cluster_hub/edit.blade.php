@extends('layouts.app')
@section('content')

  <div class="col-lg-8 col-lg-offset-2">
    <div class="panel">
      <div class="panel-heading">
          <h3 class="panel-title">{{translate('Edit Cluster Hub')}}</h3>
      </div>
      <form class="form-horizontal" action="{{ route('clusterhub.update', encrypt($cluster->id)) }}" method="POST" enctype="multipart/form-data">
        @csrf
         <input type="hidden" name="_method" value="PATCH">
         <input type="hidden" name="cluster_id" id="cluster_id" value="{{ $cluster->id }}">
          <div class="panel-body">
            <div class="product-choose-list">
              <div class="product-choose">
                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Cluster Name')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Name')}}" id="name" name="cluster_name"  class="form-control" value="{{$cluster->user->name }}" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('User id')}}</label>
                  <div class="col-lg-9">
                      <input type="email" placeholder="{{translate('User id')}}" id="user_id" name="email" value="{{$cluster->user->email}}" class="form-control" required readonly="">
                  </div>
                </div>
                {{-- <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Region')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control region_id demo-select2" name="region_id" required>
                         <option value="">{{translate('Select Region') }}</option>
                         @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}" @if($cluster->region_id == $region->id) Selected @endif>{{$region->name}}</option>
                         @endforeach
                      </select>
                   </div>
                </div> --}}
               
                <div class="form-group" id="states">
                   <label class="col-lg-3 control-label">{{translate('States')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control state_id demo-select2" name="state_id[]"  multiple data-selected-text-format="count" data-actions-box="true">
                        @foreach (\App\State::where('status', 1)->get() as $key => $state)
                          @php $mapped_state = json_decode($cluster->state_id); @endphp
                          <option value="{{$state->id}}" @if(in_array($state->id,$mapped_state)) Selected @endif>{{$state->name}}</option>
                        @endforeach
                      </select>
                   </div>
                </div>

                <div class="form-group" id="states">
                   <label class="col-lg-3 control-label">{{translate('City')}}</label>
                   <div class="col-lg-9 city_load">
                      <select class="form-control city_id demo-select2" name="city_ids[]" multiple data-selected-text-format="count" data-actions-box="true">
                         @foreach (\App\City::where('status', 1)->whereIn('state_id', json_decode($cluster->state_id,true))->get() as $key => $city)
                            @php $mapped_city = json_decode($cluster->cities,true); @endphp
                            <option value="{{$city->id}}" @if(in_array($city->id, $mapped_city)) Selected @endif>{{$city->name}}</option>
                        @endforeach
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
        $(el).closest('.product-choose').find('.city_id').html(null);
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
    //alert($(el).val());
    var state_id = $(el).val();
    var cluster_id = $("#cluster_id").val();
        console.log(state_id);
        //$(el).closest('.product-choose').find('.city_id').html(null);
    $.post('{{ route('clusterhub.load_city') }}',{_token:'{{ csrf_token() }}', state_id:state_id,cluster_id:cluster_id}, function(data){
      $(".city_load").html(data);
        $('.demo-select2').select2();
            //$(el).closest('.product-choose').find('.city_id').select2();
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