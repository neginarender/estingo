@extends('frontend.layouts.app')

@section('content')
<style type="text/css">
     .loc_form .form-control {margin-bottom: 10px; border-color: #ccc}
     .loc_form label {margin-bottom: 5px}
      #map-canvas {height: 300px; margin: 0; padding: 0; width: 100%;
      }
    .modal-dialog{ margin-top: 10px }
    .modal-header {padding: .5rem 1.5rem;}
    .modal-title {font-weight: 600}
      @media(max-width: 800px){
        .referral {width: 100%; position: relative;}
    }
</style>

    <div id="page-content">
        <section class="slice-xs sct-color-2 border-bottom">
            <div class="container container-sm">
                <div class="row cols-delimited justify-content-center">
                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center ">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="la la-shopping-cart"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">1. My Cart</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center active">
                            <div class="block-icon mb-0">
                                <i class="la la-map-o"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">2. Shipping info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon mb-0 c-gray-light">
                                <i class="la la-truck"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">3. Delivery info</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="la la-credit-card"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">4. Payment</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class="icon-block icon-block--style-1-v5 text-center">
                            <div class="block-icon c-gray-light mb-0">
                                <i class="la la-check-circle"></i>
                            </div>
                            <div class="block-content d-none d-md-block">
                                <h3 class="heading heading-sm strong-300 c-gray-light text-capitalize">5. Confirmation</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
 <style type="text/css">
    .checkout_form  label {font-size: 14px;}
    .checkout_form .form-control {border:solid 1px #aaa;}
    @media(max-width: 640px){
     .checkout_form  label {font-size: 14px; font-weight: 600}
    }
 </style>
        <section class="py-4 gry-bg">
            <div class="container">
                <div class="row cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-lg-8">
                        <form class="form-default checkout_form" data-toggle="validator" action="{{ route('checkout.store_shipping_infostore') }}" role="form" method="POST">
                            @csrf
                                @if(Auth::check())
                                    <div class="row gutters-5">
                                        @foreach (Auth::user()->addresses as $key => $address)
                                            <div class="col-md-6">
                                                <label class="aiz-megabox d-block bg-white">
                                                    <input type="radio" name="address_id" value="{{ $address->id }}" @if ($address->set_default)
                                                        checked
                                                    @endif>
                                                    <span class="d-flex p-3 aiz-megabox-elem">
                                                        <span class="aiz-rounded-check flex-shrink-0 mt-1"></span>
                                                        <span class="flex-grow-1 pl-3">
                                                        @if(!empty($address->name))
                                                            <div>
                                                                <span class="alpha-6">Name:</span>
                                                                <span class="strong-600 ml-2">{{ $address->name }}</span>
                                                            </div>
                                                        @endif  
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
                                        <input type="hidden" name="checkout_type" value="logged">
                                        <!-- <div class="col-md-6 mx-auto" onclick="add_new_address()">
                                            <div class="border p-3 rounded mb-3 c-pointer text-center bg-white">
                                                <i class="la la-plus la-2x"></i>
                                                <div class="alpha-7">{{ translate('Add New Address') }}</div>
                                            </div>
                                        </div> -->

                                        <div class="col-md-6 mx-auto">
                                        <a href= "{{route('show.map',[encrypt(1)])}}">
                                            <div class="border p-3 rounded mb-3 c-pointer text-center bg-white">
                                               
                                                <i class="la la-plus la-2x"></i>
                                                <div class="alpha-7">Add New Address</div>
                                            </div>
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Name <span style="color:red;">*</span></label>
                                                    <input type="text" class="form-control" name="name" placeholder="Name" required>
                                                </div>
                                            </div>
                                             <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="control-label">Email <span style="color:red;">*</span></label>
                                                    <input type="text" class="form-control" name="email" placeholder="Email" required>
                                                </div>
                                            </div>
                                        </div>
 

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label class="control-label">Address <span style="color:red;">*</span></label>
                                                    <input id="map-search" class="form-control controls" type="text" placeholder="Search Box"  required="" name = "address" >
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group has-feedback">
                                                    <label class="control-label">City <span style="color:red;">*</span></label>
                                                    <input type="text" class="reg-input-city form-control"  placeholder="City" required="" name = "city">
                                                </div>
                                            </div>
                                             <div class="col-md-6">
                                                <div class="form-group has-feedback">
                                                    <label class="control-label">State <span style="color:red;">*</span></label>
                                                    <input type="text" class="reg-input-state form-control" placeholder="State" required="" name = "state">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group has-feedback">
                                                    <label class="control-label">Pin code<span style="color:red;">*</span></label>
                                                     <input type="text" class="reg-input-postal form-control" placeholder="postal code" required="" name = "postal_code">
                                                      <input type="hidden" class="latitude form-control" name = "lat">
                                                      <input type="hidden" class="longitude form-control" name = "long">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group has-feedback">
                                                    <label class="control-label">Phone <span style="color:red;">*</span></label>
                                                    <input type="number" min="0" class="form-control" placeholder="Phone" name="phone" required>
                                                </div>
                                            </div>
                                            <div class="col-md-12  ">
                                                <div id="map-canvas"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="checkout_type" value="guest">
                                    </div>
                                    </div>
                                @endif
                            <div class="row align-items-center pt-4 p_bottom">
                                <div class="col-md-6 col-5 col-sm-6">
                                <a href="{{ route('cart') }}" class="link link--style-3">
                                        <i class="ion-android-arrow-back"></i>
                                        Return to cart
                                    </a>
                                </div>
                            <div class="col-md-6 text-right col-7 col-sm-6">
                                    <button type="submit" class="btn btn-styled btn-base-1">Continue to Delivery Info</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-lg-4 ml-lg-auto" id="cart-summary">
                        @include('frontend.partials.cart_summary')
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script src="{{static_asset('js/location.js')}}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAI3w2M4TTzj7JRpmO3gfGm5q6aaC9lnTM&libraries=places&callback=initialize"></script>

@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
<script src="{{asset('public/bootstrap3-typeahead.min.js')}}"></script>
<script src="{{asset('public/bootstrap3-typeahead.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        get_state_by_country_id();
    });
    //CityTags Search Functionlity
    var cityroute = "{{ route('city.get_city_by_state') }}";
    var state = $('#state_id').val();
    $('#citytags').typeahead({
        source:  function (term, process) {
        return $.post(cityroute, { term: term , "_token": "{{ csrf_token() }}",state: state}, function (data) {
                return process(data);
            });
        }
    });

    function add_new_address(){
        $('#new-address-modal').modal('show');
    }
    function get_state_by_country_id(){
        var country_id = $('#country_id').val();
        $.post('{{ route('state.get_state_by_country_id') }}',{_token:'{{ csrf_token() }}', country_id:country_id}, function(data){
            
            $('#state_id').html(null);
            for (var i = 0; i < data.length; i++) {
                $('#state_id').append($('<option>', {
                    value: data[i].name,
                    text: data[i].name
                }));
                
            }

            //get_city_by_state();
        });
    }


 function get_state_by_country_id(){
        var country_id = $('#country_id').val();
        $.post('{{ route('state.get_state_by_country_id') }}',{_token:'{{ csrf_token() }}', country_id:country_id}, function(data){
            
            $('#state_id').html(null);
            for (var i = 0; i < data.length; i++) {
                $('#state_id').append($('<option>', {
                    value: data[i].name,
                    text: data[i].name
                }));
                
            }

            //get_city_by_state();
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


//   function get_area_by_city(el){
//     var city_id = $(el).val();
//         $(el).closest('.product-choose').find('.area_id').html(null);
//         $.post('{{ route('area.get_area_for_delivery') }}',{_token:'{{ csrf_token() }}', city_id:city_id}, function(data){
//             for (var i = 0; i < data.length; i++) {
//                 $(el).closest('.product-choose').find('.area_id').append($('<option>', {
//                     value: data[i].pincode,
//                     text: data[i].area_name+' | '+data[i].pincode
//                 }));
//             }
//                 $(el).closest('.product-choose').find('.area_id').select2();
//         });
//   }

  $('.state_id').on('change', function() {
        get_mapped_city_by_state_id(this);
    });

   

    $('#country_id').on('change', function() {
        get_state_by_country_id();
    });


    $('.city_id').on('change', function() {
        get_area_by_city(this);
       // get_cluster_by_city(this);
    });

    function check_delivery_availability(el){
        var pincode = $(el).val();

    }

    $('.area_id').on('change', function() {
        check_delivery_availability(this);
       // get_cluster_by_city(this);
    });
    // $('#state_id').on('change', function() {
    //     get_city_by_state();
    // });
</script>
@endsection
