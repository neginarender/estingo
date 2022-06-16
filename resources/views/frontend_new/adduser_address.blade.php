
@extends('frontend_new.layouts.app')
@section('content')
<section class="slice-xs sct-color-2 border-bottom">
     

        <!-- main content start -->
         <div class="  py-2 py-lg-4   gry-bg  ">   
          <div class="container pb-5">    
            <div class="row"> 
              <div class="col-md-10 mx-auto">   
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">New Address</h5>
                        <button class="btn btn-secondary btn-sm" onclick="window.history.back()">X</button>
                    </div>
                    <form class="form-default" role="form" action="{{url('new/addshippingaddress')}}" method="POST" autocomplete="on">
                        
                        <input type="hidden" name="_token" value="{{csrf_token()}}" />
                        <div class="modal-body product-choose loc_form ">
                            <div class="row">
                                <div class="col-md-3 col-sm-6 col-6">
                                     <label>Name</label>
                                     <input class="form-control controls" type="text" placeholder="Name" name="name" required="">
                                </div>
                                <div class="col-md-6">
                                    <label>Address Detail</label>
                                    <input id="map-search" class="form-control controls pac-target-input" type="text" placeholder="Address Detail" required="" name="address" autocomplete="off">
                                </div>
 
                                 <div class="col-md-3 col-sm-6 col-6 mb-3">
                                     <label>City</label>
                                      <input type="text" class="reg-input-city form-control" placeholder="City" required="" name="city" >
                                  </div>
                                  <div class="col-md-3 col-sm-6 col-6 mb-3">
                                     <label>State</label>
                                      <input type="text" class="reg-input-state form-control" placeholder="State" required="" name="state" >
                                      
                                  </div>
                                   <input type="hidden" class="reg-input-country form-control" name="country" value="India">
                                  <div class="col-md-3 col-sm-6 col-6 mb-3">
                                     <label>Postal</label>
                                      <input type="text" class="reg-input-postal form-control" placeholder="postal code" required="" name="postal_code">
                                  </div>

                                  <div class="col-md-3 col-sm-6 col-6 mb-3">
                                     <label>Tag</label>
                                      <input type="text" class="form-control" placeholder="Tag" name="tag" required="">
                                  </div>
                                  <div class="col-md-3 col-sm-6 col-6 mb-3">
                                     <label>Mobile Number</label>
                                      <input type="number" class="form-control" placeholder="Phone" name="phone" required="">
                                  </div>

                                   <div class="col-md-12 col-sm-12 col-12 mb-3">
                                        <div id="map-canvas"></div>
                                   </div>

                              </div>
                          </div>
                          <div class="modal-footer">
                             <button type="submit" class="btn btn-base-1 btn-lg">Save</button>
                          </div>
                      </form>

                  </div>
                </div>
            </div>
          </div>
      </div>

   </section>
        @endsection
           