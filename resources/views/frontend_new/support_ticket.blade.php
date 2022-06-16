
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
                                    Support Ticket
                                </h2>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="float-md-right">
                                    <ul class="breadcrumb">
                                        <li><a href="index.html">Home</a></li>
                                        <li ><a href="user-dashboard.html">Dashboard</a></li>
                                         <li class="active"><a >Support Ticket</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!--  content -->
                     <div class="row">
                        <div class="col-md-4 offset-md-4">
                            <div class="dashboard-widget text-center plus-widget mt-4 c-pointer" data-toggle="modal" data-target="#ticket_modal">
                                <i class="fa fa-4x">+</i>
                                <span class="d-block title heading-6 strong-400 c-base-1">Create a Ticket</span>
                            </div>
                        </div>
                    </div>
                    <div class="card no-border mt-4">
                        <table class="table table-sm table-hover table-responsive-md">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Sending Date</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Options</th>
                                </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>#2147483647</td>
                                <td>2022-02-08 09:58:38</td>
                                <td>aaa</td>
                                <td>
                                     <span class="badge badge-pill badge-danger">Pending</span>
                                </td>
                                 <td>
                                    <a href="support-ticket-detail.html" class="btn btn-styled btn-link py-1 px-0 icon-anim text-underline--none">
                                        View Details
                                        <i class="fa fa-angle-right text-sm"></i>
                                    </a>
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