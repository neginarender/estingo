@extends('layouts.app')

@section('content')

<div class="col-lg-6 col-lg-offset-3">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{translate('Banner Information')}}</h3>
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <form class="form-horizontal" action="{{ route('brandslider.store') }}" method="POST" enctype="multipart/form-data">
        	@csrf
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="name">{{translate('Name')}}</label>
                    <div class="col-sm-10">
                        <input type="text" placeholder="{{translate('Name')}}" id="name" name="name" class="form-control" required>
                    </div>
                </div>
            
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="banner">{{translate('Banner')}} <small>({{ translate('200x300') }})</small></label>
                    <div class="col-sm-10">
                        <input type="file" id="banner" name="banner" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="icon">{{translate('Icon')}} <small>({{ translate('32x32') }})</small></label>
                    <div class="col-sm-10">
                        <input type="file" id="icon" name="icon" class="form-control">
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
