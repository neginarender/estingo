
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
                                        Future Purchase History 
                                        <span class="d-block text-info mt-1"> Wallet Balance: <span class="text-dark">₹50</span> </span>
                                    </h2>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="float-md-right">
                                        <ul class="breadcrumb">
                                            <li><a href="index.html">Home</a></li>
                                            <li ><a href="user-dashboard.html">Dashboard</a></li>
                                             <li class="active"><a >Future Purchase History</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--  content -->
                          <div class="card no-border mt-0 mt-md-4">
                                <table class="table table-sm future-order table-hover table-responsive-md">
                                    <thead>
                                        <tr>
                                            <th>Sr.No.</th>
                                            <th>Product</th>
                                            <th>Start Date</th>
                                            <th>Amount</th>
                                            <th>Quantity</th>
                                            <th>Total Amount</th>
                                            <th>Sorting Hub</th>
                                            <th>Order Created</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                      <tr>
                                        <td class="strong-600">1</td>
                                        <td>
                                            <a href="" target="_blank" class="media-block">
                                              <img   class="img-md  " src="assets/images/products/1.jpg" alt=""  >
                                              <div class="media-body">Refined Oil</div>
                                              <div class="text-info">ROZ02757</div>
                                           </a>
                                        </td>
                                        <td>02-02-2022</td>
                                        <td>10.06</td>
                                        <td>1</td>
                                        <td>10.06</td>
                                        <td>Gomti Nagar Sorting Hub<br>226002</td>
                                        <td>02-02-2022 11:47:11</td>
                                        <td>
                                            <label class="switch">
                                            <input  value="10" onchange="error()" type="checkbox" checked="">
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    </tr>
                                    <tr>
                                     <td class="strong-600">2</td>
                                        <td>
                                            <a href="" target="_blank" class="media-block">
                                            <div class="media-left">
                                               <img   class="img-md  " src="assets/images/products/2.jpg" alt=""  >
                                            </div>
                                            <div class="media-body">Chana Daal</div>
                                            <div class="text-info">ROZ07460</div>
                                        </a>
                                        </td>
                                        <td>04-02-2022</td>
                                        <td>40.16</td>
                                        <td>2</td>
                                        <td>80.32</td>
                                        <td>Gomti Nagar Sorting Hub<br>226002</td>
                                        <td>02-02-2022 06:28:51</td>
                                        <td>
                                            <label class="switch">
                                            <input onchange="updated()" value="7" type="checkbox">
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    </tr>
                                   </tbody>
                              </table>
                           
                        </div>


                </div>
            </div>
           </section>
        
 

@endsection