
@extends('frontend_new.layouts.app')
@section('content')
<section class="slice-xs sct-color-2 border-bottom">
            <div class="container container-sm">
                <div class="row cols-delimited justify-content-center">
                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon mb-0">
                                <i class="fa fa-shopping-cart"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">1. My Cart</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center active" >
                            <div class="block-icon  mb-0">
                                <i class="fa fa-map"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">2. Shipping info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon mb-0 c-gray-light">
                                <i class="fa fa-truck"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">3. Delivery info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">4. Payment</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">5. Confirmation</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
         </section>

         <section class="py-4 gry-bg">
            <div class="container">
                <div class="row cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-lg-8">
                        <form class="form-default" id="shipping_form" data-toggle="validator" action="{{ route('phoneapi.delivery_info') }}" role="form" method="POST">
                            <input name="_token" type="hidden" value="{{ csrf_token() }}" />   
                        <div class="row gutters-5">
                                @foreach($addresses as $key => $address)
                                <div class="col-md-6">
                                    <label class="aiz-megabox d-block bg-white">
                                        <input type="radio" name="address_id" value="{{ $address->id }}" @if($address->set_default) checked @endif>
                                        <span class="d-flex p-3 aiz-megabox-elem">
                                            <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                            <span class="flex-grow-1 pl-3">
                                                <div>
                                                    <span class="alpha-6">Name:</span>
                                                    <span class="strong-600 ml-2">{{ $address->name }}</span>
                                                </div>
                                                
                                                <div>
                                                    <span class="alpha-6">Address:</span>
                                                    <span class="strong-600 ml-2">{{ $address->address }}</span>
                                                </div>
                                                <div>
                                                    <span class="alpha-6">Pin Code:</span>
                                                    <span class="strong-600 ml-2">{{ $address->postal_code }}</span>
                                                </div>
                                                <div>
                                                    <span class="alpha-6">City:</span>
                                                    <span class="strong-600 ml-2">{{ $address->city }}</span>
                                                </div>
                                                <div>
                                                    <span class="alpha-6">State:</span>
                                                    <span class="strong-600 ml-2">{{ $address->state }}</span>
                                                </div>
                                                <div>
                                                    <span class="alpha-6">Country:</span>
                                                    <span class="strong-600 ml-2">{{ $address->country }}</span>
                                                </div>
                                                <div>
                                                    <span class="alpha-6">Phone:</span>
                                                    <span class="strong-600 ml-2">{{ $address->phone }}</span>
                                                </div>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                @endforeach
                                <!-- <div class="col-md-6 mx-auto">
                                   <a href="{{route('phoneapi.shippingadress')}}">
                                    <div class="border p-3 rounded mb-3 c-pointer text-center bg-white">
                                        <i class="fa fa-plus fa-2x"></i>
                                        <div class="alpha-7">Add New Address</div>
                                    </div>
                                  </a>
                                </div> -->
                            </div>
                             <div class="row align-items-center pt-4 cart_bottom pb-4">
                                <div class="col-md-6 col-5">
                                <a href="javascript:void(0)" onclick="window.history.go(-1); return false;" class="link link--style-3">
                                        <i class="fa fa-arrow-left"></i>
                                        Return Back
                                    </a>
                                </div>
                                <div class="col-md-6 text-right col-7">
                                    <button   class="btn btn-styled btn-base-1" onclick="proceedToNext()">Continue to Delivery Info
                                </button></div>
                            </div>
                        </form>
                    </div>
                     <!-- right sidebar  -->
                
                    <div class="col-xl-4 ml-lg-auto" id="cart-summary">
                      
                  </div>
                </div>
            </div>
        </section>
        @endsection
        @section('script')
            <script>
                $(document).ready(function(){
                    loadCartSummary();
                });
            function loadCartSummary(){
                $.ajax({
                        url: '{{ route('ajax.cart_summary') }}',
                        type: 'post',
                        data: {_token:"{{ csrf_token() }}",sessionID:getCookie("sessionID")} ,
                        cache:true,
                        async:false,
                        success:function(data){
                            $("#cart-summary").html(data);
                        }
                    });
            }

            function proceedToNext(){
                if("{{ Cookie::get('logged')}}"){
                    $("#shipping_form").submit();
                }else{
                    window.location.href="{{ route('userapi.login',['checkout']) }}";
                }
            }
            </script>
        @endsection