<div class="panel-heading">
    <h3 class="panel-title">{{translate('Create Sorting Hub')}}</h3>
</div>

<div class="product-choose-list">
    <div class="product-choose">

    	<div class="form-group">
		    <label class="col-lg-3 control-label" for="coupon_code">{{translate('User id')}}</label>
		    <div class="col-lg-9">
		        <input type="email" placeholder="{{translate('User id')}}" id="user_id" name="email" class="form-control" required>
		    </div>
		</div>

        <div class="form-group">
           <label class="col-lg-3 control-label">{{translate('Region')}}</label>
           <div class="col-lg-9">
              <select class="form-control region_id demo-select2" name="region_id" required>
              	 <option value="">{{translate('Select Region') }}</option>
                 @foreach (\App\Region::where('status', 1)->get() as $key => $region)
                     <option value="{{$region->id}}">{{$region->name}}</option>
                 @endforeach
              </select>
           </div>
        </div>

        <div class="form-group" id="states">
           <label class="col-lg-3 control-label">{{translate('States')}}</label>
           <div class="col-lg-9">
              <select class="form-control state_id demo-select2" name="state_id" required="">
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

        <div class="form-group" id="cluster">
           <label class="col-lg-3 control-label">{{translate('Cluster Hub')}}</label>
           <div class="col-lg-9">
              <select class="form-control cluster_id demo-select2" name="cluster_hub" required="">
              	 <option value="">{{translate('Select Cluster Hub') }}</option>
                
              </select>
           </div>
        </div>

        <div class="panel-heading">
         <h3 class="panel-title">{{translate('Area List')}}</h3>
     	</div>

     	<div class="form-group" id="areas">
           <label class="col-lg-3 control-label">{{translate('Area Pincodes')}}</label>
           <div class="col-lg-9">
              <select class="form-control area_id demo-select2" name="area_ids[]" multiple data-selected-text-format="count" data-actions-box="true" required="">
              </select>
           </div>
        </div>

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

	function get_area_by_city(el){
		var city_id = $(el).val();

        $(el).closest('.product-choose').find('.area_id').html(null);
		$.post('{{ route('area.get_area_by_city') }}',{_token:'{{ csrf_token() }}', city_id:city_id}, function(data){
		    for (var i = 0; i < data.length; i++) {
		        $(el).closest('.product-choose').find('.area_id').append($('<option>', {
		            value: data[i].pincode,
		            text: data[i].area_name+' | '+data[i].pincode
		        }));
		    }
            $(el).closest('.product-choose').find('.area_id').select2();
		});
	}

    $(document).ready(function(){
        $('.demo-select2').select2();
    });

    $('.region_id').on('change', function() {
        get_states_by_region(this);
    });

    $('.state_id').on('change', function() {
        get_mapped_city_by_state_id(this);

    });

    $('.city_id').on('change', function() {
        get_area_by_city(this);
        get_cluster_by_city(this);
    });

</script>