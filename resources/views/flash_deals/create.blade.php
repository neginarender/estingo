@extends('layouts.app')

@section('content')

<div class="col-sm-12">
    <div class="panel">
        <div class="panel-heading">
            <h3 class="panel-title">{{translate('Flash Deal Information')}}</h3>
        </div>

        <!--Horizontal Form-->
        <!--===================================================-->
        <form class="form-horizontal" action="{{ route('flash_deals.store') }}" method="POST" enctype="multipart/form-data">
        	@csrf
            <div class="panel-body">
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="name">{{translate('Title')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Title')}}" id="name" name="title" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="background_color">{{translate('Background Color')}} <small>(Hexa-code)</small></label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('#FFFFFF')}}" id="background_color" name="background_color" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="name">{{translate('Text Color')}}</label>
                    <div class="col-lg-9">
                        <select name="text_color" id="text_color" class="form-control demo-select2" required>
                            <option value="">{{translate('Select One')}}</option>
                            <option value="white">{{translate('White')}}</option>
                            <option value="dark">{{translate('Dark')}}</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="banner">{{translate('Banner')}} <small>(1920x500)</small></label>
                    <div class="col-sm-9">
                        <input type="file" id="banner" name="banner" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label" for="start_date">{{translate('Date')}}</label>
                    <div class="col-sm-9">
                        <div id="demo-dp-range">
                            <div class="input-daterange input-group" id="datepicker">
                                <input type="text" class="form-control" name="start_date">
                                <span class="input-group-addon">{{translate('to')}}</span>
                                <input type="text" class="form-control" name="end_date">
                            </div>
                        </div>
                    </div>
                </div>
                @php
                    $sortinghub = \App\ShortingHub::all();
                @endphp
                <div class="form-group mb-3">
                    <label class="col-sm-3 control-label" for="sorting_hub">{{translate('Sorting Hub')}}</label>
                    <div class="col-sm-9">
                        <select class="form-control demo-select2" name="sorting_hub" id="sorting_hub">
                            <option value="">Select</option>
                            @foreach($sortinghub as $key => $sorthub)
                            <option value="{{ $sorthub->user_id }}">{{ $sorthub->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group mb-3">
                    <label class="col-sm-3 control-label" for="products">{{translate('Products')}}</label>
                    <div class="col-sm-9">
                        <select name="products[]" id="products" class="form-control demo-select2" multiple required data-placeholder="{{ translate('Choose Products') }}">
                            <!-- @foreach(\App\Product::all() as $product)
                                <option value="{{$product->id}}">{{__($product->name)}}</option>
                            @endforeach -->
                        </select>
                    </div>
                </div>
                <br>
                <div class="form-group" id="discount_table">

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

@section('script')
    <script type="text/javascript">
        $(document).ready(function(){

            $('#products').on('change', function(){
                var product_ids = $('#products').val();
                if(product_ids.length > 0){
                    $.post('{{ route('flash_deals.product_discount') }}', {_token:'{{ csrf_token() }}', product_ids:product_ids,sorting_hub_id:$("#sorting_hub").val()}, function(data){
                        $('#discount_table').html(data);
                        $('.demo-select2').select2();
                    });
                }
                else{
                    $('#discount_table').html(null);
                }
            });

            $("#sorting_hub").on('change',function(){
                $("#products").empty();
                $.post('{{ route('flash_deals.products') }}', {_token:'{{ csrf_token() }}', sorting_hub_id:$("#sorting_hub").val()}, function(data){
                        var product = $("#products");
                        $.map(data,function(item){
                            product.append("<option value="+item.id+">"+item.name+"</option>");
                        });
                        $('.demo-select2').select2();
                    });
            });

        });
    </script>
@endsection
