
@extends('frontend_new.layouts.app')
@section('content')

 <section class="gry-bg   profile pb-5 ">
            <div class="page-title new mb-3">
                <div class="container"> 
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h2 class="heading heading-4 text-capitalize strong-600 mb-0">
                            Track Order
                        </h2>
                    </div>
                    <div class="col-md-6">
                        <div class="float-md-right">
                            <ul class="breadcrumb">
                                <li><a href="index.html">Home</a></li>
                                <li class="active"><a > Track Order</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
              </div>
            </div>
            <div class="container pb-5">
                <div class="row cols-xs-space cols-sm-space cols-md-space">
                    <div class="col-lg-10 mx-auto">
                        <div class="main-content">
                            
                            <form class="" action="{{route('phoneapi.showtrack_order')}}" method="POST">
                                <div class="form-box bg-white mt-4">
                                    <div class="form-box-title px-3 py-2 heading-6 strong-600">
                                        Order Info
                                    </div>
                                 
                                    <div class="form-box-content p-3">
                                           @if(!empty($success))
                                              <div class="alert alert-danger"> {{ $success }}</div>
                                           @endif
                                        <div class="row">
                                            <div class="col-md-2 pt-2">
                                                <label>Order Code <span class="required-star">*</span></label>
                                            </div>




                                            <input type="hidden" name="_token" value="{{csrf_token()}}" />


                                            <div class="col-md-8">
                                                @error('order_code')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                                <input type="text" class="form-control mb-3" placeholder="Order Code" value=" ORD80588699150575" name="order_code" required="">
                                            </div>
                                            <div class="text-right col-md-2 ">
                                                <button type="submit"  class="btn btn-styled btn-base-1">Track Order</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                              
                               
                            </form>
                           
                        </div>
                   
                
                        <div id="order-detail">
                            <div class="card mt-4">
                                <div class="card-header form-box-title py-2 px-3 heading-6 strong-600 clearfix">
                                    <div class="float-left">Order Summary</div>
                                </div>
                                <div class="card-body pb-0">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <table class="details-table table">
                                                <tbody><tr>
                                                    <td class="w-50 strong-600">Order Code:</td>
                                                    <td>ORD7777797752912</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">Customer:</td>
                                                    <td>dsadsa</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">Email:</td>
                                                    <td>apblog88@gmail.com</td>
                                                </tr>
                                                <tr>
                                                    <td class="w-50 strong-600">Shipping address:</td>
                                                    <td>Sector 11, Indira Nagar, Lucknow, Uttar Pradesh 226016, India</td>
                                                </tr>
                                            </tbody></table>
                                        </div>
                                        <div class="col-lg-6">
                                            <table class="details-table table">
                                                    <tbody><tr>
                                                        <td class="w-50 strong-600">Order date:</td>
                                                        <td>08-02-2022 12:09 PM</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="w-50 strong-600">Total order amount:</td>
                                                        <td> ₹1,170.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="w-50 strong-600">Shipping method:</td>
                                                        <td>Flat shipping rate</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="w-50 strong-600">Payment method:</td>
                                                        <td>Paytm</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-body p-4">
                                    <div class="col-12 table-responsive">
                                      <table class="details-table table  table-bordered">
                                        <thead>
                                            <tr><th>#</th>
                                            <th>Image</th>
                                            <th>Product Name</th>
                                            <th>Quantity </th>
                                            <th>Order status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td><img src="assets/images/products/1.jpg"></td>
                                                <td>Fortune Soya Health Refined Soyabean Oil - 5L</td>
                                                <td>1</td>
                                                <td><span class="alert alert-danger">pending<span></span></span></td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td><img src="assets/images/products/2.jpg"></td>
                                                <td>Organic Tattva Chana Dal</td>
                                                <td>1</td>
                                                <td><span class="alert alert-danger">pending<span></span></span></td>
                                            </tr>
                                        </tbody>
                                            
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
              </div>
        </section>
     

@endsection