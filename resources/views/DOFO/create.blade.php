@extends('layouts.app')

@section('content')

<div class="col-lg-6 col-lg-offset-3">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{translate('DOFO')}}</h3>
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <form class="form-horizontal" action="{{ route('DOFO.store') }}" method="POST" enctype="multipart/form-data">
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
                        <input type="text" placeholder="{{translate('Email')}}" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="mobile">{{translate('Phone')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Phone')}}" id="mobile" name="mobile" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="mobile">{{translate('Pin Code')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Pin Code')}}" id="pin" name="pin" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label" for="mobile">{{translate('Address')}}</label>
                    <div class="col-sm-9">
                        <textarea  placeholder="{{translate('Address')}}" id="address" name="address" class="form-control" ></textarea>
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
