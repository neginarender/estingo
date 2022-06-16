
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
                                    Purchase History
                                </h2>

                                   @if(session()->has('message'))
                                     <h1 class="heading heading-4 strong-600" style="color: red!important;">{{ session('message') }}</h1>
                                    @endif
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li ><a href="user-dashboard.html">Dashboard</a></li>
                                        <li class="active"><a >Purchase History</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- dashboard content -->
                       <div class="card no-border mt-4">
                            <div>
                                <table class="table table-sm table-hover table-responsive-md">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Delivery Detail</th>
                                            <th>Delivery Status</th>
                                            <th>Payment Status</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        @php

                                        $test = count($orderslist);
                                                                               @endphp
                                        @if($test>0)
                                        @foreach($orderslist as $orders)
                                        <tr>
                                            <td>
                                                <a href="{{URL('new/purchaseorderdetail/'.$orders->id)}}" >{{$orders->code}}</a>
                                            </td>
                                            <td>{{$orders->date}}</td>
                                            <td>₹{{$orders->grand_total}}  </td>

                                            <td>

                                                 @if($orders->is_fresh==1)
                                                    <h6 style="color:#4183c4">{{'Fresh'}}</h6>
                                                 @else   

                                                   <h6 style="color:#4183c4">{{'GROCERY'}}</h6>

                                                 @endif
                                                <span class="d-block">Type: <strong>{{$orders->deliveryType}}</strong></span>
                                                <!-- <span class="d-block">Date: <strong>08 Feb, 2022</strong></span>
                                                <span class="d-block">Schedule: <strong>09:00-10:00 AM</strong></span> -->
                                                <hr>
                                            </td>
                                            <td>
                                                <span  > {{$orders->order_status}}</span>
                                            </td>
                                            <td>
                                                <span class="badge badge--2 mr-4">
                                                     <i class="bg-red"></i> {{$orders->payment_status}}
                                                </span>
                                            </td>
                                            <td>
                                               <div class="dropdown">
                                                    <button class="btn" type="button" id="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="">
                                                       <!--  <button onclick="{{URL('new/purchaseorderdetail/'.$orders->id)}}" class="dropdown-item">Order Details</button> -->
                                                        <a href="{{URL('new/purchaseorderdetail/'.$orders->id)}}" class="dropdown-item">Order Details</a>
                                                        <a href="" class="dropdown-item">Download Invoice</a>
                                                        <a href="{{URL('new/cancel/order/'.$orders->id)}}" class="dropdown-item">Cancel</a>
                                                        <a href="{{URL('new/help/'.$orders->id)}}" class="dropdown-item">Need Help?</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr><td>
                                        {{'No Orders Found'}}
                                          </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                    </div>
                </div>
           </section>
            <div class="aiz-mobile-bottom-nav d-md-none fixed-bottom bg-white shadow-lg border-top">

                <div class="d-flex justify-content-around align-items-center">

                    <a href="index.html" class="text-reset flex-grow-1 text-center py-3 border-right bg-soft-primary">
                         <i class="fa fa-home la-2x"></i>
                   </a>

                    <a href="categories.html" class="text-reset flex-grow-1 text-center py-3 border-right ">
                        <span class="d-inline-block position-relative px-2">
                            <i class="fa fa-list-ul la-2x"></i>
                        </span>
                    </a>

                    <a href="cart.html" class="text-reset flex-grow-1 text-center py-3 border-right ">

                        <span class="d-inline-block position-relative px-2">

                            <i class="fa fa-shopping-cart la-2x"></i>
                                <span class="badge badge-circle badge-success position-absolute absolute-top-right" id="cart_items_sidenav">0</span>

                            
                        </span>

                     </a>


                    <a href="user-dashboard.html" class="text-reset flex-grow-1 text-center py-2">
                        <span class="avatar avatar-xs d-block mx-auto">
                            <img width="30px"  src="assets/images/user.png">
                        </span>

                     </a>


                </div>

            </div>

   

         

            <!-- order detail modal -->
          <div class="modal  show" id="order_details" tabindex="-1" role="dialog" aria-labelledby="   exampleModalLabel"  >
             <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-zoom product-modal"  role="document">
               <div class="modal-content position-relative">
                <div class="c-preloader d-none"  >
                    <i class="fa fa-spin fa-spinner"></i>
                </div>
                    <div class="modal-header">
                        <h5 class="modal-title strong-600 heading-5">Order id: ORD7777797752912</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                    </div>

                  <div class="modal-body gry-bg px-3 pt-0">
                    <div class="pt-4">
                        <ul class="process-steps clearfix">
                            <li class="active">
                                <div class="icon">1</div>
                                <div class="title">Order placed</div>
                            </li>
                            <li>
                                <div class="icon">2</div>
                                <div class="title">In Process</div>
                            </li>
                            <li>
                                <div class="icon">3</div>
                                <div class="title">On review</div>
                            </li>
                            <li>
                                <div class="icon">4</div>
                                <div class="title">On delivery</div>
                            </li>
                            <li>
                                <div class="icon">5</div>
                                <div class="title">Partially Delivered</div>
                            </li>
                            <li>
                                <div class="icon">6</div>
                                <div class="title">Delivered</div>
                            </li>
                        </ul>
                    </div>
                    <div class="card mt-4">
                        <div class="card-header py-2 px-3 heading-6 strong-600 clearfix">
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
                                            <td>mymail@gmail.com</td>
                                        </tr>
                                        <tr>
                                            <td class="w-50 strong-600">Shipping address:</td>
                                            <td>Sector 19, Indira Nagar, Lucknow, Uttar Pradesh 226016, India, Lucknow, 226016, India</td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                                <div class="col-lg-6">
                                    <table class="details-table table">
                                        <tbody><tr>
                                            <td class="w-50 strong-600">Order date:</td>
                                            <td>08-02-2022 12:09 PM</td>
                                        </tr>
                                        <tr>
                                            <td class="w-50 strong-600">Order status:</td>
                                            <td>Pending</td>
                                        </tr>
                                                                <tr>
                                            <td class="w-50 strong-600">Total order amount:</td>
                                            
                                            <td>₹1,170.00</td>
                                        </tr>
                                                                <tr>
                                            <td class="w-50 strong-600">Shipping method:</td>
                                            <td>Flat shipping rate</td>
                                        </tr>
                                                                <tr>
                                            <td class="w-50 strong-600">Payment method:</td>
                                            <td>Paytm</td>
                                        </tr>
                                    </tbody></table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-9">
                            <div class="card mt-4">
                                <div class="card-header py-2 px-3 heading-6 strong-600">Order Details</div>
                                <div class="card-body pb-0">
                                    <table class="details-table table table-responsive">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th width="30%">Product</th>
                                                <th>Variation</th>
                                                <th>Quantity</th>
                                                 <th>Price</th>
                                                 <th>Order Status</th>
                                                 <th>Replacement</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                               <tr>
                                                    <td>1</td>
                                                    <td>
                                                        <a href="#" target="_blank">Fortune Soya Health Refined Soyabean Oil - 5L</a>
                                                    </td>
                                                    <td>
                                                        5L
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>₹980.00</td>
                                                    <td>Pending</td>
                                                </tr>
                                                <tr>
                                                    <td>2</td>
                                                    <td>
                                                        <a href="#" target="_blank">Organic Tattva Chana Dal</a>
                                                    </td>
                                                    <td>
                                                        1Kg
                                                    </td>
                                                    <td>
                                                        1
                                                    </td>
                                                    <td>₹190.00</td>
                                                     <td>Pending</td>
                                                 </tr>
                                                  
                                            </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card mt-4">
                                <div class="card-header py-2 px-3 heading-6 strong-600">Order Amount</div>
                                <div class="card-body pb-0">
                                    <table class="table details-table">
                                        <tbody>
                                            <tr>
                                                <th>Total Value</th>
                                                <td class="text-right">
                                                    <span class="strong-600">₹1,170.00</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Delivery Charges</th>
                                                <td class="text-right">
                                                    <span class="text-italic">₹0.00</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Total Tax</th>
                                                <td class="text-right">
                                                    <span class="text-italic">₹55.72</span>
                                                </td>
                                            </tr>
                                             <tr>
                                                <th><span class="strong-600">Grand Total</span></th>
                                                <td class="text-right">
                                                    <strong><span>₹1,170.00</span></strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Amount Paid from Wallet :</th>
                                                <td class="text-right">
                                                    <span class="text-italic">₹0.00</span>
                                                </td>
                                            </tr>
                                             <tr><th></th><td class="text-right">Inclusive of all Taxes</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                      </div>
                    </div>
                </div>
             </div>
          </div>
       </div> 
 

@endsection