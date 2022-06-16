
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
                                    Dashboard
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li class="active"><a href="user-dashboard.html">Dashboard</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- dashboard content -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="dashboard-widget text-center green-widget mt-4 c-pointer">
                                    <a href="javascript:;" class="d-block">
                                        <i class="fa fa-shopping-cart"></i>
                                        <span class="d-block title">2 Product(s)</span>
                                        <span class="d-block sub-title">In your cart</span>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-widget text-center red-widget mt-4 c-pointer">
                                    <a href="javascript:;" class="d-block">
                                        <i class="fa fa-heart"></i>
                                        <span class="d-block title">0 Product(s)</span>
                                        <span class="d-block sub-title">In your wishlist</span>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="dashboard-widget text-center yellow-widget mt-4 c-pointer">
                                    <a href="javascript:;" class="d-block">
                                        <i class="fa fa-building"></i>
                                         <span class="d-block title">0 Product(s)</span>
                                        <span class="d-block sub-title">You ordered</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-box bg-white mt-4">
                                    <div class="form-box-title px-3 py-2 clearfix ">
                                        Default Shipping Address
                                        <div class="float-right">
                                            <a href="{{route('phoneapi.profile')}}" class="btn btn-link btn-sm">Edit</a>
                                        </div>
                                    </div>
                                    <div class="form-box-content p-3">
                                        <table>
                                            <tbody>
                                                <tr>
                                                  <td>Address:</td>
                                                   <td class="p-2">{{$addres->address}}</td>
                                                    </tr>
                                               <tr>
                                                <tr>
                                                    <td>Country:</td>
                                                    <td class="p-2">
                                                        India
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>City:</td>
                                                    <td class="p-2">{{$addres->city}}</td>
                                                </tr>
                                                  <tr>
                                                    <td>Postal Code:</td>
                                                    <td class="p-2">{{$addres->postal_code}}</td>
                                                </tr>
                                                <tr>
                                                    <td>Phone:</td>
                                                    <td class="p-2">{{$addres->phone}}</td>
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
            </div>
        </section>



@endsection

