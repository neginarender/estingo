
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
                                    My Wallet
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li ><a href="user-dashboard.html">Dashboard</a></li>
                                         <li class="active"><a >My Wallet</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  content -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-widget text-center green-widget text-white mt-4 c-pointer">
                                    <i class="fa fa-inr"></i>
                                    <span class="d-block title heading-3 strong-400">₹{{$wallet->balance}}.00</span>
                                    <span class="d-block sub-title">Wallet Balance</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-widget text-center plus-widget mt-4 c-pointer" onclick="show_wallet_modal()">
                                    <i class="fa fa-plus"></i>
                                    <span class="d-block title heading-6 strong-400 c-base-1">Recharge Wallet</span>
                                </div>
                            </div>

                            
                        </div>

                        <div class=" wallet-btns pt-3">
                           <button class="btn btn-rounded btn-info   float-md-right ml-0 ml-md-3">Total Order Amount:{{$wallet->balance}} </button>
                           <button class="btn btn-rounded btn-info float-md-right"  >Total Peer Commission: {{$wallet->orderCommission}} </button>
                        </div>

                        <div class="card no-border mt-5 w-100">
                            <div class="card-header py-3">
                                <h4 class="mb-0 h6">Peer History</h4>
                            </div>

                            <div class="panel-heading bord-btm clearfix p-4 h-100">       
                                <div class="pull-left clearfix"  >
                                     <form method="post" action="">
                                        <div class="row">
                                          <div class="box-inline pad-rgt  col-md-5">
                                               <label for="users-list-role">Start Date</label>
                                                <div class="" style="">
                                                    <input type="date" class="form-control w-100" id="start_date" name="start_date"  value="2022-02-01">
                                                </div>
                                          </div>
                                          <div class="box-inline pad-rgt  col-md-5 pt-3 pt-md-0"  >
                                               <label for="users-list-role">End Date</label>
                                                <div class="" style="">
                                                   <input type="date" class="form-control w-100" id="end_date" name="end_date"   value="2022-02-08">
                                                </div>
                                          </div>
                                           <div class="box-inline pad-rgt  p-4 mt-2 col-md-2"  >
                                              <button class="btn btn-primary"   type="submit">Filter</button>
                                          </div>
                                      </div>
                                      
                                   </form>
                                </div>
                            </div>

                            <div class="card-body pt-0"  >
                                <table class="table table-sm table-responsive-md mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                           
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i=1; @endphp
                                        @foreach($history as $histor)
                                        <tr>
                                            <td>{{$i++}}</td>
                                            <td>{{$histor->amount}}</td>
                                            <td>{{$histor->created_at}}</td>
                                            <td>{{$histor->payment_method}}</td>
                                           
                                          
                                        </tr>
                                        @endforeach
                                       <!--   <tr>
                                            <td class="text-center pt-5 h4" colspan="100%">
                                                <i class="fa fa-meh-o d-block heading-1 alpha-5"></i>
                                                <span class="d-block">No history found.</span>
                                            </td>
                                        </tr> -->
                                     </tbody>
                                </table>
                            </div>

                        </div>


                        
                    </div>
                </div>
            </div>
             <div class="modal fade" id="wallet_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-zoom product-modal" id="modal-size" role="document">
                    <div class="modal-content position-relative">
                        <div class="modal-header">
                            <h5 class="modal-title strong-600 heading-5">Recharge Wallet</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="" action="{{route('phoneapi.walletrecharge')}}" method="POST">
                           <input type="hidden" name="_token" value="{{csrf_token()}}" />
                           <input type="hidden" name="payment_type" value="wallet_payment" />

                           <div class="modal-body gry-bg px-3 pt-3">
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Amount <span class="required-star">*</span></label>
                                    </div>
                                    <div class="col-md-10">
                                        <input type="number" class="form-control mb-3" name="amount" placeholder="Amount" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <label>Payment Method</label>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="mb-3">
                                            <select class="form-control selectpicker" data-minimum-results-for-search="Infinity" name="payment_option">
                                                        <option value='razorpay'>Razorpay</option>

                                            </select>

                                         </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-base-1">Confirm</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div> 
           </section>
        
 

@endsection