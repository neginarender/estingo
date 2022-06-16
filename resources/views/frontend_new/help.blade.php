
@extends('frontend_new.layouts.app')
@section('content')


        <!-- main content start -->
        <section class="gry-bg py-4 profile">
          <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">

                  @include('frontend_new.inc.customer_side_nav')

                   <div class="col-lg-9">
                    <!-- Page title -->
                    <div class="page-title">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-12">
                                <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                    Need Help?
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li ><a href="user-dashboard.html">Dashboard</a></li>
                                        <li  ><a href="order-history.html" >Purchase History</a></li>
                                         <li class="active"><a >Need Help?</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  content -->
                        <form class="" action="#" method="POST"  >
                            <div class="form-box bg-white mt-4">
                                <div class="form-box bg-white mt-4">
                                 <div class="form-box-content p-4">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Order No. <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control mb-3" name="order_no" placeholder="Order No." value="{{$orderd->code}}" readonly="">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Choose Product <span class="required-star">*</span></label>
                                        </div>
                                       
                                        <div class="col-md-10">
                                            <select name="order_details" class="form-control mb-3" id="choose_product">
                                             <option value="">Choose Product</option>
                                           </select>
                                         </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Quantity <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="text" class="form-control mb-3" name="qty" placeholder="Quantity" value="" readonly="">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Order Amount <span class="required-star">*</span></label>
                                        </div>
                                        
                                        <div class="col-md-10">
                                            <input type="number" id="price" class="form-control mb-3" name="price" placeholder="Amount" value="{{$orderd->grand_total}}" readonly="">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Write Here <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                            <textarea class="form-control mb-3" name="reason" placeholder="Message"></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Upload Image</label>
                                        </div>
                                        <div class="col-md-10">
                                            <input type="file" id="photo" class="form-control mb-3" name="photo[]" multiple="">
                                            *Try selecting more than one file when browsing for files
                                        </div>
                                        <div class="col-md-10" id="dvPreview">
                                        </div>
                                    </div>
                                    
                                 </div>
                             </div>
                            </div>
                            <div class="form-box mt-4 text-right">
                                <button type="submit" class="btn btn-styled btn-base-1">Send</button>
                            </div>
                        </form>
                        
                    </div>
              </div>
          </div>
           </section>
          


@endsection