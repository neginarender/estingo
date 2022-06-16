@extends('layouts.app')
@section('content')

  <div class="col-lg-8 col-lg-offset-2">
    <div class="panel">
      <div class="panel-heading">
          <h3 class="panel-title">{{translate('Create Sorting Hub')}}</h3>
      </div>
      <form class="form-horizontal" action="{{ route('sorthinghub.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="mapping_type" value="sorting_hub">
          <div class="panel-body">
            <div class="product-choose-list">
              <div class="product-choose">


                @if(in_array(Auth::user()->user_type, ['admin']))

                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Sorting Hub Name')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Sorting Name')}}" id="sorting_name" name="sorting_name"  class="form-control" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('User id')}}</label>
                  <div class="col-lg-9">
                      <input type="email" placeholder="{{translate('User id')}}" id="user_id" name="email"  class="form-control" required>
                  </div>
                </div>
               

                <div class="form-group" id="cluster">
                   <label class="col-lg-3 control-label">{{translate('Cluster Hub')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
                        <option value="">{{translate('Select Cluster Hub') }}</option>
                        @foreach ($getCluster as $key => $clusterhub)
                        <option value="<?php echo $clusterhub->id;?>">{{$clusterhub->user->name }}</option>
                        @endforeach
                      </select>
                   </div>
                </div>

                <!-- <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Region')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control region_id demo-select2" name="region_id" required>
                         <option value="">{{translate('Select Region') }}</option>
                         @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}">{{$region->name}}</option>
                         @endforeach
                      </select>
                   </div>
                </div> -->

                <div class="form-group" id="states">
                   <label class="col-lg-3 control-label">{{translate('States')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control state_id demo-select2" name="state_id" required="">
                      <option value="">{{translate('Select Region') }}</option>
                         {{-- @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}">{{$region->name}}</option>
                         @endforeach --}}
                      </select>
                   </div>
                </div>

              <div class="form-group" id="city">
                 <label class="col-lg-3 control-label">{{translate('City')}}</label>
                 <div class="col-lg-9">
                    <select class="form-control city_id demo-select2" name="city_id" required="">
                    </select>
                 </div>
              </div>


              @else
              <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('Sorting Hub Name')}}</label>
                  <div class="col-lg-9">
                      <input type="text" placeholder="{{translate('Sorting Name')}}" id="sorting_name" name="sorting_name"  class="form-control" required>
                  </div>
                </div>

                <div class="form-group">
                  <label class="col-lg-3 control-label" for="coupon_code">{{translate('User id')}}</label>
                  <div class="col-lg-9">
                      <input type="email" placeholder="{{translate('User id')}}" id="user_id" name="email"  class="form-control" required>
                  </div>
                </div>
               

                <div class="form-group" id="cluster">
                   <label class="col-lg-3 control-label">{{translate('Cluster Hub')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
                        <option value="">{{translate('Select Cluster Hub') }}</option>
                        <option value="<?php echo $cluster->id;?>">{{$cluster->user->name }}</option>
                      </select>
                   </div>
                </div>

                <!-- <div class="form-group">
                   <label class="col-lg-3 control-label">{{translate('Region')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control region_id demo-select2" name="region_id" required>
                         <option value="">{{translate('Select Region') }}</option>
                         @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}">{{$region->name}}</option>
                         @endforeach
                      </select>
                   </div>
                </div> -->

                <div class="form-group" id="states">
                   <label class="col-lg-3 control-label">{{translate('States')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control state_id demo-select2" name="state_id" required="">
                      <option value="">{{translate('Select Region') }}</option>
                         {{-- @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                             <option value="{{$region->id}}">{{$region->name}}</option>
                         @endforeach --}}
                      </select>
                   </div>
                </div>

              <div class="form-group" id="city">
                 <label class="col-lg-3 control-label">{{translate('City')}}</label>
                 <div class="col-lg-9">
                    <select class="form-control city_id demo-select2" name="city_id[]"  required="">
                    </select>
                 </div>
              </div>

              @endif

              <!-- <div class="panel-heading">
               <h3 class="panel-title">{{translate('Area List')}}</h3>
              </div> -->

              <div class="form-group" id="pincpdes">
                   <label class="col-lg-3 control-label">{{translate('Area Pincodes')}}</label>
                   <div class="col-lg-9">
                      <textarea class="form-control" name="pincodes" id="pincodes" onBlur="filter_pincodes()" required></textarea>
                   </div>
                </div>

                <!-- <div class="form-group" id="areas">
                   <label class="col-lg-3 control-label">{{translate('Area pincodes')}}</label>
                   <div class="col-lg-9">
                      <select class="form-control area_id demo-select2" name="area_ids[]" multiple data-selected-text-format="count" data-actions-box="true" required="">
                        @foreach (\App\Area::where('status', 1)->groupBy('pincode')->whereNotIn('pincode', get_mapped_pins())->get() as $key => $vale)
                          <option value="{{$vale->pincode}}">{{$vale->pincode}}</option>
                        @endforeach
                      </select>
                   </div>
                </div> -->
                        
                <div class="form-group" id="phone">
                   <label class="col-lg-3 control-label">{{translate('Phone No.')}}</label>
                   <div class="col-lg-9">
                      <input type="text" name="phone" value="" placeholder="Phone No." class="form-control" />
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
        $(".state_id").prepend("<option value='' selected='selected'>Select state</option>");
            $(el).closest('.product-choose').find('.state_id').select2();
    });
  }

  function get_mapped_city_by_state_id(el){
    var state_id = $(el).val();
        console.log(state_id);
        $(el).closest('.product-choose').find('.city_id').html(null);
    $.post('{{ route('cities.get_mapped_city_by_state_id') }}',{_token:'{{ csrf_token() }}', state_id:state_id}, function(data){
        for (var i = 0; i < data.length; i++) {
            $(el).closest('.product-choose').find('.city_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
        }
        $(".city_id").prepend("<option value='' selected='selected'>Select city</option>");
            $(el).closest('.product-choose').find('.city_id').select2();
    });
  }

  function get_cluster_by_city(el){
    var city_id = $(el).val();

    $(el).closest('.product-choose').find('.cluster_id').html(null);
      $.post('{{ route('area.get_cluster_by_city_id') }}',{_token:'{{ csrf_token() }}', city_id:city_id}, function(data){
          for (var i = 0; i < data.length; i++) {
              $(el).closest('.product-choose').find('.cluster_id').append($('<option>', {
                  value: data[i].user_id,
                  text: data[i].email
              }));
          }
          $(".cluster_id").prepend("<option value='' selected='selected'>Select cluster</option>");
              $(el).closest('.product-choose').find('.cluster_id').select2();
      });
  }

  let pcode = [];
  function get_area_by_city(el){
    var city_id = $(el).val();
        $(el).closest('.product-choose').find('.area_id').html(null);
        $.post('{{ route('area.get_area_by_city') }}',{_token:'{{ csrf_token() }}', city_id:city_id}, function(data){
            for (var i = 0; i < data.length; i++) {
              pcode.push(data[i].pincode);
                $(el).closest('.product-choose').find('.area_id').append($('<option>', {
                    value: data[i].pincode,
                    text: data[i].area_name+' | '+data[i].pincode
                }));
            }
                $(el).closest('.product-choose').find('.area_id').select2();
        });
  }

  function get_states_by_cluster(el){
    var cluster_id = $(el).val();
        $(el).closest('.product-choose').find('.state_id').html(null);
    $.post('{{ route('states.get_states_by_cluster') }}',{_token:'{{ csrf_token() }}', cluster_id:cluster_id}, function(data){
        for (var i = 0; i < data.length; i++) {
            $(el).closest('.product-choose').find('.state_id').append($('<option>', {
                value: data[i].id,
                text: data[i].name
            }));
        }
        $(".state_id").prepend("<option value='' selected='selected'>Select state</option>");
            $(el).closest('.product-choose').find('.state_id').select2();
    });
  }

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

    $(document).ready(function(){
        $('.demo-select2').select2();
    });

    $('.cluster_id').on('change', function() {
        get_states_by_cluster(this);
    });

    $('.state_id').on('change', function() {
        get_mapped_city_by_state_id(this);
    });

    $('.city_id').on('change', function() {
        get_area_by_city(this);
       // get_cluster_by_city(this);
    });


</script>
@endsection