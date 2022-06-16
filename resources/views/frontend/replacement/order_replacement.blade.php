
@extends('frontend.layouts.app')

@section('content')
<style>
    .error{
        color: #ec0404;
    font-size: 11px;
    }
</style>
@php
$replacement = [];
$replace = \App\ReplacementOrder::where('order_id',$order->id)->select('order_detail_id')->get();
$replacement = $replace->map(function($item){
return $item->order_detail_id;
});
@endphp
    <section class="gry-bg py-4 profile">

        <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                    @if(Auth::user()->user_type == 'seller')
                        @include('frontend.inc.seller_side_nav')
                    @elseif(Auth::user()->user_type == 'customer')
                        @include('frontend.inc.customer_side_nav')
                    @endif
                </div>

                <div class="col-lg-9">
                    <div class="main-content">
                        <!-- Page title -->
                        <div class="page-title">
                            <div class="row align-items-center">
                                <div class="col-md-6 col-12 d-flex align-items-center">
                                    <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                        {{__('Need Help?')}}
                                    </h2>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="{{ route('home') }}">{{ translate('Home')}}</a></li>
                                            <li><a href="{{ route('dashboard') }}">{{ translate('Dashboard')}}</a></li>
                                            <li><a href="{{ route('purchase_history.index') }}">{{ translate('Purchase History')}}</a></li>
                                            <li class="active"><a href="javascript:void(0);" style="cursor:text;">{{ translate('Need Help')}}</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form  action="{{route('orderReplacement.store')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ $order->id }}" />
                            <div class="form-box bg-white mt-4">
                                <div class="form-box-content p-3">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Order No.')}} <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control mb-3" name="order_no" placeholder="{{__('Order No.')}}" value="{{ $order->code }}" readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Choose Product')}} <span class="required-star">*</span></label>
                                        </div>
                                       
                                        <div class="col-md-10">
                                            <select name="order_details" class="form-control mb-3" id="choose_product">
                                            <option value="">Choose Product</option>
                                            @foreach ($order->orderDetails->where('delivery_status','delivered') as $key => $value)
                                                @if(!in_array($value->id,$replacement->toArray()))
                                                <option value="{{$value->id}}">{{$value->product->name."-".$value->variation}}</option>
                                                @endif
                                            @endforeach
                                            </select>
                                            @if($errors->has('order_details'))
                                                <div class="error">{{ $errors->first('order_details') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Quantity')}} <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control mb-3" name="qty" placeholder="{{__('Quantity')}}" value="" readonly>
                                            @if($errors->has('qty'))
                                                <div class="error">{{ $errors->first('qty') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Order Amount')}} <span class="required-star">*</span></label>
                                        </div>
                                        
                                        <div class="col-md-10">
                                            <input type="number" id = "price" class="form-control mb-3" name="price"  placeholder="{{__('Amount')}}" value="" readonly>
                                            @if($errors->has('price'))
                                                <div class="error">{{ $errors->first('price') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Write Here')}} <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control mb-3" name="reason" placeholder="Message"></textarea>
                                            @if($errors->has('reason'))
                                                <div class="error">{{ $errors->first('reason') }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>{{__('Upload Image')}}</label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type = "file" id = "photo" class="form-control mb-3" name="photo[]" multiple>
                                            *Try selecting more than one file when browsing for files
                                        </div>

                                        <div class="col-md-10" id="dvPreview">
                                        </div>
                                    </div>

                                    
                                </div>
                            </div>
                            <div class="form-box mt-4 text-right">
                                <button type="submit" class="btn btn-styled btn-base-1">{{ __('Send') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript">
    $('#choose_product').on('change', '', function (e) {
        var product_detail_id = this.value;
        if(product_detail_id != ""){
            $.post('{{ route('detail.order') }}',{_token:'{{ csrf_token() }}', product_detail_id:product_detail_id}, function(data){
                console.log(data.price);
                $("input[name=price]").val(data.price - data.peer_discount);
                $("input[name=qty]").val(data.quantity);
                
            });

        }




    });

    var fileUpload = document.getElementById("photo");
    fileUpload.onchange = function () {
        if (typeof (FileReader) != "undefined") {
            var dvPreview = document.getElementById("dvPreview");
            dvPreview.innerHTML = "";
            var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpg|.jpeg|.gif|.png|.bmp)$/;
            for (var i = 0; i < fileUpload.files.length; i++) {
                var file = fileUpload.files[i];
                if (regex.test(file.name.toLowerCase())) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        var img = document.createElement("IMG");
                        img.height = "100";
                        img.width = "100";
                        img.src = e.target.result;
                        dvPreview.appendChild(img);

                    }
                    reader.readAsDataURL(file);
                } else {
                    alert(file.name + " is not a valid image file.");
                    dvPreview.innerHTML = "";
                    return false;
                }
            }
        } else {
            alert("This browser does not support HTML5 FileReader.");
        }
    };

   

    </script>

@endsection
