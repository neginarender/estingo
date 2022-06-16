
@extends('frontend_new.layouts.app')
@section('content')


        <!-- main content start -->
       <section class="gry-bg py-4 profile">
          <div class="container">
            <div class="row cols-xs-space cols-sm-space cols-md-space">
                <div class="col-lg-3 d-none d-lg-block">
                    <div class="sidebar sidebar--style-3 no-border stickyfill p-0">
                      <div class="widget mb-0">
                        <div class="widget-profile-box text-center p-3">
                            <img src="assets/images/user.png" class="image rounded-circle">
                            <div class="name">Anuj Pathak</div>
                       </div>

                        <div class="sidebar-widget-title py-3">
                            <span>Menu</span>
                        </div>
                        <div class="widget-profile-menu py-3">
                            <ul class="categories categories--style-3">
                                <li>
                                    <a href="user-dashboard.html" >
                                        <i class="fa fa-tachometer"></i>
                                        <span class="category-name">
                                            Dashboard
                                        </span>
                                    </a>
                                </li>
                                 <li>
                                    <a href="order-history.html" >
                                        <i class="fa fa-file-text"></i>
                                        <span class="category-name">
                                            Purchase History 
                                        </span>
                                    </a>
                                </li>
                                 <li>
                                    <a href="future-order.html"  >
                                        <i class="fa fa-file-text"></i>
                                        <span class="category-name">
                                            Future Orders 
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="wishlist.html">
                                        <i class="fa fa-heart "></i>
                                        <span class="category-name">
                                            Wishlist
                                        </span>
                                    </a>
                                </li>
                                 <li>
                                    <a href="conversations.html" >
                                        <i class="fa fa-comment"></i>
                                        <span class="category-name">
                                            Conversations
                                        </span>
                                    </a>
                                 </li>
                                 <li>
                                    <a href="profile.html" >
                                        <i class="fa fa-user"></i>
                                        <span class="category-name">
                                            Manage Profile
                                        </span>
                                    </a>
                                </li>
                                <li>
                                    <a href="redeem-gift-card.html"  >
                                        <i class="fa fa-user"></i>
                                        <span class="category-name">
                                            Redeem a gift card
                                        </span>
                                    </a>
                                </li>
                                  <li>
                                    <a href="wallet.html"  class="active">
                                        <i class="fa fa-inr"></i>
                                        <span class="category-name">
                                            My Wallet
                                        </span>
                                    </a>
                                  </li>
                                  <li>
                                    <a href="support-ticket.html"  >
                                        <i class="fa fa-support"></i>
                                        <span class="category-name">
                                            Support Ticket 
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                 </div>
                </div>
                <div class="col-lg-9">
                    <!-- Page title -->
                    <div class="page-title">
                        <div class="row align-items-center">
                            <div class="col-md-6 col-12">
                                <h2 class="heading heading-6 text-capitalize strong-600 mb-0">
                                    {{ translate('My Wallet')}}
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="{{ route('new.home') }}">{{ translate('Home')}}</a></li>
                                        <li ><a href="#">{{ translate('Dashboard')}}</a></li>
                                         <li class="active"><a >{{ translate('My Wallet')}}</a></li>
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
                                    <span class="d-block title heading-3 strong-400">₹ {{$balance->balance}}</span>
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
                           <button class="btn btn-rounded btn-info   float-md-right ml-0 ml-md-3">Total Order Amount: {{$balance->totalOrderAmount}}</button>
                           <button class="btn btn-rounded btn-info float-md-right"  >Total Peer Commission: {{$balance->peerCommission}} </button>
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
                                            <th>Refferal Code</th>
                                            <th>Customer Name</th>
                                            <th>Order ID</th>
                                            <th>Total Order Amount</th>
                                            <th>Total Peer Commission</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                         <tr>
                                            <td class="text-center pt-5 h4" colspan="100%">
                                                <i class="fa fa-meh-o d-block heading-1 alpha-5"></i>
                                                <span class="d-block">No history found.</span>
                                            </td>
                                        </tr>
                                     </tbody>
                                </table>
                            </div>

                        </div>


                        
                    </div>
                </div>
           </section>


@endsection
           