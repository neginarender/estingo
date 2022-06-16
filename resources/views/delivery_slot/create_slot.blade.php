@extends('layouts.app')

@section('content')
<!--Metro CSS-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/metro/4.4.3/css/metro-components.css" rel="stylesheet">
<style type="text/css">
    .wheel-picker .select-wrapper {z-index: 999999;}
</style>
<div class="col-lg-6 col-lg-offset-3">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{translate('Slot Information')}}</h3>
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <form class="form-horizontal" action="{{ route('deliveryslot.store') }}" method="POST">
        	@csrf
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="category_name">{{translate('Category Name')}}</label>
                    <div class="col-sm-9">
                        <select name="category_name" required class="form-control demo-select2-placeholder" required>
                                    <option value="">Select Category Name</option>
                                    <option value="Fresh_1">Fresh</option>
                                    <option value="Grocery/Precut_2">Grocery/Precut</option>
                                    <option value="Grocery_2">Grocery</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="category_detail">{{translate('Category Deatils')}}</label>
                        <div class="col-sm-9">
                            <select name="category_detail[]" required class="form-control demo-select2-placeholder" multiple required>
                            @foreach ($category as $key => $value)
                                <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach

                            </select>
                        </div>
                </div>
                @if(Auth::user()->user_type == "admin")
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="shorting_hub">{{translate('Shorting Hub')}}</label>
                        <div class="col-sm-9">
                            <select name="shorting_hub" required class="form-control demo-select2-placeholder" required>
                                            <option value="">{{"Select shorting hub"}}</option>
                                        @foreach($shorting_hub as $key => $value)
                                            <option value="{{$value->id}}">{{ucfirst($value->user->name)}}</option>
                                        @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label class="col-sm-3 control-label" for="cut_off">{{translate('Cut Off Time')}}</label>
                    <div class="col-sm-9">
                        <input type="time" id="cut_off" name="cut_off" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="delivery_time">{{translate('Delivery Time Start')}}</label>
                    <div class="col-sm-9">
                        <input type="text" data-role="timepicker" data-seconds="false" class = "delivery_time" data-on-set="getTime()" name="delivery_time_start"  required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="delivery_time">{{translate('Delivery Time End')}}</label>
                    <div class="col-sm-9">
                        <input type="text" data-role="timepicker" data-seconds="false"  name="delivery_time_end" class = "delivery_time" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="delivery_shift">{{translate('Delivery Shift')}}</label>
                    <div class="col-sm-9">
                    <select name="delivery_shift" required class="form-control demo-select2-placeholder" required>
                        <option value="">Select Delivery Shift</option>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Evening">Evening</option>
                    </select>
                    </div>
                </div>
               
            </div>
            <div class="panel-footer text-right">
                <button class="btn btn-purple" type="submit">{{translate('Save')}}</button>
            </div>
        </form>
        <!--===================================================-->
        <!--End Horizontal Form-->

    </div>
</div>

@endsection
<script src ="https://cdnjs.cloudflare.com/ajax/libs/metro/4.4.3/js/metro.min.js"></script>
<script>
  function getTime(el){
      console.log(el);

  }
</script>



