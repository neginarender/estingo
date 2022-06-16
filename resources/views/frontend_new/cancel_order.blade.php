
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
                                    Cancel Order
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li ><a href="user-dashboard.html">Dashboard</a></li>
                                        <li  ><a href="order-history.html" >Purchase History</a></li>
                                         <li class="active"><a >Cancel Order</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  content -->
                        <form class="" action="{{URL('new/cancelorder')}}"  method="POST" >
                            <div class="form-box bg-white mt-4">
                                <div class="form-box-content p-4">

                                      @if(session()->has('message'))
                                     <h1 class="heading heading-4 strong-600" style="color: red!important;">{{ session('message') }}</h1>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Order No. <span class="required-star">*</span></label>
                                        </div>
                                       <input type="hidden" name="_token" value="{{csrf_token()}}" />

                                        <div class="col-md-10">
                                            <input type="hidden" name="order_id" value="{{$orderd->order_id}}">
                                             @error('order_no')
                                                        <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                            <input type="text" class="form-control mb-3" name="order_no" placeholder="Order No." value="{{$orderd->code}}" readonly="">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>Order Amount <span class="required-star">*</span></label>
                                        </div>
                                         <div class="col-md-10">
                                             @error('amount')
                                                        <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                            <input type="number" class="form-control mb-3" name="amount" placeholder="Amount" value="{{$orderd->grand_total}}" readonly="">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">

                                            <label>Cancel Reason <span class="required-star">*</span></label>
                                        </div>
                                        <div class="col-md-10">
                                             @error('reason')
                                                        <div class="errorc">{{ $message }}</div>
                                                     @enderror
                                            <textarea class="form-control mb-3" name="reason" placeholder="Cancel Reason"></textarea>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="form-box mt-4 text-right">
                                <button type="submit" class="btn btn-styled btn-base-1">Cancel Order</button>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
           </section>
        
 

@endsection