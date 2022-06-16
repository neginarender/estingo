@extends('layouts.app')

@section('content')

<div class="col-lg-6 col-lg-offset-3">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{ translate('Attribute Information')}}</h3>
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <?php 
            // echo '<pre>'; 
            // print_r($attribute);
            // die;

        ?>
        <form class="form-horizontal" action="{{ URL::to('admin/attributes/updateoptionattribute')}}/{{$attribute[0]->attribute_option_value_id }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label" for="name">{{ translate('Name')}}</label>
                    <div class="col-sm-10">
                        <input type="hidden" id="attr_id" name="attr_id" class="form-control" value="{{$attribute[0]->attribute_id}}">
                        <input type="text" placeholder="{{ translate('Name')}}" id="name" name="name" class="form-control" required value="{{$attribute[0]->attribute_option_value}}">
                    </div>
                </div>
            </div>
            <div class="panel-footer text-right">
                <button class="btn btn-purple" type="submit">{{ translate('Save')}}</button>
            </div>
        </form>
        <!--===================================================-->
        <!--End Horizontal Form-->

    </div>
</div>

@endsection
