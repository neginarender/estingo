@extends('layouts.app')

@section('content')

<div class="col-lg-6 col-lg-offset-3">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{translate(' Add Operation users')}}</h3>
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <form class="form-horizontal" action="{{ route('callceter.addoperation') }}" method="POST" enctype="multipart/form-data">
        	@csrf
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="name">{{translate('Name')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" value="{{old('name')}}" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="email">{{translate('Email')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Email')}}" id="email" name="email" value="{{old('email')}}"  class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="mobile">{{translate('Phone')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Phone')}}" id="mobile" name="mobile" value="{{old('mobile')}}"  class="form-control" required>
                    </div>
                </div>
                 <div class="form-group">
                    <label class="col-sm-3 control-label" for="mobile">{{translate('Role')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Phone')}}" name="role_id"  value="operations" class="form-control" disabled="">
                        <input type="hidden" placeholder="{{translate('Phone')}}" name="role_id"  value="operations" class="form-control">
                    </div>
                </div>
              <!--   <div class="form-group">
                    <label class="col-sm-3 control-label" for="password">{{translate('Password')}}</label>
                    <div class="col-sm-9">
                        <input type="password" placeholder="{{translate('Password')}}" id="password" name="password" class="form-control" required>
                    </div>
                </div> -->
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="name">{{translate('Sorting Hub')}}</label>
                    <div class="col-sm-9">
                        <select name="sorting_hub" required class="form-control ">
                                @foreach($sorting_hubs as $sorting_hub )
                          <option value="{{$sorting_hub->user->id}}">{{$sorting_hub->user->name}} </option>
                          
                          @endforeach
                     <!--      <option value="callcenter">Call Center</option>
                          <option value="operations">Operations </option> -->
                          <!-- <option value="callceterandopration">Call Center and Opration</option> -->
                                                    
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
