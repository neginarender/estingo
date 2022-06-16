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
     
</style>
 
 <div class="" id="" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="" class="second">
        <div class="modal-dialog modal-dialog-zoom modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title" id="exampleModalLabel">{{ translate('New Address') }}</h6>
                    <button class="btn btn-danger btn-sm" onclick="window.history.back()">X</button>
                </div>
               
                <form class="form-default" role="form" action="{{ route('addresses.store') }}" method="POST" autocomplete="on">
                    @csrf
                   <input type="hidden" name="page" value="{{ $page }}" />
                    <div class="modal-body product-choose loc_form ">
                        <div class="">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 col-6">
                                     <label>{{ translate('Name') }}</label><span style="color:red">*</span>
                                     <input class="form-control controls" type="text" placeholder="Name" name = "name" required>
                                </div>
                                <div class="col-md-6">
                                    <label>{{ translate('Address Detail') }}</label><span style="color:red">*</span>
                                    <input id="map-search" class="form-control controls" type="text" placeholder="Address Detail"  required="" name = "address" required>
                                </div>
                                 {{-- <div class="col-md-3 col-sm-6 col-6">
                                     <label>{{ translate('Lat')}}</label>
                                      <input type="text" class="latitude form-control" required>
                                  </div>
                                  <div class="col-md-3 col-sm-6 col-6">
                                     <label>{{ translate('Long')}}</label>
                                      <input type="text" class="longitude form-control">
                                  </div> --}}

                                <input type="hidden" class="latitude form-control" name = "lat">
                                <input type="hidden" class="longitude form-control" name = "long">

                                
                                <input type="hidden" class="reg-input-address form-control" placeholder="Address">
                                <div class="col-md-3 col-sm-6 col-6">
                                     <label>{{ translate('City')}}</label><span style="color:red">*</span>
                                      <input type="text" class="reg-input-city form-control"  placeholder="City" required="" name = "city" required>
                                  </div>
                                  <div class="col-md-3 col-sm-6 col-6">
                                     <label>{{ translate('State')}}</label><span style="color:red">*</span>
                                      <input type="text" class="reg-input-state form-control" placeholder="State" required="" name = "state" required>
                                  </div>
                                   <input type="hidden" class="reg-input-country form-control">
                                  <div class="col-md-3 col-sm-6 col-6">
                                     <label>{{ translate('Postal')}}</label><span style="color:red">*</span>
                                      <input type="text" class="reg-input-postal form-control" placeholder="postal code" required="" name = "pincode" required>
                                  </div>

                                  <div class="col-md-3 col-sm-6 col-6">
                                     <label>{{ translate('Mobile Number')}}</label><span style="color:red">*</span>
                                      <input type="number" class="form-control"  placeholder="Phone"  name = "phone" required>
                                  </div>


                                  <div class="col-md-12  ">
                                     <div id="map-canvas"></div>
                                  </div>

                            </div>
  
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-base-1">{{  translate('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script src="{{static_asset('js/location.js')}}"></script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAI3w2M4TTzj7JRpmO3gfGm5q6aaC9lnTM&libraries=places&callback=initialize"></script>
<script type="text/javascript">
  $(".btn-base-1").click(function() {
      $('html,body').animate({
          scrollTop: 0}, 0);
  });
</script>
    @endsection