@extends('layouts.app')

@section('content')

<div class="col-lg-6 col-lg-offset-3">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{translate(' Add Call Center users')}}</h3>
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <form class="form-horizontal" action="{{ route('callceter.addcustomer') }}" method="POST" enctype="multipart/form-data">
        	@csrf
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="name">{{translate('Name')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="email">{{translate('Email')}}</label>
                    <div class="col-sm-9">
                        <input type="email" placeholder="{{translate('Email')}}" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="mobile">{{translate('Phone')}}</label>
                    <div class="col-sm-9">
                        <input type="tel" placeholder="{{translate('Phone')}}" id="mobile" name="mobile" class="form-control"  pattern="[0-9]{10}" required>
                    </div>
                </div>
              <!--   <div class="form-group">
                    <label class="col-sm-3 control-label" for="password">{{translate('Password')}}</label>
                    <div class="col-sm-9">
                        <input type="password" placeholder="{{translate('Password')}}" id="password" name="password" class="form-control" required>
                    </div>
                </div> -->
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="name">{{translate('Role')}}</label>
                    <div class="col-sm-9">
                        <select name="role_id" required class="form-control ">
                                
                          <option value="">Selects</option>
                          <option value="fieldofficer">Field Officer </option>
                          <option value="callcenter">Call Center</option>
                          <option value="productlisting">Products Listing </option>
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
